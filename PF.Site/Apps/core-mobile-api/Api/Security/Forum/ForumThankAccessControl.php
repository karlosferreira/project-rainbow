<?php

namespace Apps\Core_MobileApi\Api\Security\Forum;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class ForumThankAccessControl extends AccessControl
{
    const ADD = "add";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([self::ADD, self::DELETE]);
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
        // Item Owner always able to do any permission
        $isOwner = false;

        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }

        $granted = false;
        switch ($permission) {
            case self::ADD:
                $granted = \Phpfox::getParam('forum.enable_thanks_on_posts');
                break;
            case self::DELETE:
                $granted = $isOwner || $this->isGrantedSetting('forum.can_delete_thanks_by_other_users');
                break;
        }

        return $granted;
    }

}