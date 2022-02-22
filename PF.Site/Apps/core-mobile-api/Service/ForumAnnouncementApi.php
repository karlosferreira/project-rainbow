<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Forums\Service\Forum;
use Apps\Core_Forums\Service\Thread\Process;
use Apps\Core_Forums\Service\Thread\Thread;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Forum\ForumAnnouncementForm;
use Apps\Core_MobileApi\Api\Resource\ForumAnnouncementResource;
use Apps\Core_MobileApi\Api\Resource\ForumResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Forum\ForumAnnouncementAccessControl;
use Phpfox;

class ForumAnnouncementApi extends AbstractResourceApi
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

    public function __construct()
    {
        parent::__construct();
        $this->forumService = Phpfox::getService('forum');
        $this->threadService = Phpfox::getService('forum.thread');
        $this->processService = Phpfox::getService('forum.thread.process');
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
            'forum', 'module_id', 'item_id'
        ])
            ->setAllowedTypes('item_id', 'int')
            ->setAllowedTypes('forum', 'int')
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
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
        } else {
            if (empty($params['forum'])) {
                return $this->missingParamsError(['forum']);
            } else {
                $item = $this->database()->select('*')
                    ->from(':forum')
                    ->where('forum_id = ' . (int)$params['forum'])
                    ->execute('getSlaveRow');
                if (!$item) {
                    return $this->notFoundError();
                }
                if (!Phpfox::getService('forum')->hasAccess($item['forum_id'], 'can_view_forum')) {
                    return $this->permissionError();
                }
            }
        }
        if ($parentModule === null) {
            $items = $this->threadService->getAnnoucements($params['forum']);
        } else {
            $items = $this->threadService->getAnnoucements(null, $parentModule['item_id']);
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
        $conditions = 'ft.thread_id = ' . $id . '';
        list(, $item) = Phpfox::getService('forum.thread')->getThread($conditions, [],
            'fp.time_stamp ASC');
        if (!$item || !$item['is_announcement']) {
            return $this->notFoundError();
        }
        $item['is_detail'] = true;
        /** @var ForumAnnouncementResource $resource */
        $resource = $this->populateResource(ForumAnnouncementResource::class, $item);

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
        $this->denyAccessUnlessGranted(ForumAnnouncementAccessControl::ADD, $forum);
        /** @var ForumAnnouncementForm $form */
        $form = $this->createForm(ForumAnnouncementForm::class, [
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
            $this->denyAccessUnlessGranted(ForumAnnouncementAccessControl::EDIT, $thread);
            $form->assignValues($thread);
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
        /** @var ForumAnnouncementForm $form */
        $form = $this->createForm(ForumAnnouncementForm::class);
        if ($form->isValid()) {
            $values = $form->getValues();
            $id = $this->processCreate($values);
            if ($id) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => ForumAnnouncementResource::populate([])->getResourceName(),
                    'module_name'   => 'forum'
                ]);
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
        $forum = $this->getForum($values['forum_id']);
        $this->denyAccessUnlessGranted(ForumAnnouncementAccessControl::ADD, $forum);
        $callback = false;
        if (!empty($values['item_id']) && !empty($values['module_id'])) {
            $callback = Phpfox::hasCallback($values['module_id'], 'addForum')
                ? Phpfox::callback($values['module_id'] . '.addForum', $values['item_id']) : false;
            if (!$callback) {
                return $this->error($this->getLocalization()->translate('cannot_find_the_parent_item'));
            }
        }
        $values['type_id'] = 'announcement';
        $values['announcement_forum_id'] = $values['forum_id'];
        if (isset($values['tags'])) {
            $values['tag_list'] = $values['tags'];
            unset($values['tags']);
        }
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
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
        /** @var ForumAnnouncementForm $form */
        $form = $this->createForm(ForumAnnouncementForm::class);
        $thread = $this->loadResourceById($id, true);
        if (empty($thread)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(ForumAnnouncementAccessControl::EDIT, $thread);

        if ($form->isValid() && ($values = $form->getValues())) {
            $values['user_id'] = $thread->getAuthor()->getId();
            $values['post_id'] = $thread->start_id;
            $values['forum_id'] = $thread->forum_id;
            $success = $this->processUpdate($id, $values);
            if ($success) {
                return $this->success([
                    'id'            => $id,
                    'resource_name' => ForumAnnouncementResource::populate([])->getResourceName(),
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
        if (isset($values['tags'])) {
            $values['tag_list'] = $values['tags'];
            unset($values['tags']);
        }
        $values['type_id'] = 'announcement';
        if (!empty($values['attachment'])) {
            $values['attachment'] = implode(",", $values['attachment']);
        }
        $values['announcement_forum_id'] = $values['forum_id'];
        return $this->processService->update($id, $values['user_id'], $values);
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
        if (!$item || count($item) <= 1 || !$item['is_announcement']) {
            return $this->notFoundError();
        }
        $canDelete = false;
        if ((int)$item['group_id'] > 0) {
            if ((Phpfox::getUserParam('forum.can_delete_own_post') && $item['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_delete_other_posts') || $this->threadService->isAdminOfParentItem($item['thread_id'])) {
                $canDelete = true;
            }
        } else {
            if ((Phpfox::getService('forum.moderate')->hasAccess($item['forum_id'],
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

    /**
     * @param $id
     * @param $returnResource boolean
     *
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $thread = $this->threadService->getForEdit($id);
        if (empty($thread['thread_id'])) {
            return null;
        }
        if ($returnResource) {
            $thread['is_detail'] = true;
            return ForumAnnouncementResource::populate($thread);
        }
        return $thread;
    }

    public function processRow($item)
    {
        /** @var ForumAnnouncementResource $resource */
        $resource = $this->populateResource(ForumAnnouncementResource::class, $item);
        $this->setHyperlinks($resource);
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new ForumAnnouncementAccessControl($this->getSetting(), $this->getUser());

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

    private function setHyperlinks(ForumAnnouncementResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            ForumAnnouncementAccessControl::VIEW   => $this->createHyperMediaLink(ForumAnnouncementAccessControl::VIEW, $resource,
                HyperLink::GET, 'forum-announcement/:id', ['id' => $resource->getId()]),
            ForumAnnouncementAccessControl::DELETE => $this->createHyperMediaLink(ForumAnnouncementAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'forum-announcement/:id', ['id' => $resource->getId()]),
            ForumAnnouncementAccessControl::EDIT   => $this->createHyperMediaLink(ForumAnnouncementAccessControl::EDIT, $resource,
                HyperLink::GET, 'forum-announcement/form/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes' => $this->createHyperMediaLink(null, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getPostStarter()['id'], 'item_type' => 'forum_post']),
            ]);
        }
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