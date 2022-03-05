<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if isset($aStickers) && count($aStickers)}
<div class="table form-group-follow comment-sticker-manage clearfix panel-body dont-unbind-children">
    <div class="sortable table_right">
        {foreach from=$aStickers name=images item=aSticker}
        <div id="js_photo_holder_" class="js_photo_item" style="">
            <input type="hidden" name="val[ordering][{$aSticker.sticker_id}]" class="js_mp_order" value="{$aSticker.ordering}">
            {if !isset($aForms) || !$aForms.view_only}
            <a href="" class="comment-delete-btn" title="{_p('delete_this_sticker')}" onclick="$Core.jsConfirm({l}message:'{_p('are_you_sure_you_want_to_delete_this_sticker')}'{r},function(){l}$.ajaxCall('comment.deleteSticker', 'id={$aSticker.sticker_id}'){r});return false;">{img theme='misc/delete_hover.gif' alt=''}</a>
            {/if}
            {$aSticker.full_path}
        </div>
        {if is_int($phpfox.iteration.images/4)}
        {/if}
        {/foreach}
    </div>
</div>

{literal}
<script type="text/javascript">
    $Behavior.updateOrderStickers = function(){
        $('.sortable').sortable({
                opacity: 0.6,
                cursor: 'move',
                scrollSensitivity: 40,
                update: function(element, ui)
                {
                    var iCnt = 0;
                    sParams = '';
                    $('.sortable .js_mp_order').each(function()
                    {
                        iCnt++;
                        this.value = iCnt;
                        sParams += '&' + $(this).attr('name') + '=' + iCnt;
                    });
                    $Core.ajaxMessage();
                    $.ajaxCall('comment.updateStickersOrdering', sParams + '&global_ajax_message=true' + '&set_id=' + {/literal}{if $bIsEdit}{$iEditId}{/if}{literal});
                },
            }
        );
    };
</script>
{/literal}
{/if}
