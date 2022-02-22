<?php
    defined('PHPFOX') or exit('NO DICE!');
?>

<div class="form-group bts-add-gender clearfix">
    <label for="gender" class="d-block">{required}{_p var='gender'}</label>
    <div class="checkbox">
        <label>
            <input type="checkbox" name="val[gender][]" value="0" {if !$bIsEdit}checked="checked"{/if} {value type='checkbox' id='gender' default='0'}>
            {_p var='any'}
        </label>
    </div>
    {checkbox_gender}
    <div class="checkbox">
        <label>
            <input type="checkbox" name="val[gender][]" value="127" {if !$bIsEdit}checked="checked"{/if} {value type='checkbox' id='gender' default='127'}>
            {_p var='better_ads_custom_genders'}
        </label>
    </div>
</div>

{if $bAdvancedAdFilters}
    <div class="form-group bts-add-location js_core_init_selectize_form_group">
        <label for="country_iso_custom">{_p var='better_ads_location'}</label>
        {if isset($aAllCountries)}
            <select multiple="multiple" name="val[country_iso_custom][]" id="country_iso_custom" class="form-control">
                <option value="" {if isset($aForms.countries_list.0) && empty($aForms.countries_list.0)}selected{/if}>{_p var='any'}</option>
                {foreach from=$aAllCountries key=sIso item=aCountry}
                    <option value="{$sIso}" {if isset($aForms) && isset($aForms.countries_list)}{foreach from=$aForms.countries_list item=sChosen} {if $sChosen == $sIso} selected="selected" {/if}{/foreach}{/if}>{$aCountry.name}</option>
                {/foreach}
            </select>
        {else}
            {select_location value_title='phrase var=core.any' name='country_iso_custom'}
        {/if}
    </div>

    {if !isset($bNotShowStateProvince)}
        <div class="form-group tbl_province" style="display: none;">
            <label>{_p var="State/Province"}</label>
            {foreach from=$aAllCountries item=aCountry}
            {if is_array($aCountry.children) && !empty($aCountry.children)}
            <div id="country_{$aCountry.country_iso}" class="select_child_country form-group" style="display: none;">
                <div>{$aCountry.name}</div>
                <select class="sct_child_country form-control" id="sct_country_{$aCountry.country_iso}" name="val[child_country][{$aCountry.country_iso}][]" multiple="multiple">
                    {foreach from=$aCountry.children item=aChild}
                    <option value="{$aChild.child_id}">{$aChild.name_decoded}</option>
                    {/foreach}
                </select>
            </div>
            {/if}
            {/foreach}
        </div>
    {/if}

    <div class="form-group">
        <label for="postal_code">{_p var="better_ads_postal_code"}</label>
        <input type="text" name="val[postal_code]" id='postal_code' value="{value type='input' id='postal_code'}">
        <p class="help-block">
            {_p var="Separate multiple postal codes by a comma."}
        </p>
    </div>

    <div class="form-group">
        <label for="city_location">{_p var="better_ads_city"}</label>
        <input type="text" name="val[city_location]" id='city_location' value="{value type='input' id='city_location'}">
        <p class="help-block">
            {_p var="better_ads_separate_multiple_cities_by_a_comma"}
        </p>
    </div>
{/if}

<div class="form-group bts-add-age">
    <label for="age_from">{_p var='age_between'}</label>
    <div class="form-inline">
        <div class="form-group js_core_init_selectize_form_group">
            <select name="val[age_from]" id="age_from" class="form-control">
                <option value="">{_p var='any'}</option>
                {foreach from=$aAge item=iAge}
                <option value="{$iAge}"{value type='select' id='age_from' default=$iAge}>{$iAge}</option>
                {/foreach}
            </select>
        </div><div class="form-group px-1">{_p var='and'}</div><div class="form-group js_core_init_selectize_form_group">
            <select name="val[age_to]" id="age_to" class="form-control">
                <option value="">{_p var='any'}</option>
                {foreach from=$aAge item=iAge}
                <option value="{$iAge}"{value type='select' id='age_to' default=$iAge}>{$iAge}</option>
                {/foreach}
            </select>
        </div>
    </div>
</div>

<div class="form-group">
    <div class="bts-add-language clearfix">
        <label class="d-block">{_p var='languages'}</label>
        {foreach from=$aLanguages key=language_key item=aLanguage}
        <div class="checkbox {if (int)$language_key > 0}ml-2{/if}">
            <label>
                <input type="checkbox" name="val[languages][]" value="{$aLanguage.language_id}" {if (!$bIsEdit) || ($bIsEdit && in_array($aLanguage.language_id, $aEditLanguages))}checked{/if}> {$aLanguage.title}
            </label>
        </div>
        {/foreach}
    </div>
    <div class="mt-1 bts-language-notice">{_p var='better_ads_notice_choose_languages' title=$sPaymentType}</div>
</div>

{if !empty($userGroups)}
    <div class="form-group bts-add-user-groups js_core_init_selectize_form_group">
        <label for="user_groups">{_p var='user_groups'}</label>
        <select multiple="multiple" name="val[user_groups][]" id="user_groups" class="form-control">
            <option value="">{_p var='any'}</option>
            {foreach from=$userGroups item=userGroup}
                <option value="{$userGroup.user_group_id}" {if !empty($aForms.user_groups) && is_array($aForms.user_groups) && in_array($userGroup.user_group_id, $aForms.user_groups)}selected="true"{/if}>{$userGroup.title}</option>
            {/foreach}
        </select>
        <div class="mt-1 bts-language-notice">{_p var='better_ads_notice_choose_user_groups' title=$sPaymentType}</div>
    </div>
{/if}