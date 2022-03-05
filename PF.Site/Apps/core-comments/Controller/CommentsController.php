<?php

namespace Apps\Core_Comments\Controller;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;


class CommentsController extends Phpfox_Component
{
    public function process()
    {
        $aFeed = [
            'type_id' => $this->request()->get('type'),
            'item_id' => $this->request()->get('id'),
            'total_comment' => $this->request()->get('total-comment')
        ];
        $iShownTotal = $this->request()->getInt('shown-total');
        $this->setParam('aFeed', array_merge($aFeed, ['feed_display' => 'view']));
        $this->setParam([
            'iTimeStamp'         => $this->request()->get('time-stamp'),
            'bIsLoadMoreComment' => true,
            'iShownTotal'        => !$iShownTotal ? 0 : $iShownTotal
        ]);
        $this->template()->assign([
            'showOnlyComments' => true,
        ]);
        Phpfox::getBlock('comment.comment');

        $out = "var comment = " . json_encode(['html' => ob_get_contents()]) . "; ";
        $out .= "var oCommentContainer = $('#js_feed_comment_pager_{$aFeed['type_id']}_{$aFeed['item_id']}').parent().find('.comment-container .js_comment_items'); ";
        $out .= "var oOldViewMore = oCommentContainer.closest('.js_feed_comment_view_more_holder').find('.comment-viewmore:first');";
        $out .= "if (oOldViewMore.length) { oOldViewMore.remove(); }";
        $out .= "oCommentContainer.prepend(comment.html);";
        $out .= "var oViewMore = oCommentContainer.find('.core_comment_viemore_holder');";
        $out .= "if (oViewMore.length) { oCommentContainer.closest('.js_feed_comment_view_more_holder').prepend(oViewMore.html()); oViewMore.remove();}";
        $out .= "\$Core.loadInit();";
        $out .= "obj.remove();";
        ob_clean();

        header('Content-type: application/json');
        echo json_encode(['run' => $out]);
        exit;
    }
}