<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


class EmailNotificationSettingsResource extends ResourceBase
{
    const RESOURCE_NAME = "notification-settings";
    public $resource_name = self::RESOURCE_NAME;

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'email_notification_settings'
            ],
            'urls.base'     => 'mobile/account/email-notification',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
        ]);
    }
}