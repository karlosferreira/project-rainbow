<?php
defined('PHPFOX') or exit('NO DICE!');

?>
{if !empty($aForms) && !empty($aMenus)}
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title">
            {$sTitle}
        </div>
    </div>
    <form method="post" class="mobile_api_menu_form" action="{url link='admincp.mobile.add' edit=$aForms.item_id}" onsubmit="$Core.onSubmitForm(this, true);">
        <div class="panel-body">
            <div><input type="hidden" name="val[edit_id]" value="{$aForms.item_id}" /></div>
            <div><input type="hidden" name="val[name]" value="{$aForms.name}" /></div>
            <div><input type="hidden" name="val[allow_all]" value="1" /></div>
            <div class="form-group">
                {field_language phrase='name' label=$sHeaderTitle field='name' format='val[name_' size=30 maxlength=100}
            </div>
            <input type="submit" class="btn btn-primary btn_submit" style="display: none" value="{_p var='update'}" />
        </div>
    </form>
    <div class="table-responsive flex-sortable">
        <table class="table table-bordered" id="_sort" data-sort-url="{url link='mobile.admincp.menu.order'}">
            <thead>
                <tr>
                    <th class="w60"></th>
                    <th class="w60"></th>
                    <th class="t_center w50">{_p var='Icon'}</th>
                    <th>{_p var='Name'}</th>
                    <th class="t_center w80" style="width:60px;">{_p var='active__u'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$aMenus key=iKey item=aMenu}
                <tr class="tr" data-sort-id="{$aMenu.item_id}">
                    <td class="t_center w40">
                        <i class="fa fa-sort"></i>
                    </td>
                    <td class="t_center w60">
                        <a href="javascript:void(0)" class="js_drop_down_link" title="{_p var='Manage'}"></a>
                        <div class="link_menu">
                            <ul>
                                <li><a class="popup" href="{url link='admincp.mobile.add' edit=$aMenu.item_id}">{_p('Edit')}</a></li>
                            </ul>
                        </div>
                    </td>
                    <td class="t_center w50">
                        {if $aMenu.icon_family == 'Lineficon'}
                        <i class="ico ico-{$aMenu.icon_name}" style="font-size: 25px; color:{$aMenu.icon_color}"></i>
                        {/if}
                    </td>
                    <td class="td-flex">
                        {_p var=$aMenu.name|convert}
                    </td>
                    <td class="on_off w80">
                        <div class="js_item_is_active"{if !$aMenu.is_active} style="display:none;"{/if}>
                            <a href="#?call=mobile.toggleActiveMenu&amp;id={$aMenu.item_id}&amp;active=0" class="js_item_active_link" title="{_p var='Deactivate'}"></a>
                        </div>
                        <div class="js_item_is_not_active"{if $aMenu.is_active} style="display:none;"{/if}>
                            <a href="#?call=mobile.toggleActiveMenu&amp;id={$aMenu.item_id}&amp;active=1" class="js_item_active_link" title="{_p var='Activate'}"></a>
                        </div>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
{/if}
