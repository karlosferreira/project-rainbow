<?php
if (isset($aOut['comments']) && count($aOut['comments'])) {
    foreach ($aOut['comments'] as $key => $aComment) {
        $aOut['comments'][$key]['extra_data'] = Phpfox::getService('comment')->getExtraByComment($aComment['comment_id']);
        $aOut['comments'][$key]['is_hidden'] = Phpfox::getService('comment')->checkHiddenComment($aComment['comment_id'], Phpfox::getUserId());
        $aOut['comments'][$key]['total_hidden'] = 1;
        $aOut['comments'][$key]['hide_ids'] = $aComment['comment_id'];
        $aOut['comments'][$key]['hide_this'] = $aOut['comments'][$key]['is_hidden'];
        if ($key && $aOut['comments'][$key - 1]['is_hidden'] && $aOut['comments'][$key]['is_hidden']) {
            $aOut['comments'][$key - 1]['hide_this'] = false;
            $aOut['comments'][$key]['hide_ids'] = $aOut['comments'][$key - 1]['hide_ids'] . ',' . $aComment['comment_id'];
            $aOut['comments'][$key]['total_hidden'] = $aOut['comments'][$key - 1]['total_hidden'] + 1;
        }
        if (isset($aComment['children']['comments']) && count($aComment['children']['comments'])) {
            foreach ($aComment['children']['comments'] as $ckey => $aChild) {
                $aOut['comments'][$key]['children']['comments'][$ckey]['extra_data'] = Phpfox::getService('comment')->getExtraByComment($aChild['comment_id']);
                $aOut['comments'][$key]['children']['comments'][$ckey]['is_hidden'] = Phpfox::getService('comment')->checkHiddenComment($aChild['comment_id'], Phpfox::getUserId());;
                $aOut['comments'][$key]['children']['comments'][$ckey]['total_hidden'] = 1;
                $aOut['comments'][$key]['children']['comments'][$ckey]['hide_ids'] = $aChild['comment_id'];
                $aOut['comments'][$key]['children']['comments'][$ckey]['hide_this'] = $aOut['comments'][$key]['children']['comments'][$ckey]['is_hidden'];
                if ($ckey && $aOut['comments'][$key]['children']['comments'][$ckey - 1]['is_hidden'] && $aOut['comments'][$key]['children']['comments'][$ckey]['is_hidden']) {
                    $aOut['comments'][$key]['children']['comments'][$ckey - 1]['hide_this'] = false;
                    $aOut['comments'][$key]['children']['comments'][$ckey]['hide_ids'] = $aOut['comments'][$key]['children']['comments'][$ckey - 1]['hide_ids'] . ',' . $aChild['comment_id'];
                    $aOut['comments'][$key]['children']['comments'][$ckey]['total_hidden'] = $aOut['comments'][$key]['children']['comments'][$ckey - 1]['total_hidden'] + 1;
                }
            }
        }
        if (!setting('comment.comment_show_replies_on_comment')) {
            $aOut['comments'][$key]['last_reply'] = Phpfox::getService('comment')->getLastChild($aComment['comment_id'], $aComment['type_id'], $aComment['item_id']);
        }
    }
}