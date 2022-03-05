<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


class ActivityPointResource extends ResourceBase
{
    const RESOURCE_NAME = "activitypoint";
    public $resource_name = self::RESOURCE_NAME;

    public $module_name = 'activitypoint';

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'activity_point',
            ],
            'urls.base'     => 'mobile/activitypoint',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false
        ]);
    }

    public function getUrlMapping($url, $queryArray)
    {
        $result = $url;
        switch ($url) {
            case 'activitypoint':
                $result = [
                    'routeName' => 'viewItemActivityPoint',
                    'params'    => [
                        'module_name'   => $this->module_name,
                        'resource_name' => $this->resource_name,
                        'id'            => (int)\Phpfox::getUserId()
                    ]
                ];
                break;
        }
        return $result;
    }
}