<?php

namespace Apps\Core_Comments\Controller;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class RepliesController extends Phpfox_Component
{
    public function process()
    {
        $iCommentId = $this->request()->get('comment_id');
        $sTypeId = $this->request()->get('comment_type_id');
        $iItemId = $this->request()->get('item_id');
        $isFeed = $this->request()->get('is_feed');
        $this->setParam([
            'sCommentTypeId'     => $sTypeId,
            'bIsLoadMoreReplies' => true,
            'iItemId'            => $iItemId,
            'iCommentId'         => $iCommentId,
            'iShownTotal'        => $this->request()->getInt('shown-total'),
            'iTotalReplies'      => $this->request()->getInt('total-replies'),
            'iTimeStamp'         => $this->request()->getInt('time-stamp'),
            'iMaxTime'           => $this->request()->getInt('max-time'),
            'aUser'              => $this->getParam('aUser'),
            'is_feed'            => $isFeed
        ]);

        Phpfox::getBlock('comment.more-replies');
        $sContent = ob_get_contents();
        ob_clean();

        $out = "var replies = " . json_encode(['html' => $sContent]) . "; ";
        $out .= "$('#js_comment_{$iCommentId}').addClass('has-replies');";
        $out .= "var oCommentContainer = $('#js_comment_children_holder_{$iCommentId}'); ";
        $out .= "var oReplyContainer = oCommentContainer.closest('.comment-container-reply');";
        $out .= "var oOldViewMore = oReplyContainer.find('.js_comment_view_more_replies_{$iCommentId}');";
        $out .= "if (oOldViewMore.length) { oOldViewMore.hide();oOldViewMore.find('.js_user_image').remove(); }";
        $out .= "var oViewLess = oReplyContainer.find('.comment-viewless');";
        $out .= "if (oViewLess.length) { oViewLess.remove(); }";
        $out .= "var oViewedMore = oReplyContainer.find('.comment-viewmore:not(.js_comment_view_more_replies_{$iCommentId})');";
        $out .= "if (oViewedMore.length) { oViewedMore.remove(); }";
        $out .= "var oAddedNew = oCommentContainer.find('.is_added_more');";
        $out .= "if(oAddedNew.length) { $(replies.html).insertBefore(oAddedNew); } else { oCommentContainer.append(replies.html); }";
        $out .= "var oViewMore = oCommentContainer.find('.js_view_more_reply');";
        $out .= "if (oViewMore.length) {oCommentContainer.closest('.comment-container-reply').find('.js_comment_view_more_reply_wrapper').append(oViewMore.html()); oViewMore.remove();}";
        $out .= "\$Core.Comment.hideLineThreeDot();";
        $out .= "\$Core.Comment.initCanvasForSticker('.core_comment_gif:not(.comment_built)');";
        $out .= "\$Core.loadInit();";
        $out .= "obj.remove();";

        header('Content-type: application/json');
        echo json_encode(['run' => $out]);
        exit;
    }
}