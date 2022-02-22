<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_BetterAds\Installation\Database\BetterAds;
use Apps\Core_Forums\Service\Forum;
use Apps\Core_Forums\Service\Moderate\Moderate;
use Apps\Core_Forums\Service\Thread\Process;
use Apps\Core_Forums\Service\Thread\Thread;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Forum\ForumThreadForm;
use Apps\Core_MobileApi\Api\Form\Forum\ForumThreadSearchForm;
use Apps\Core_MobileApi\Api\Resource\ForumResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Forum\ForumThreadAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;
use Phpfox_Database;

class ForumThreadApi extends AbstractResourceApi
{
    const ERROR_FORUM_NOT_FOUND = "Forum not found";

    /**
     * @var Forum
     */
    protected $forumService;

    /**
     * @var Thread
     */
    protected $threadService;
    /**
     * @var Process
     */
    protected $processService;

    /**
     * @var \User_Service_User
     */
    protected $userService;

    /**
     * @var BetterAds
     */
    protected $adProcessService = null;

    /**
     * @var Moderate
     */
    protected $moderatorService;

    public function __construct()
    {
        parent::__construct();
        $this->forumService = Phpfox::getService('forum');
        $this->threadService = Phpfox::getService('forum.thread');
        $this->processService = Phpfox::getService('forum.thread.process');
        $this->userService = Phpfox::getService('user');
        $this->moderatorService = Phpfox::getService('forum.moderate');
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adProcessService = Phpfox::getService('ad.process');
        }
    }

    public function __naming()
    {
        return [
            'forum-thread/search-form'   => [
                'get' => 'searchForm'
            ],
            'forum-thread/close/:id'     => [
                'put' => 'closeThread'
            ],
            'forum-thread/stick/:id'     => [
                'put' => 'stickThread'
            ],
            'forum-thread/subscribe/:id' => [
                'put' => 'subscribeThread'
            ]
        ];
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'view', 'q', 'sort', 'sort_type', 'limit', 'page', 'author', 'forum', 'days_prune', 'module_id', 'item_id', 'tag', 'forums'
        ])
            ->setAllowedValues('view', ['recent_discussion', 'my', 'pending', 'subscribed', 'sponsor'])
            ->setAllowedValues('sort', ['time_stamp', 'full_name', 'total_post', 'title', 'total_view'])
            ->setAllowedValues('sort_type', ['desc', 'asc', 'DESC', 'ASC'])
            ->setAllowedValues('days_prune', ['1', '2', '7', '10', '14', '30', '45', '60', '75', '100', '365', '-1'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('subscribed', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->setAllowedTypes('forum', 'int')
            ->setDefault([
                'limit'     => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'      => 1,
                'sort_type' => 'DESC'
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
        $isSearch = false;
        $sort = $params['sort'];
        $view = $params['view'];
        $parentModule = null;

        if (in_array($view, ['sponsor'])) {
            $function = 'find' . ucfirst($view);
            return $this->success($this->{$function}($params));
        }

        if (!empty($params['module_id']) && !empty($params['item_id'])) {
            $parentModule = [
                'module_id' => $params['module_id'],
                'item_id'   => $params['item_id'],
            ];
            if (Phpfox::hasCallback($parentModule['module_id'],
                    'checkPermission') && !Phpfox::callback($parentModule['module_id'] . '.checkPermission',
                    $parentModule['item_id'], 'forum.view_browse_forum')
            ) {
                return $this->permissionError();
            }
        } else if (!empty($params['forum'])) {
            $item = $this->database()->select('*')
                ->from(':forum')
                ->where('forum_id = ' . (int)$params['forum'])
                ->execute('getSlaveRow');
            if (!$item) {
                return $this->notFoundError();
            }
            if (!$this->forumService->hasAccess($item['forum_id'], 'can_view_forum')) {
                return $this->permissionError();
            }
        }
        //If search multiple forum
        if (!empty($params['forums'])) {
            $forumIds = [];
            if (is_array($params['forums'])) {
                $searchForumId = $params['forums'];
            } else {
                $searchForumId = explode(',', $params['forums']);
            }
            foreach ($searchForumId as $iSearchForum) {
                if (!is_numeric($iSearchForum)) {
                    continue;
                }
                $forumIds[] = $iSearchForum;
            }
            $forumIds = $this->threadService->getCanViewForumIdList($forumIds);
            if (!empty($forumIds)) {
                $this->search()->setCondition('AND ft.forum_id IN(' . implode(',', $forumIds) . ')');
                $params['forum'] = 0;
            }
        }
        $pendingSearch = false;
        $viewId = 'ft.view_id = 0';
        if ($parentModule === null) {
            if (!empty($view)) {
                $isSearch = true;
                switch ($view) {
                    case 'recent_discussion':
                        $sort = 'time_update';
                        break;
                    case 'my':
                        if (!Phpfox::isUser()) {
                            return $this->permissionError();
                        } else {
                            $this->search()->setCondition('AND ft.user_id = ' . Phpfox::getUserId());
                            $viewId = 'ft.view_id >= 0';
                        }
                        break;
                    case 'pending':
                        if (Phpfox::getUserParam('forum.can_approve_forum_thread')) {
                            $viewId = 'ft.view_id = 1';
                        } else {
                            return $this->permissionError();
                        }
                        $pendingSearch = true;
                        break;
                    default:
                        break;
                }
                $this->search()->setCondition(($pendingSearch ? 'AND ' : 'AND ft.group_id = 0 AND ft.is_announcement = 0 AND ') . $viewId);
            } else {
                if (!$params['forum'] && !$sort) {
                    $sort = 'time_update';
                }
                $this->search()->setCondition(($params['forum'] ? 'AND ft.forum_id = ' . $params['forum'] : '') . ' AND ft.group_id = 0 AND ' . $viewId . ' AND ft.is_announcement = 0');
            }
        } else {
            $isSearch = true;
            $this->search()->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $parentModule['item_id'] . ' AND ' . $viewId . ' AND ft.is_announcement = 0');
        }

        if ($params['author']) {
            $isSearch = true;
            $this->search()->setCondition('AND u.full_name LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['author'] . '%') . '"');
        }
        $sortBy = $params['sort_type'];
        switch ($sort) {
            case 'time_stamp':
                $sort = 'ft.time_stamp';
                break;
            case 'time_update':
                $sort = 'ft.time_update';
                break;
            case 'full_name':
                $sort = 'u.full_name';
                break;
            case 'total_post':
                $sort = 'ft.total_post';
                break;
            case 'title':
                $sort = 'ft.title';
                break;
            case 'total_view':
                $sort = 'ft.total_view';
                break;
            default:
                $sort = 'ft.time_stamp';
                break;
        }
        $sort = $sort . ' ' . $sortBy;
        if (($params['days_prune']) && $params['days_prune'] != '-1') {
            $isSearch = true;
            $this->search()->setCondition('AND ft.time_stamp >= ' . (PHPFOX_TIME - ($params['days_prune'] * 86400)));
        }
        $isTagSearch = false;
        $isModuleTagSearch = false;
        if (!empty($params['tag'])) {
            $isSearch = true;
            $isTagSearch = true;
            if ($parentModule) {
                $isModuleTagSearch = true;
                $this->search()->setCondition('AND ft.group_id = ' . (int)$parentModule['item_id'] . ' AND tag.tag_url = \'' . Phpfox_Database::instance()->escape(urldecode($params['tag'])) . '\'');
            } else {
                $this->search()->setCondition('AND ft.group_id = 0 AND tag.tag_url = \'' . Phpfox_Database::instance()->escape(urldecode($params['tag'])) . '\'');
            }
        }
        if ($params['q']) {
            $isSearch = true;
            $this->search()->setCondition('AND ft.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '"');
        }
        list(, $items) = $this->threadService->isSearch($isSearch)
            ->isAdvSearch($isSearch)
            ->isTagSearch($isTagSearch)
            ->isNewSearch(($view == 'new' ? true : false))
            ->isSubscribeSearch(($view == 'subscribed' ? true : false))
            ->isModuleSearch($isModuleTagSearch)
            ->get($this->search()->getConditions(), 'ft.order_id DESC, ' . $sort, $params['page'],
                $params['limit']);
        $this->processRows($items);
        return $this->success($items);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $params = $this->resolver
            ->setDefined([
                'limit', 'page', 'post'
            ])
            ->setRequired(['id'])
            ->resolve(array_merge(['limit' => Phpfox::getParam('forum.total_posts_per_thread'), 'page' => 1], $params))
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
        $conditions = 'ft.thread_id = ' . $params['id'] . '';
        $permaView = isset($params['post']) && $params['post'] > 0 ? $params['post'] : null;
        list(, $item) = $this->getThread($conditions, [],
            'fp.time_stamp ASC', $params['page'], $params['limit'], $permaView);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($item['forum_id'] && (!$this->forumService->hasAccess($item['forum_id'], 'can_view_forum') || !$this->forumService->hasAccess($item['forum_id'], 'can_view_thread_content'))) {
            return $this->permissionError();
        }

        if ($item['view_id'] != '0' && $item['user_id'] != Phpfox::getUserId()) {
            if (!Phpfox::getUserParam('forum.can_approve_forum_thread') && !$this->moderatorService->hasAccess($item['forum_id'],
                    'approve_thread')
            ) {
                return $this->notFoundError();
            }
        }
        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!Phpfox::getUserBy('is_invisible')) {
                if (!$item['is_seen']) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('forum', $item['thread_id']);
                } else {
                    if (!setting('track.unique_viewers_counter')) {
                        $updateCounter = true;
                        Phpfox::getService('track.process')->add('forum', $item['thread_id']);
                    } else {
                        Phpfox::getService('track.process')->update('forum_thread', $item['thread_id']);
                    }
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            $this->processService->updateTrack($item['thread_id'], true);
        }
        $item['is_detail'] = true;
        /** @var ForumThreadResource $resource */
        $resource = $this->populateResource(ForumThreadResource::class, $item);
        $this->setHyperlinks($resource, true);
        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->lazyLoad(['user'])
            ->toArray());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        $params = $this->resolver
            ->setDefined(['module_id', 'item_id', 'id', 'forum_id'])
            ->setAllowedTypes('forum_id', 'int', ['min' => 0])
            ->setAllowedTypes('id', 'int', ['min' => 0])
            ->setAllowedTypes('item_id', 'int', ['min' => 0])
            ->resolve($params)->getParameters();
        $editId = $params['id'];
        if (empty($editId) && empty($params['forum_id']) && empty($params['module_id'])) {
            return $this->missingParamsError(['forum_id', 'module_id']);
        }
        $forum = $this->getForum($params['forum_id']);
        /** @var ForumThreadForm $form */
        $form = $this->createForm(ForumThreadForm::class, [
            'title'  => !$editId ? 'post_new_thread' : 'editing_thread',
            'method' => $editId ? 'PUT' : 'POST'
        ]);
        $form->setForumId($params['forum_id']);
        if ($editId) {
            $thread = $this->loadResourceById($editId, true);
            if (empty($thread)) {
                return $this->notFoundError();
            }
            $form->setEditing(true);
            $form->setAction(UrlUtility::makeApiUrl('forum-thread/:id', $editId));
            $this->denyAccessUnlessGranted(ForumThreadAccessControl::EDIT, $thread);
            $form->assignValues($thread);
        } else {
            if (($iFlood = $this->getSetting()->getUserSetting('forum.forum_thread_flood_control')) !== 0) {
                $aFlood = array(
                    'action' => 'last_post', // The SPAM action
                    'params' => array(
                        'field' => 'time_stamp', // The time stamp field
                        'table' => Phpfox::getT('forum_thread'), // Database table we plan to check
                        'condition' => 'user_id = ' . $this->getUser()->getId(), // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    )
                );

                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood)) {
                    return $this->error($this->getLocalization()->translate('posting_a_new_thread_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }
            $this->denyAccessUnlessGranted(ForumThreadAccessControl::ADD, $forum);
        }
        return $this->success($form->getFormStructure());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        /** @var ForumThreadForm $form */
        $form = $this->createForm(ForumThreadForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => ForumThreadResource::populate([])->getResourceName(),
                    'module_name'   => 'forum'
                ], [], $this->localization->translate('thread_successfully_created'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($values)
    {
        if (empty($values['forum_id']) && empty($values['module_id'])) {
            return $this->validationParamsError(['forum_id', 'module_id']);
        }

        if (!empty($values['forum_id'])) {
            $forum = $this->getForum($values['forum_id']);
        }
        $this->denyAccessUnlessGranted(ForumThreadAccessControl::ADD, isset($forum) ? $forum : null);

        $callback = false;
        if (!empty($values['item_id']) && !empty($values['module_id'])) {
            $callback = Phpfox::hasCallback($values['module_id'], 'addForum')
                ? Phpfox::callback($values['module_id'] . '.addForum', $values['item_id']) : false;
            if (!$callback) {
                return $this->error($this->getLocalization()->translate('cannot_find_the_parent_item'));
            }
        }
        $values['type_id'] = 'thread';
        if (isset($values['tags'])) {
            $values['tag_list'] = $values['tags'];
            unset($values['tags']);
        }
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        // Set default for forum service.
        if (!isset($values['forum_id'])) {
            $values['forum_id'] = 0;
        }
        return $this->processService->add($values, $callback);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var ForumThreadForm $form */
        $form = $this->createForm(ForumThreadForm::class);
        /** @var ForumThreadResource $thread */
        $thread = $this->loadResourceById($id, true);
        $form->setEditing(true);
        if (empty($thread)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(ForumThreadAccessControl::EDIT, $thread);

        if ($form->isValid() && ($values = $form->getValues())) {
            $values['user_id'] = $thread->getAuthor()->getId();
            $values['post_id'] = $thread->start_id;
            $values['forum_id'] = $thread->forum_id;
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => ForumThreadResource::populate([])->getResourceName(),
                    'module_name'   => 'forum'
                ], [], $this->localization->translate('thread_successfully_updated'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        if (isset($values['tags'])) {
            $values['tag_list'] = $values['tags'];
            unset($values['tags']);
        }
        $values['type_id'] = 'thread';
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        return $this->processService->update($id, $values['user_id'], $values);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        $id = $this->resolver->resolveId($params);
        $item = $this->threadService->getActualThread($id);
        if (!$item || count($item) <= 1) {
            return $this->notFoundError();
        }
        $canDelete = false;
        if ((int)$item['group_id'] > 0) {
            if ((Phpfox::getUserParam('forum.can_delete_own_post') && $item['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_delete_other_posts') || $this->threadService->isAdminOfParentItem($item['thread_id'])) {
                $canDelete = true;
            }
        } else {
            if (($this->moderatorService->hasAccess($item['forum_id'],
                    'delete_post') || Phpfox::getService('user.auth')->hasAccess('forum_thread', 'thread_id',
                    $id, 'forum.can_delete_own_post', 'forum.can_delete_other_posts'))
            ) {
                $canDelete = true;
            }
        }
        if ($canDelete && $this->processService->delete($id)) {
            return $this->success([], [], $this->getLocalization()->translate('thread_successfully_deleted'));
        }
        return $this->permissionError();
    }


    private function getForum($id)
    {
        if (!empty($id)) {
            $forum = NameResource::instance()->getApiServiceByResourceName(ForumResource::RESOURCE_NAME)->loadResourceById($id, true);
            if (!$forum) {
                return $this->notFoundError();
            }
            return $forum;
        }
        return null;
    }

    /**
     * @param $id
     * @param $returnResource boolean
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $thread = $this->threadService->getForEdit($id);
        $iUserId = $this->getUser()->getId();
        if (empty($thread['thread_id'])) {
            return null;
        }
        if (!empty($thread['forum_id'])) {
            $thread['forum_is_closed'] = $this->database()->select('is_closed')->from(':forum')->where('forum_id =' . (int)$thread['forum_id'])->execute('getField');
        }
        if (!isset($thread['is_subscribed'])) {
            $thread['is_subscribed'] = !!$this->database()->select('subscribe_id')->from(':forum_subscribe')->where(['thread_id' => (int)$thread['thread_id'], 'user_id' => $iUserId])->execute('getField');
        }
        if ($returnResource) {
            $thread['is_detail'] = true;
            return ForumThreadResource::populate($thread);
        }
        return $thread;
    }

    public function processRow($item)
    {
        /** @var ForumThreadResource $resource */
        $resource = $this->populateResource(ForumThreadResource::class, $item);
        $this->setHyperlinks($resource);

        $view = $this->request()->get('view');
        $shortFields = [];

        if (in_array($view, ['sponsor'])) {
            $shortFields = [
                'resource_name', 'title', 'user', 'statistic', 'id', 'creation_date', 'description', 'sponsor_id', 'order_id'
            ];
        }

        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray($shortFields);
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new ForumThreadAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get("module_id");
        $itemId = $this->request()->get("item_id");

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
        $forumId = $this->resolver->resolveSingle($params, 'forum', 'int');
        $this->denyAccessUnlessGranted(ForumThreadAccessControl::VIEW);
        /** @var ForumThreadSearchForm $form */
        $form = $this->createForm(ForumThreadSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('forum-thread')
        ]);
        $form->setForumId((int)$forumId);
        $form->setForums($this->getForumsList());
        return $this->success($form->getFormStructure());
    }

    private function getForumsList()
    {
        $forums = $this->forumService->live()->getForums();
        return $this->checkForum($forums, 0);
    }

    private function checkForum($forums, $forumId)
    {
        $results = [];
        foreach ($forums as $key => $forum) {
            if ($forum['parent_id'] != $forumId || $forum['is_closed'] == 1) {
                continue;
            }
            $results[$forum['forum_id']] = [
                'forum_id' => $forum['forum_id'],
                'name'     => $this->getLocalization()->translate($forum['name'])
            ];
            if (!empty($forum['sub_forum'])) {
                $results[$forum['forum_id']]['sub_forum'] = $this->checkForum($forum['sub_forum'], $forum['forum_id']);
            }

        }
        return $results;
    }

    private function setHyperlinks(ForumThreadResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            ForumThreadAccessControl::VIEW   => $this->createHyperMediaLink(ForumThreadAccessControl::VIEW, $resource,
                HyperLink::GET, 'forum-thread/:id', ['id' => $resource->getId()]),
            ForumThreadAccessControl::EDIT   => $this->createHyperMediaLink(ForumThreadAccessControl::EDIT, $resource,
                HyperLink::GET, 'forum-thread/form/:id', ['id' => $resource->getId()]),
            ForumThreadAccessControl::DELETE => $this->createHyperMediaLink(ForumThreadAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'forum-thread/:id', ['id' => $resource->getId()]),
        ]);
        if ($includeLinks) {
            $resource->setLinks([
                'likes' => $this->createHyperMediaLink(null, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getPosts()[0]['id'], 'item_type' => 'forum_post']),
                'posts' => $this->createHyperMediaLink(ForumThreadAccessControl::VIEW, $resource,
                    HyperLink::GET, 'forum-post', ['thread' => $resource->getId()]),
            ]);
        }
    }

    public function getRouteMap()
    {
        $resource = str_replace('-', '_', ForumThreadResource::RESOURCE_NAME);
        $module = 'forum';
        return [
            [
                'path'      => 'forum/thread/:id(/*)',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ],
            [
                'path'      => 'forum/tag/:tag',
                'routeName' => ROUTE_MODULE_LIST,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => $resource,
                ]
            ]
        ];
    }

    /**
     * @param array       $aThreadCondition
     * @param array       $mConditions
     * @param string      $sOrder
     * @param string|int  $iPage
     * @param string|int  $iPageSize
     * @param null|string $sPermaView
     *
     * @return array
     **/
    public function getThread(
        $aThreadCondition = [],
        $mConditions = [],
        $sOrder = 'fp.time_stamp ASC',
        $iPage = '',
        $iPageSize = '',
        $sPermaView = null
    )
    {
        if (Phpfox::isModule('track')) {
            $sJoinQuery = Phpfox::isUser() ? 'ftr.user_id = ' . Phpfox::getUserBy('user_id') : 'ftr.ip_address = \'' . $this->database()->escape(Phpfox::getIp()) . '\'';
            $this->database()->select('ftr.item_id AS is_seen, ftr.time_stamp AS last_seen_time, ')
                ->leftJoin(Phpfox::getT('track'), 'ftr',
                    'ftr.item_id = ft.thread_id AND ftr.type_id=\'forum_thread\' AND ' . $sJoinQuery);
        }

        $aThread = $this->database()->select('ft.thread_id, ft.time_stamp, ft.time_update, ft.group_id, ft.view_id, ft.forum_id, ft.is_closed, ft.user_id, ft.is_announcement, ft.order_id, ft.title_url, ft.time_update AS last_time_stamp, ft.title, ft.total_view, ft.total_post, fs.subscribe_id AS is_subscribed, ft.poll_id, f.forum_id, f.is_closed as forum_is_closed, ft.start_id')
            ->from(Phpfox::getT('forum_thread'), 'ft')
            ->leftjoin(Phpfox::getT('forum'), 'f', 'f.forum_id = ft.forum_id')
            ->leftJoin(Phpfox::getT('forum_subscribe'), 'fs',
                'fs.thread_id = ft.thread_id AND fs.user_id = ' . Phpfox::getUserId())
            ->where($aThreadCondition)
            ->execute('getSlaveRow');

        if (!isset($aThread['thread_id'])) {
            return [0, []];
        }

        if (!isset($aThread['is_seen'])) {
            $aThread['is_seen'] = 0;
        }

        // Thread not seen
        if (!$aThread['is_seen'] && Phpfox::isUser()) {
            // User has signed up after the post so they have already seen the post
            if ((Phpfox::isUser() && Phpfox::getUserBy('joined') > $aThread['last_time_stamp']) || (!Phpfox::isUser() && Phpfox::getCookie('visit') > $aThread['last_time_stamp'])) {
                $aThread['is_seen'] = 1;
            } else if (($iLastTimeViewed = Phpfox::getLib('session')->getArray('forum_view',
                    $aThread['thread_id'])) && (int)$iLastTimeViewed > $aThread['last_time_stamp']
            ) {
                $aThread['is_seen'] = 1;
            } // Checks if the post is older then our default active post time limit
            else if ((PHPFOX_TIME - Phpfox::getParam('forum.keep_active_posts') * 60) > $aThread['last_time_stamp']) {
                $aThread['is_seen'] = 1;
            }
        } else {
            // New post was added
            if ($aThread['last_time_stamp'] > $aThread['last_seen_time']) {
                $aThread['is_seen'] = 0;
            }
        }

        $sViewId = (Phpfox::getUserParam('forum.can_approve_forum_post') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'approve_post')) ? '' : ' AND fp.view_id = 0';

        $mConditions[] = 'fp.thread_id = ' . $aThread['thread_id'] . $sViewId;

        $iCnt = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('forum_post'), 'fp')
            ->where($mConditions)
            ->execute('getSlaveField');

        $aThread['last_update_on'] = '';

        if ($sPermaView !== null) {
            $mConditions[] = 'AND fp.post_id = ' . (int)$sPermaView;
        }

        if (!empty($aThread['poll_id']) && Phpfox::isAppActive('Core_Polls')) {
            $aThread['poll'] = Phpfox::getService('poll')->getPollByUrl((int)$aThread['poll_id'], false, false, false,
                true);
            $aThread['poll']['bCanEdit'] = false;
            $aThread['poll']['bCanDelete'] = false;
            $aThread['poll']['canViewResult'] = ((Phpfox::getUserParam('poll.can_view_user_poll_results_own_poll') && $aThread['poll']['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('poll.can_view_user_poll_results_other_poll'));
            $aThread['poll']['canViewResultVote'] = isset($aThread['poll']['user_voted_this_poll']) && ($aThread['poll']['user_voted_this_poll'] == false && Phpfox::getUserParam('poll.view_poll_results_before_vote')) || ($aThread['poll']['user_voted_this_poll'] == true && Phpfox::getUserParam('poll.view_poll_results_after_vote'));
            $aThread['poll']['canVotesWithCloseTime'] = $aThread['poll']['close_time'] == 0 || $aThread['poll']['close_time'] > PHPFOX_TIME;
        }

        (($sPlugin = \Phpfox_Plugin::get('forum.service_thread_getthread_query')) ? eval($sPlugin) : false);

        if (!isset($bLeftJoinQuery)) {
            $bLeftJoinQuery = false;
        }

        $theJoins = function () use ($bLeftJoinQuery) {
            if (isset($bLeftJoinQuery) && $bLeftJoinQuery !== false) {
                $this->database()->leftJoin(Phpfox::getT('user'), 'u',
                    'u.user_id = fp.user_id')->leftJoin(Phpfox::getT('user_field'), 'uf', 'uf.user_id = fp.user_id');
            } else {
                $this->database()->join(Phpfox::getT('user'), 'u',
                    'u.user_id = fp.user_id')->join(Phpfox::getT('user_field'), 'uf', 'uf.user_id = fp.user_id');
            }

            if (Phpfox::isModule('like')) {
                $this->database()->select('l.like_id AS is_liked, ')
                    ->leftJoin(Phpfox::getT('like'), 'l',
                        'l.type_id = \'forum_post\' AND l.item_id = fp.post_id AND l.user_id = ' . Phpfox::getUserId());
            }
        };
        if (!$iPage) {
            $theJoins();
            $aThread['post_starter'] = $this->database()->select('fp.*, ft.thank_id, ' . (Phpfox::getParam('core.allow_html') ? 'fpt.text_parsed' : 'fpt.text') . ' AS text, ' . Phpfox::getUserField() . ', u.joined, u.country_iso, uf.signature, uf.total_post')
                ->from(Phpfox::getT('forum_post'), 'fp')
                ->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id')
                ->leftJoin(':forum_thank', 'ft', 'ft.post_id = fp.post_id AND ft.user_id =' . (int)Phpfox::getUserId())
                ->where($mConditions)
                ->order('fp.time_stamp ASC')
                ->executeRow();
        }

        if (!$iPage) {
            $iPageSize = Phpfox::getParam('forum.total_posts_per_thread');
            if (\Phpfox_Request::instance()->get('is_ajax_get')) {
                $iPageSize = null;
            }
            $sOrder = 'fp.time_stamp DESC';
        }

        $theJoins();
        if (isset($aThread['post_starter']) && !empty($aThread['post_starter'])) {
            $mConditions[] = ' AND fp.post_id <> ' . $aThread['post_starter']['post_id'];
        }
        $aThread['posts'] = $this->database()->select('fp.*, ft.thank_id, ' . (Phpfox::getParam('core.allow_html') ? 'fpt.text_parsed' : 'fpt.text') . ' AS text, ' . Phpfox::getUserField() . ', u.joined, u.country_iso, uf.signature, uf.total_post')
            ->from(Phpfox::getT('forum_post'), 'fp')
            ->join(Phpfox::getT('forum_post_text'), 'fpt', 'fpt.post_id = fp.post_id')
            ->leftJoin(':forum_thank', 'ft', 'ft.post_id = fp.post_id AND ft.user_id =' . (int)Phpfox::getUserId())
            ->where($mConditions)
            ->order($sOrder)
            ->limit($iPage, $iPageSize, $iCnt, false, false)
            ->execute('getSlaveRows');

        if (isset($aThread['post_starter'])) {
            $aThread['posts'][] = $aThread['post_starter'];
            $aThread['posts'] = array_reverse($aThread['posts']);
        }
        $sPostIds = '';
        $aThread['has_pending_post'] = false;

        foreach ($aThread['posts'] as $iKey => $aPost) {

            $aThread['posts'][$iKey]['count'] = Phpfox::getService('forum.post')->getPostCount($aThread['thread_id'], $aPost['post_id']) - 1;
            $aThread['posts'][$iKey]['forum_id'] = $aThread['forum_id'];
            $aThread['posts'][$iKey]['last_update_on'] = $this->getLocalization()->translate('last_update_on_time_stamp_by_update_user', [
                    'time_stamp'  => Phpfox::getTime(Phpfox::getParam('forum.forum_time_stamp'), $aPost['update_time']),
                    'update_user' => $aPost['update_user']
                ]
            );

            $aThread['posts'][$iKey]['aFeed'] = [
                'privacy'               => 0,
                'comment_privacy'       => 0,
                'like_type_id'          => 'forum_post',
                'feed_is_liked'         => ($aPost['is_liked'] ? true : false),
                'item_id'               => $aPost['post_id'],
                'user_id'               => $aPost['user_id'],
                'total_like'            => $aPost['total_like'],
                'feed_link'             => Phpfox::permalink('forum.thread', $aThread['thread_id'],
                        $aThread['title']) . 'view_' . $aPost['post_id'] . '/',
                'feed_title'            => $aThread['title'],
                'feed_display'          => 'mini',
                'feed_total_like'       => $aPost['total_like'],
                'report_module'         => 'forum_post',
                'report_phrase'         => $this->getLocalization()->translate('report_this_post'),
                'force_report'          => true,
                'time_stamp'            => $aPost['time_stamp'],
                'type_id'               => 'forum_post',
                'disable_like_function' => Phpfox::getParam('forum.enable_thanks_on_posts')
            ];
            if ($aPost['view_id'] == 1) {
                $aThread['posts'][$iKey]['pending_action'] = [
                    'message' => $this->getLocalization()->translate('this_post_is_waiting_for_approval_please_review_the_content'),
                    'actions' => [
                        'approve' => [
                            'is_ajax' => true,
                            'label'   => $this->getLocalization()->translate('approve'),
                            'action'  => '$.ajaxCall(\'forum.approvePost\', \'detail=true&amp;post_id=' . $aPost['post_id'] . '\', \'GET\'); return false;'
                        ]
                    ]
                ];
                if ((!isset($aThread['forum_is_closed']) || !$aThread['forum_is_closed']) && ((user('forum.can_edit_own_post') && $aPost['user_id'] == Phpfox::getUserId()) || user('forum.can_edit_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'edit_post'))) {
                    $aThread['posts'][$iKey]['pending_action']['actions']['edit'] = [
                        'is_ajax' => true,
                        'label'   => $this->getLocalization()->translate('edit'),
                        'action'  => '$Core.box(\'forum.reply\', 800, \'id=' . $aPost['thread_id'] . '&amp;edit=' . $aPost['post_id'] . '\'); return false;'
                    ];
                }
                if (((Phpfox::getUserParam('forum.can_delete_own_post') && $aPost['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_delete_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'], 'delete_post') || (!empty($aThread['group_id']) && (Phpfox::isAppActive('Core_Pages') || Phpfox::isAppActive('PHPfox_Groups')) && ($sModule = Phpfox::getPagesType($aThread['group_id'])) && Phpfox::isModule($sModule) && Phpfox::getService($sModule)->isAdmin($aThread['group_id'])))) {
                    $aThread['posts'][$iKey]['pending_action']['actions']['delete'] = [
                        'is_ajax' => true,
                        'label'   => $this->getLocalization()->translate('delete'),
                        'action'  => 'return $Core.forum.deletePost(\'' . $aPost['post_id'] . '\');'
                    ];
                }
            }
            if (Phpfox::isModule('like') && Phpfox::isModule('feed')) {
                $aThread['posts'][$iKey]['aFeed']['feed_like_phrase'] = Phpfox::getService('feed')->getPhraseForLikes($aThread['posts'][$iKey]['aFeed']);
            }

            if (isset($aThread['post_starter']) && $aThread['post_starter']['post_id'] == $aPost['post_id']) {
                $iFirstPostKey = $iKey;
            }

            if ($aPost['total_attachment']) {
                $sPostIds .= $aPost['post_id'] . ',';
            }
            if ($aPost['view_id']) {
                $aThread['has_pending_post'] = true;
            }
        }
        $sPostIds = rtrim($sPostIds, ',');

        if (!empty($sPostIds)) {
            list(, $aAttachments) = Phpfox::getService('attachment')->get('attachment.item_id IN(' . $sPostIds . ') AND attachment.view_id = 0 AND attachment.category_id = \'forum\' AND attachment.is_inline = 0',
                'attachment.attachment_id DESC', false);

            $aAttachmentCache = [];
            foreach ($aAttachments as $aAttachment) {
                $aAttachmentCache[$aAttachment['item_id']][] = $aAttachment;
            }

            foreach ($aThread['posts'] as $iKey => $aPost) {
                if (isset($aAttachmentCache[$aPost['post_id']])) {
                    $aThread['posts'][$iKey]['attachments'] = $aAttachmentCache[$aPost['post_id']];
                }
            }
        }
        if (isset($aThread['post_starter']) && isset($iFirstPostKey)) {
            $aThread['post_starter'] = array_merge($aThread['post_starter'], $aThread['posts'][$iFirstPostKey]);
            $aThread['post_starter']['is_started'] = true;
            unset($aThread['posts'][$iFirstPostKey]);
        }

        return [$iCnt, $aThread];
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var ForumThreadResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(ForumThreadAccessControl::APPROVE, $item);
        if ($this->processService->approve($id)) {
            return $this->success([
                'is_pending' => false
            ], [], $this->getLocalization()->translate('thread_successfully_approved'));
        }
        return $this->error();
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
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

        $sponsoredItems = $this->threadService->getRandomSponsored($limit, $cacheTime);
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
                $this->adProcessService->addSponsorViewsCount($sponsorItem['sponsor_id'], 'forum.thread');
            }
        }
    }

    function sponsor($params)
    {
        $id = $this->resolver->resolveId($params);
        $sponsor = (int)$this->resolver->resolveSingle($params, 'sponsor', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        if (!$this->getAccessControl()->isGranted(ForumThreadAccessControl::SPONSOR, $item) && !$this->getAccessControl()->isGranted(ForumThreadAccessControl::PURCHASE_SPONSOR, $item)) {
            return $this->permissionError();
        }

        if ($this->processService->sponsor($id, $sponsor ? 2 : 0)) {
            if ($sponsor == 1) {
                $sModule = $this->getLocalization()->translate('forum_thread');
                Phpfox::getService('ad.process')->addSponsor([
                    'module'  => 'forum',
                    'section' => 'thread',
                    'item_id' => $id,
                    'name'    => $this->getLocalization()->translate('default_campaign_custom_name', ['module' => $sModule, 'name' => $item->getTitle()])
                ], false);
            } else {
                Phpfox::getService('ad.process')->deleteAdminSponsor('forum_thread', $id);
            }
            return $this->success([
                'order_id' => $sponsor ? 2 : 0
            ], [], $sponsor ? $this->getLocalization()->translate('thread_successfully_sponsored') : $this->getLocalization()->translate('thread_successfully_unsponsored'));
        }
        return $this->error();
    }

    public function closeThread($params)
    {
        $id = $this->resolver->resolveId($params);
        $close = (int)$this->resolver->resolveSingle($params, 'close', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        $this->denyAccessUnlessGranted(ForumThreadAccessControl::CLOSE, $item);

        if ($this->processService->close($id, $close)) {
            return $this->success([
                'is_closed' => $close
            ], [], $close ? $this->getLocalization()->translate('thread_successfully_closed') : $this->getLocalization()->translate('thread_successfully_opened'));
        }
        return $this->error();
    }

    public function stickThread($params)
    {
        $id = $this->resolver->resolveId($params);
        $stick = (int)$this->resolver->resolveSingle($params, 'stick', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id, true);
        $this->denyAccessUnlessGranted(ForumThreadAccessControl::CLOSE, $item);

        if ($this->processService->stick($id, $stick)) {
            return $this->success([
                'order_id' => $stick
            ], [], $stick ? $this->getLocalization()->translate('thread_successfully_stuck') : $this->getLocalization()->translate('thread_successfully_unstuck'));
        }
        return $this->error();
    }

    public function subscribeThread($params)
    {
        $id = $this->resolver->resolveId($params);
        $subscribe = (int)$this->resolver->resolveSingle($params, 'subscribe', null, ['1', '0'], 1);

        $item = $this->loadResourceById($id);
        if (!$item) {
            return $this->notFoundError();
        }

        if ($subscribe) {
            $result = Phpfox::getService('forum.subscribe.process')->add($id, $this->getUser()->getId());
        } else {
            Phpfox::getService('forum.subscribe.process')->delete($id, $this->getUser()->getId());
            $result = true;
        }
        if ($result) {
            return $this->success([
                'is_subscribed' => $subscribe
            ], [], $subscribe ? $this->getLocalization()->translate('thread_successfully_subscribe') : $this->getLocalization()->translate('thread_successfully_unsubscribe'));
        }
        return $this->error();
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
            ->setAllowedValues('action', [Screen::ACTION_APPROVE_ITEMS, Screen::ACTION_DELETE_ITEMS]);
        $action = $this->resolver->resolveSingle($params, 'action', 'string', [], '');
        $ids = $this->resolver->resolveSingle($params, 'ids', 'array', [], []);
        if (!count($ids)) {
            return $this->missingParamsError(['ids']);
        }

        $data = ['ids' => $ids];
        $sMessage = '';
        switch ($action) {
            case Screen::ACTION_APPROVE_ITEMS:
                $this->denyAccessUnlessGranted(ForumThreadAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    $this->processService->approve($id);
                }
                $data = array_merge($data, ['is_pending' => false]);
                $sMessage = $this->getLocalization()->translate('thread_s_successfully_approved');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(ForumThreadAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    $this->processService->delete($id);
                }
                $sMessage = $this->getLocalization()->translate('thread_s_successfully_deleted');
                break;
        }
        return $this->success($data, [], $sMessage);
    }
}