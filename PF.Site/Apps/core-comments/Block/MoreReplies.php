<?php

namespace Apps\Core_Comments\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class MoreReplies extends Phpfox_Component
{
    public function process()
    {
        $iCommentId = $this->getParam('iCommentId');
        if (empty($iCommentId)) {
            return false;
        }
        $iItemId = $this->getParam('iItemId');
        $sCommentTypeId = $this->getParam('sCommentTypeId');
        $iShownTotal = $this->getParam('iShownTotal');
        $iTotalReplies = $this->getParam('iTotalReplies');
        $iTimeStamp = $this->getParam('iTimeStamp');
        $iMaxTime = $this->getParam('iMaxTime');
        $isFeed = $this->getParam('is_feed');
        $iLimit = 10;
        $aComment = Phpfox::getService('comment')->getComment($iCommentId);
        if ($aComment['child_total'] > $iTotalReplies) {
            $iShownTotal = $iShownTotal + ($aComment['child_total'] - $iTotalReplies);
        } else if ($aComment['child_total'] < $iTotalReplies) {
            $iShownTotal = $iShownTotal - ($iTotalReplies - $aComment['child_total']);
        }
        if (!$iCommentId) {
            return false;
        }
        $aReplies = Phpfox::getService('comment')->loadMoreChild($iCommentId, $sCommentTypeId, $iItemId, $iTimeStamp, $iMaxTime, $iLimit);
        if (!$aReplies) {
            return false;
        }
        $iShownTotal = $iShownTotal + count($aReplies);
        $aUser = $this->getParam('aUser');
        if (!empty($aUser)) {
            $this->template()->assign([
                'aUser' => $aUser,
            ]);
        }

        $threadCommentTotalDisplay = $isFeed ? Phpfox::getParam('comment.comment_replies_show_on_activity_feeds') : Phpfox::getParam('comment.comment_replies_show_on_item_details');

        $this->template()->assign([
            'aComment'       => $aComment,
            'isFeed'         => $isFeed,
            'aReplies'       => $aReplies,
            'iShownTotal'    => $iShownTotal,
            'iMaxTime'       => $iMaxTime,
            'iLoadMoreTotal' => !setting('comment.comment_show_replies_on_comment') ? $iShownTotal : $iShownTotal - $threadCommentTotalDisplay
        ]);
        return 'block';
    }
}