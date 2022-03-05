<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="core-activitypoint__admincp-member-points" id="js_core_activitypoint_admincp_member_points">
    <p>{_p var='activitypoint_admincp_points_introduce'}</p>
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {_p var='activitypoint_member_points'}
            </div>
        </div>
        <div class="panel-body">
            <div class="core-activitypoint__admincp-member-points-search">
                <form id="js_form_search_member_points" method="get" action="{url link='admincp.activitypoint.point'}">
                    <label>
                        {_p var='activitypoint_member'}
                    </label>
                    <div class="core-activitypoint__admincp-member-points-search-input form-group">
                        <input type="text" name="search[user]" value="{value type='input' id='user'}" class="form-control">
                    </div>
                    <div class="core-activitypoint__admincp-member-points-search-button form-group">
                        <button class="btn btn-primary">{_p var='activitypoint_search'}</button>
                    </div>
                </form>
            </div>
            {if !empty($aMemberPoints)}
            <div class="table-responsive">
                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th class="t_center w20"><input type="checkbox" id="js_select_all_member_points"></th>
                        <th {table_sort class="t_center w80" asc="s.user_id asc" desc="s.user_id desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_user_id'}</th>
                        <th {table_sort class="t_center w160" asc="u.full_name asc" desc="u.full_name desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_user_name'}</th>
                        <th {table_sort class="t_center" asc="a.activity_points asc" desc="a.activity_points desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_current_a_p'}</th>
                        <th class="t_center">{_p var='activitypoint_total_earned'}</th>
                        <th class="t_center">{_p var='activitypoint_total_received'}</th>
                        <th class="t_center">{_p var='activitypoint_total_bought'}</th>
                        <th class="t_center">{_p var='activitypoint_total_spent'}</th>
                        <th class="t_center">{_p var='activitypoint_total_sent'}</th>
                        <th class="t_center">{_p var='activitypoint_total_retrieved'}</th>
                        <th class="t_center w60">{_p var='activitypoint_actions'}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$aMemberPoints item=aMemberPoint}
                    <tr>
                        <td class="t_center"><input type="checkbox" class="js_select_member_points" data-id="{$aMemberPoint.user_id}"></td>
                        <td class="t_center w100">{$aMemberPoint.user_id}</td>
                        <td class="t_center">{$aMemberPoint|user}</td>
                        <td class="t_center">{$aMemberPoint.activity_points|number_format}</td>
                        <td class="t_center">{$aMemberPoint.total_earned|number_format}</td>
                        <td class="t_center">{$aMemberPoint.total_received|number_format}</td>
                        <td class="t_center">{$aMemberPoint.total_bought|number_format}</td>
                        <td class="t_center">{$aMemberPoint.total_spent|number_format}</td>
                        <td class="t_center">{$aMemberPoint.total_sent|number_format}</td>
                        <td class="t_center">{$aMemberPoint.total_retrieved|number_format}</td>
                        <td class="t_center">
                            <a role="button" class="js_drop_down_link" title="{_p var='activitypoint_point_actions'}"></a>
                            <div class="link_menu">
                                <ul class="dropdown-menu dropdown-menu-right">
                                    {if Phpfox::getUserParam('activitypoint.can_admin_adjust_activity_points')}
                                    <li>
                                        <a href="javascript:void(0)" onclick="tb_show('{_p var='activitypoint_point_actions' phpfox_squote=true}', $.ajaxBox('activitypoint.adjustPoint', 'height=400&amp;width=600&amp;user_id={$aMemberPoint.user_id}'));">{_p var='activitypoint_adjust_points'}</a>
                                    </li>
                                    {/if}
                                    <li>
                                        <a href="{url link='admincp.activitypoint.transaction' val[user_id]=$aMemberPoint.user_id}" target="_blank">{_p var='activitypoint_view_transaction'}</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                    </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
            {pager}
            {else}
            <div class="alert alert-empty">
                {_p var='Members not found'}
            </div>
            {/if}
            <div class="core-activitypoint__admincp-member-points-actions">
                {if Phpfox::getUserParam('activitypoint.can_admin_adjust_activity_points')}
                    <a class="btn btn-success" id="js_adjust_all_member_points">{_p var='activitypoint_adjust_points'}</a>
                {/if}
                <a class="btn btn-info" id="js_view_all_transactions_member_points" data-url="{url link='admincp.activitypoint.transaction'}">{_p var='activitypoint_view_transactions'}</a>
            </div>
        </div>
    </div>
</div>
