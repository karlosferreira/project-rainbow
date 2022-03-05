<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if count($aCollections)}
<form method="post" id="manage_reactions" action="{url link='admincp.pstatusbg.manage-collections'}">
    <div class="panel panel-default">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                <tr>
                    <th class="w40 js_checkbox">
                        <input type="checkbox" name="val[ids]" value="" id="js_check_box_all" class="main_checkbox" /></th>
                    </th>
                    <th class="w40">{_p var='actions'}</th>
                    <th>{_p var='collection_name'}</th>
                    <th class="w160 t_center">{_p var='main_image'}</th>
                    <th class="t_center w120">{_p var='no_of_image'}</th>
                    <th class="t_center w80">{_p var='active'}</th>
                </tr>
                </thead>
                <tbody>
                    {foreach from=$aCollections key=iKey item=aItem}
                    <tr class="tr">
                        <td class="t_center js_checkbox">
                            {if !$aItem.is_default}
                            <input type="checkbox" name="ids[]" class="checkbox" value="{$aItem.collection_id}" id="js_id_row{$aItem.collection_id}" />
                            {/if}
                        </td>
                        <td class="t_center w60">
                            <a href="#" class="js_drop_down_link" title="{_p var='Manage'}"></a>
                            <div class="link_menu">
                                <ul>
                                    <li><a href="{url link='admincp.pstatusbg.add-collection' id=$aItem.collection_id}">{_p var='edit'}</a></li>
                                    {if !$aItem.is_default}
                                    <li><a href="{url link='admincp.pstatusbg.manage-collections' default=$aItem.collection_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_if_set_as_default_this_collection_is_activated_and_the_current_default_one_will_be_set_inactive'}">{_p var='set_as_default'}</a></li>
                                    {/if}
                                    {if $aItem.view_id != 1 && $aItem.is_default == 0}
                                        <li><a href="{url link='admincp.pstatusbg.manage-collections' delete=$aItem.collection_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_collection_permanently'}">{_p var='delete'}</a></li>
                                    {/if}
                                </ul>
                            </div>
                        </td>
                        <td class="">
                            {if $aItem.is_default}({_p var='default'}) {/if}{_p var=$aItem.title}
                        </td>
                        <td class="w160 t_center">
                            {if !empty($aItem.full_path)}
                                <span class="p-statusbg-main-image" style="background-image: url('{$aItem.full_path}')"></span>
                            {/if}
                        </td>
                        <td class="w120 t_center">
                            {$aItem.total_background}
                        </td>
                        <td class="t_center w140" {if $aItem.is_default}style="opacity:0.5; pointer-events: none;"{/if}>
                            <div class="js_item_is_active"{if !$aItem.is_active} style="display:none;"{/if}>
                                <a href="#?call=pstatusbg.toggleActiveCollection&amp;id={$aItem.collection_id}&amp;active=0" class="js_item_active_link" title="{_p var='Deactivate'}"></a>
                            </div>
                            <div class="js_item_is_not_active"{if $aItem.is_active} style="display:none;"{/if}>
                                <a href="#?call=pstatusbg.toggleActiveCollection&amp;id={$aItem.collection_id}&amp;active=1" class="js_item_active_link" title="{_p var='Activate'}"></a>
                            </div>
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <input type="submit" name="val[delete_selected]" id="delete_selected" data-message="{_p var='are_you_sure_you_want_to_delete_these_collection_s_permanently'}" disabled value="{_p('delete_selected')}" class="sJsConfirm sJsCheckBoxButton btn btn-danger disabled"/>
        </div>
    </div>
    {pager}
</form>
{/if}

{literal}
<style>
 #manage_reactions .p-statusbg-main-image{
    width: 100%;
    padding-bottom: 56.25%;
    display: block;
    position: relative;
    background-size: cover;
    background-position: center center;
    background-repeat: no-repeat;
    background-origin: border-box;
    border: 1px solid rgba(0, 0, 0, 0.1);
}
</style>
<script type="text/javascript">
    $Behavior.onLoadManageCollection = function() {
        var btnAdd = $('.toolbar-top .btn-group').find('a.popup');
        btnAdd.removeClass('popup');
        $('.toolbar-top').find('a:eq(0)').addClass('active');
        if (!$('input[name="ids[]"]').length) {
            $('#js_check_box_all').remove();
            $('.js_checkbox').remove();
        }
    }
</script>
{/literal}