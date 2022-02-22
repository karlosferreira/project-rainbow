<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


class UserInfoResource extends ResourceBase
{
    const RESOURCE_NAME = "user-info";
    public $resource_name = self::RESOURCE_NAME;

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'about_info',
            ],
            'urls.base'     => 'mobile/user/info',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false
        ]);
    }
}