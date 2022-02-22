<?php
$sData .= '<script>var pf_im_site_title = "' . Phpfox::getParam('core.site_title') . ' - ' . _p('messengers') . '"; var ban_filters = []; var ban_users = []; var pf_minimise_chat_dock = ' . (int)Phpfox::getParam('im.pf_im_minimise_chat_dock') . ';';
// bring ban filters word to fe
$aFilters = Phpfox::getService('ban')->getFilters('word');
$pf_im_chat_server = setting('pf_im_chat_server', 'nodejs');

if (is_array($aFilters)) {
    foreach ($aFilters as $aFilter) {
        $sData .= "ban_filters['$aFilter[find_value]'] = '" . html_entity_decode($aFilter['replacement']) . "';";
        $aUserGroupsAffected = $aFilter['user_groups_affected'];
        if (is_array($aUserGroupsAffected) && !empty($aUserGroupsAffected)) {
            foreach ($aUserGroupsAffected as $aUserGroup) {
                if ($aUserGroup['user_group_id'] == Phpfox::getUserBy('user_group_id')) {
                    if ($aFilter['return_user_group'] !== null) {
                        $sData .= "ban_users['$aFilter[find_value]'] = '".$aFilter['ban_id']."';";
                    }
                    break;
                }
            }
        }
    }
}
$sData .= 'var global_update_time ="' . setting('core.global_update_time') . '";';

if($pf_im_chat_server == 'nodejs') {
// generate token
    if (!defined('PHPFOX_IM_TOKEN') || !PHPFOX_IM_TOKEN) {
        if (setting('pf_im_node_server_key')) {
            date_default_timezone_set("UTC");
            $imToken = md5(strtotime('today midnight') . setting('pf_im_node_server_key'));
        } else {
            $imToken = '';
        }
    } else {
        $imToken = PHPFOX_IM_TOKEN;
        $sData .= 'var pf_im_using_host = true;';
    }
    $sData .= 'var pf_im_token ="' . $imToken . '";';
    $sData .= 'var pf_im_node_server ="' . setting('pf_im_node_server') . '";';
// end generate token
}

// check blocked users
$aBlockedUsers = Phpfox::getService('user.block')->get(null, true);
if (!empty($aBlockedUsers)) {
    $sData .= 'var pf_im_blocked_users = [' . implode(',', $aBlockedUsers) . '];';
}

// get delete message time
if (setting('pf_time_to_delete_message')) {
    $sData .= 'var pf_time_to_delete_message = ' . setting('pf_time_to_delete_message') * 86400000 . ';';
}

// get custom sound
if (storage()->get('core-im/sound')) {
    $aCusSound = (array)storage()->get('core-im/sound')->value;
    if ($aCusSound['option'] === 'custom' && $aCusSound['custom_file']) {
        $sData .= 'var pf_im_custom_sound = "' . $aCusSound['custom_file'] . '";';
    }
}

// check module Attachment enable
if (Phpfox::isModule('attachment')) {
    $sData .= 'var pf_im_attachment_enable = true;';
    // get attachment file types
    $sData .= 'var pf_im_attachment_types = "' . implode(', ', \Phpfox::getService('attachment.type')->getTypes()) . '";';
}

// check App Twemoji
if (Phpfox::isApps('PHPfox_Twemoji_Awesome')) {
    $sData .= 'var pf_im_twemoji_enable = true;';
}

$sData .= 'var pf_im_chat_server ="' . $pf_im_chat_server . '";';
if($pf_im_chat_server == 'firebase') {
    $sData .= 'var pf_im_algolia_app_id = "' . setting('pf_im_algolia_app_id') . '";';
    $sData .= 'var pf_im_algolia_api_key = "' . setting('pf_im_algolia_api_key') . '";';
    //Support mobile push
    $sData .= 'var pf_im_firebase_server_key ="'.setting('mobile.mobile_firebase_server_key').'";';
    $sData .= 'var pf_im_firebase_sender_id ="'.setting('mobile.mobile_firebase_sender_id').'";';
}
$sData .= '</script>';

if($pf_im_chat_server == 'firebase') {
    $fireBaseConfig = setting('pf_firebase_auth_code_snippet');
    if ($fireBaseConfig && preg_match('/firebaseConfig/', $fireBaseConfig)) {
        $sData .= '<script>' . $fireBaseConfig . '</script>';
        if (strpos($sData, 'firebase-app.js') === false) {
            $sData .= '<script src="https://www.gstatic.com/firebasejs/6.0.2/firebase-app.js"></script>';
        }
        if (strpos($sData, 'firebase-auth.js') === false) {
            $sData .= '<script src="https://www.gstatic.com/firebasejs/6.0.2/firebase-auth.js"></script>';
        }
        if (strpos($sData, 'firebase-firestore.js') === false) {
            $sData .= '<script src="https://www.gstatic.com/firebasejs/6.0.2/firebase-firestore.js"></script>';
        }
        $sData .= '<script src="https://cdn.jsdelivr.net/algoliasearch/3/algoliasearch.min.js"></script> ';
    }
    $sData .= '<script>var firebasePassword = "' . md5(Phpfox::getUserId() . Phpfox::getParam('core.salt')) . '";</script>';
}
//b5395a778c6a5b77ab1240765a64cc1a