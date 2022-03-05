<?php

namespace Apps\Core_MobileApi\Api\Security\Forum;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\ForumPostResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class ForumPostAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const APPROVE = "approve";
    const THANK = "thank";
    const QUOTE = "quote";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([self::ADD, self::DELETE, self::EDIT, self::VIEW, self::APPROVE, self::THANK, self::QUOTE]);
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
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }

        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('forum.can_view_forum');
                break;
            case self::ADD:
                /** @var $resource ForumThreadResource * */
                $granted = $resource && $resource->resource_name === ForumThreadResource::RESOURCE_NAME && !$resource->forum_is_closed && !$resource->is_closed && !$resource->view_id && !$resource->is_announcement
                    && $this->userContext->getId() && \Phpfox::getService('forum.thread')->canReplyOnThread($resource->id)
                    && (($this->isGrantedSetting('forum.can_reply_to_own_thread') && $isOwner) ||
                        \Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id, 'can_reply') ||
                        $this->isGrantedSetting('forum.can_reply_on_other_threads'));
                break;
            case self::EDIT:
                /** @var $resource ForumThreadResource * */
                $granted = ($resource &&
                        (!$resource->forum_is_closed && ($this->isGrantedSetting('forum.can_edit_own_post') && ((!empty($resource->post_user_id) && $resource->post_user_id == $this->userContext->getId()) || ($this->userContext->getId() == $resource->user->getId())))
                            || ($resource->resource_name === ForumThreadResource::RESOURCE_NAME && \Phpfox::getService('forum.moderate')->hasAccess($resource->forum_id, 'edit_post'))))
                    || $this->isGrantedSetting('forum.can_edit_other_posts');
                break;
            case self::DELETE:
                /** @var $resource ForumPostResource * */
                $granted = $this->isGrantedSetting('forum.can_delete_other_posts') || ($this->isGrantedSetting('forum.can_delete_own_post') && $isOwner);
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('forum.can_delete_own_post');
                break;
            case self::APPROVE:
                /** @var $resource ForumPostResource * */
                $granted = $this->isGrantedSetting('forum.can_approve_forum_post') && (!$resource || $resource->getIsPending());
                break;
            case self::THANK:
                /** @var $resource ForumPostResource * */
                $granted = $resource && !$resource->getIsPending() && !$isOwner && \Phpfox::getParam('forum.enable_thanks_on_posts') && $this->isGrantedSetting('forum.can_thank_on_forum_posts') && !$this->isBlocked($resource->getAuthor());
                break;
            case self::QUOTE:
            case self::LIKE:
                $granted = $resource && !$resource->getIsPending();
                break;
        }

        // Check Pages/Group permission
        if ($granted && $this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('forum.view_browse_forum');
                    break;
                case self::ADD:
                    $granted = $this->appContext->hasPermission('forum.share_forum');
            }
        }

        return $granted;
    }

}