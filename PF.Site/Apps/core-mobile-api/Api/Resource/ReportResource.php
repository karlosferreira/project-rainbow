<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


class ReportResource extends ResourceBase
{
    const RESOURCE_NAME = "report";
    public $resource_name = self::RESOURCE_NAME;

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'urls.base'     => 'mobile/report',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
            'list_view'     => [
                'item_view' => 'report',
            ],
        ]);
    }
}