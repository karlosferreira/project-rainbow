<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


class BlockedUserResource extends UserResource
{
    const RESOURCE_NAME = "blocked-user";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = 'user_id';

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'urls.base'     => 'mobile/account/blocked-user',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
            'list_view'     => [
                'item_view'       => 'blocked_user',
                'noItemMessage'   => [
                    'image' => $this->getAppImage('no-member'),
                    'label' => $l->translate('no_blocked_members'),
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
            ],
        ]);
    }
}