<?php
defined('PHPFOX') or exit('NO DICE!');

if (Phpfox_Request::instance()->get('module') == 'pages' && !empty($aFeed['parent_user_id'])) {
    $check = Phpfox::getService('pages')->checkIfPageUser($aFeed['parent_user_id']);
    if($check) {
        $aPage = Phpfox::getService('pages')->getPage($aFeed['parent_user_id']);
        if (isset($aPage['page_id']) && Phpfox::getService('pages')->isAdmin($aPage)) {
            define('PHPFOX_FEED_CAN_DELETE', true);
        }
    }
}

if(in_array($sType, ['pages_photo', 'pages_cover_photo']) && isset($aFeed['user_id'])) {
    $pageUser = Phpfox::getService('user')->getUser($aFeed['user_id'], 'profile_page_id');
    $aPage = Phpfox::getService('pages')->getPage($pageUser['profile_page_id']);
    if (isset($aPage['page_id']) && Phpfox::getService('pages')->isAdmin($aPage)) {
        define('PHPFOX_FEED_CAN_DELETE', true);
    }
}
