<?php

namespace Apps\Core_MobileApi\Api\Security\Forum;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\ForumAnnouncementResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class ForumAnnouncementAccessControl extends ForumThreadAccessControl
{
    const ADD_POLL = "add_poll";
    const DELETE_POLL = "delete_poll";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::DELETE, self::EDIT, self::VIEW, self::APPROVE,
            self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR, self::CLOSE, self::COPY, self::MERGE,
            self::MOVE, self::STICK, self::ADD_POLL, self::DELETE_POLL
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (!$granted = parent::isGranted($permission, $resource)) {
            return false;
        }

        $isParentAdmin = false;
        /** @var ForumAnnouncementResource $resource * */
        if ($resource instanceof ForumAnnouncementResource) {
            $isParentAdmin = \Phpfox::getService('forum.thread')->isAdminOfParentItem($resource->id);
        }
        switch ($permission) {
            case self::EDIT:
                $granted = $resource && $resource->is_announcement;
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('forum.can_post_announcement') ||
                    ($resource && !$resource->is_closed && \Phpfox::getService('forum.moderate')->hasAccess($resource->id, 'post_announcement'));
                break;
            case self::ADD_POLL:
                $granted = $resource && !$resource->poll_id && $this->isGranted(self::EDIT, $resource) && $resource->forum_id > 0 && \Phpfox::isAppActive('Core_Polls') && $this->isGrantedSetting('poll.can_create_poll') && $this->isGrantedSetting('forum.can_add_poll_to_forum_thread');
                break;
            case self::DELETE_POLL:
                $granted = $resource && $resource->poll_id > 0 && $this->isGranted(self::EDIT, $resource) && \Phpfox::getService('user.auth')->hasAccess('poll', 'poll_id', $resource->poll_id, 'poll.poll_can_delete_own_polls', 'poll.poll_can_delete_others_polls');
                break;
        }

        // Check Pages/Group permission
        if ($granted && $this->appContext && !$isParentAdmin) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('forum.view_browse_forum');
                    break;
                case self::ADD:
                    $granted = $this->appContext->hasPermission('forum.share_forum');
                    break;
            }
        }

        return $granted;
    }

}