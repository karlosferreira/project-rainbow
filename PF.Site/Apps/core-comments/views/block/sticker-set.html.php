<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="comment-stickerstore-item {if !empty($aSet.is_my)}js_comment_my_sticker_set_{$aSet.set_id}{/if}">
    <div class="item-outer">
        <div class="item-media">
            {$aSet.full_path}
        </div>
        <div class="item-inner">
            <div class="item-title">
                {$aSet.title|clean}
            </div>
            <div class="item-list comment-store-preview limit">
                {foreach from=$aSet.stickers item=aSticker}
                <div class="item-sticker">
                    {$aSticker.full_path}
                </div>
                {/foreach}
            </div>
            <div class="item-btn-group">
                <button class="btn btn-default btn-sm item-preview" onclick="$(this).ajaxCall('comment.previewStickerSet','id={$aSet.set_id}&feed_id={$iStickerFeedId}&parent_id={$iStickerParentId}&edit_id={$iStickerEditId}','post',null,function(e,self){l}self.removeClass('disabled');{r}); $(this).addClass('disabled');">{_p var='preview'}</button>
                <button {if !$aSet.is_added}style="display:none !important"{/if} class="btn btn-default btn-sm item-add js_comment_remove_sticker_set_{$aSet.set_id} " onclick="return $Core.Comment.updateMyStickerSet(this, {$aSet.set_id}, 0);" data-feed-id="{$iStickerFeedId}" data-parent-id="{$iStickerParentId}" data-edit-id="{$iStickerEditId}">{_p var='remove'}</button>
                <button {if $aSet.is_added}style="display:none !important"{/if} class="btn btn-primary btn-sm item-add js_comment_add_sticker_set_{$aSet.set_id} " onclick="return $Core.Comment.updateMyStickerSet(this, {$aSet.set_id}, 1);" data-feed-id="{$iStickerFeedId}" data-parent-id="{$iStickerParentId}" data-edit-id="{$iStickerEditId}">{_p var='add'}</button>
            </div>
        </div>
    </div>
</div>
