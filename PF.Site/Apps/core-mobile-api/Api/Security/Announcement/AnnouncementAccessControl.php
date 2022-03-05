<?php

namespace Apps\Core_MobileApi\Api\Security\Announcement;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\AnnouncementResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class AnnouncementAccessControl extends AccessControl
{

    const CLOSE = 'close';

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);
        $this->supports = $this->mergePermissions([self::CLOSE, self::VIEW]);
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
        /** @var AnnouncementResource $resource */
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('announcement.can_view_announcements');
                break;
            case self::CLOSE:
                $granted = $resource && $resource->can_be_closed && $this->isGrantedSetting('announcement.can_close_announcement');
                break;
        }

        return $granted;
    }
}