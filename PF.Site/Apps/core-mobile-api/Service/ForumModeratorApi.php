<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Forums\Service\Forum;
use Apps\Core_Forums\Service\Moderate\Process;
use Apps\Core_Forums\Service\Thread\Thread;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\ForumModeratorResource;
use Apps\Core_MobileApi\Api\Resource\ForumResource;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

class ForumModeratorApi extends AbstractResourceApi
{
    const ERROR_FORUM_NOT_FOUND = "Forum not found";

    /**
     * @var Forum
     */
    private $forumService;

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
        $this->forumService = Phpfox::getService('forum');
        $this->threadService = Phpfox::getService('forum.thread');
        $this->processService = Phpfox::getService('forum.moderate.process');
        $this->userService = Phpfox::getService('user');
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'forum', 'limit', 'page'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('forum', 'int')
            ->setRequired(['forum'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $forum = NameResource::instance()->getApiServiceByResourceName(ForumResource::RESOURCE_NAME)->loadResourceById($params['forum']);
        if (!$forum) {
            return $this->notFoundError();
        }
        if (!Phpfox::getUserParam('forum.can_view_forum') && !Phpfox::isAdmin()) {
            return $this->permissionError();
        }
        $cnt = $this->database()->select('COUNT(*)')
            ->from(':forum_moderator', 'fm')
            ->join(':forum', 'f', 'f.forum_id = fm.forum_id')
            ->join(':user', 'u', 'fm.user_id = u.user_id')
            ->where('fm.forum_id = ' . $params['forum'])
            ->execute('getField');
        $items = [];
        if ($cnt) {
            $items = $this->database()->select('fm.*')
                ->from(':forum_moderator', 'fm')
                ->join(':forum', 'f', 'f.forum_id = fm.forum_id')
                ->join(':user', 'u', 'fm.user_id = u.user_id')
                ->where('fm.forum_id = ' . $params['forum'])
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
        $params = $this->resolver->setDefined(['forum_id', 'user_id'])
            ->setRequired(['forum_id', 'user_id'])
            ->setAllowedTypes('forum_id', 'int', ['min' => 1])
            ->setAllowedTypes('user_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        if (!$this->getUser()->getId() || !$this->getAccessControl()->isGrantedSetting('admincp.has_admin_access') || !$this->getAccessControl()->isGrantedSetting('forum.can_manage_forum_moderators')) {
            return $this->permissionError();
        }
        $forum = NameResource::instance()->getApiServiceByResourceName(ForumResource::RESOURCE_NAME)->loadResourceById($params['forum_id']);
        if (!$forum) {
            return $this->notFoundError();
        }
        $vals = [
            'forum'   => $params['forum_id'],
            'user_id' => $params['user_id'],
            'param'   => [] // Need to implement permissions.
        ];
        if ($this->processService->add($vals)) {
            return $this->success([
                'id' => $params['forum_id']
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
        $moderator = $this->database()->select('moderator_id')->from(':forum_moderator')->where('moderator_id = ' . (int)$id)->execute('getSlaveRow');
        if (!$moderator) {
            return $this->notFoundError();
        }
        if (Phpfox::getUserParam('forum.can_manage_forum_moderators')) {
            Phpfox::getService('forum.moderate.process')->delete($id);
            return $this->success([], [], $this->getLocalization()->translate('moderator_deleted_successfully'));
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
        return ForumModeratorResource::populate($item)->setExtra($this->permission($item))->lazyLoad(['user'])->toArray();
    }

    /**
     * @param $item
     *
     * @return array
     */
    public function permission($item)
    {
        $access = $this->database()->select('var_name')
            ->from(':forum_moderator_access')
            ->where('moderator_id = ' . $item['moderator_id'])
            ->execute('getSlaveRows');
        $permission = null;
        if ($access) {
            foreach ($access as $ac) {
                $permission[$ac['var_name']] = true;
            }
        }
        return $permission;
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