<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="core-activitypoint__admincp-index" id="js_core_activitypoint_admincp_index">
    <p>{_p var='activitypoint_admincp_title_title'}</p>
    <div class="core-activitypoint__admincp-index-content">
        <div class="panel panel-default">
            <div class="panel-body">
                <div class="col-sm-3">
                    <label>{_p var='activitypoint_choose_user_group'}</label>
                    <select class="form-control core-activitypoint__admincp-index-usergroup-choose" id="js_core_activitypoint_choose_usergroup">
                        {foreach from=$aUserGroups item=aGroup}
                        <option value="{url link='admincp.activitypoint' group_id=$aGroup.user_group_id}" {value type='select' id='group_id' default=$aGroup.user_group_id}>{$aGroup.title}</option>
                        {/foreach}
                    </select>
                </div>
            </div>
        </div>
        <div class="panel panel-default" id="js_core_activitypoint_admincp_point_setting">
            <div class="table-responsive">
                <form id="js_form_point_settings" method="post" action="{url link='admincp.activitypoint'}">
                    <input type="hidden" value="{$iGroupId}" name="group_id">
                    <table class="table" cellpadding="0" cellspacing="0" style="margin-bottom: 0;">
                        <thead>
                        <tr>
                            <th class="js_toggle_header_all" style="width: 50%;">
                                <span class="module-icon-collapse">
                                    <span class="ico ico-angle-down"></span>
                                </span>
                                {_p var='activitypoint_admincp_index_action'}
                            </th>
                            <th class="text-center w160">{_p var='activitypoint_admincp_index_earned'}</th>
                            <th class="text-center w160">{_p var='activitypoint_admincp_index_max_earned'}</th>
                            <th class="text-center w160">{_p var='activitypoint_admincp_index_period'}</th>
                            <th class="text-center">{_p var='activitypoint_admincp_index_active'}</th>
                        </tr>
                        </thead>
                        <tbody>
                        {foreach from=$aModules key=module_id item=aModule}
                            <tr class="js_admincp_table_header_toggle core-table-header-toggle {if $aModule.active}open{/if}" data-module="{$module_id}" data-togglecontent="{$module_id}" data-action="{if $aModule.active}close{else}open{/if}">
                                <td class="core-activitypoint__admincp-index-settings-module-name">
                                    <span class="module-icon-collapse">
                                        <span class="ico ico-angle-down"></span>
                                    </span>
                                    {_p var=$aModule.name}
                                </td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"></td>
                                <td class="text-center"><input type="checkbox" name="val[{$module_id}][is_active]" class="js_check_all_module change_warning" data-module="{$module_id}"  {if $aModule.bActive}checked{/if}></td>
                            </tr>
                            {foreach from=$aModule.settings key=var_name item=aSetting}
                                <tr class="js_toggle_module_{$module_id} {if $aModule.active}open{/if} core-table-content-toggle">
                                    <td style="padding-left: 32px;">{_p var=$aSetting.text}</td>
                                    <td class="text-center js_point_setting w160"><span class="js_value_text">{$aSetting.value|number_format}</span><input type="hidden" value="{$aSetting.value}" name="val[{$module_id}][settings][{$var_name}][value]" class="js_change_value form-control change_warning"></td>
                                    <td class="text-center js_point_setting w160"><span class="js_value_text">{$aSetting.max_earned|number_format}</span><input type="hidden" value="{$aSetting.max_earned}" name="val[{$module_id}][settings][{$var_name}][max_earned]" class="js_change_value form-control change_warning"></td>
                                    <td class="text-center js_point_setting w160"><span class="js_value_text">{$aSetting.period}</span><input type="hidden" value="{$aSetting.period}" name="val[{$module_id}][settings][{$var_name}][period]" class="js_change_value form-control change_warning"></td>
                                    <td class="text-center"><input type="checkbox" name="val[{$module_id}][settings][{$var_name}][is_active]" class="js_check_setting_{$aSetting.module_id} change_warning" {if $aSetting.is_active}checked{/if} {if !$aModule.bActive}disabled{/if}></td>
                                </tr>
                            {/foreach}
                        {/foreach}

                        </tbody>
                    </table>
                    <div class="form-group lines form-group-save-changes" style="z-index: 99;">
                        <button class="btn btn-primary">{_p var='save_changes'}</button>
                        <button class="btn btn-default" onclick="$Core.reloadPage();return false;">{_p var='cancel'}</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
