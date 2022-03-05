<?php

namespace Apps\Core_MobileApi\Api\Security\ActivityPoint;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\BlogResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class ActivityPointAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([self::ADD, self::EDIT]);
    }

    /**
     * @inheritdoc
     *
     * @param $resource BlogResource
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

        $isOwner = false;
        // Item Owner always able to do any permission
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }

        /** @var BlogResource $resource */
        $granted = false;
        switch ($permission) {
            case self::ADD:
                $granted = $this->isGrantedSetting('activitypoint.can_purchase_points');
                break;
        }

        return $granted;
    }

}