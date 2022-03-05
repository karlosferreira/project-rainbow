<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Version1_7_3\Api\Resource;


use Apps\Core_MobileApi\Api\Resource\ResourceBase;

class PushNotificationSettingsResource extends ResourceBase
{
    const RESOURCE_NAME = "push-notification-settings";
    public $resource_name = self::RESOURCE_NAME;

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'push_notification_settings'
            ],
            'urls.base'     => 'mobile/account/mobile-push-notification',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
        ]);
    }
}