<?php

defined('PHPFOX') or exit('NO DICE!');
?>

<div class="js_view_more_reply hide">
    {if $iLoadMoreTotal > 0}
        <div class="comment-viewless">
            <a data-hide-total="{$iLoadMoreTotal}" href="#" onclick="$Core.Comment.hideLoadedReplies(this, {$aComment.comment_id}); return false;" class="item-viewless">
                {if $iLoadMoreTotal == 1}
                    {_p var='hide_one_reply'}
                {else}
                    {_p var='hide_number_replies' number=$iLoadMoreTotal}
                {/if}
            </a>
        </div>
    {/if}
    {if Phpfox::getParam('comment.thread_comment_total_display') !== null && $aComment.child_total > $iShownTotal}
        <div class="comment-viewmore js_comment_replies_viewmore_{$aComment.comment_id}">
            <?php
                $this->_aVars['aLastReply'] = end($this->_aVars['aReplies']);
            ?>
            <a href="{url link='comment.replies'}?is_feed={$isFeed}&comment_type_id={$aComment.type_id}&item_id={$aComment.item_id}&comment_id={$aComment.comment_id}&time-stamp={$aLastReply.time_stamp}&max-time={$iMaxTime}&shown-total={$iShownTotal}&total-replies={$aComment.child_total}" class="item-viewmore ajax" onclick="$(this).addClass('active');">
                {if $aComment.child_total - $iShownTotal == 1}
                    {_p var='view_one_more_reply'}
                {elseif ($iRemain = $aComment.child_total - $iShownTotal) < 10}
                    {_p var='view_span_number_more_replies' number=$iRemain}
                {else}
                    {_p var='view_more_replies'}
                {/if}
            </a>
            <div class="item-number">
                {$iShownTotal}/{$aComment.child_total}
            </div>
        </div>
    {/if}
</div>
{foreach from=$aReplies item=aReply}
    {module name='comment.mini' comment_custom=$aReply}
{/foreach}
