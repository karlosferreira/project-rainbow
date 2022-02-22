<?php

namespace Apps\P_SavedItems\Service\Friend;

use Phpfox;

class Process extends \Phpfox_Service
{
    public function removeFriend($iFriendId, $iCollectionId)
    {
        //delete notification
        Phpfox::getService('notification.process')->delete('saveditems_collection_addfriend', $iCollectionId, $iFriendId);

        return db()->delete(':saved_collection_friend', 'friend_id = '. $iFriendId .  ' AND collection_id = ' . $iCollectionId);
    }
}