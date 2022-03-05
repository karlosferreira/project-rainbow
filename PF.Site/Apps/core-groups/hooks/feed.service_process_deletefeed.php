<?php
defined('PHPFOX') or exit('NO DICE!');

if (Phpfox_Request::instance()->get('module') == 'groups' && !empty($aFeed['parent_user_id'])) {
    $check = Phpfox::getService('groups')->checkIfGroupUser($aFeed['parent_user_id']);
    if($check) {
        $aGroup = Phpfox::getService('groups')->getPage($aFeed['parent_user_id']);
        if (isset($aGroup['page_id']) && Phpfox::getService('groups')->isAdmin($aGroup)) {
            define('PHPFOX_FEED_CAN_DELETE', true);
        }
    }
}

if(in_array($sType, ['groups_photo', 'groups_cover_photo']) && isset($aFeed['user_id'])) {
    $groupUser = Phpfox::getService('user')->getUser($aFeed['user_id'], 'profile_page_id');
    $aGroup = Phpfox::getService('groups')->getPage($groupUser['profile_page_id']);
    if (isset($aGroup['page_id']) && Phpfox::getService('groups')->isAdmin($aGroup)) {
        define('PHPFOX_FEED_CAN_DELETE', true);
    }
}
