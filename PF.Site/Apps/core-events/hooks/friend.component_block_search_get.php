<?php
defined('PHPFOX') or exit('NO DICE!');

if (count($aFriends)) {
    $iEventId = $this->getParam('friend_item_id');
    $aEvent = Phpfox::getService('event')->getEventSimple($iEventId);
    if ($aEvent['module_id'] == 'groups') {
        $iGroupRegMethod = db()->select('reg_method')
            ->from(':pages')
            ->where(['page_id' => $aEvent['item_id'], 'item_type' => 1])
            ->executeField();

        if ((int)$iGroupRegMethod != 0) {
            list(, $aMembers) = Phpfox::getService('groups')->getMembers($aEvent['item_id']);
            foreach ($aFriends as $iKey => $aFriend) {
                if (!array_search($aFriend['user_id'], array_column($aMembers, 'user_id'))) {
                    unset($aFriends[$iKey]);
                }
            }
        }
    }
}
