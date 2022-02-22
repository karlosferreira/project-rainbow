<?php

namespace Apps\Core_MobileApi\Api\Security\Forum;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\ForumResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class ForumAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const ADD_THREAD = "add_thread";
    const MANAGE_MODERATOR = 'manage_moderator';

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([self::ADD, self::DELETE, self::EDIT, self::VIEW, self::MANAGE_MODERATOR, self::ADD_THREAD]);
        $this->userContext = null;
    }

    /**
     * @inheritdoc
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (in_array($permission, [self::IS_AUTHENTICATED, self::SYSTEM_ADMIN])) {
            return parent::isGranted($permission);
        }
        if (in_array($permission, [self::LIKE, self::SHARE, self::REPORT])) {
            return parent::isGranted($permission, $resource);
        }

        if (!parent::isGranted($permission, $resource)) {
            return false;
        }
        $bHasResource = $resource !== null && $resource instanceof ResourceBase;
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('forum.can_view_forum') && (!$bHasResource || \Phpfox::getService('forum')->hasAccess($resource->getId(), 'can_view_forum'));
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('forum.can_add_new_forum');
                break;
            case self::ADD_THREAD:
                /** @var ForumResource $resource */
                $granted = $this->isGrantedSetting('forum.can_add_new_thread') ||
                    ($resource !== null && !$resource->is_closed && \Phpfox::getService('forum.moderate')->hasAccess($resource->id,
                            'add_thread'));
                break;
            case self::EDIT:
                $granted = $this->isGrantedSetting('forum.can_edit_forum');
                break;
            case self::MANAGE_MODERATOR:
                $granted = $this->isGrantedSetting('forum.can_manage_forum_moderators');
                break;
            case self::DELETE:
                $granted = $this->isGrantedSetting('forum.can_delete_forum');
                break;
        }

        // Check Pages/Group permission
        if ($granted && $this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('forum.view_browse_forum');
                    break;
            }
        }

        return $granted;
    }

}