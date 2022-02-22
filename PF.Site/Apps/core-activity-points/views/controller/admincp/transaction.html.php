<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="core-activitypoint__admincp-transaction" id="js_core_activitypoint_admincp_transaction">
    <p>{_p var='activitypoint_admincp_transaction_introduce'}</p>

    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {_p var='activitypoint_transaction_history'}
            </div>
        </div>
        <div class="panel-body">
            <div class="core-activitypoint__admincp-transaction-search">
                <input type="hidden" id="js_date_from_default" value="{$sFromDate}">
                <input type="hidden" id="js_date_to_default" value="{$sToDate}">
                <form id="js_form_search_transaction" method="get" action="{url link='admincp.activitypoint.transaction'}">
                    <div class="form-group core-activitypoint__admincp-transaction-search-item">
                        <label for="user">
                            {_p var='activitypoint_member'}
                        </label>
                        <input type="text" name="val[user]" id="user" value="{value type='input' id='user'}" placeholder="{_p var='activitypoint_enter_member_name'}" class="form-control">
                    </div>
                    <div class="form-group core-activitypoint__admincp-transaction-search-item js_core_init_selectize_form_group">
                        <label for="type">
                            {_p var='activitypoint_type'}
                        </label>
                        <select name="val[type]" class="form-control">
                            <option value="">{_p var='activitypoint_all'}</option>
                            {foreach from=$aPointTypes item=sType}
                            <option value="{$sType}" {value type='select' id='type' default=$sType}>{$sType}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group core-activitypoint__admincp-transaction-search-item">
                        <label for="date_from">
                            {_p var='activitypoint_from_date'}
                        </label>
                        <div style="position: relative;" class="">
                            {select_date prefix='from_' id='_start' start_year='current_year' end_year='+1' field_separator=' / ' default_all=true}
                        </div>
                    </div>
                    <div class="form-group core-activitypoint__admincp-transaction-search-item">
                        <label for="date_to">
                            {_p var='activitypoint_to_date'}
                        </label>
                        <div style="position: relative;" class="">
                            {select_date prefix='to_' id='_start' start_year='current_year' end_year='+1' field_separator=' / ' default_all=true}
                        </div>
                    </div>
                    <div class="form-group core-activitypoint__admincp-transaction-search-item js_core_init_selectize_form_group">
                        <label for="module_id">
                            {_p var='activitypoint_module_app'}
                        </label>
                        <select name="val[module_id]" class="form-control">
                            <option value="">{_p var='activitypoint_all'}</option>
                            {foreach from=$aSettingApps key=module_id item=app_name}
                            <option value="{$module_id}" {value type='select' id='module_id' default=$module_id}>{_p var=$app_name}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group core-activitypoint__admincp-transaction-search-item js_core_init_selectize_form_group">
                        <label for="module_id">
                            {_p var='activitypoint_action_type'}
                        </label>
                        <select name="val[action]" class="form-control">
                            <option value="">{_p var='activitypoint_all'}</option>
                            {foreach from=$aActions item=aAction}
                                <option value="{$aAction.var_name}" {value type='select' id='action' default=$aAction.var_name}>{$aAction.text}</option>
                            {/foreach}
                        </select>
                    </div>
                    <div class="form-group core-activitypoint__admincp-transaction-search-button mt-2">
                        <button class="btn btn-primary">{_p var='activitypoint_filter'}</button>
                        <button class="btn btn-default" onclick="coreActivityPointAdmincpTransaction.resetForm(); return false;">{_p var='activitypoint_reset'}</button>
                    </div>
                </form>
            </div>
            {if !empty($aTransactions)}
            <div class="table-responsive core-activitypoint-transactions">
                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                        <tr>
                            <th {table_sort class="t_center w100" asc="t.transaction_id asc" desc="t.transaction_id desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_trans_id'}</th>
                            <th {table_sort class="t_center w120" asc="t.user_id asc" desc="t.user_id desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_member_id'}</th>
                            <th class="t_center w160">{_p var='activitypoint_member_name'}</th>
                            <th {table_sort class="t_center w160" asc="t.time_stamp asc" desc="t.time_stamp desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_date'}</th>
                            <th class="t_center w120">{_p var='activitypoint_point_type'}</th>
                            <th class="w120">{_p var='activitypoint_app'}</th>
                            <th>{_p var='activitypoint_admincp_index_action'}</th>
                            <th {table_sort class="t_center w80" asc="t.points asc" desc="t.points desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_points'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        {foreach from=$aTransactions item=aTransaction}
                        <tr>
                            <td class="text-center">{$aTransaction.transaction_id|number_format}</td>
                            <td class="text-center">{$aTransaction.user_id}</td>
                            <td class="text-center">{$aTransaction|user}</td>
                            <td class="text-center">{if !empty($aTransaction.time_stamp)}{$aTransaction.time_stamp|convert_time}{/if}</td>
                            <td class="text-center">{_p var=$aTransaction.type}</td>
                            <td>{_p var=$aTransaction.module_title}</td>
                            <td><div>{$aTransaction.phrase}</div></td>
                            <td class="t_center"><div class="td-content center {$aTransaction.custom_class}">{if $aTransaction.custom_class == 'minus'}-{/if}{$aTransaction.points|number_format}</div></td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            {pager}
            {else}
            <div class="alert alert-empty">
                {_p var='activitypoint_transactions_not_found'}
            </div>
            {/if}
        </div>
    </div>
</div>
