<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if isset($aBackgrounds) && count($aBackgrounds)}
<div class="table form-group-follow p-statusbg-collection-manage-image clearfix panel-body dont-unbind-children">
    <div class="sortable table_right">
        {foreach from=$aBackgrounds name=images item=aBackground}
        <div id="js_photo_holder_" class="js_photo_item" style="">
            <input type="hidden" name="val[ordering][{$aBackground.background_id}]" class="js_mp_order" value="{$aBackground.ordering}">
            {if !isset($aForms) || !$aForms.view_id}
            <a href="" class="p-statusbg-delete-btn" title="{_p('delete_this_image')}" onclick="$Core.jsConfirm({l}message:'{_p('are_you_sure_you_want_to_delete_this_image')}'{r},function(){l}$.ajaxCall('pstatusbg.deleteBackground', 'id={$aBackground.background_id}'){r});return false;">{img theme='misc/delete_hover.gif' alt=''}</a>
            {/if}
            <span class="p-statusbg-collection-image" style="background-image: url('{$aBackground.full_path}')"></span>
        </div>
        {if is_int($phpfox.iteration.images/4)}
        {/if}
        {/foreach}
    </div>
</div>

{literal}
<script type="text/javascript">
    $Behavior.ycsbUpdateOrderBackgrounds = function(){
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
                    $.ajaxCall('pstatusbg.updateImagesOrdering', sParams + '&global_ajax_message=true' + '&collection_id=' + {/literal}{if $bIsEdit}{$iEditId}{/if}{literal});
                },
            }
        );
    };
</script>
{/literal}
{/if}
