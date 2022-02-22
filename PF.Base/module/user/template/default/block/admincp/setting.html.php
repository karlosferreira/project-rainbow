<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{if !empty($aSettings)}
<div class="panel panel-default">
    <div class="panel-heading">
        <div class="panel-title">{_p var='user_group_settings'}</div>
    </div>
    <div class="panel-body">
        {foreach from=$aSettings item=aProduct}
            {foreach from=$aProduct key=sKey item=aSetting}
                {foreach from=$aSetting name=settings item=aItem}
                    <div id="iSettingId{$aItem.setting_id}" class="form-group {if isset($aItem.error)}has-error{/if} lines {if $aItem.is_admin_setting}has-warning{/if} setting {if ($aItem.type_id == 'boolean' or $aItem.type_id =='input:radio')}boolean-setting{/if}">
                        {if ($aItem.type_id == 'boolean' or $aItem.type_id =='input:radio')}
                        <div>
                        {/if}
                        {if PHPFOX_DEBUG}
                            <div class="p_4">
                                <input readonly type="text" name="param[{$aItem.setting_id}]" value="{$sKey}.{$aItem.name}" style="font-size:9pt; padding:0 3px;width:200px" onclick="this.select();" />
                            </div>
                        {/if}
                        <span class="sr-only">{$aItem.name}</span>
                        <label>{$aItem.setting_name}</label>
                        {if $aItem.is_admin_setting}
                        <div class="alert alert-warning alert-labeled">
                            <div class="alert-labeled-row">
                                <p class="alert-body alert-body-right alert-labelled-cell">
                                    <strong>{_p var="Warning"}</strong>
                                    {_p var="This is an important setting. Select a wrong option here can break the site or affect some features. If you are at all unsure about which option to configure, use the default value or contact us for support"}.
                                </p>
                            </div>
                        </div>
                        {/if}

                        {if ($aItem.type_id == 'boolean' or $aItem.type_id =='input:radio')}
                            <p class="help-block">{$aItem.setting_info}</p>
                        </div>
                        {/if}

                        {if $aItem.type_id === 'currency' || (!empty($aCurrency) && in_array($aItem.name,$aCurrency) == true) || isset($aItem.isCurrency)}
                            <input type="hidden" name="val[sponsor_setting_id_{$aItem.setting_id}]" value="{$aItem.setting_id}" />
                            {module name='core.currency' currency_field_name='val[value_actual]['$aItem.setting_id']' value_actual=$aItem.value_actual}
                        {elseif $aItem.type_id == 'big_string'}
                            <textarea class="form-control change_warning" rows="8" name="val[value_actual][{$aItem.setting_id}]">{$aItem.value_actual}</textarea>
                        {elseif ($aItem.type_id == 'integer' or $aItem.type_id == 'string' or $aItem.type_id=='input:text')}
                            <input class="form-control change_warning" type="text" name="val[value_actual][{$aItem.setting_id}]" value="{$aItem.value_actual}" size="25" onclick="this.select();" />
                        {elseif ($aItem.type_id == 'boolean' or $aItem.type_id =='input:radio')}
                            <div class="item_is_active_holder">
                                <span class="js_item_active item_is_active hide">
                                    <input type="radio" class="radio_yes change_warning" name="val[value_actual][{$aItem.setting_id}]" value="1" {if $aItem.value_actual == true || $aItem.value_actual == "1"}data-it="1yes" checked="checked" {/if}/>
                                </span>
                                <span class="js_item_active item_is_not_active hide">
                                    <input type="radio" class="radio_no change_warning" name="val[value_actual][{$aItem.setting_id}]" value="0" {if !$aItem.value_actual}checked="checked" {/if}/>
                                </span>
                            </div>
                        {elseif ($aItem.type_id == 'multi_text')}
                            {foreach from=$aItem.value_actual key=mKey item=sDropValue}
                            <div class="p_4">
                                <div class="input-group">
                                    <span class="input-group-addon">{$mKey}</span>
                                    <input class="form-control change_warning" type="text" name="val[value_actual][{$aItem.setting_id}][{$mKey}]" value="{$sDropValue|clean}" />
                                </div>
                            </div>
                            {/foreach}
                        {elseif ($aItem.type_id == 'multi_checkbox')}
                            <input type="hidden" name="val[value_actual][{$aItem.setting_id}][]" value="core_multi_checkbox_off">
                            {foreach from=$aItem.values key=mKey item=sDropValue}
                            <div class="custom-checkbox-wrapper">
                                <label>
                                    <input type="checkbox" name="val[value_actual][{$aItem.setting_id}][]" value="{$mKey}" {if is_array($aItem.value_actual) && in_array($mKey, $aItem.value_actual)}checked{/if} />
                                    <span class="custom-checkbox"></span>
                                    {$sDropValue}
                                </label>
                            </div>
                            {/foreach}
                        {elseif $aItem.type_id =='drop' || $aItem.type_id == 'drop_with_key' || $aItem.type_id=='select'}
                            <select name="val[value_actual][{$aItem.setting_id}]" class="form-control change_warning">
                                {foreach from=$aItem.values key=mKey item=sDropValue}
                                <option value="{$mKey}" {if $aItem.value_actual == $mKey} selected="selected" {/if}>{$sDropValue}</option>
                                {/foreach}
                            </select>
                        {elseif ($aItem.type_id == 'array')}
                            <div class="js_array_holder">
                                {if is_array($aItem.value_actual)}
                                {foreach from=$aItem.value_actual key=iKey item=sValue}
                                <div class="p_4 js_array{$iKey}">
                                    <div class="input-group">
                                        <input type="text" name="val[value_actual][{$aItem.setting_id}][]" value="{$sValue}" size="120" class="form-control change_warning" />
                                        <span class="input-group-btn">
                                            <a class="btn btn-danger" data-cmd="admincp.site_setting_remove_input"><i class="fa fa-remove"></i></a>
                                        </span>
                                    </div>
                                </div>
                                {/foreach}
                                {/if}
                                <div class="js_array_data"></div>
                                <div class="js_array_count" style="display:none;">{if isset($iKey)}{$iKey+1}{/if}</div>
                                <br />
                                <div class="p_4">
                                    <div class="input-group">
                                        <input type="text" name="" placeholder="{_p var='add_a_new_value' phpfox_squote=true}" size="30" class="js_add_to_array form-control" />
                                        <span class="input-group-btn">
                                            <input type="button" value="{_p var='add'}" class="btn btn-primary" data-rel="val[value_actual][{$aItem.setting_id}][]" data-cmd="admincp.site_setting_add_input" />
                                        </span>
                                    </div>
                                </div>
                            </div>
                        {/if}
                        {if ($aItem.type_id != 'boolean' && $aItem.type_id !='input:radio')}
                            <p class="help-block">{$aItem.setting_info}</p>
                        {/if}
                    </div>
                {/foreach}
            {/foreach}
        {/foreach}
        <div class="form-group lines form-group-save-changes">
            <button type="submit" class="btn btn-primary" name="val[submit]">{_p var='Save Changes'}</button>
        </div>
    </div>
</div>
{else}
<div class="alert alert-empty">
    {_p var='there_are_no_settings'}
</div>
{/if}

