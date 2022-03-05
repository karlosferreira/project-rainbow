<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !empty($aModules)}
<div class="core-activitypoint-addpackage"><a class="btn btn-primary btn-sm popup" href="{url link='activitypoint.package'}"><i class="ico ico-money-bag-o"></i><span class="item-text">{_p var='activitypoint_purchase_packages'}</span></a></div>
<div id="js_core_activitypoint_information">
    <div class="core-activitypoint__information" id="js_core_activitypoint_admincp_point_setting">
        <div class="table-responsive  core-activitypoint-table earnpoint-table">
            <form id="js_form_point_settings" method="post" action="{url link='admincp.activitypoint'}">
                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th class="js_toggle_header_all" style="width: 50%;min-width: 250px;">
                            <span class="module-icon-collapse">
                                <span class="ico ico-angle-down"></span>
                            </span>
                            {_p var='activitypoint_admincp_index_action'}
                        </th>
                        <th class="t_center">{_p var='activitypoint_admincp_index_earned'}</th>
                        <th class="t_center">{_p var='activitypoint_admincp_index_max_earned'}</th>
                        <th class="t_center">{_p var='activitypoint_admincp_index_period'}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$aModules key=module_id item=aModule}
                        <tr class="core-activitypoint-tr-module js_toggle_header_module {if $aModule.active}open{/if}" data-module="{$module_id}">
                            <td class="">
                                <span class="module-icon-collapse">
                                    <span class="ico ico-angle-down"></span>
                                </span>
                                {_p var=$aModule.name}
                            </td>
                            <td></td>
                            <td></td>
                            <td></td>
                        </tr>
                        {foreach from=$aModule.settings key=var_name item=aSetting}
                        <tr class="core-activitypoint-tr-module-content js_toggle_module_{$module_id} {if $aModule.active}open{/if}">
                            <td><div class="td-content">{_p var=$aSetting.text}</div></td>
                            <td><div class="td-content center">{$aSetting.value|number_format}</div></td>
                            <td><div class="td-content center">{$aSetting.max_earned|number_format}</div></td>
                            <td><div class="td-content center">{$aSetting.period}</div></td>
                        </tr>
                        {/foreach}
                    {/foreach}
                    </tbody>
                </table>
            </form>
        </div>
    </div>
</div>
{else}
<div class="alert alert-empty">
    {_p var='activitypoint_no_setting_available'}
</div>
{/if}