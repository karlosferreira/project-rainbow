<?php

namespace Apps\Core_MobileApi\Api\Security\Forum;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\ForumResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Phpfox;


class ForumThreadAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const REPLY = "reply";
    const APPROVE = "approve";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = "purchase_sponsor";
    const CLOSE = "close";
    const MERGE = "merge";
    const MOVE = "move";
    const COPY = "copy";
    const STICK = "stick";
    const ADD_POLL = "add_poll";
    const DELETE_POLL = "delete_poll";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::DELETE, self::EDIT, self::VIEW, self::APPROVE,
            self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR, self::CLOSE, self::COPY, self::MERGE,
            self::MOVE, self::STICK, self::REPLY, self::ADD_POLL, self::DELETE_POLL
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (in_array($permission, [self::IS_AUTHENTICATED, self::SYSTEM_ADMIN])) {
            return parent::isGranted($permission);
        }
        if (in_array($permission, [self::SHARE, self::REPORT])) {
            return parent::isGranted($permission, $resource);
        }
        if (!parent::isGranted($permission, $resource)) {
            return false;
        }
        // Item Owner always able to do any permission
        $isOwner = false;
        if ($resource instanceof ResourceBase && !$resource instanceof ForumResource) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }
        $isParentAdmin = false;
        if ($resource instanceof ForumThreadResource) {
            $isParentAdmin = Phpfox::getService('forum.thread')->isAdminOfParentItem($resource->id);
        }
        $granted = false;
        /** @var $resource ForumThreadResource * */
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('forum.can_view_forum');
                break;
            case self::ADD:
                /** @var $resource ForumResource * */
                $granted = $this->isGrantedSetting('forum.can_add_new_thread') ||
                    ($resource && !$resource->is_closed && Phpfox::getService('forum.moderate')->hasAccess($resource->id,
                            'add_thread'));
                break;
            case self::REPLY:
                /** @var $resource ForumThreadResource * */
                $granted = $resource && !$resource->forum_is_closed && !$resource->is_closed && !$resource->view_id && !$resource->is_announcement
                    && $this->userContext->getId() && Phpfox::getService('forum.thread')->canReplyOnThread($resource->id)
                    && (($this->isGrantedSetting('forum.can_reply_to_own_thread') && $isOwner) ||
                        Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id, 'can_reply') ||
                        $this->isGrantedSetting('forum.can_reply_on_other_threads'));
                break;
            case self::EDIT:
                $granted = $resource && !$resource->forum_is_closed && (($this->isGrantedSetting('forum.can_edit_own_post') && $isOwner)
                        || $this->isGrantedSetting('forum.can_edit_other_posts')
                        || Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id, 'edit_post')
                        || $isParentAdmin);
                break;
            case self::DELETE:
                $granted = $this->isGrantedSetting('forum.can_delete_other_posts') || ($this->isGrantedSetting('forum.can_delete_own_post') && $isOwner)
                    || ($resource && Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id, 'delete_post'));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('forum.can_delete_own_post');
                break;
            case self::APPROVE:
                $granted = (!$resource || $resource->getIsPending()) && $this->isGrantedSetting('forum.can_approve_forum_thread');
                break;
            case self::STICK:
                $granted = $resource && !$resource->forum_is_closed && ($this->isGrantedSetting('forum.can_stick_thread') || Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id,
                            'post_sticky') || $isParentAdmin) && !$resource->view_id && !$resource->is_announcement;
                break;
            case self::CLOSE:
                $granted = $resource && !$resource->forum_is_closed && ($this->isGrantedSetting('forum.can_close_a_thread') || Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id,
                            'close_thread') || $isParentAdmin) && !$resource->view_id && !$resource->is_announcement;
                break;
            case self::MERGE:
                $granted = $resource && !$resource->forum_is_closed && ($this->isGrantedSetting('forum.can_merge_forum_threads') || Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id,
                            'move_thread') || $isParentAdmin) && !$resource->view_id && !$resource->is_announcement;
                break;
            case self::COPY:
                $granted = $resource && !$resource->forum_is_closed && !$resource->item_id && (($this->isGrantedSetting('forum.can_copy_forum_thread') && !$resource->view_id) || Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id,
                            'copy_thread'));
                break;
            case self::MOVE:
                $granted = $resource && !$resource->forum_is_closed && !$resource->item_id && (($this->isGrantedSetting('forum.can_move_forum_thread') && !$resource->view_id) || Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id,
                            'move_thread'));
                break;
            case self::SPONSOR:
                $granted = $resource && !$resource->forum_is_closed && $this->isGrantedSetting('forum.can_sponsor_thread') && !$resource->view_id && !$resource->is_announcement && Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = Phpfox::isAppActive('Core_BetterAds') && $resource && !$resource->forum_is_closed && !$resource->view_id && !$resource->is_announcement && !$this->isGrantedSetting('forum.can_sponsor_thread') && $this->isGrantedSetting('forum.can_purchase_sponsor') && $resource->getCanPurchaseSponsor();
                break;
            case self::ADD_POLL:
                $granted = $resource && !$resource->poll_id && $this->isGranted(self::EDIT, $resource) && $resource->forum_id > 0 && Phpfox::isAppActive('Core_Polls') && $this->isGrantedSetting('poll.can_create_poll') && $this->isGrantedSetting('forum.can_add_poll_to_forum_thread');
                break;
            case self::DELETE_POLL:
                $granted = Phpfox::isAppActive('Core_Polls') && $resource && $resource->poll_id > 0 && $this->isGranted(self::EDIT, $resource) && Phpfox::getService('user.auth')->hasAccess('poll', 'poll_id', $resource->poll_id, 'poll.poll_can_delete_own_polls', 'poll.poll_can_delete_others_polls');
                break;
            case self::LIKE:
                $granted = $resource && !$resource->getIsPending() && !$isOwner;
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