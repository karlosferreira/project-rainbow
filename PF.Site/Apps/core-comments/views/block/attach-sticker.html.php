<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !$bUpdateOpened}
<div class="dropdown-menu comment-sticker-container js_comment_sticker_container js_sticker_set_{if !empty($bIsGlobal)}[FEED-ID]_[PARENT-ID]_[EDIT-ID]{else}{$iFeedId}_{$iParentId}_{$iEditId}{/if}">
{/if}
    <ul class="nav comment-sticker-header">
        <div class="header-sticker-list">
            <li class="active" title="{_p var='recent_stickers'}"><a class="item-recent" href="#1b1_{if !empty($bIsGlobal)}[FEED-ID]_[PARENT-ID]_[EDIT-ID]{else}{$iFeedId}_{$iParentId}_{$iEditId}{/if}" data-toggle="tab"><span class="ico ico-clock-o"></span></a></li>
            <a class="comment-prev-sticker" style="display: none;"><span class="ico ico-angle-left"></span></a>
            {if count($aStickerSets)}
                <div class="item-container">
                    <div class="comment-full-sticker">
                        {foreach from=$aStickerSets key=iKey item=aSet}
                            <li class="item-header-sticker" onclick="setTimeout(function(){l}$Core.Comment.initCanvasForSticker('.core_comment_gif:not(.comment_built)'){r},100); return true;">
                                <a href="#1b2_{if !empty($bIsGlobal)}[FEED-ID]_[PARENT-ID]_[EDIT-ID]{else}{$iFeedId}_{$iParentId}_{$iEditId}{/if}_{$aSet.set_id}" data-toggle="tab" title="{$aSet.title|clean}">
                                    {$aSet.full_path}
                                </a>
                            </li>
                        {/foreach}
                    </div>
                </div>
            {/if}
            <a class="comment-next-sticker" ><span class="ico ico-angle-right"></span></a>
        </div>

        <a class="item-add" href="#" data-feed-id="{if !empty($bIsGlobal)}[FEED-ID]{else}{$iFeedId}{/if}" data-parent-id="{if !empty($bIsGlobal)}[PARENT-ID]{else}{$iParentId}{/if}" data-edit-id="{if !empty($bIsGlobal)}[EDIT-ID]{else}{$iEditId}{/if}" onclick="$Core.Comment.loadStickerCollection(this); return false;"><span class="ico ico-plus"></span></a>
    </ul>
    <div class="tab-content comment-sticker-content">
        <div class="tab-pane active js_recent_stickers_list" id="1b1_{if !empty($bIsGlobal)}[FEED-ID]_[PARENT-ID]_[EDIT-ID]{else}{$iFeedId}_{$iParentId}_{$iEditId}{/if}">
            {template file='comment.block.recent-stickers'}
        </div>
        {if count($aStickerSets)}
            {foreach from=$aStickerSets key=iKey item=aSet}
                <div class="tab-pane" id="1b2_{if !empty($bIsGlobal)}[FEED-ID]_[PARENT-ID]_[EDIT-ID]{else}{$iFeedId}_{$iParentId}_{$iEditId}{/if}_{$aSet.set_id}">
                    <div class="{if !empty($bIsGlobal)}[COMMENT-STICKER-LIST-CLASS]{else}comment-sticker-list{/if}">
                        <div class="item-container">
                            {foreach from=$aSet.stickers item=aSticker}
                                <div class="item-sticker">
                                    <div class="item-outer">
                                        <a href="#" onclick="return $Core.Comment.selectSticker(this,{$aSticker.sticker_id});" data-feed-id="{if !empty($bIsGlobal)}[FEED-ID]{else}{$iFeedId}{/if}" data-parent-id="{if !empty($bIsGlobal)}[PARENT-ID]{else}{$iParentId}{/if}" data-edit-id="{if !empty($bIsGlobal)}[EDIT-ID]{else}{$iEditId}{/if}">
                                            {$aSticker.full_path}
                                        </a>
                                    </div>
                                </div>
                            {/foreach}
                        </div>
                    </div>
                </div>
            {/foreach}
        {/if}
    </div>
{if !$bUpdateOpened}
</div>
{/if}
