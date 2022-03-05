<?php

if ($aOut['type_id'] == 'event' || $aOut['parent_module_id'] == 'event') {
    $iEventId = $aOut['item_id'];
    $iUserId = 0;
    $privacy = 0;

    if ($aOut['parent_module_id'] == 'event') {
        $iEventId = $aOut['parent_feed_id'];
        $aEvent = Phpfox::getService('event')->getEventSimple($iEventId);
        if (!empty($aEvent)) {
            $privacy = $aEvent['privacy'];
            $iUserId = $aEvent['user_id'];
        } else {
            $aOut = [];
        }
    } else {
        $privacy = $aOut['custom_data_cache']['privacy'];
        $iUserId = $aOut['custom_data_cache']['user_id'];
    }

    if (!empty($aOut) && $privacy == 5) {
        if (!Phpfox::getService('event')->isInvitedByOwner($iEventId, $iUserId, Phpfox::getUserId())) {
            $aOut = [];
        }
    }
}