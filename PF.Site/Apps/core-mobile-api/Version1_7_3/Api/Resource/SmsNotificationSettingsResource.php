<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Version1_7_3\Api\Resource;


use Apps\Core_MobileApi\Api\Resource\ResourceBase;

class SmsNotificationSettingsResource extends ResourceBase
{
    const RESOURCE_NAME = "sms-notification-settings";
    public $resource_name = self::RESOURCE_NAME;

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'sms_notification_settings'
            ],
            'urls.base'     => 'mobile/account/sms-notification',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
        ]);
    }
}