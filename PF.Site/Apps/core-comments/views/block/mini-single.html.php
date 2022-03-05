<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{if $aComment.parent_id}
    <div id="js_comment_{$aComment.parent_id}" class="js_mini_feed_comment comment-item js_mini_comment_item_{$aComment.item_id}" style="padding-top: 0;padding-bottom: 0;">
        <div id="js_comment_form_holder_{$aComment.parent_id}" class="js_comment_form_holder"></div>
        <div id="js_comment_mini_child_holder_{$aComment.parent_id}" class="comment_mini_child_holder comment_mini_child_holder_padding">
            <div id="js_comment_children_holder_{$aComment.parent_id}" class="comment_mini_child_content">
                {template file='comment.block.mini'}
            </div>
        </div>
    </div>
{else}
    {template file='comment.block.mini'}
{/if}
