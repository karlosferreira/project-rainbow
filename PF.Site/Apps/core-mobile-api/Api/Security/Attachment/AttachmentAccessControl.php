<?php

namespace Apps\Core_MobileApi\Api\Security\Attachment;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class AttachmentAccessControl extends AccessControl
{
    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([]);
    }


    /**
     * @param              $permission
     * @param ResourceBase $resource
     *
     * @return bool|mixed
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if ($permission == self::IS_AUTHENTICATED || $permission == self::SYSTEM_ADMIN) {
            return parent::isGranted($permission);
        }
        if (!parent::isGranted($permission, $resource)) {
            return false;
        }

        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = true;
                break;
            case self::DELETE:
                $granted = $resource && ($this->service()->hasAccess($resource->getId(), 'delete_own_attachment', 'delete_user_attachment'));
                break;
        }

        return $granted;
    }

    /**
     * @return \Attachment_Service_Attachment
     */
    private function service()
    {
        return \Phpfox::getService('attachment');
    }
}