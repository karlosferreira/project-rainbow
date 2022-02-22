<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Quiz\QuizForm;
use Apps\Core_MobileApi\Api\Form\Quiz\QuizSearchForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\QuizResource;
use Apps\Core_MobileApi\Api\Resource\QuizResultResource;
use Apps\Core_MobileApi\Api\Resource\QuizUserResultResource;
use Apps\Core_MobileApi\Api\Security\Quiz\QuizAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Quizzes\Service\Process;
use Apps\Core_Quizzes\Service\Quiz;
use Phpfox;

class QuizApi extends AbstractResourceApi implements ActivityFeedInterface, MobileAppSettingInterface
{
    /**
     * @var Quiz
     */
    protected $quizService;

    /**
     * @var Process
     */
    protected $processService;

    /**
     * @var \User_Service_User
     */
    protected $userService;

    /**
     * @var \Apps\Core_BetterAds\Service\Process
     */
    protected $adProcessService = null;

    public function __construct()
    {
        parent::__construct();
        $this->quizService = Phpfox::getService('quiz');
        $this->processService = Phpfox::getService('quiz.process');
        $this->userService = Phpfox::getService('user');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'quiz/search-form' => [
                'get' => 'searchForm'
            ],
        ];
    }

    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(QuizAccessControl::VIEW);
        $params = $this->resolver->setDefined([
            'view', 'q', 'sort', 'profile_id', 'limit', 'page', 'when'
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'friend', 'pending', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $sort = $params['sort'];
        $view = $params['view'];
        $isProfile = $params['profile_id'];
        $user = [];

        if (in_array($view, ['feature', 'sponsor'])) {
            $function = 'find' . ucfirst($view);
            return $this->success($this->{$function}($params));
        }

        if ($isProfile) {
            $user = $this->userService->get($isProfile);
            if (empty($user)) {
                return $this->notFoundError();
            }
        }
        $this->search()->setBIsIgnoredBlocked(true);
        $browseParams = [
            'module_id' => 'quiz',
            'alias'     => 'q',
            'field'     => 'quiz_id',
            'table'     => Phpfox::getT('quiz'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'quiz.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'q'
        ]);
        switch ($view) {
            case 'my':
                if (Phpfox::isUser()) {
                    $this->search()->setCondition('AND q.user_id = ' . (int)Phpfox::getUserId());
                } else {
                    return $this->permissionError();
                }
                break;
            case 'pending':
                if (Phpfox::isUser() && Phpfox::getUserParam('quiz.can_approve_quizzes')) {
                    $this->search()->setCondition('AND q.view_id = 1');
                } else {
                    return $this->permissionError();
                }
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition('AND q.view_id IN(' . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0') . ') AND q.user_id = ' . (int)$user['user_id'] . ' AND  q.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($user)) . ')');
                } else {
                    $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%)');
                }
                break;
        }

        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'q.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'q.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'q.total_comment DESC';
                break;
            default:
                $sort = 'q.time_stamp DESC';
                break;
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND q.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }

        $this->search()->setSort($sort)->setLimit($params['limit'])->setPage($params['page']);

        $this->browse()->changeParentView($params['module_id'], $params['item_id'])->params($browseParams)->execute();

        $items = $this->browse()->getRows();
        //Reset key
        $items = array_values($items);

        $this->processRows($items);
        return $this->success($items);
    }

    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $item = $this->quizService->getQuizByUrl($id, false, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(QuizAccessControl::VIEW, QuizResource::populate($item));
        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$item['is_viewed'] && !Phpfox::getUserBy('is_invisible')) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('quiz', $item['quiz_id']);
            } else if ($item['is_viewed'] && !Phpfox::getUserBy('is_invisible')) {
                if (!Phpfox::getParam('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('quiz', $item['quiz_id']);
                } else {
                    Phpfox::getService('track.process')->update('quiz', $item['quiz_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }
        if ($bUpdateCounter) {
            $this->processService->updateCounter($item['quiz_id']);
        }
        $item['is_user_played'] = $playQuiz = $this->quizService->hasTakenQuiz($this->getUser()->getId(), $item['quiz_id']);
        $item['is_detail'] = true;
        if ($playQuiz) {
            $item['results'] = $this->getQuizResultApiService()->getByUser($item, $this->getUser()->getId());
        }
        /** @var QuizResource $resource */
        $resource = $this->populateResource(QuizResource::class, $item);
        $this->setHyperlinks($resource, true);

        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->lazyLoad(['user'])
            ->loadFeedParam()
            ->toArray());
    }

    /**
     * @return QuizResultApi
     */
    private function getQuizResultApiService()
    {
        return Phpfox::getService("mobile.quiz_result_api");
    }

    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        /** @var QuizForm $form */
        $form = $this->createForm(QuizForm::class, [
            'title'  => 'add_new_quiz',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('quiz')
        ]);

        $quiz = $this->loadResourceById($editId, true);
        if ($editId && empty($quiz)) {
            return $this->notFoundError();
        }
        if ($quiz) {
            $this->denyAccessUnlessGranted(QuizAccessControl::EDIT, $quiz);
            $form->setTitle('editing_quiz')
                ->setAction(UrlUtility::makeApiUrl('quiz/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($quiz);
        } else {
            $this->denyAccessUnlessGranted(QuizAccessControl::ADD);
        }
        return $this->success($form->getFormStructure());
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(QuizAccessControl::ADD);
        /** @var QuizForm $form */
        $form = $this->createForm(QuizForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => QuizResource::populate([])->getResourceName()
                ], [], $this->localization->translate('quiz_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    protected function processCreate($values)
    {
        $this->convertSubmitForm($values);
        return $this->processService->add($values, $this->getUser()->getId());
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var QuizForm $form */
        $form = $this->createForm(QuizForm::class);
        $quiz = $this->loadResourceById($id, true);
        if (empty($quiz)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(QuizAccessControl::EDIT, $quiz);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => QuizResource::populate([])->getResourceName()
                ], [], $this->localization->translate('quiz_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    protected function processUpdate($id, $values)
    {
        $this->convertSubmitForm($values, true);
        $values['quiz_id'] = $id;
        list($id,) = $this->processService->update($values, $this->getUser()->getId());

        return $id;
    }

    /**
     * @param      $values
     * @param bool $edit
     */
    protected function convertSubmitForm(&$values, $edit = false)
    {
        $values['description'] = '';
        if (!empty($values['text'])) {
            $values['description'] = $values['text'];
        }
        if (!empty($values['file'])) {
            if (!$edit) {
                $values['temp_file'] = $values['file']['temp_file'];
            } else {
                if ($values['file']['status'] == FileType::NEW_UPLOAD || $values['file']['status'] == FileType::CHANGE) {
                    $values['temp_file'] = $values['file']['temp_file'];
                } else if ($values['file']['status'] == FileType::REMOVE) {
                    $values['remove_photo'] = 1;
                }
            }
        }
        //Convert question to form
        $data = [];
        foreach ($values['questions'] as $key => $question) {
            $data[$key] = [
                'question' => $question['question']
            ];
            foreach ($question['answers'] as $datum) {
                $data[$key]['answers'][] = [
                    'is_correct'  => (int)$datum['is_correct'] > 0 ? 1 : 0,
                    'answer_id'   => $datum['answer_id'],
                    'question_id' => $question['question_id'],
                    'answer'      => $datum['answer']
                ];
            }
        }
        unset($values['questions']);
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        $values['q'] = $data;
    }

    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    function delete($params)
    {
        $itemId = $this->resolver->resolveId($params);
        $item = $this->loadResourceById($itemId);
        if (!$item) {
            return $this->notFoundError();
        }

        if (Phpfox::getUserParam('quiz.can_access_quiz') && $this->processService->deleteQuiz($itemId, Phpfox::getUserId()) == true) {
            return $this->success([], [], $this->getLocalization()->translate('quiz_successfully_deleted'));
        }

        return $this->permissionError();
    }

    function loadResourceById($id, $returnResource = false, $detail = false)
    {
        $item = $returnResource || $detail ? $this->getQuiz($id) : $this->quizService->getQuizByUrl($id);
        if (empty($item['quiz_id'])) {
            return null;
        }
        $item['is_user_played'] = $this->quizService->hasTakenQuiz($this->getUser()->getId(), $item['quiz_id']);
        if ($returnResource) {
            $item['is_detail'] = true;
            if (isset($item['questions'])) {
                $item['question'] = $item['questions'];
            }
            return QuizResource::populate($item);
        }
        return $item;
    }

    /**
     * Fetches a quiz ready to be edited
     *
     * @param integer $iQuiz The quiz identifier
     *
     * @return array
     */
    private function getQuiz($iQuiz)
    {
        $aQuiz = $this->database()->select('q.*, qq.question_id, qq.question, u.user_name')
            ->from(':quiz', 'q')
            ->join(':quiz_question', 'qq', 'q.quiz_id = qq.quiz_id')
            ->join(':user', 'u', 'q.user_id = u.user_id')//useful to forward after the edit
            ->order('qq.question_id ASC')
            ->where('q.quiz_id = ' . (int)$iQuiz)
            ->execute('getSlaveRows');
        if (empty($aQuiz)) {
            return [];
        }

        if (!empty($aQuiz[0]['image_path'])) {
            $aQuiz[0]['current_image'] = Phpfox::getLib('image.helper')->display([
                'server_id'  => $aQuiz[0]['server_id'],
                'path'       => 'quiz.url_image',
                'file'       => $aQuiz[0]['image_path'],
                'suffix'     => '',
                'return_url' => true
            ]);
        }
        // now get the answers
        $sQuestions = '';
        foreach ($aQuiz as $aQuestion) {
            $sQuestions .= 'OR qa.question_id = ' . $aQuestion['question_id'] . ' ';
        }
        $sQuestions = substr($sQuestions, 3);
        $aAnswers = $this->database()->select('qa.answer_id, qa.answer, qa.is_correct, qa.question_id')
            ->from(':quiz_answer', 'qa')
            ->order('qa.answer_id ASC')
            ->where($sQuestions)
            ->execute('getSlaveRows');

        // glue them
        foreach ($aAnswers as $aAnswer) {
            foreach ($aQuiz as $aKey => $aQuestions) {
                if ($aQuestions['question_id'] == $aAnswer['question_id']) {
                    $aQuiz[$aKey]['answers'][] = $aAnswer;
                }
            }
        }
        $aFull = $aQuiz[0];
        $aFull['questions'] = $aQuiz;

        return $aFull;
    }

    public function processRow($item)
    {
        /** @var QuizResource $resource */
        $resource = $this->populateResource(QuizResource::class, $item);
        $this->setHyperlinks($resource);

        $shortFields = [];
        $view = $this->request()->get('view');
        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'title', 'image', 'statistic', 'user', 'id', 'is_sponsor', 'is_featured'
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
            }
        }
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray($shortFields);
    }

    /**
     * Get for display on activity feed
     *
     * @param array $feed
     * @param array $item detail data from database
     *
     * @return array
     */
    function getFeedDisplay($feed, $item)
    {
        if (empty($item) && !$item = $this->loadResourceById($feed['item_id'])) {
            return null;
        }
        $resource = $this->populateResource(QuizResource::class, $item);

        return $resource->getFeedDisplay();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new QuizAccessControl($this->getSetting(), $this->getUser());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(QuizAccessControl::VIEW);
        /** @var QuizSearchForm $form */
        $form = $this->createForm(QuizSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('quiz')
        ]);

        return $this->success($form->getFormStructure());
    }

    private function setHyperlinks(QuizResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            QuizAccessControl::VIEW   => $this->createHyperMediaLink(QuizAccessControl::VIEW, $resource,
                HyperLink::GET, 'quiz/:id', ['id' => $resource->getId()]),
            QuizAccessControl::EDIT   => $this->createHyperMediaLink(QuizAccessControl::EDIT, $resource,
                HyperLink::GET, 'quiz/form/:id', ['id' => $resource->getId()]),
            QuizAccessControl::DELETE => $this->createHyperMediaLink(QuizAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'quiz/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(QuizAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'quiz']),
                'comments' => $this->createHyperMediaLink(QuizAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'quiz']),
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', QuizResource::RESOURCE_NAME);
        $module = 'quiz';
        return [
            [
                'path'      => 'quiz/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'quiz/add',
                'routeName' => ROUTE_MODULE_ADD,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'quiz(/*)',
                'routeName' => ROUTE_MODULE_HOME,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('quiz', [
            'title'           => $l->translate('quizzes'),
            'home_view'       => 'menu',
            'main_resource'   => new QuizResource([]),
            'other_resources' => [
                new QuizResultResource([]),
                new QuizUserResultResource([])
            ]
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $resourceName = (new QuizResource([]))->getResourceName();
        $headerButtons[$resourceName] = [

        ];
        if ($this->getAccessControl()->isGranted(QuizAccessControl::ADD)) {
            $headerButtons[$resourceName][] = [
                'icon'   => 'plus',
                'action' => Screen::ACTION_ADD,
                'params' => ['resource_name' => $resourceName]
            ];
        }
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var QuizResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(QuizAccessControl::APPROVE, $item);
        if ($this->processService->approveQuiz($id)) {
            $item = $this->loadResourceById($id, true);
            $permission = $this->getAccessControl()->getPermissions($item);
            return $this->success(array_merge($permission, ['is_pending' => false]), [], $this->getLocalization()->translate('quiz_has_been_approved'));
        }
        return $this->error();
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver->resolveSingle($params, 'feature', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(QuizAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('quiz_successfully_featured') : $this->getLocalization()->translate('quiz_successfully_un_featured'));
        }
        return $this->error();
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $isSponsorFeed = $this->resolver->resolveSingle($params, 'is_sponsor_feed', null, [], 0);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($isSponsorFeed) {
            //Support un-sponsor in feed
            $this->denyAccessUnlessGranted(QuizAccessControl::SPONSOR_IN_FEED, $item);
            $sponsorId = Phpfox::getService('feed')->canSponsoredInFeed('quiz', $id);
            if ($sponsorId !== true && Phpfox::getService('ad.process')->deleteSponsor($sponsorId, true)) {
                return $this->success([
                    'is_sponsored_feed' => false
                ], [], $this->getLocalization()->translate('better_ads_this_item_in_feed_has_been_unsponsored_successfully'));
            }
        } else {
            if (!$this->getAccessControl()->isGranted(QuizAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(QuizAccessControl::PURCHASE_SPONSOR, $item)) {
                return $this->permissionError();
            }
            if ($this->processService->sponsor($id, $sponsor)) {
                if ($sponsor == 1) {
                    $sModule = $this->getLocalization()->translate('quizzes');
                    Phpfox::getService('ad.process')->addSponsor([
                        'module' => 'quiz',
                        'item_id' => $id,
                        'name' => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                    ], false);
                } else {
                    Phpfox::getService('ad.process')->deleteAdminSponsor('quiz', $id);
                }
                return $this->success([
                    'is_sponsor' => !!$sponsor
                ], [], $sponsor ? $this->getLocalization()->translate('quiz_successfully_sponsored') : $this->getLocalization()->translate('quiz_successfully_un_sponsored'));
            }
        }
        return $this->permissionError();
    }

    /**
     * @param $params
     *
     * @return array|int|string
     */
    protected function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->quizService->getSponsored($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->updateViewCount($sponsoredItems);
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    /**
     * Update view count for sponsored items
     *
     * @param $sponsorItems
     */
    private function updateViewCount($sponsorItems)
    {
        if (!empty($this->adProcessService) && method_exists($this->adProcessService, 'addSponsorViewsCount')) {
            foreach ($sponsorItems as $sponsorItem) {
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'quiz');
            }
        }
    }

    /**
     * @param $params
     *
     * @return array|int|string
     */
    protected function findFeature($params)
    {
        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $featuredItems = $this->quizService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('quiz', []);
        $resourceName = QuizResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => ['component' => 'item_header'],
            ScreenSetting::LOCATION_BOTTOM => ['component' => 'item_like_bar'],
            ScreenSetting::LOCATION_MAIN   => [
                'component'       => 'item_simple_detail',
                'embedComponents' => [
                    [
                        'component'    => 'item_image',
                        'imageDefault' => false
                    ],
                    'item_title',
                    'item_author',
                    [
                        'component' => 'item_stats',
                        'stats'     => ['play' => 'total_play', 'view' => 'total_view']
                    ],
                    'item_like_phrase',
                    'poll_close_time',
                    ['component' => 'item_pending', 'message' => 'this_quiz_is_awaiting_moderation'],
                    'item_html_content',
                    'item_separator',
                    'item_quiz_answers'
                ],
            ],
            'screen_title'                 => $l->translate('quiz') . ' > ' . $l->translate('quiz') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addSetting($resourceName, 'viewQuizTakenResult', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => $l->translate('quiz_results')
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                'module_name'   => 'quiz',
                'resource_name' => QuizResultResource::populate([])->getResourceName()
            ],
            'screen_title'                 => $l->translate('quiz') . ' > ' . $l->translate('quiz_results')
        ]);

        $screenSetting->addSetting($resourceName, 'viewQuizUserResult', [
            ScreenSetting::LOCATION_HEADER => ['component' => ScreenSetting::SIMPLE_HEADER],
            ScreenSetting::LOCATION_MAIN   => [
                'component'     => 'quiz_user_result_detail',
                'module_name'   => 'quiz',
                'resource_name' => QuizUserResultResource::populate([])->getResourceName()
            ],
            'screen_title'                 => $l->translate('quiz') . ' > ' . $l->translate('quiz_results_detail_of_user')
        ]);

        $screenSetting->addBlock($resourceName, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_quizzes'),
                'resource_name' => $resourceName,
                'module_name'   => 'quiz',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored'),
                'resource_name' => $resourceName,
                'module_name'   => 'quiz',
                'refresh_time'  => 3000, //secs
                'item_props'    => [
                    'click_ref' => '@view_sponsor_item',
                ],
                'query'         => ['view' => 'sponsor']
            ]
        ]);

        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'quiz.index',
            ScreenSetting::MODULE_LISTING => 'quiz.index',
            ScreenSetting::MODULE_DETAIL  => 'quiz.view'
        ];
    }

    /**
     * Moderation items
     *
     * @param $params
     *
     * @return array|bool|mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     */
    public function moderation($params)
    {
        $this->resolver
            ->setAllowedValues('action', [Screen::ACTION_APPROVE_ITEMS, Screen::ACTION_DELETE_ITEMS, Screen::ACTION_FEATURE_ITEMS, Screen::ACTION_REMOVE_FEATURE_ITEMS]);
        $action = $this->resolver->resolveSingle($params, 'action', 'string', [], '');
        $ids = $this->resolver->resolveSingle($params, 'ids', 'array', [], []);
        if (!count($ids)) {
            return $this->missingParamsError(['ids']);
        }

        $data = [];
        $sMessage = '';
        switch ($action) {
            case Screen::ACTION_APPROVE_ITEMS:
                $this->denyAccessUnlessGranted(QuizAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->approveQuiz($id)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_pending' => false];
                $sMessage = $this->getLocalization()->translate('quiz_zes_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(QuizAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_featured' => !!$value];
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('quiz_zes_successfully_featured') : $this->getLocalization()->translate('quiz_zes_successfully_un_featured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(QuizAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->deleteQuiz($id, Phpfox::getUserId())) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('quiz_zes_successfully_deleted');
                break;
        }
        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}