<?php

namespace Apps\Core_MobileApi\Api\Resource;


class FriendFeedResource extends FriendResource
{
    //This resource to prevent conflict with FriendResource in Feed on App
    const RESOURCE_NAME = "friend-feed";

    public $resource_name = self::RESOURCE_NAME;

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'friend_feed',
            ],
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false
        ]);
    }
}