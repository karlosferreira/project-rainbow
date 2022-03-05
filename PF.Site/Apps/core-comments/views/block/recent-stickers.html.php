<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="{if !empty($bIsGlobal)}[COMMENT-STICKER-LIST-CLASS]{else}comment-sticker-list{/if}">
    <div class="item-container">
        {if count($aRecentStickers)}
            {foreach from=$aRecentStickers key=iKey item=aSticker}
            <div class="item-sticker ">
                <div class="item-outer">
                    <a href="#" onclick="return $Core.Comment.selectSticker(this,{$aSticker.sticker_id});" data-feed-id="{if !empty($bIsGlobal)}[FEED-ID]{else}{$iFeedId}{/if}" data-parent-id="{if !empty($bIsGlobal)}[PARENT-ID]{else}{$iParentId}{/if}" data-edit-id="{if !empty($bIsGlobal)}[EDIT-ID]{else}{$iEditId}{/if}">
                        {$aSticker.full_path}
                    </a>
                </div>
            </div>
            {/foreach}
        {else}
            <div class="comment-none-sticker">
                <div class="none-sticker-icon"><span class="ico ico-smile"></span></div>
                <div class="none-sticker-info">{_p var='you_havent_used_any_stickers_yet'}</div>
            </div>
        {/if}
    </div>
</div>
