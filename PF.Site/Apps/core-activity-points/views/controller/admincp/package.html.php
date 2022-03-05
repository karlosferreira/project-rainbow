<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="core-activitypoint__admincp-package">
    <p>{_p var='activitypoint_introduce_point_package_title'}</p>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {_p var='activitypoint_points_package'}
            </div>
        </div>
        <div>
            {if !empty($bEmpty)}
                {if !empty($aPackages)}
                <div class="table-responsive">
                    <table class="table" cellpadding="0" cellspacing="0">
                        <thead>
                            <tr>
                                <th {table_sort class="t_center" asc="package_id asc" desc="package_id desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_id'}</th>
                                <th class="t_center">{_p var='activitypoint_package_name'}</th>
                                <th {table_sort class="t_center" asc="points asc" desc="points desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_number_points'}</th>
                                <th class="t_center">{_p var='activitypoint_price_title'} ({$sDefaultCurrencySymbol})</th>
                                <th {table_sort class="t_center" asc="time_updated asc" desc="time_updated desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_last_updated'}</th>
                                <th {table_sort class="t_center" asc="total_active asc" desc="total_active desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_total_active_title'}</th>
                                <th {table_sort  class="t_center" asc="is_active asc" desc="is_active desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_package_active'}</th>
                                <th class="t_center">{_p var='activitypoint_action'}</th>
                            </tr>
                        </thead>
                        <tbody>
                        {foreach from=$aPackages item=aPackage}
                        <tr>
                            <td class="t_center">
                                <a href="{url link='admincp.activitypoint.add-package' id=$aPackage.package_id}" title="{_p var='edit'}" class="popup">#{$aPackage.package_id}</a>
                            </td>
                            <td class="t_center">{$aPackage.title|convert|clean}</td>
                            <td class="t_center">{$aPackage.points}</td>
                            <td class="t_center">{$aPackage.default_price}</td>
                            <td class="t_center">{$aPackage.time_updated|convert_time}</td>
                            <td class="t_center">{$aPackage.total_active}</td>
                            <td class="on_off">
                                <div class="js_item_is_active" {if !$aPackage.is_active}style="display:none"{/if}>
                                    <a href="#?call=activitypoint.updateActivity&amp;package_id={$aPackage.package_id}&amp;active=0" class="js_item_active_link" title="{_p var='deactivate'}"></a>
                                </div>
                                <div class="js_item_is_not_active" {if $aPackage.is_active}style="display:none"{/if}>
                                    <a href="#?call=activitypoint.updateActivity&amp;package_id={$aPackage.package_id}&amp;active=1" class="js_item_active_link" title="{_p var='activate'}"></a>
                                </div>
                            </td>
                            <td class="t_center">
                                <a href="{url link='admincp.activitypoint.add-package' id=$aPackage.package_id}" title="{_p var='edit'}" class="popup">{_p var='edit'}</a>
                                {if (int)$aPackage.total_active == 0}
                                    <span> / </span>
                                    <a href="{url link='admincp.activitypoint.package' delete=$aPackage.package_id}" title="{_p var='delete'}" class="sJsConfirm">
                                        {_p var='delete'}
                                    </a>
                                {/if}
                            </td>
                        </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                {pager}
                {else}
                    <div class="alert alert-empty">{_p var='Packages not found'}</div>
                {/if}
            {else}
                <div class="alert alert-empty">
                    <a href="{url link='admincp.activitypoint.add-package'}" class="popup btn btn-default">
                        <span class="ico ico-plus"></span>
                        {_p var='Add'}
                    </a>
                </div>
            {/if}
        </div>
    </div>
</div>
