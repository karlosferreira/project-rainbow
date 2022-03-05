<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Forums\Service\Forum;
use Apps\Core_Forums\Service\Moderate\Moderate;
use Apps\Core_Forums\Service\Post\Post;
use Apps\Core_Forums\Service\Post\Process;
use Apps\Core_Forums\Service\Thread\Thread;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Form\Forum\ForumPostForm;
use Apps\Core_MobileApi\Api\Form\Forum\ForumPostSearchForm;
use Apps\Core_MobileApi\Api\Resource\FeedResource;
use Apps\Core_MobileApi\Api\Resource\ForumPostResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Forum\ForumPostAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

class ForumPostApi extends AbstractResourceApi implements ActivityFeedInterface
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
    protected $postProcessService;

    /**
     * @var Post
     */
    protected $postService;
    /**
     * @var \User_Service_User
     */
    protected $userService;

    /**
     * @var Moderate
     */
    protected $moderatorService;


    public function __construct()
    {
        parent::__construct();
        $this->forumService = Phpfox::getService('forum');
        $this->postProcessService = Phpfox::getService('forum.post.process');
        $this->userService = Phpfox::getService('user');
        $this->postService = Phpfox::getService('forum.post');
        $this->threadService = Phpfox::getService('forum.thread');
        $this->moderatorService = Phpfox::getService('forum.moderate');
    }

    public function __naming()
    {
        return [
            'forum-post/search-form' => [
                'get' => 'searchForm'
            ],
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
            'view', 'q', 'sort', 'sort_type', 'limit', 'page', 'show', 'author', 'forum', 'days_prune', 'module_id', 'item_id', 'tag', 'thread', 'forums'
        ])
            ->setAllowedValues('view', ['new', 'pending'])
            ->setAllowedValues('sort', ['time_stamp', 'full_name', 'total_post', 'title', 'total_view'])
            ->setAllowedValues('sort_type', ['desc', 'DESC', 'asc', 'ASC'])
            ->setAllowedValues('days_prune', ['1', '2', '7', '10', '14', '30', '45', '60', '75', '100', '365', '-1'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('profile_id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->setAllowedTypes('forum', 'int')
            ->setAllowedTypes('thread', 'int')
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
        }
        $isThreadDetail = !empty($params['thread']);
        if (!empty($params['forum'])) {
            $forum = $this->database()->select('*')
                ->from(':forum')
                ->where('forum_id = ' . (int)$params['forum'])
                ->execute('getSlaveRow');
            if (!$forum) {
                return $this->notFoundError();
            }
            if (!$this->forumService->hasAccess($forum['forum_id'], 'can_view_forum')) {
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
        if ($isThreadDetail) {
            $thread = $this->threadService->getActualThread($params['thread']);
            if (!$thread) {
                return $this->notFoundError();
            }
            if ($thread['forum_id'] && (!$this->forumService->hasAccess($thread['forum_id'], 'can_view_forum') || !$this->forumService->hasAccess($thread['forum_id'], 'can_view_thread_content'))) {
                return $this->permissionError();
            }
        }
        if (!$isThreadDetail) {
            $pendingSearch = false;
            $viewId = ' ';
            if ($view == 'pending') {
                $isSearch = true;
                if (!Phpfox::getUserParam('forum.can_approve_forum_post')) {
                    return $this->permissionError();
                }
                $pendingSearch = true;
                $this->search()->clearConditions();
                $this->search()->setCondition('AND fp.view_id = 1');
            } else {
                $viewId .= 'AND ft.view_id = 0';
            }
            $isTagSearch = false;
            $isModuleTagSearch = false;
            if ($parentModule !== null) {
                $this->search()->setCondition('AND ft.forum_id = 0 AND ft.group_id = ' . $parentModule['item_id'] . $viewId . ' AND ft.is_announcement = 0');
            } else {
                $this->search()->setCondition(($params['forum'] ? 'AND ft.forum_id = ' . $params['forum'] : '') . $viewId . ($pendingSearch ? '' : ' AND ft.is_announcement = 0 AND ft.group_id = 0'));
            }
            if (!empty($params['tag'])) {
                $isTagSearch = true;
                $isSearch = true;
                if ($parentModule) {
                    $isModuleTagSearch = true;
                    $this->search()->setCondition("AND ft.group_id = " . (int)$parentModule['item_id'] . " AND tag.tag_url = '" . $params['tag'] . "'");
                } else {
                    $this->search()->setCondition("AND ft.group_id = 0 AND tag.tag_url = '" . $params['tag'] . "'");
                }
            }
            if ($params['q']) {
                $isSearch = true;
                $this->search()->setCondition('AND (fp.title LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '" OR fpt.text LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['q'] . '%') . '")');
            }
            if ($params['author']) {
                $isSearch = true;
                $this->search()->setCondition('AND u.full_name LIKE "' . Phpfox::getLib('parse.input')->clean('%' . $params['author'] . '%') . '"');
            }

            $sortBy = $params['sort_type'];
            switch ($sort) {
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
                $this->search()->setCondition('AND fp.time_stamp >= ' . (PHPFOX_TIME - ($params['days_prune'] * 86400)));
            }
            list(, $items) = $this->postService->isTagSearch($isTagSearch)
                ->isSearch($isSearch)
                ->isAdvSearch($isSearch)
                ->isModuleSearch($isModuleTagSearch)
                ->isSubscribeSearch($view == 'subscribed')
                ->isNewSearch($view == 'new')
                ->get($this->search()->getConditions(), $sort, $params['page'], $params['limit']);
        } else {
            $items = $this->getPosts($params);
        }
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
        $id = $this->resolver->resolveId($params);
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
        $item = $this->database()->select('fp.*, ft.thank_id, ' . (Phpfox::getParam('core.allow_html') ? 'fpt.text_parsed' : 'fpt.text') . ' AS text, ' . Phpfox::getUserField() . ', u.joined, u.country_iso, uf.signature, uf.total_post')
            ->from(':forum_post', 'fp')
            ->join(':forum_post_text', 'fpt', 'fpt.post_id = fp.post_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id')
            ->join(Phpfox::getT('user_field'), 'uf', 'uf.user_id = fp.user_id')
            ->leftJoin(':forum_thank', 'ft', 'ft.post_id = fp.post_id AND ft.user_id =' . (int)Phpfox::getUserId())
            ->where('fp.post_id = ' . (int)$id)
            ->order('fp.time_stamp ASC')
            ->execute('getSlaveRow');
        if (!$item) {
            $this->notFoundError();
        }
        $thread = $this->threadService->getActualThread($item['thread_id']);

        if (!$thread) {
            return $this->notFoundError();
        }
        if ($item['view_id'] && !Phpfox::getUserParam('forum.can_approve_forum_post') && !$this->moderatorService->hasAccess($thread['forum_id'], 'approve_post')) {
            return $this->permissionError();
        }
        if ($thread['view_id'] != '0' && $thread['user_id'] != Phpfox::getUserId()) {
            if (!Phpfox::getUserParam('forum.can_approve_forum_thread') && !$this->moderatorService->hasAccess($thread['forum_id'],
                    'approve_thread')
            ) {
                return $this->notFoundError();
            }
        }
        if ($thread['forum_id'] && (!$this->forumService->hasAccess($thread['forum_id'], 'can_view_forum') || !$this->forumService->hasAccess($thread['forum_id'], 'can_view_thread_content'))) {
            return $this->permissionError();
        }
        $item['is_detail'] = true;
        $item['thread_title'] = $thread['title'];
        $thread['post_user_id'] = $item['user_id'];
        /** @var ForumPostResource $resource */
        $resource = $this->populateResource(ForumPostResource::class, $item);
        $threadResource = ForumThreadResource::populate($thread);
        $this->setHyperlinks($resource, $threadResource, true);
        $permissionByThread = $this->getAccessControl()->getPermissions($threadResource);
        $permissionByPost = $this->getAccessControl()->getPermissions($resource);
        unset($permissionByPost['can_add']);
        unset($permissionByPost['can_edit']);

        return $this->success($resource
            ->setExtra(array_merge($permissionByThread, $permissionByPost))
            ->lazyLoad(['user'])
            ->loadFeedParam()
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
            ->setDefined(['thread_id', 'id'])
            ->setAllowedTypes('thread_id', 'int', ['min' => 1])
            ->resolve($params)->getParameters();
        $editId = $params['id'];
        if (empty($editId) && empty($params['thread_id'])) {
            return $this->missingParamsError(['thread_id']);
        }
        $thread = null;
        if (!$editId) {
            $thread = $this->getThread($params['thread_id']);
            $this->denyAccessUnlessGranted(ForumPostAccessControl::ADD, $thread);
        }
        /** @var ForumPostForm $form */
        $form = $this->createForm(ForumPostForm::class, [
            'title'  => !$editId ? 'reply' : 'editing_post',
            'method' => $editId ? 'PUT' : 'POST',
        ]);
        /** @var ForumPostResource $post */
        $post = $this->loadResourceById($editId, true);

        if ($editId && empty($post)) {
            return $this->notFoundError();
        }

        if ($post) {
            $thread = $this->getThread($post->thread_id, $post->getAuthor()->getId());
            $form->setEditing(true);
            $form->setAction('mobile/forum-post/' . $editId);
            $this->denyAccessUnlessGranted(ForumPostAccessControl::EDIT, $thread);
            $post->setIsForm(true);
            $form->assignValues($post);
        } else {
            if (($iFlood = $this->getSetting()->getUserSetting('forum.forum_post_flood_control')) !== 0) {
                $aStartPostIds = $this->getStartPostIds();
                $sCond = !empty($aStartPostIds) ? ' AND post_id NOT IN (' . implode(',', $aStartPostIds) . ')' : '';
                $aFlood = [
                    'action' => 'last_post', // The SPAM action
                    'params' => [
                        'field'      => 'time_stamp', // The time stamp field
                        'table'      => Phpfox::getT('forum_post'), // Database table we plan to check
                        'condition'  => 'user_id = ' . $this->getUser()->getId() . $sCond, // Database WHERE query
                        'time_stamp' => $iFlood * 60 // Seconds);
                    ]
                ];
                // actually check if flooding
                if (Phpfox::getLib('spam')->check($aFlood)) {
                    return $this->error($this->getLocalization()->translate('posting_a_new_reply_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                }
            }
            $form->assignValues([
                'is_subscribed' => $thread->is_subscribed
            ]);
        }

        return $this->success($form->getFormStructure());
    }

    private function getThread($id, $postUserId = null)
    {
        if (!empty($id)) {
            $thread = NameResource::instance()->getApiServiceByResourceName(ForumThreadResource::RESOURCE_NAME)->loadResourceById($id);
            if ($postUserId !== null) {
                $thread['post_user_id'] = $postUserId;
            }
            if (empty($thread['thread_id'])) {
                return $this->notFoundError();
            }
            $thread = ForumThreadResource::populate($thread);
            return $thread;
        }
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        /** @var ForumPostForm $form */
        $form = $this->createForm(ForumPostForm::class);
        if ($form->isValid()) {
            $id = $this->processCreate($form->getValues());
            if (is_numeric($id)) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => ForumPostResource::populate([])->getResourceName(),
                    'module_name'   => 'forum'
                ]);
            } else if ($id === false && $this->getSetting()->getUserSetting('forum.approve_forum_post')) {
                //Pending approve
                return $this->success([], [], $this->getLocalization()->translate('your_post_has_successfully_been_added_however_it_is_pending_an_admins_approval_before_it_can_be_displayed_publicly'));
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processCreate($values)
    {
        $thread = $this->getThread($values['thread_id']);
        $this->denyAccessUnlessGranted(ForumPostAccessControl::ADD, $thread);
        if (!Phpfox::getService('ban')->checkAutomaticBan($values['text'])) {
            return $this->permissionError();
        }

        if (($iFlood = $this->getSetting()->getUserSetting('forum.forum_post_flood_control')) !== 0) {
            $aStartPostIds = $this->getStartPostIds();
            $sCond = !empty($aStartPostIds) ? ' AND post_id NOT IN (' . implode(',', $aStartPostIds) . ')' : '';
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp', // The time stamp field
                    'table'      => Phpfox::getT('forum_post'), // Database table we plan to check
                    'condition'  => 'user_id = ' . $this->getUser()->getId() . $sCond, // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ]
            ];
            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                return $this->error($this->getLocalization()->translate('posting_a_new_reply_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }
        $values['forum_id'] = $thread->forum_id;
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        return $this->postProcessService->add($values);
    }

    public function getStartPostIds()
    {
        $sCacheId = $this->cache()->set('forum_start_posts_ids');
        if (false === ($aIds = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('start_id')
                ->from(Phpfox::getT('forum_thread'))
                ->where('is_closed = 0')
                ->execute('getSlaveRows');
            $aIds = array_column($aRows, 'start_id');
            $this->cache()->save($sCacheId, $aIds);
        }
        return $aIds;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var ForumPostForm $form */
        $form = $this->createForm(ForumPostForm::class);
        $form->setEditing(true);
        $post = $this->loadResourceById($id);
        if (empty($post)) {
            return $this->notFoundError();
        }
        $thread = $this->getThread($post['thread_id'], $post['user_id']);
        $this->denyAccessUnlessGranted(ForumPostAccessControl::EDIT, $thread);

        if ($form->isValid() && ($values = $form->getValues())) {
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => ForumPostResource::populate([])->getResourceName(),
                    'module_name'   => 'forum'
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values)
    {
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        return $this->postProcessService->updateText($id, $values['text'], $this->getUser()->getId());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function patchUpdate($params)
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $post = $this->postService->getPost($params['id']);
        if (!$post || count($post) < 4) {
            return $this->notFoundError();
        }
        $canDelete = false;
        if ((int)$post['group_id'] > 0 && (Phpfox::isAppActive('Core_Pages') || Phpfox::isAppActive('PHPfox_Groups'))) {
            $sModule = Phpfox::getPagesType($post['group_id']);
            if (Phpfox::getService($sModule)->isAdmin($post['group_id'])) {
                $canDelete = true;
            }
        } else {
            if (($this->moderatorService->hasAccess($post['forum_id'],
                    'delete_post') || Phpfox::getService('user.auth')->hasAccess('forum_post', 'post_id',
                    $params['id'], 'forum.can_delete_own_post', 'forum.can_delete_other_posts'))
            ) {
                $canDelete = true;
            }
        }
        if ($canDelete && $this->postProcessService->delete($params['id'])) {
            return $this->success([], [], $this->getLocalization()->translate('post_deleted_successfully'));
        }
        return $this->permissionError();
    }


    /**
     * @param $id
     * @param $returnResource boolean
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $post = $this->postService->getForEdit((int)$id);
        if (empty($post['post_id'])) {
            return null;
        }
        if ($returnResource) {
            $post['is_detail'] = true;
            return ForumPostResource::populate($post);
        }
        return $post;
    }

    public function processRow($item)
    {
        /** @var ForumPostResource $resource */
        $resource = $this->populateResource(ForumPostResource::class, $item);
        $this->setHyperlinks($resource);
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->loadFeedParam()->toArray();
    }

    /**
     * @param      $params
     * @param null $postId
     *
     * @return array|int|string
     */
    private function getPosts($params, $postId = null)
    {
        $conditions = [];
        $thread = $this->database()->select('ft.thread_id, ft.time_stamp, ft.time_update, ft.group_id, ft.view_id, ft.forum_id, ft.is_closed, ft.user_id, ft.is_announcement, ft.order_id, ft.title_url, ft.time_update AS last_time_stamp, ft.title, fs.subscribe_id AS is_subscribed, ft.poll_id, f.forum_id, f.is_closed as forum_is_closed')
            ->from(':forum_thread', 'ft')
            ->leftjoin(':forum', 'f', 'f.forum_id = ft.forum_id')
            ->leftJoin(':forum_subscribe', 'fs',
                'fs.thread_id = ft.thread_id AND fs.user_id = ' . Phpfox::getUserId())
            ->where('ft.thread_id = ' . $params['thread'])
            ->execute('getSlaveRow');
        if (empty($thread['thread_id'])) {
            $this->notFoundError();
        }
        if ($thread['view_id'] != '0' && $thread['user_id'] != Phpfox::getUserId()) {
            if (!Phpfox::getUserParam('forum.can_approve_forum_thread') && !$this->moderatorService->hasAccess($thread['forum_id'],
                    'approve_thread')
            ) {
                $this->permissionError();
            }
        }
        $viewId = (Phpfox::getUserParam('forum.can_approve_forum_post') || $this->moderatorService->hasAccess($thread['forum_id'],
                'approve_post')) ? '' : ' AND fp.view_id = 0';
        $conditions[] = 'fp.thread_id = ' . $thread['thread_id'] . $viewId;
        if (!empty($postId)) {
            $conditions[] = 'AND fp.post_id = ' . (int)$postId;
        }
        if (!empty($blockedUserIds = $this->forumService->getBlockedUserIds())) {
            $conditions[] = 'AND (fp.user_id NOT IN (' . implode(',', $blockedUserIds) . ') AND fth.user_id NOT IN (' . implode(',', $blockedUserIds) . '))';
        }
        //Item in pages/group
        if ($thread['group_id'] || empty($thread['forum_id'])) {
            $thread['forum_is_closed'] = 0;
            $thread['forum_id'] = 0;
        }
        $cnt = $this->database()->select('COUNT(*)')
            ->from(':forum_post', 'fp')
            ->join(':forum_thread', 'fth', 'fth.thread_id = fp.thread_id')
            ->where($conditions)
            ->execute('getSlaveField');
        return $this->database()->select('fp.*, ft.thank_id, ' . (Phpfox::getParam('core.allow_html') ? 'fpt.text_parsed' : 'fpt.text') . ' AS text, ' . Phpfox::getUserField() . ', u.joined, u.country_iso, uf.signature, uf.total_post, ' . $thread['forum_is_closed'] . ' as forum_is_closed, ' . $thread['forum_id'] . ' as forum_id, ' . $thread['group_id'] . ' as group_id, \'' . $thread['title'] . '\' as thread_title, l.like_id AS is_liked')
            ->from(':forum_post', 'fp')
            ->join(':forum_thread', 'fth', 'fth.thread_id = fp.thread_id')
            ->join(':forum_post_text', 'fpt', 'fpt.post_id = fp.post_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = fp.user_id')->join(Phpfox::getT('user_field'), 'uf',
                'uf.user_id = fp.user_id')
            ->leftJoin(':forum_thank', 'ft', 'ft.post_id = fp.post_id AND ft.user_id =' . (int)Phpfox::getUserId())
            ->leftJoin(':like', 'l',
                'l.type_id = \'forum_post\' AND l.item_id = fp.post_id AND l.user_id = ' . Phpfox::getUserId())
            ->where($conditions)
            ->order('fp.time_stamp ASC')
            ->limit($params['page'], $params['limit'], $cnt)
            ->execute('getSlaveRows');
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
        $resource = $this->populateResource(ForumPostResource::class, $item);
        $resource->setViewMode(!empty($feed['is_detail']) ? ResourceBase::VIEW_DETAIL : ResourceBase::VIEW_LIST);
        return $resource->getFeedDisplay();
    }

    /**
     * @param FeedResource $feedResource
     */
    public function updateFeedResource(&$feedResource)
    {
        $feedResource->status = '';
    }
    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new ForumPostAccessControl($this->getSetting(), $this->getUser());

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
        $this->denyAccessUnlessGranted(ForumPostAccessControl::VIEW);
        /** @var ForumPostSearchForm $form */
        $form = $this->createForm(ForumPostSearchForm::class, [
            'title'  => 'search',
            'method' => 'GET',
            'action' => UrlUtility::makeApiUrl('forum-post')
        ]);
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

    private function setHyperlinks(ForumPostResource $resource, ForumThreadResource $thread = null, $includeLinks = false)
    {
        $self = [
            ForumPostAccessControl::VIEW   => $this->createHyperMediaLink(ForumPostAccessControl::VIEW, $resource,
                HyperLink::GET, 'forum-post/:id', ['id' => $resource->getId()]),
            ForumPostAccessControl::EDIT   => !empty($thread) ? $this->createHyperMediaLink(ForumPostAccessControl::EDIT, $thread,
                HyperLink::GET, 'forum-post/form/:id', ['id' => $resource->getId()]) : $this->createHyperMediaLink(null, $resource,
                HyperLink::GET, 'forum-post/form/:id', ['id' => $resource->getId()]),
            ForumPostAccessControl::DELETE => $this->createHyperMediaLink(ForumPostAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'forum-post/:id', ['id' => $resource->getId()])
        ];
        $resource->setSelf($self);
        if ($includeLinks) {
            $resource->setLinks([
                'likes' => $this->createHyperMediaLink(null, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getId(), 'item_type' => 'forum_post'])
            ]);
        }
    }

    function approve($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var ForumPostResource $item */
        $item = $this->loadResourceById($id, true);

        if (!$item) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(ForumPostAccessControl::APPROVE, $item);
        if ($this->postProcessService->approve($id)) {
            return $this->success([
                'is_pending' => false
            ], [], $this->getLocalization()->translate('post_successfully_approved'));
        }
        return $this->error();
    }

    function feature($params)
    {
        return null;
    }

    function sponsor($params)
    {
        return null;
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
                $this->denyAccessUnlessGranted(ForumPostAccessControl::APPROVE);
                foreach ($ids as $key => $id) {
                    $this->postProcessService->approve($id);
                }
                $data = array_merge($data, ['is_pending' => false]);
                $sMessage = $this->getLocalization()->translate('post_s_successfully_approved');
                break;
            case Screen::ACTION_DELETE_ITEMS:
                $this->denyAccessUnlessGranted(ForumPostAccessControl::DELETE);
                foreach ($ids as $key => $id) {
                    $item = $this->loadResourceById($id, true);
                    if (!$item) {
                        return $this->notFoundError();
                    }
                    $this->postProcessService->delete($id);
                }
                $sMessage = $this->getLocalization()->translate('post_s_successfully_deleted');
                break;
        }
        return $this->success($data, [], $sMessage);
    }

}