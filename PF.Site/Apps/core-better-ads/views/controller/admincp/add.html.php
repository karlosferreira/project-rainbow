<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if count($aAllPlans) || $bIsEdit}
{$sCreateJs}
<div id="page_route_ads_add" xmlns:>
<form method="post" action="{url link='admincp.ad.add'}" enctype="multipart/form-data" onsubmit="{$sGetJsForm}" id="betterads-add-ad-form">
    <div class="panel panel-default">
        <div class="panel-body">
            <h2>{_p var='general_infomation'}</h2>
            <hr>
            {if $bIsEdit}
            <input type="hidden" name="val[type_id]" value="{$aForms.type_id}" id="type_id"/>
            <input type="hidden" name="ads_id" value="{$aForms.ads_id}"/>
            {/if}
            <div class="form-group">
                <label class="required" for="type_id">{_p var='ad_type'}</label>
                {if $bIsEdit}
                    {if $aForms.type_id == 1}
                    - {_p var='better_ads_image'}
                    {elseif $aForms.type_id == 2}
                    - {_p var='better_ads_html'}
                    {/if}
                {else}
                <div class="custom-radio-wrapper">
                    <label>
                        <input type="radio" value="1" name="val[type_id]" id="type_image" class="type_id" {value type='radio' id='type_id' default='1' selected=true}>
                        <span class="custom-radio"></span>
                        {_p var='image'}
                    </label>
                </div>
                <div class="custom-radio-wrapper">
                    <label>
                        <input type="radio" value="2" name="val[type_id]" id="type_html" class="type_id" {value type='radio' id='type_id' default='2'}>
                        <span class="custom-radio"></span>
                        {_p var='html'}
                    </label>
                </div>
                {/if}
            </div>

            {if count($aAllPlans)}
            <div class="form-group" {if ($bIsEdit && !\Phpfox::isAdminPanel())} style="display:none;" {/if}>
                <label class="required" for="better_ads_location">{_p var='better_ads_placement'}</label>
                <select name="val[location]" id="better_ads_location" class="form-control">
                    {foreach from=$aAllPlans key=ikey value=aPlan}
                    <option value="{$aPlan.plan_id}" {value type='select' id='location' default=$aPlan.plan_id} data-block-id="{$aPlan.block_id}" data-cpm="{$aPlan.is_cpm}">
                        {$aPlan.title} &bull; {$aPlan.block_title}
                    </option>
                    {/foreach}
                </select>
                <div class="help-block">
                    <a href="#?call=ad.sample&amp;fullmode=true&amp;no-click=1" class="inlinePopup"
                       title="{_p var='view_sample_layout'}"><span class="ico ico-eye-o"></span> {_p var='view_sample_layout'}</a>
                </div>
            </div>
            {/if}

            <div class="form-group">
                <label class="required">{_p var='better_ads_image'}</label>
                {if !empty($sCurrentPhoto)}
                {module name='core.upload-form' type='ad' current_photo=$sCurrentPhoto id=$aForms.ads_id}
                {else}
                {module name='core.upload-form' type='ad'}
                {/if}
                <p class="help-block">{_p var='recommended_dimension'}: <span id="recommended-demension"></span></p>
            </div>

            <div class="form-group">
                <label class="required" for="url_link">{_p var='better_ads_destination_url'}</label>
                <input type="text" name="val[url_link]" value="{value type='input' id='url_link'}" id="url_link" size="40" class="form-control"/>
            </div>

            <div class="form-group {if (!$bIsEdit && empty($aForms.type_id)) || $aForms.type_id==1}hide{/if}" data-type="html">
                <label class="required" for="title">{_p var='better_ads_title'}</label>
                <input type="text" name="val[title]" value="{value type='input' id='title'}" size="25" maxlength="25" id="title" class="form-control" data-character-limit="25"/>
                <p class="help-block">{_p var='remain_number_characters' number=25}.</p>
            </div>

            <div class="form-group {if (!$bIsEdit && empty($aForms.type_id)) || $aForms.type_id==1}hide{/if}" data-type="html">
                <label for="body" class="required">{_p var='better_ads_body_text'}</label>
                <textarea type="text" name="val[body]" id="body" class="form-control" cols="40" rows="6" data-character-limit="135" maxlength="135">{value type='textarea' id='body'}</textarea>
                <p class="help-block">{_p var='remain_number_characters' number=135}.</p>
            </div>


            <div class="form-group {if !empty($aForms.type_id) && $aForms.type_id==2}hide{/if}" data-type="image">
                <label for="image_tooltip_text">{_p var='image_tooltip_text'}</label>
                <input type="text" name="val[image_tooltip_text]" value="{value type='input' id='image_tooltip_text'}" id="image_tooltip_text" size="40" class="form-control"/>
            </div>

            {if !$bIsEdit}
            <div class="form-group">
                <button class="btn btn-primary" id="betterads-add-continue">{_p var='continue'}</button>
                <a role="button" class="btn btn-default betterads-preview" title="{_p var='preview'}">{_p var='preview'}</a>
            </div>
            {/if}

            <div id="ad_details" {if !$bIsEdit}class="hide"{/if}>
                <h2>{_p var='ad_details'}</h2>
                <hr>

                <div class="form-group">
                    <label class="required" for="name">{_p var='ad_name'}</label>
                    <input type="text" name="val[name]" value="{value type='input' id='name'}" id="name" size="40" maxlength="150" class="form-control"/>
                </div>

                <div class="form-group row ad-date-time">
                    <div class="col-md-6">
                        <div>
                            <label class="required">{_p var='better_ads_start_date'}</label>
                            {select_date prefix='start_' start_year='current_year' end_year='+10' field_separator=' / ' field_order='MDY' default_all=true add_time=true time_separator='core.time_separator'}
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="custom-checkbox-wrapper">
                            <label>
                                <input type="checkbox" name="val[end_option]" id="end_option" value="1" {if !empty($aForms.end_date)}checked{/if}>
                                <span class="custom-checkbox"></span>
                                {_p var='better_ads_end_date'}
                            </label>
                            {select_date prefix='end_' start_year='current_year' end_year='+10' field_separator=' / ' field_order='MDY' default_all=true add_time=true time_separator='core.time_separator'}
                        </div>
                    </div>
                </div>

                <div class="form-group" id="js_total_view">
                    <div class="custom-checkbox-wrapper checkbox-input">
                        <label>
                            <input type="checkbox" name="val[use_total_view]" value="1" id="view_unlimited" class="v_middle" {if (isset($aForms) && !empty($aForms.total_view))} checked="checked" {/if}/>
                            <span class="custom-checkbox"></span>
                            {_p var='better_ads_total_views'}
                        </label>
                        <input type="text" name="val[total_view]" value="{value type='input' id='total_view'}" id="total_view" class="form-control v_middle {if (!isset($aForms) || empty($aForms.total_view))}disabled {/if}" size="10" {if (!isset($aForms) || empty($aForms.total_view))} disabled="disabled" {/if}/>
                    </div>
                </div>

                <div class="form-group" id="js_total_click">
                    <div class="custom-checkbox-wrapper checkbox-input">
                        <label>
                            <input type="checkbox" name="val[use_total_click]" value="1" id="click_unlimited" class="v_middle" {if (isset($aForms) && !empty($aForms.total_click))} checked="checked" {/if}/>
                            <span class="custom-checkbox"></span>
                            {_p var='better_ads_total_clicks'}
                        </label>
                        <input type="text" name="val[total_click]" value="{value type='input' id='total_click'}" id="total_click" class="form-control v_middle {if (!isset($aForms) || empty($aForms.total_click))}disabled {/if}" size="10" {if (!isset($aForms) || empty($aForms.total_click))} disabled="disabled" {/if}/>
                    </div>
                </div>

                {if isset($aForms.is_custom) && $aForms.is_custom == '2'}
                    <div><input type="hidden" name="val[is_active]" value="1"/></div>
                {else}
                    <div class="form-group">
                        <label>{_p var='better_ads_active'}</label>
                        <div class="custom-radio-wrapper">
                            <label>
                                <input type="radio" name="val[is_active]" value="1" {value type='radio' id='is_active' default='1' selected=true}/>
                                <span class="custom-radio"></span>
                                {_p var='yes'}
                            </label>
                        </div>
                        <div class="custom-radio-wrapper">
                            <label>
                                <input type="radio" name="val[is_active]" value="0" {value type='radio' id='is_active' default='0'}/>
                                <span class="custom-radio"></span>
                                {_p var='no'}
                            </label>
                        </div>
                    </div>
                {/if}

                <div class="form-group js_core_init_selectize_form_group">
                    <label for="country_iso_custom">{_p var='better_ads_location'}</label>
                    {if isset($aAllCountries)}
                    <select multiple="multiple" name="val[country_iso_custom][]" id="country_iso_custom" class="form-control">
                        <option value="" {if empty($aForms.countries_list)}selected="selected"{/if}>{_p var='any'}</option>
                        {foreach from=$aAllCountries key=sIso item=aCountry}
                        <option value="{$sIso}"
                            {if isset($aForms) && isset($aForms.countries_list)}
                                {foreach from=$aForms.countries_list item=sChosen}
                                    {if $sChosen== $sIso} selected="selected" {/if}
                                {/foreach}
                            {/if}
                        > {$aCountry.name}
                        </option>
                        {/foreach}
                    </select>
                    {else}
                        {select_location value_title='phrase var=core.any' multiple=1 name='country_iso_custom'}
                    {/if}
                </div>

                {if setting('better_ads_advanced_ad_filters')}
                <div class="tbl_province form-group" style="display:none;">
                    <label>{_p var="better_ads_state_province"}</label>
                    {foreach from=$aAllCountries item=aCountry}
                    {if is_array($aCountry.children) && !empty($aCountry.children)}
                    <div id="country_{$aCountry.country_iso}" class="select_child_country form-group" style="display:none;">
                        <div>{$aCountry.name}</div>
                        <select class="sct_child_country form-control" id="sct_country_{$aCountry.country_iso}"
                                name="val[child_country][{$aCountry.country_iso}][]" multiple="multiple">
                            {foreach from=$aCountry.children item=aChild}
                                <option value="{$aChild.child_id}" data-id="{$aChild.child_id}">{$aChild.name_decoded}</option>
                            {/foreach}
                        </select>
                    </div>
                    {/if}
                    {/foreach}
                </div>

                <div class="form-group">
                    <label for="postal_code">{_p var='better_ads_postal_code'}</label>
                    <input type="text" name="val[postal_code]" id='postal_code' value="{value type='input' id='postal_code'}" class="form-control">
                    <div class="help-block">{_p var='better_ads_separate_multiple_postal_codes_by_a_comma'}</div>
                </div>

                <div class="form-group">
                    <label for="city_location">{_p var='better_ads_city'}</label>
                    <input type="text" name="val[city_location]" id='city_location' value="{value type='input' id='city_location'}" class="form-control">
                    <div class="help-block">{_p var='better_ads_separate_multiple_cities_by_a_comma'}</div>
                </div>
                {/if}

                <div class="form-group">
                    <label for="gender">{_p var='gender'}</label>
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
                <div class="form-group js_core_init_selectize_form_group">
                    <label for="age_from">{_p var='age_between'}</label>
                    <div class="form-inline">
                        <select name="val[age_from]" id="age_from" class="form-control">
                            <option value="">{_p var='any'}</option>
                            {foreach from=$aAge item=iAge}
                            <option value="{$iAge}" {value type='select' id='age_from' default=$iAge}>{$iAge}</option>
                            {/foreach}
                        </select>
                        <span class="and-inline">{_p var="and"}</span>
                        <select name="val[age_to]" id="age_to" class="form-control">
                            <option value="">{_p var='any'}</option>
                            {foreach from=$aAge item=iAge}
                            <option value="{$iAge}" {value type='select' id='age_to' default=$iAge}>{$iAge}</option>
                            {/foreach}
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <div class="bts-admincp-add-language clearfix">
                        <label class="d-block">{_p var='languages'}</label>
                        {foreach from=$aLanguages key=language_key item=aLanguage}
                        <div class="checkbox {if (int)$language_key > 0}ml-2{/if}">
                            <label>
                                <input type="checkbox" name="val[languages][]" value="{$aLanguage.language_id}" {if (!$bIsEdit) || ($bIsEdit && in_array($aLanguage.language_id, $aEditLanguages))}checked{/if}> {$aLanguage.title}
                            </label>
                        </div>
                        {/foreach}
                    </div>
                    <div class="mt-1 help-block">{_p var='better_ads_notice_choose_languages' title=$sPaymentType}</div>
                </div>

                <div class="form-group">
                    <input type="submit" value="{_p var='submit'}" class="btn btn-primary"/>
                    <a role="button" class="btn btn-default betterads-preview" title="{_p var='preview'}">{_p var='preview'}</a>
                </div>
            </div>
        </div>
    </div>
</form>
</div>
{else}
<div class="alert alert-warning">
    <a href="{url link='admincp.ad.addplacement'}">{_p var="Please create a placement first"}.</a>
</div>
{/if}