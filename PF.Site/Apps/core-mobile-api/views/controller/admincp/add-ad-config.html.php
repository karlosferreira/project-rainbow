<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<form action="" method="post" id="js_add_ad_config_form" class="mobile-add-ad-config" >
    {if !empty($aForms)}
    <input type="hidden" value="{$aForms.id}" name="id">
    {/if}
    <div class="panel panel-default">
        <div class="panel-body">
            <div class="form-group">
                <label for="name">{required}{_p var='name'}</label>
                <input type="text" required class="form-control" value="{value type='input' id='name'}" name="val[name]" id="name" maxlength="255"/>
                <div class="extra_info">{_p var='mobile_ad_config_name_info'}</div>
            </div>
            <div class="form-group">
                <label>{required}{_p var='select_type'}</label>
                <div class="custom-radio-wrapper">
                    {foreach from=$aAdTypes item=sType key=sKey}
                    <label class="radio-inline">
                        <input type="radio" name="val[type]" value="{$sKey}" {if (isset($aForms.type) && $aForms.type == $sKey) || (!isset($aForms.type) && $sKey == 'banner')}checked{/if} />
                        <span class="custom-radio"></span>
                        {$sType}
                    </label>
                    {/foreach}
                </div>
                <div class="extra_info">{_p var='mobile_add_ad_config_type_info'}</div>
            </div>
            <div class="form-group js_core_init_selectize_form_group">
                <label for="screen">{required}{_p var='select_page_to_apply'}</label>
                <select class="form-control" multiple name="val[screens][]" id="screen">
                    {foreach from=$aMobilePages item=sScreen key=sValue}
                    <option value="{$sValue}" <?php if(isset($this->_aVars['aForms']['screens']) && in_array($this->_aVars['sValue'],$this->_aVars['aForms']['screens'])) echo 'selected'; ?>>{$sScreen}</option>
                    {/foreach}
                </select>
                <div class="extra_info">{_p var='mobile_ad_config_select_page_info'}</div>
            </div>

            {template file='mobile.block.admincp.add-ad-config-extra'}

            <div class="form-group">
                <label>{_p var='allow_access'}</label>
                {foreach from=$aUserGroups item=aUserGroup}
                <div class="custom-checkbox-wrapper">
                    <label>
                        <input type="checkbox" name="val[allow_access][]" value="{$aUserGroup.user_group_id}"{if isset($aAccess) && is_array($aAccess)}{if !in_array($aUserGroup.user_group_id, $aAccess)} checked="checked" {/if}{else} checked="checked" {/if}/>
                        <span class="custom-checkbox"></span>
                        {$aUserGroup.title|convert|clean}
                    </label>
                </div>
                {/foreach}
            </div>
            <div class="form-group">
                <label for="is_active">{_p var='is_active'}</label>
                <div class="item_is_active_holder">
                    <span class="js_item_active item_is_active">
                        <input type="radio" name="val[is_active]" value="1" {value type='radio' id='is_active'
                               default='1' selected='true' }/></span>
                    <span class="js_item_active item_is_not_active">
                        <input type="radio" name="val[is_active]" value="0" {value type='radio' id='is_active'
                               default='0' }/></span>
                </div>
                <div class="extra_info">{_p var='mobile_is_active_ad_config_info'}</div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" class="btn btn-primary" id="">{_p var='submit'}</button>
            <a href="{url link='admincp.mobile.manage-ads-config'}" class="btn btn-default" id="">{_p var='cancel'}</a>
        </div>
    </div>
</form>
