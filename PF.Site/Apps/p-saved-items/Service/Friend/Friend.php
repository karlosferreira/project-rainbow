<?php

namespace Apps\P_SavedItems\Service\Friend;

use Phpfox;

class Friend extends \Phpfox_Service
{
    public function getFriendInCollection($iCollectionId)
    {
        $aFriends = db()->select('cf.collection_id, c.user_id as owner_id, ' . Phpfox::getUserField())
            ->from(':saved_collection_friend', 'cf')
            ->join(':saved_collection', 'c', 'c.collection_id = cf.collection_id')
            ->join(':user', 'u', 'u.user_id = cf.friend_id')
            ->where(['cf.collection_id' => $iCollectionId])
            ->executeRows();

        return $aFriends;
    }
}