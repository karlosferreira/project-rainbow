<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="core-messages-admincp-messages">
    <div class="advanced-search">
        <form id="js_form_messsages_search" method="get" action="{url link='admincp.mail.messages' id=$iId}">
            <input type="hidden" value="{$iId}" name="id">
            <input type="hidden" value="{$sCalendarImagePath}" id="js_calendar_path">
            <div class="form-group col-md-6">
                <label for="member_name">{_p var='Member Name'}</label>
                <input type="text" class="form-control" name="search[member_name]" id="member_name" value="{value type='input' id='member_name'}">
            </div>
            <div class="form-group col-md-6">
                <label for="member_id">{_p var='Member ID'}</label>
                <input type="text" class="form-control" name="search[member_id]" id="member_id" value="{value type='input' id='member_id'}">
            </div>
            <div class="form-group col-md-6">
                <label for="date_from">{_p var='From Date'}</label>
                <div class="time-selection">
                    {select_date prefix='from_' id='_start' start_year='2000' end_year='+1' field_separator=' / ' field_order='MDY' default_all=true time_separator='core.time_separator' name='search'}
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="date_to">{_p var='To Date'}</label>
                <div class="time-selection">
                    {select_date prefix='to_' id='_end' start_year='2000' end_year='+1' field_separator=' / ' field_order='MDY' default_all=true time_separator='core.time_separator' name='search'}
                </div>
            </div>
            <div class="form-group col-md-6">
                <label for="keyword">{_p var='Keyword'}</label>
                <input type="text" class="form-control" name="search[keyword]" id="keyword" value="{value type='input' id='keyword'}">
            </div>
            <div class="form-group col-md-6">
                <label>{_p var='Status'}</label>
                <select id="js_select_status" class="form-control" name="search[status]">
                    <option value="" {value type='select' id='status' default=''}>{_p var='All'}</option>
                    <option value="showing" {value type='select' id='status' default='showing'}>{_p var='Showing'}</option>
                    <option value="hidden" {value type='select' id='status' default='hidden'}>{_p var='Hidden'}</option>
                </select>
            </div>
            <div class="form-group button-actions">
                <button type="submit" class="btn btn-primary">{_p var='Search'}</button>
                <button type="button" class="btn btn-default" id="js_clear_data">{_p var='Clear'}</button>
            </div>
        </form>
    </div>
    <div class="message-list">
        {if count($aMessages)}
            <div class="panel panel-default table-responsive">
                <table class="table table-admin">
                    <thead>
                    <tr>
                        <th class="t_center w20"><input type="checkbox" id="js_select_all"></th>
                        <th class="t_center w100">{_p var='Member ID'}</th>
                        <th class="w180">{_p var='Member Name'}</th>
                        <th>{_p var='Message'}</th>
                        <th class="t_center w160">{_p var='Time'}</th>
                        <th class="t_center w120">{_p var='Status'}</th>
                        <th class="t_center w40">{_p var='Actions'}</th>
                    </tr>
                    </thead>
                    <tbody>
                    {foreach from=$aMessages key=iKey item=aMessage}
                    <tr>
                        <td class="t_center">
                            <input type="checkbox" value="{$aMessage.message_id}" class="js_select_message">
                        </td>
                        <td class="t_center">
                            #{$aMessage.user_id}
                        </td>
                        <td>
                            {$aMessage|user}
                        </td>
                        <td class="mail_text" style="word-break: break-word;word-wrap: break-word;">
                            {$aMessage.text}
                            {if $aMessage.total_attachment}
                            {module name='attachment.list' sType=mail iItemId=$aMessage.message_id}
                            {/if}
                        </td>
                        <td class="t_center">
                            {$aMessage.time_stamp|convert_time}
                        </td>
                        <td class="t_center">
                            {if $aMessage.is_show}
                            {_p var='Showing'}
                            {else}
                            {_p var='Hidden'}
                            {/if}
                        </td>
                        <td class="t_center">
                            <a role="button" class="js_drop_down_link" title="{_p var='manage'}"></a>
                            <div class="link_menu">
                                <ul class="dropdown-menu dropdown-menu-right">
                                    {if $aMessage.is_show}
                                    <li><a href="javascript:void(0)" onclick="$.ajaxCall('mail.actionMessageAdmincp','id={$aMessage.message_id}&action=hide&thread_id={$iId}');">{_p var='Hide Message'}</a></li>
                                    {else}
                                    <li><a href="javascript:void(0)" onclick="$.ajaxCall('mail.actionMessageAdmincp','id={$aMessage.message_id}&action=unhide&thread_id={$iId}');">{_p var='Unhide Message'}</a></li>
                                    {/if}
                                    {if Phpfox::getUserParam('mail.can_delete_others_messages')}
                                    <li><a href="javascript:void(0)" onclick="$Core.jsConfirm({left_curly}message: '{_p var='are_you_sure'}'{right_curly}, function(){left_curly} $.ajaxCall('mail.actionMessageAdmincp','id={$aMessage.message_id}&action=delete&thread_id={$iId}');{right_curly}, function(){left_curly}{right_curly}); return false;">{_p var='Delete Message'}</a></li>
                                    {/if}
                                </ul>
                            </div>
                        </td>
                    </tr>
                    {/foreach}
                    </tbody>
                </table>
            </div>
        {else}
            <div class="alert alert-empty">
                {_p var='Messages not found'}
            </div>
        {/if}
    </div>
    {pager}
    {if count($aMessages)}
        <div class="mass-actions col-md-12">
            <div class="left">
                <span class="number-selected">
                    <span class="number">0</span>
                    {_p var='items selected'}
                </span>
                <button class="btn btn-success disabled" disabled="disabled" id="js_unselect_all">{_p var='un_select_all'}</button>
            </div>
            <div class="right">
                {if Phpfox::getUserParam('mail.can_delete_others_messages')}
                    <button class="btn btn-danger disabled" disabled="disabled" id="js_delete_messages" data-id="{$iId}">{_p var='Delete'}</button>
                {/if}
                <button class="btn btn-default disabled" disabled="disabled" id="js_hidden_messages" data-id="{$iId}">{_p var='Hide'}</button>
            </div>
        </div>
    {/if}
</div>
{literal}
<style rel="stylesheet">
    .message-list .mail_text img{
        max-width: 300px;
        display: block;
        margin: 0 auto;
    }
</style>
{/literal}