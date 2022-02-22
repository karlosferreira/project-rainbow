<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if $sType == 'photo'}
    <div class="item-edit-photo js_comment_attach_preview">
        <div class="item-photo">
            {img server_id=$aForms.server_id path='core.url_pic' file="comment/".$aForms.path suffix=''}
        </div>
        <a class="item-delete" onclick="$Core.Comment.deleteAttachment($(this),{$aForms.file_id},'photo', {if isset($bIsEdit)}true{else}false{/if}); return false;"><span class="ico ico-close"></span></a>
    </div>
{elseif $sType == 'sticker'}
    <div class="item-edit-sticker js_comment_attach_preview">
        <div class="item-sticker">
            {$aForms.full_path}
        </div>
        <a class="item-delete" onclick="$Core.Comment.deleteAttachment($(this),{$aForms.sticker_id},'sticker', {if isset($bIsEdit)}true{else}false{/if}); return false;"><span class="ico ico-close"></span></a>
    </div>
{/if}
