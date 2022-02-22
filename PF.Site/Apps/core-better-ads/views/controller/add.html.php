<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div id="ads_add_form_content">
    {if !$iPlacementCount}
    <div class="alert alert-warning">
        {_p var='better_ads_no_ad_placements_have_been_created_check_back_here_shortly'}.
    </div>
    {else}
        {if $bIsEdit}
        <form method="post" action="{url link='ad.add' id=$aForms.ads_id}">
            <div><input type="hidden" name="val[id]" value="{$aForms.ads_id}" /></div>
            <div class="main_break">
                <div class="form-group">
                    <label class="required">{_p var='ad_name'}</label>
                    <input type="text" name="val[name]" value="{value type='input' id='name'}" size="25" id="name" class="form-control" required autofocus/>
                </div>

                {template file='ad.block.targetting'}

                <div class="form-group">
                    <input type="submit" value="{_p var='better_ads_submit'}" class="btn btn-primary" />
                </div>
            </div>
        </form>
        {else}
            {if $bCompleted}
                <div class="main_break"></div>
                {if isset($bIsFree) && $bIsFree == true}
                    <div class="message">
                        {_p var='better_ads_your_ad_has_been_created'}
                    </div>
                    {else}
                    <div class="message">
                        {_p var='better_ads_your_ad_has_successfully_been_submitted_to_complete_the_process_continue_with_paying_below'}
                    </div>
                    <h3>{_p var='better_ads_payment_methods'}</h3>
                    {module name='api.gateway.form'}
                {/if}
            {else}
                <form method="post" action="{url link='ad.add'}" id="js_custom_ads_form" enctype="multipart/form-data">
                    <div><input type="hidden" name="val[image_path]" value="{value type='input' id='image_path'}" id="js_image_id" /></div>
                    <div><input type="hidden" name="val[color_bg]" value="{value type='input' id='color_bg' default='fff'}" id="js_colorpicker_drop_bg" /></div>
                    <div><input type="hidden" name="val[color_border]" value="{value type='input' id='color_border' default='bcccd1'}" id="js_colorpicker_drop_border" /></div>
                    <div><input type="hidden" name="val[color_text]" value="{value type='input' id='color_text' default='1280c9'}" id="js_colorpicker_drop_text" /></div>
                    <div style="display:none;"><textarea cols="40" rows="6" name="val[html_code]" id="html_code">{value type='textarea' id='html_code'}</textarea></div>

                    <div id="step_1">
                        <div class="profile-edit-headline"><span class="content">{_p var='general_infomation'}</span></div>

                        <div class="form-group">
                            <label for="type_image" class="control-label mr-2 mb-0">{required}{_p var='ad_type'}</label>
                            <label class="radio-inline control-label">
                                <input type="radio" value="1" name="val[type_id]" id="type_image" class="type_id" checked>
                                {_p var='image'}
                            </label>
                            <label class="radio-inline control-label">
                                <input type="radio" value="2" name="val[type_id]" id="type_html" class="type_id">
                                {_p var='html'}
                            </label>
                        </div>

                        <div class="form-group">
                            <label for="better_ads_location">{required}{_p var='better_ads_ad_placement'} <a href="#?call=ad.sample&amp;no-click&amp;fullmode=true" class="fw-normal fz-12 inlinePopup" title="{_p var='admincp.sample_layout'}">({_p var='view_sample_layout'})</a></label>
                            <select name="val[location]" id="better_ads_location" class="form-control">
                                {foreach from=$aAllPlans key=ikey value=aPlan}
                                <option value="{$aPlan.plan_id}"{value type='select' id='location' default=$aPlan.plan_id} data-is-cpm="{$aPlan.is_cpm}" data-cost="{$aPlan.default_cost}" data-block-id="{$aPlan.block_id}">
                                    {$aPlan.title} &bull; {$aPlan.block_title}&nbsp;({$aPlan.price_text})
                                </option>
                                {/foreach}
                            </select>
                        </div>

                        <div class="form-group">
                            <label>{required}{_p var='better_ads_image'}</label>
                            {if !empty($sCurrentPhoto)}
                                {module name='core.upload-form' type=ad current_photo=$sCurrentPhoto id=$aForms.ads_id}
                            {else}
                                {module name='core.upload-form' type='ad'}
                            {/if}
                            <p class="help-block">{_p var='recommended_dimension'}: <span id="recommended-demension"></span></p>
                        </div>

                        <div class="form-group">
                            <label>{required}{_p var='better_ads_destination_url'}</label>
                            <input type="text" name="val[url_link]" value="{value type='input' id='url_link'}" size="50" id="url_link" class="form-control" placeholder="http://www.yourwebsite.com" required />
                        </div>

                        <div class="form-group" data-type="image">
                            <label for="image_tooltip_text">{_p var='image_tooltip_text'}</label>
                            <input type="text" name="val[image_tooltip_text]" value="{value type='input' id='image_tooltip_text'}" id="image_tooltip_text" size="40" class="form-control"/>
                        </div>

                        <div class="form-group hide" data-type="html">
                            <label for="title">{required}{_p var='better_ads_title'}</label>
                            <input type="text" class="form-control" id="title" placeholder="{_p var='ad_title_heading'}" name="val[title]" data-character-limit="25" maxlength="25">
                            <p class="help-block">{_p var='remain_number_characters' number=25}</p>
                        </div>

                        <div class="form-group hide" data-type="html">
                            <label for="body">{required}{_p var='better_ads_body_text'}</label>
                            <textarea class="form-control" cols="40" rows="6" name="val[body]" id="body" data-character-limit="135" maxlength="135">{value type='textarea' id='ads_text'}</textarea>
                            <p class="help-block">{_p var='remain_number_characters' number=135}</p>
                        </div>

                        <div class="form-group {if isset($isSubmitted)}hide{/if}" id="js_ads_continue_form_button">
                            <input type="button" value="{_p var='better_ads_continue'}" class="btn btn-primary mr-1 pull-left" id="betterads-add-continue" />
                            <a role="button" class="btn btn-default betterads-preview">{_p var='preview'}</a>
                        </div>
                    </div>

                    <div id="ad_details" class="{if !isset($isSubmitted)}hide{/if}">
                        <div class="profile-edit-headline"><span class="content">{_p var='ad_details'}</span></div>

                        <div class="form-group">
                            <label>{required}{_p var='ad_name'}</label>
                            <input type="text" name="val[name]" value="{value type='input' id='name'}" size="25" id="name" class="form-control"/>
                        </div>

                        {template file='ad.block.targetting'}

                        <div class="form-group bts-add-startday">
                            <label>{_p var='better_ads_start_date'}</label>
                            {select_date prefix='start_' start_year='current_year' end_year='+10' field_separator=' / ' field_order='MDY' default_all=true add_time=true time_separator='core.time_separator'}
                            <p class="help-block">
                                {_p var='better_ads_note_the_time_is_set_to_your_registered_time_zone'}
                            </p>
                        </div>

                        <div class="form-group">
                            <div class="form-inline ml--1 mr--1">
                                <div class="form-group px-1">
                                    <label id="js_ads_cpm" class="d-block">{if $aAllPlans.0.is_cpm}{_p var='better_ads_impressions'}{else}{_p var='better_ads_clicks'}{/if}</label>
                                    <div><input type="hidden" name="val[ad_cost]" value="" size="15" id="js_total_ads_cost" /></div>
                                    <div><input type="hidden" name="val[is_cpm]" value="" size="15" id="js_is_cpm" /></div>
                                    <input type="number" name="val[total_view]" value="{value type='input' id='total_view' default='1000'}" size="15" id="total_view" min="1000" class="form-control"/>
                                    <span id="js_ads_cost" style="font-weight:bold;"></span>
                                    <div class="extra_info" id="js_ad_info_cost"></div>
                                </div>
                                <div class="form-group px-1">
                                    <label class="d-block" for="total_cost">{_p var='total_cost'}</label>
                                    <div class="input-group betterads-disable-input">
                                        <input type="text" class="form-control disabled" id="total_cost" value="{$aAllPlans.0.default_cost}" data-cost="{$aAllPlans.0.default_cost}" disabled>
                                        <div class="input-group-addon">{$sCurrency}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group core-ads-add-button-group">
                            <input type="submit" value="{_p var='better_ads_submit'}" class="btn btn-primary mr-1" id="js_submit_button" />
                            <a role="button" class="btn btn-default betterads-preview">{_p var='preview'}</a>
                        </div>
                    </div>
                </form>
            {/if}
        {/if}
    {/if}
</div>