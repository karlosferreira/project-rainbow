<?php

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class FriendListItemResource extends FriendResource
{
    const RESOURCE_NAME = "friend-list-item";
    public $resource_name = self::RESOURCE_NAME;

    public $module_name = 'friend';
    public $list_id;

    public function getId()
    {
        $this->id = $this->list_id . ':' . $this->friend_user_id;
        return $this->id;
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();

        return self::createSettingForResource([
            'urls.base'     => 'mobile/friend/list/item',
            'resource_name' => $this->resource_name,
            'can_filter'    => false,
            'can_sort'      => false,
            'fab_buttons'   => false,
            'can_add'       => false,
            'list_view'     => [
                'item_view'     => 'friend_list_item',
                'noItemMessage'   => [
                    'image'     => $this->getAppImage('no-friend'),
                    'label'     => $l->translate('no_friends_found'),
                    'sub_label' => $l->translate('start_adding_new_friends'),
                    'action'    => [
                        'module_name' => 'friend',
                        'resource_name' => 'friend_list',
                        'value'       => '@friend/add-to-list',
                        'label'       => $l->translate('add_friend'),
                        'use_query' => [
                            'item_id' => ':list_id',
                            'item_type' => 'friend',
                            'for_friend_list' => true
                        ]
                    ]
                ],
            ],
            'action_menu'   => [
                ['value' => 'friend/unfriend', 'label' => $l->translate('unfriend'), 'style' => 'danger', 'show' => 'friendship==1'],
                ['value' => 'friend/cancel_request', 'label' => $l->translate('cancel_request'), 'style' => 'danger', 'show' => 'friendship==3'],
                ['value' => 'friend/accept_request', 'label' => $l->translate('accept_friend_request'), 'style' => 'danger', 'show' => 'friendship==2'],
                ['value' => Screen::ACTION_CHAT_WITH, 'label' => $l->translate('send_message'), 'show' => 'friendship==1'],
                ['value' => Screen::ACTION_REPORT_ITEM, 'label' => $l->translate('report_this_user'), 'show' => '!is_owner', 'acl' => 'can_report'],
            ],
            'search_input'  => [
                'placeholder' => $l->translate('search_friends_dot_dot_dot'),
            ],
        ]);
    }

    public function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('list_id', ['type' => ResourceMetadata::INTEGER]);
    }
}