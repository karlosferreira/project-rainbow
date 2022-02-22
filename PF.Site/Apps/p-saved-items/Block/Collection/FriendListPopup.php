<?php

namespace Apps\P_SavedItems\Block\Collection;

use Phpfox;

class FriendListPopup extends \Phpfox_Component
{
    public function process()
    {
        $iCollectionId = $this->request()->get('collection_id');
        $aFriendInCollection = [];
        $bIsOwner = false;
        if (empty($aCollection = Phpfox::getService('saveditems.collection')->getByFriend($iCollectionId)))
        {
            \Phpfox_Error::set(_p('saveditems_collection_not_found'));
        } else {
            $bIsOwner = $aCollection['user_id'] == Phpfox::getUserId();
            $aFriendInCollection = Phpfox::getService('saveditems.friend')->getFriendInCollection($iCollectionId);
        }

        $this->template()->assign([
           'aFriends' => $aFriendInCollection,
            'bIsOwner' => $bIsOwner,
        ]);
        return 'block';
    }
}