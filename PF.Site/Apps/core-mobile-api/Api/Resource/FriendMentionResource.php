<?php

namespace Apps\Core_MobileApi\Api\Resource;


class FriendMentionResource extends ResourceBase
{
    const RESOURCE_NAME = "friend-mention";

    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'friend';

    public $item_type;
    public $item_detail;

    public function getId()
    {
        if ($this->item_type == 'user') {
            $this->id = isset($this->item_detail['user']['id']) ? $this->item_detail['user']['id'] : 0;
        } else {
            $this->id = isset($this->item_detail['id']) ? $this->item_detail['id'] : 0;
        }
        return $this->id;
    }

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'urls.base'     => 'mobile/friend/mentions',
            'can_filter'    => false,
            'can_sort'      => false,
            'fab_buttons'   => false,
            'can_search'    => false,
            'search_input'  => [
                'can_search' => false,
            ],
            'list_view'     => [
                'item_view'     => 'friend_mention',
                'apiUrl'        => 'mobile/friend/mentions',
            ],
        ]);
    }
}