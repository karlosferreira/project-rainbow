<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Forums\Service\Forum;
use Apps\Core_Forums\Service\Process;
use Apps\Core_Forums\Service\Thread\Thread;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\ForumSubscribeResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

class ForumSubscribeApi extends AbstractResourceApi
{
    const ERROR_FORUM_NOT_FOUND = "Forum not found";

    /**
     * @var Forum
     */
    private $forumService;

    /**
     * @var Forum
     */
    private $subscribeService;

    /**
     * @var Thread
     */
    private $threadService;
    /**
     * @var Process
     */
    private $processService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->subscribeService = Phpfox::getService('forum.subscribe');
        $this->threadService = Phpfox::getService('forum.thread');
        $this->processService = Phpfox::getService('forum.subscribe.process');
        $this->userService = Phpfox::getService('user');
        $this->forumService = Phpfox::getService('forum');
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'thread', 'limit', 'page'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('thread', 'int')
            ->setRequired(['thread'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
        $thread = Phpfox::getService('forum.thread')->getActualThread($params['thread']);
        if (empty($thread['thread_id'])) {
            return $this->notFoundError();
        }
        if ($thread['forum_id'] && (!$this->forumService->hasAccess($thread['forum_id'], 'can_view_forum') || !$this->forumService->hasAccess($thread['forum_id'], 'can_view_thread_content'))) {
            return $this->permissionError();
        }
        $cnt = $this->database()->select('COUNT(*)')
            ->from(':forum_subscribe', 'fs')
            ->join(':forum_thread', 'ft', 'ft.thread_id = fs.thread_id')
            ->where('fs.thread_id = ' . (int)$params['thread'])
            ->execute('getField');
        $items = [];
        if ($cnt) {
            $items = $this->database()->select('fs.*, ft.forum_id, ft.group_id, ft.title, f.name AS forum_name')
                ->from(':forum_subscribe', 'fs')
                ->join(':forum_thread', 'ft', 'ft.thread_id = fs.thread_id')
                ->leftJoin(':forum', 'f', 'f.forum_id = ft.forum_id')
                ->where('fs.thread_id = ' . (int)$params['thread'])
                ->limit($params['page'], $params['limit'], $cnt)
                ->execute('getSlaveRows');
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
        return [];
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        $params = $this->resolver->setDefined(['thread_id'])
            ->setRequired(['thread_id'])
            ->setAllowedTypes('thread_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        $thread = NameResource::instance()->getApiServiceByResourceName(ForumThreadResource::RESOURCE_NAME)->loadResourceById($params['thread_id']);
        if (!$thread) {
            return $this->notFoundError();
        }
        $userId = $this->getUser()->getId();
        if ($thread['is_announcement'] || !$userId) {
            return $this->permissionError();
        }
        if ($this->processService->add($params['thread_id'], $userId)) {
            return $this->success([
                'id' => $params['thread_id']
            ]);
        }
        return $this->error($this->getErrorMessage());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        // TODO: Implement update() method.
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
        $subscribe = $this->database()->select('subscribe_id, user_id')->from(':forum_subscribe')->where('subscribe_id = ' . (int)$id)->execute('getSlaveRow');
        if (!$subscribe) {
            return $this->notFoundError();
        }
        if ($subscribe['user_id'] == Phpfox::getUserId() && $this->database()->delete(':forum_subscribe', 'subscribe_id = ' . (int)$id)) {
            return $this->success([], [], $this->getLocalization()->translate('thread_successfully_unsubscribe'));
        }
        return $this->permissionError();
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        // TODO: Implement form() method.
    }

    /**
     * @param $id
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        // TODO: Implement loadResourceById() method.
    }

    public function processRow($item)
    {
        return ForumSubscribeResource::populate($item)->lazyLoad(['user'])->toArray();
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }
}