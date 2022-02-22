<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


class UserStatisticResource extends ResourceBase
{
    const RESOURCE_NAME = "user-statistic";
    public $resource_name = self::RESOURCE_NAME;

    public $module_name = 'user';

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'detail_view' => [
                'apiUrl' => 'mobile/user-statistic/:id'
            ],
            'urls.base'     => 'mobile/user-statistic',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false
        ]);
    }
}