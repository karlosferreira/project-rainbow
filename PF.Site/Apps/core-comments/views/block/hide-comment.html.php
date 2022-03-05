<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="comment-item {if !empty($iParentId)}comment-item-reply{/if} comment-item-hide" id="js_hide_comment_{$iCommentId}">
    <div class="item-outer">
        <div class="item-inner">
            <div class="item-comment-content">
                <div class="content-text">{_p var='this_comment_has_been_hidden'}</div>
            </div>
            <div class="item-action">
                <div class="action-list">
                    <span class="item-un-hide"><a href="#" onclick="return $Core.Comment.hideComment(this, true);" data-comment-id="{$iCommentId}">{_p var='unhide'}</a></span>
                    {if Phpfox::getUserParam('user.can_block_other_members') && isset($aOwner.user_group_id) && Phpfox::getUserGroupParam('' . $aOwner.user_group_id . '', 'user.can_be_blocked_by_others')}
                        <span class="item-reply"><a href="#?call=user.block&amp;height=120&amp;width=400&amp;user_id={$aOwner.user_id}" class="inlinePopup js_block_this_user" title="{_p var='block_first_name' first_name=$sFirstName}">{_p var='block_first_name' first_name=$sFirstName}</a></span>
                    {/if}
                </div>
            </div>
        </div>
    </div>
</div>