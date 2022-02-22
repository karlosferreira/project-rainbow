<?php
defined('PHPFOX') or exit('NO DICE!');

if (count($aFriends)) {
    if ($this->getParam('friend_module_id') == 'marketplace') {
        $iListingId = $this->getParam('friend_item_id');
        $aListing = Phpfox::getService('marketplace')->getForEdit($iListingId);
        if ($aListing['module_id'] == 'groups') {
            $iGroupRegMethod = db()->select('reg_method')
                ->from(':pages')
                ->where(['page_id' => $aListing['item_id'], 'item_type' => 1])
                ->executeField();

            if ((int)$iGroupRegMethod != 0) {
                list(, $aMembers) = Phpfox::getService('groups')->getMembers($aListing['item_id']);
                $aMemberIds = [];
                foreach  ($aMembers as $aMember) {
                    $aMemberIds[] = $aMember['user_id'];
                }
                foreach ($aFriends as $iKey => $aFriend) {
                    if (!in_array($aFriend['user_id'], $aMemberIds)) {
                        unset($aFriends[$iKey]);
                    }
                }
            }
        }
    }
}
