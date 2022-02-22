<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


class ItemPrivacySettingsResource extends ResourceBase
{
    const RESOURCE_NAME = "item-privacy-settings";
    public $resource_name = self::RESOURCE_NAME;

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'item_privacy_settings'
            ],
            'urls.base'     => 'mobile/account/item-privacy',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
        ]);
    }
}