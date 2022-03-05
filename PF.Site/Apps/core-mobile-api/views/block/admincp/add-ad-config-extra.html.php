<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div id="js_ad_config_extra_config" style="display: none">
    <div class="form-group" id="js_ad_config_frequency_capping">
        <label for="frequency_capping">{_p var='frequency_capping'}</label>
        <select class="form-control" name="val[frequency_capping]" id="frequency_capping">
            {foreach from=$aFrequencyCapping item=aFrequency key=sKey}
            <option value="{$sKey}" {if isset($aForms.frequency_capping) && $aForms.frequency_capping == $sKey}selected{/if}>{$aFrequency}</option>
            {/foreach}
        </select>
        <div class="extra_info">{_p var='mobile_ad_config_frequency_capping_info'}</div>
    </div>
    <div class="form-group" id="js_ad_config_capping_views" {if !isset($aForms) || $aForms.frequency_capping !="views"}style="display:none"{/if}>
        <label for="view_capping">{required}{_p var='how_many_times_after_user_access_this_page_will_not_see_ad'}</label>
        <input id="view_capping" class="form-control" type="text" name="val[view_capping]" value="{value type='input' id='view_capping'}"/>
        <div class="extra_info">{_p var='mobile_ad_config_view_capping_info'}</div>
    </div>
    <div class="form-inline" id="js_ad_config_capping_time" style="padding-bottom: 16px; {if !isset($aForms) || $aForms.frequency_capping !="time"}display:none{/if}">
        <div class="form-group" style="margin-right: 32px">
            <label for="time_capping_impression">{required}{_p var='number_of_impressions_show_to_per_user'}</label>
            <br/>
            <input id="time_capping_impression" style="min-width: 250px" class="form-control" type="text" name="val[time_capping_impression]" value="{value type='input' id='time_capping_impression'}"/>
        </div>
        <div class="form-group">
            <label for="time_capping_frequency">{_p var='frequency'}</label>
            <br/>
            <select id="time_capping_frequency" class="form-control" name="val[time_capping_frequency]">
                <option value="per_minute" {if isset($aForms.time_capping_frequency) && $aForms.time_capping_frequency == 'per_minute'}selected{/if}>{_p var='per_minute'}</option>
                <option value="per_hour" {if isset($aForms.time_capping_frequency) && $aForms.time_capping_frequency == 'per_hour'}selected{/if}>{_p var='per_hour'}</option>
                <option value="per_day" {if isset($aForms.time_capping_frequency) && $aForms.time_capping_frequency == 'per_day'}selected{/if}>{_p var='per_day'}</option>
            </select>
        </div>
        <div class="extra_info">{_p var='mobile_ad_config_time_capping_info'}</div>
    </div>
    {if isset($aForms.location) && count($aForms.location)}
        {foreach from=$aForms.location item=sLocal key=iKey}
        <div class="form-inline js_ad_location_priority" style="padding-bottom: 16px;">
            <div class="form-group" style="margin-right: 32px">
                <label for="location" style="padding-right: 16px">{required}{_p var='select_location'}</label>
                <br/>
                <select class="form-control" name="val[location][]" id="location" style="min-width: 150px">
                    {foreach from=$aLocation item=sLocation key=sKey}
                    <option value="{$sKey}" {if $sLocal == $sKey}selected{/if}>{$sLocation}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
                <label for="name" style="padding-right: 16px">{required}{_p var='priority'}</label>
                <br/>
                <select class="form-control" name="val[priority][]" style="min-width: 100px">
                    {for $i = 1; $i <= 10; $i++}
                        <option value="{$i}" <?php if (isset($this->_aVars['aForms']['priority'][$this->_aVars['iKey']]) && $this->_aVars['aForms']['priority'][$this->_aVars['iKey']] == $this->_aVars['i']) echo 'selected'; ?>>{$i}</option>
                    {/for}
                </select>
            </div>
            {if $iKey > 0}
            <div class="form-group" style="padding: 16px;margin-top: 20px;">
                <i class="ico ico-minus-circle" style="color: red;font-size: 18px;" onclick="$(this).parents('.js_ad_location_priority').remove();"></i>
            </div>
            {/if}
        </div>
        {/foreach}
    {else}
        <div class="form-inline js_ad_location_priority" style="padding-bottom: 16px;">
            <div class="form-group" style="margin-right: 32px">
                <label for="location" style="padding-right: 16px">{required}{_p var='select_location'}</label>
                <br/>
                <select class="form-control" name="val[location][]" id="location" style="min-width: 150px">
                    {foreach from=$aLocation item=sLocation key=sKey}
                    <option value="{$sKey}" {if isset($aForms.location) && $aForms.location == $sKey}selected{/if}>{$sLocation}</option>
                    {/foreach}
                </select>
            </div>
            <div class="form-group">
                <label for="name" style="padding-right: 16px">{required}{_p var='select_priority'}</label>
                <br/>
                <select class="form-control" name="val[priority][]" style="min-width: 100px">
                {for $i = 1; $i <= 10; $i++}
                    <option value="{$i}">{$i}</option>
                {/for}
                </select>
            </div>
        </div>
    {/if}
    <div class="extra_info">{_p var='mobile_ad_config_location_priority_info'}</div>
    <div class="extra_info" style="padding-bottom: 8px;">{_p var='please_view_sample_layout_first_to_set_up_more_accurately'}&nbsp;&nbsp;<a href="#?call=mobile.sampleLayout&width=1000" class="inlinePopup" title="{_p var='sample_layout'}"><i class="ico ico-eye"></i>&nbsp;{_p var='view_sample_layout'}</a></div>
    <div style="padding-bottom: 16px;">
        <a href="javascript:void(0)" id="js_ad_config_add_more_location"><i class="ico ico-plus" style="font-size: 11px"></i> {_p var='add_more_location'}</a>
    </div>
    <div class="form-group">
        <label for="is_active">{_p var='is_sticky'}</label>
        <div class="item_is_active_holder">
            <span class="js_item_active item_is_active"><input type="radio" name="val[is_stick]" value="1" {value type='radio' id='is_stick' default='1' }/></span>
            <span class="js_item_active item_is_not_active"><input type="radio" name="val[is_stick]" value="0" {value type='radio' id='is_stick' default='0' selected='true' }/></span>
        </div>
        <div class="extra_info">{_p var='mobile_is_sticky_ad_config_info'}</div>
    </div>
</div>