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
use Apps\Core_MobileApi\Api\Form\Poll\PollForm;
use Apps\Core_MobileApi\Api\Form\Poll\PollSearchForm;
use Apps\Core_MobileApi\Api\Form\Type\FileType;
use Apps\Core_MobileApi\Api\Resource\ForumAnnouncementResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\PollAnswerResource;
use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\PollResultResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Poll\PollAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Polls\Service\Poll;
use Apps\Core_Polls\Service\Process;
use Phpfox;

class PollApi extends AbstractResourceApi implements ActivityFeedInterface, MobileAppSettingInterface
{
    /**
     * @var Poll
     */
    protected $pollService;

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
        $this->pollService = Phpfox::getService('poll');
        $this->processService = Phpfox::getService('poll.process');
        $this->userService = Phpfox::getService('user');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'poll/search-form' => [
                'get' => 'searchForm',
            ],
        ];
    }

    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(PollAccessControl::VIEW);
        $params = $this->resolver->setDefined([
            'view',
            'q',
            'sort',
            'profile_id',
            'limit',
            'page',
            'when',
        ])
            ->setAllowedValues('sort', ['latest', 'most_viewed', 'most_liked', 'most_discussed'])
            ->setAllowedValues('view', ['my', 'friend', 'pending', 'sponsor', 'feature'])
            ->setAllowedValues('when', ['all-time', 'today', 'this-week', 'this-month'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE,
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1,
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
            'module_id' => 'poll',
            'alias'     => 'poll',
            'field'     => 'poll_id',
            'table'     => Phpfox::getT('poll'),
            'hide_view' => ['pending', 'my'],
            'service'   => 'poll.browse',
        ];
        $this->search()->setSearchTool([
            'table_alias' => 'poll'
        ]);
        switch ($view) {
            case 'my':
                if (Phpfox::isUser()) {
                    $this->search()->setCondition('AND poll.user_id = ' . (int)Phpfox::getUserId());
                } else {
                    return $this->permissionError();
                }
                break;
            case 'pending':
                if (Phpfox::isUser() && Phpfox::getUserParam('poll.poll_can_moderate_polls')) {
                    $this->search()->setCondition('AND poll.view_id = 1');
                } else {
                    return $this->permissionError();
                }
                break;
            default:
                if ($isProfile) {
                    $this->search()->setCondition('AND poll.item_id = 0 AND poll.user_id = ' . (int)$user['user_id'] . ' AND poll.view_id IN(' . ($user['user_id'] == Phpfox::getUserId() ? '0,1' : '0')
                        . ') AND poll.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : \Phpfox::getService('core')->getForBrowse($user)) . ')');
                } else {
                    $this->search()->setCondition('AND poll.item_id = 0 AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%)');
                }
                $this->search()->setCondition('AND (poll.close_time = 0 OR poll.close_time > ' . PHPFOX_TIME . ')');
                break;
        }

        // sort
        switch ($sort) {
            case 'most_viewed':
                $sort = 'poll.total_view DESC';
                break;
            case 'most_liked':
                $sort = 'poll.total_like DESC';
                break;
            case 'most_discussed':
                $sort = 'poll.total_comment DESC';
                break;
            default:
                $sort = 'poll.time_stamp DESC';
                break;
        }
        // search
        if (!empty($params['q'])) {
            $this->search()->setCondition('AND poll.question LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
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
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        if (!Phpfox::getUserParam('poll.can_access_polls')) {
            return $this->permissionError();
        }
        $item = $this->pollService->getPollByUrl($params['id'], false, false, true, true);
        if (!$item || ($item['view_id'] == 1 && !Phpfox::getUserParam('poll.poll_can_moderate_polls') && $item['user_id'] != Phpfox::getUserId())) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PollAccessControl::VIEW, PollResource::populate($item));
        // Increment the view counter
        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$item['poll_is_viewed'] && !Phpfox::getUserBy('is_invisible')) {
                $updateCounter = true;
                Phpfox::getService('track.process')->add('poll', $item['poll_id']);
            } else if ($item['poll_is_viewed'] && !Phpfox::getUserBy('is_invisible')) {
                if (!setting('track.unique_viewers_counter')) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('poll', $item['poll_id']);
                } else {
                    Phpfox::getService('track.process')->update('poll', $item['poll_id']);
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            $this->processService->updateView($item['poll_id']);
        }
        $item['is_detail'] = true;
        /** @var PollResource $resource */
        $resource = $this->populateResource(PollResource::class, $item);
        $this->setHyperlinks($resource, true);

        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->loadFeedParam()
            ->toArray());
    }

    function form($params = [])
    {
        $editId = $this->resolver->resolveSingle($params, 'id');
        $isInThread = $this->resolver->resolveSingle($params, 'in_thread');
        /** @var PollForm $form */
        $form = $this->createForm(PollForm::class, [
            'title'  => 'adding_poll',
            'method' => 'POST',
            'action' => UrlUtility::makeApiUrl('poll'),
        ]);
        if (!empty($isInThread)) {
            $form->setModuleId('forum');
            $form->setItemId($editId);
            $editId = 0;
        }
        $poll = $this->loadResourceById($editId, true, true);
        if ($editId && empty($poll)) {
            return $this->notFoundError();
        }
        if ($poll) {
            $this->denyAccessUnlessGranted(PollAccessControl::EDIT, $poll);
            $form->setTitle('editing_poll')
                ->setAction(UrlUtility::makeApiUrl('poll/:id', $editId))
                ->setMethod('PUT');
            $form->assignValues($poll);
        } else {
            $this->denyAccessUnlessGranted(PollAccessControl::ADD);
            $iFlood = $this->getSetting()->getUserSetting('poll.poll_flood_control');
            if ($iFlood != '0') {
                $aFlood = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field'      => 'time_stamp', // The time stamp field
                        'table'      => Phpfox::getT('poll'), // Database table we plan to check
                        'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];
                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood)) {
                    // Set an error
                    return $this->error($this->getLocalization()->translate('poll_flood_control', ['x' => $iFlood]));
                }
            }
        }
        return $this->success($form->getFormStructure());
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(PollAccessControl::ADD);
        /** @var PollForm $form */
        $form = $this->createForm(PollForm::class);
        $iFlood = $this->getSetting()->getUserSetting('poll.poll_flood_control');
        if ($iFlood != '0') {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp', // The time stamp field
                    'table'      => Phpfox::getT('poll'), // Database table we plan to check
                    'condition'  => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ]
            ];
            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                // Set an error
                return $this->error($this->getLocalization()->translate('poll_flood_control', ['x' => $iFlood]));
            }
        }
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                if (isset($values['module_id'], $values['item_id']) && $values['module_id'] == 'forum' && $values['item_id'] > 0) {
                    $thread = $this->database()->select('thread_id, is_announcement')->from(':forum_thread')->where(['thread_id' => $values['item_id']])->execute('getRow');
                    if ($thread) {
                        return $this->success([
                            'id'            => $id,
                            'resource_name' => $thread['is_announcement'] ? ForumAnnouncementResource::populate([])->getResourceName() : ForumThreadResource::populate([])->getResourceName(),
                            'module_name'   => 'forum'
                        ], [], $this->localization->translate('poll_successfully_created'));
                    }
                }
                return $this->success([
                    'id'            => $id,
                    'resource_name' => PollResource::populate([])->getResourceName(),
                ], [], $this->localization->translate('poll_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($values)
    {
        $this->convertSubmitForm($values);
        list($id,) = $this->processService->add($this->getUser()->getId(), $values);
        //Update poll in forum
        if ($id && !empty($values['module_id']) && $values['module_id'] == 'forum' && !empty($values['item_id'])) {
            $this->database()->update(Phpfox::getT('poll'), ['item_id' => $values['item_id']],
                'poll_id = ' . (int)$id . ' AND user_id = ' . $this->getUser()->getId());
            $this->database()->update(Phpfox::getT('forum_thread'), ['poll_id' => $id],
                'thread_id = ' . (int)$values['item_id']);
        }
        return $id;
    }

    protected function convertSubmitForm(&$values, $edit = false)
    {
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
        $answersList = $values['answers'];
        $answers = [];
        //Sort array by order
        $sort = [];
        $iSort = 1;
        foreach ($answersList as $key => $row) {
            $sort[$key] = isset($row['order']) ? $row['order'] : $iSort;
            $iSort++;
        }
        array_multisort($sort, SORT_ASC, $answersList);
        $i = 0;
        foreach ($answersList as $answer) {
            $answers[$i]['answer'] = $answer['value'];
            if (!empty($answer['id'])) {
                $answers[$i]['answer_id'] = $answer['id'];
            }
            $i++;
        }
        $values['answer'] = $answers;
        $closeTime = !empty($values['close_time']) ? (new \DateTime($values['close_time'])) : null;
        if (empty($closeTime) && !empty($values['enable_close'])) {
            return $this->validationParamsError([
                $this->getLocalization()->translate('field_name_field_is_invalid', [
                    'field_name' => $this->getLocalization()->translate('close_time'),
                ]),
            ]);
        }
        if ($closeTime) {
            $values['close_month'] = $closeTime->format('m');
            $values['close_day'] = $closeTime->format('d');
            $values['close_year'] = $closeTime->format('Y');
            $values['close_hour'] = $closeTime->format('H');
            $values['close_minute'] = $closeTime->format('i');
        }
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        return true;
    }

    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var PollForm $form */
        $form = $this->createForm(PollForm::class);
        $poll = $this->loadResourceById($id, true, true);
        if (empty($poll)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PollAccessControl::EDIT, $poll);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => PollResource::populate([])->getResourceName(),
                ], [], $this->localization->translate('poll_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        $this->convertSubmitForm($values, true);
        $values['poll_id'] = $id;
        list($id,) = $this->processService->add($this->getUser()->getId(), $values, true);

        return $id;
    }

    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    function delete($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $itemId = $params['id'];
        $item = $this->loadResourceById($itemId);
        if (!$item) {
            return $this->notFoundError();
        }

        if (Phpfox::getUserParam('poll.can_access_polls')
            && Phpfox::getService('user.auth')->hasAccess('poll', 'poll_id', $itemId, 'poll.poll_can_delete_own_polls',
                'poll.poll_can_delete_others_polls')
            && $this->processService->moderatePoll($itemId, 2) !== false) {
            //Remove poll from thread
            if ($item['item_id'] > 0 && $item['module_id'] == 'forum') {
                $this->database()->update(Phpfox::getT('forum_thread'), ['poll_id' => '0'],
                    'thread_id = ' . (int)$item['item_id']);
            }
            return $this->success([], [], $this->getLocalization()->translate('poll_successfully_deleted'));
        }

        return $this->permissionError();
    }

    function loadResourceById($id, $returnResource = false, $forEdit = false)
    {
        $item = $this->pollService->getPollById($id);
        if (empty($item['poll_id'])) {
            return null;
        }
        if ($returnResource) {
            $item['is_detail'] = true;
            if ($forEdit) {
                $item['is_edit'] = true;
                if (isset($item['close_time']) && empty($item['close_time'])) {
                    unset($item['close_time']);
                }
            }
            return PollResource::populate($item);
        }
        return $item;
    }

    public function processRow($item)
    {
        /** @var PollResource $resource */
        $resource = $this->populateResource(PollResource::class, $item);
        $this->setHyperlinks($resource);
        $resource->setExtra($this->getAccessControl()->getPermissions($resource));

        $view = $this->request()->get('view');
        $shortFields = [];
        if (in_array($view, ['sponsor', 'feature'])) {
            $shortFields = [
                'resource_name', 'question', 'image', 'statistic', 'user', 'id', 'is_sponsor', 'is_featured'
            ];
            if ($view == 'sponsor') {
                $shortFields[] = 'sponsor_id';
            }
        }
        return $resource->displayShortFields()->toArray($shortFields);
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
        if (empty($item) && !$item = $this->loadResourceById(isset($feed['poll_id']) ? $feed['poll_id'] : (isset($feed['item_id']) ? $feed['item_id'] : 0))) {
            return null;
        }
        $resource = $this->populateResource(PollResource::class, $item);

        return $resource->getFeedDisplay();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl
            = new PollAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get("module_id");
        $itemId = $this->request()->get("item_id");
        if ($moduleId == 'forum') {
            return true;
        }
        if ($moduleId) {
            $context = AppContextFactory::create($moduleId, $itemId);
            if ($context === null) {
                return $this->notFoundError();
            }
            $this->accessControl->setAppContext($context);
        }
        return true;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function searchForm($params = [])
    {
        $this->denyAccessUnlessGranted(PollAccessControl::VIEW);
        /** @var PollSearchForm $form */
        $form = $this->createForm(PollSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('poll'),
        ]);

        return $this->success($form->getFormStructure());
    }

    private function setHyperlinks(PollResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            PollAccessControl::VIEW   => $this->createHyperMediaLink(PollAccessControl::VIEW, $resource,
                HyperLink::GET, 'poll/:id', ['id' => $resource->getId()]),
            PollAccessControl::EDIT   => $this->createHyperMediaLink(PollAccessControl::EDIT, $resource,
                HyperLink::GET, 'poll/form/:id', ['id' => $resource->getId()]),
            PollAccessControl::DELETE => $this->createHyperMediaLink(PollAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'poll/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes'    => $this->createHyperMediaLink(PollAccessControl::VIEW, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'poll']),
                'comments' => $this->createHyperMediaLink(PollAccessControl::VIEW, $resource, HyperLink::GET, 'comment', ['item_id' => $resource->getId(), 'item_type' => 'poll']),
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', PollResource::RESOURCE_NAME);
        $module = 'poll';
        return [
            [
                'path'      => 'poll/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ],
            ],
            [
                'path'      => 'poll/add',
                'routeName' => ROUTE_MODULE_ADD,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ],
            ],
            [
                'path'      => 'poll(/*)',
                'routeName' => ROUTE_MODULE_HOME,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ],
            ],
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('poll', [
            'title'           => $l->translate('polls'),
            'home_view'       => 'menu',
            'main_resource'   => new PollResource([]),
            'other_resources' => [
                new PollAnswerResource([]),
                new PollResultResource([]),
            ],
        ], isset($param['api_version_name']) ? $param['api_version_name'] : 'mobile');
        $resourceName = (new PollResource([]))->getResourceName();
        $headerButtons[$resourceName] = [

        ];
        if ($this->getAccessControl()->isGranted(PollAccessControl::ADD)) {
            $headerButtons[$resourceName][] = [
                'icon'   => 'plus',
                'action' => Screen::ACTION_ADD,
                'params' => ['resource_name' => $resourceName],
            ];
        }
        $app->addSetting('home.header_buttons', $headerButtons);
        return $app;
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var PollResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PollAccessControl::APPROVE, $item);
        if ($this->processService->moderatePoll($id, 0)) {
            $item = $this->loadResourceById($id, true);
            $permission = $this->getAccessControl()->getPermissions($item);
            return $this->success(array_merge($permission, ['is_pending' => false]), [], $this->getLocalization()->translate('poll_has_been_approved'));
        }
        return $this->error();
    }

    function feature($params)
    {
        $id = $this->resolver->resolveId($params);
        $feature = (int)$this->resolver
            ->setAllowedValues('feature', ['1', '0'])
            ->resolveSingle($params, 'feature', null, ['1', '0'], 1);
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PollAccessControl::FEATURE, $item);

        if ($this->processService->feature($id, $feature)) {
            return $this->success([
                'is_featured' => !!$feature
            ], [], $feature ? $this->getLocalization()->translate('poll_successfully_featured') : $this->getLocalization()->translate('poll_successfully_un_featured'));
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
            $this->denyAccessUnlessGranted(PollAccessControl::SPONSOR_IN_FEED, $item);
            $sponsorId = Phpfox::getService('feed')->canSponsoredInFeed('poll', $id);
            if ($sponsorId !== true && Phpfox::getService('ad.process')->deleteSponsor($sponsorId, true)) {
                return $this->success([
                    'is_sponsored_feed' => false
                ], [], $this->getLocalization()->translate('better_ads_this_item_in_feed_has_been_unsponsored_successfully'));
            }
        } else {
            if (!$this->getAccessControl()->isGranted(PollAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(PollAccessControl::PURCHASE_SPONSOR, $item)) {
                return $this->permissionError();
            }

            if ($this->processService->sponsor($id, $sponsor)) {
                if ($sponsor == 1) {
                    $sModule = $this->getLocalization()->translate('poll');
                    Phpfox::getService('ad.process')->addSponsor([
                        'module' => 'poll',
                        'item_id' => $id,
                        'name' => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getQuestion()])
                    ], false);
                } else {
                    Phpfox::getService('ad.process')->deleteAdminSponsor('poll', $id);
                }
                return $this->success([
                    'is_sponsor' => !!$sponsor
                ], [], $sponsor ? $this->getLocalization()->translate('poll_successfully_sponsored') : $this->getLocalization()->translate('poll_successfully_un_sponsored'));
            }
        }
        return $this->error();
    }


    /**
     * @param $params
     *
     * @return array
     */
    protected function findSponsor($params)
    {
        if (!Phpfox::isAppActive('Core_BetterAds')) {
            return [];
        }

        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $sponsoredItems = $this->pollService->getSponsored($limit, $cacheTime);

        if (!empty($sponsoredItems)) {
            $this->updateViewCount($sponsoredItems);
            $this->processRows($sponsoredItems);
        }
        return $sponsoredItems;
    }

    private function updateViewCount($sponsorItems)
    {
        if (!empty($this->adProcessService) && method_exists($this->adProcessService, 'addSponsorViewsCount')) {
            foreach ($sponsorItems as $sponsorItem) {
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'poll');
            }
        }
    }

    /**
     * Get features items
     *
     * @param $params
     *
     * @return array|int|string
     */
    protected function findFeature($params)
    {
        $limit = $this->resolver->resolveSingle($params, 'limit', 'int', ['min' => 1], 4);
        $cacheTime = $this->resolver->resolveSingle($params, 'cache_time', 'int', ['min' => 0], 5);

        $featuredItems = $this->pollService->getFeatured($limit, $cacheTime);

        if (!empty($featuredItems)) {
            $this->processRows($featuredItems);
        }
        return $featuredItems;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('poll', []);
        $resourceName = PollResource::populate([])->getResourceName();
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
                        'stats'     => ['vote' => 'total_votes', 'view' => 'total_view']
                    ],
                    'item_like_phrase',
                    'poll_close_time',
                    ['component' => 'item_pending', 'message' => 'this_poll_is_being_moderated_and_no_votes_can_be_added_yet'],
                    'item_html_content',
                    'item_separator',
                    'item_poll_answers'
                ]
            ],
            'screen_title'                 => $l->translate('poll') . ' > ' . $l->translate('poll') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        $screenSetting->addSetting($resourceName, 'viewPollResult', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => $l->translate('poll_results')
            ],
            ScreenSetting::LOCATION_MAIN   => ['component' => ScreenSetting::SMART_RESOURCE_LIST],
            'screen_title'                 => $l->translate('poll') . ' > ' . $l->translate('poll_results') . ' - ' . $l->translate('mobile_detail_page')
        ]);

        $screenSetting->addBlock($resourceName, ScreenSetting::MODULE_HOME, ScreenSetting::LOCATION_RIGHT, [
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('featured_polls'),
                'resource_name' => $resourceName,
                'module_name'   => 'poll',
                'refresh_time'  => 3000, //secs
                'query'         => ['view' => 'feature']
            ],
            [
                'component'     => ScreenSetting::SIMPLE_LISTING_BLOCK,
                'title'         => $l->translate('sponsored'),
                'resource_name' => $resourceName,
                'module_name'   => 'poll',
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
            ScreenSetting::MODULE_HOME    => 'poll.index',
            ScreenSetting::MODULE_LISTING => 'poll.index',
            ScreenSetting::MODULE_DETAIL  => 'poll.view'
        ];
    }

    /**
     * Moderation items
     *
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\PermissionErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
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
                $this->denyAccessUnlessGranted(PollAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->moderatePoll($id, 0)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_pending' => false];
                $sMessage = $this->getLocalization()->translate('poll_s_successfully_approved');
                break;
            case Screen::ACTION_FEATURE_ITEMS:
            case Screen::ACTION_REMOVE_FEATURE_ITEMS:
                $value = ($action == Screen::ACTION_FEATURE_ITEMS) ? 1 : 0;
                $this->denyAccessUnlessGranted(PollAccessControl::FEATURE);
                foreach ($ids as $key => $id) {
                    if (!$this->processService->feature($id, $value)) {
                        unset($ids[$key]);
                    }
                }
                $data = ['is_featured' => !!$value];
                $sMessage = ($value == 1) ? $this->getLocalization()->translate('poll_s_successfully_featured') : $this->getLocalization()->translate('poll_s_successfully_un_featured');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(PollAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    if (!$this->processService->moderatePoll($id, 2)) {
                        unset($ids[$key]);
                    }
                }
                $sMessage = $this->getLocalization()->translate('poll_s_successfully_deleted');
                break;
        }
        return $this->success(array_merge($data, ['ids' => $ids]), [], $sMessage);
    }
}