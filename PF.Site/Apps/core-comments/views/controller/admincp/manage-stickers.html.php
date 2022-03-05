<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !count($aStickerSets)}
    <div class="alert alert-danger" style="position: relative">
        {_p var='no_stickers_set_found'}
        <button onclick="window.location.href = '{url link='admincp.comment.add-sticker-set'}'" style="position: absolute; right: 5px; bottom: 9px;" class="btn btn-success btn-sm">{_p var='add_new_sticker_set'}</button>
    </div>
{else}
<form method="post" id="manage_sticker_set" action="{url link='admincp.comment.manage-stickers'}">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title" style="position: relative">
                <a href="{url link='admincp.app' id='Core_Comments'}">{_p var='stickers_set'}</a>
                <button type="button" onclick="window.location.href = '{url link='admincp.comment.add-sticker-set'}'" style="position: absolute; right: 0; bottom: -6px;" class="btn btn-success btn-sm">{_p var='add_new_sticker_set'}</button>
            </div>
        </div>
        <div class="table-responsive flex-sortable">
            <table class="table table-bordered" id="_sort" data-sort-url="{url link='comment.admincp.stickers-set.order'}">
                <thead>
                    <tr>
                        <th class="w40"></th>
                        <th class="w40 js_checkbox">
                            <input type="checkbox" name="val[ids]" value="" id="js_check_box_all" class="main_checkbox" /></th>
                        </th>
                        <th class="w40"></th>
                        <th>{_p var='title'}</th>
                        <th class="t_center w120">{_p var='total_stickers'}</th>
                        <th class="t_center w120">{_p var='thumbnail'}</th>
                        <th class="t_center w140">{_p var='active__u'}</th>
                    </tr>
                </thead>
                <tbody>
                {foreach from=$aStickerSets key=iKey item=aSet}
                    <tr class="tr" data-sort-id="{$aSet.set_id}">
                        <td class="t_center w40">
                            <i class="fa fa-sort"></i>
                        </td>
                        <td class="t_center js_checkbox">
                            {if !$aSet.is_default && !$aSet.view_only}
                                <input type="checkbox" name="ids[]" class="checkbox" value="{$aSet.set_id}" id="js_id_row{$aSet.set_id}" />
                            {/if}
                        </td>
                        <td class="t_center w60">
                            <a href="#" class="js_drop_down_link" title="{_p var='Manage'}"></a>
                            <div class="link_menu">
                                <ul>
                                        {if !$aSet.view_only}
                                            <li><a href="{url link='admincp.comment.add-sticker-set' id=$aSet.set_id}">{_p var='edit'}</a></li>
                                        {else}
                                            <li><a href="{url link='admincp.comment.add-sticker-set' id=$aSet.set_id}">{_p var='preview_sticker_set'}</a></li>
                                        {/if}
                                        {if !$aSet.is_default}
                                            {if !$aSet.view_only}
                                                <li><a href="{url link='admincp.comment.manage-stickers' delete=$aSet.set_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_sticker'}">{_p var='delete'}</a></li>
                                            {/if}
                                            <li><a href="{url link='admincp.comment.manage-stickers' default=$aSet.set_id}" data-message="{_p var='are_you_sure_you_can_set_only_two_default_sticker_sets_and_you_can_not_delete_default_sticker_set'}" class="sJsConfirm">{_p var='mark_as_default'}</a></li>
                                        {else}
                                            <li><a href="{url link='admincp.comment.manage-stickers' un_default=$aSet.set_id}" class="sJsConfirm">{_p var='remove_default'}</a></li>
                                        {/if}
                                </ul>
                            </div>
                        </td>
                        <td class="td-flex">
                            {softPhrase var=$aSet.title|clean} {if $aSet.view_only}- {_p var='core_sticker'}{/if} {if $aSet.is_default}({_p var='default'}){/if}
                        </td>
                        <td class="t_center w100">{$aSet.total_sticker}</td>
                        <td class="t_center w80">
                            {if !empty($aSet.thumbnail_id)}
                                <div class="item-thumbnail">
                                    {$aSet.full_path}
                                </div>
                            {/if}
                        </td>
                        <td class="t_center w140">
                            <div class="js_item_is_active"{if !$aSet.is_active} style="display:none;"{/if}>
                                <a href="#?call=comment.toggleActiveStickerSet&amp;id={$aSet.set_id}&amp;active=0" class="js_item_active_link" title="{_p var='Deactivate'}"></a>
                            </div>
                            <div class="js_item_is_not_active"{if $aSet.is_active} style="display:none;"{/if}>
                                <a href="#?call=comment.toggleActiveStickerSet&amp;id={$aSet.set_id}&amp;active=1" class="js_item_active_link" title="{_p var='Activate'}"></a>
                            </div>
                        </td>
                    </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <input type="submit" name="val[delete_selected]" id="delete_selected" disabled value="{_p('delete_selected')}" class="sJsConfirm sJsCheckBoxButton btn btn-danger disabled" data-message="{_p var='are_you_sure_you_want_to_delete_selected_stickers'}"/>
        </div>
    </div>
</form>
{/if}
{literal}
<script type="text/javascript">
    $Behavior.onLoadManageStickerSet = function(){
        if (!$('input[name="ids[]"]').length) {
            $('#js_check_box_all').remove();
            $('.js_checkbox').remove();
        }
    }
</script>
<style type="text/css">
    .item-thumbnail{
        width: 50px;
        height: 50px;
        margin-left: auto;
        margin-right: auto;
    }
    .item-thumbnail img{
        width: 100%;
        height: 100%;
    }
</style>
{/literal}