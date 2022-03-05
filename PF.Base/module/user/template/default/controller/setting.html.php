<?php 
defined('PHPFOX') or exit('NO DICE!');
?>

{$sCreateJs}
<div class="main_break">
	<form class="form" method="post" action="{url link='user.setting'}" id="js_form" onsubmit="{$sGetJsForm}">
		{if Phpfox::getUserId() == $aForms.user_id && Phpfox::getUserParam('user.can_change_own_full_name')}
		
		{if Phpfox::getParam('user.split_full_name')}
		<div><input type="hidden" name="val[full_name]" id="full_name" value="{value type='input' id='full_name'}" size="30" /></div>
		<div class="form-group">
            <label for="first_name" for="first_name">{required}{_p var='first_name'}:</label>
            <input class="form-control" type="text" name="val[first_name]" id="first_name" value="{value type='input' id='first_name'}" size="30" {if $iTotalFullNameChangesAllowed != 0 && $aForms.total_full_name_change >= $iTotalFullNameChangesAllowed}readonly="readonly"{/if} />
		</div>

		<div class="form-group">
            <label for="last_name" for="last_name">{required}{_p var='last_name'}:</label>
            <input class="form-control" type="text" name="val[last_name]" id="last_name" value="{value type='input' id='last_name'}" size="30" {if $iTotalFullNameChangesAllowed != 0 && $aForms.total_full_name_change >= $iTotalFullNameChangesAllowed}readonly="readonly"{/if} />
		</div>

		{if $iTotalFullNameChangesAllowed > 0}
		<p class="help-block">
			{_p var='total_full_name_change_out_of_allowed' total_full_name_change=$aForms.total_full_name_change allowed=$iTotalFullNameChangesAllowed}
		</p>
		{/if}
		<!-- <div class="separate"></div> -->
		{else}		
		<div class="form-group">
            <label for="full_name">{required}{$sFullNamePhrase}:</label>
            {if $iTotalFullNameChangesAllowed != 0 && $aForms.total_full_name_change >= $iTotalFullNameChangesAllowed}
            <input class="form-control" type="text" name="val[full_name]" id="full_name" value="{value type='input' id='full_name'}" size="30" readonly="readonly" />
            {else}
            <input class="form-control" type="text" name="val[full_name]" id="full_name" value="{value type='input' id='full_name'}" size="30" />
            {/if}

            {if $iTotalFullNameChangesAllowed > 0}
            <div class="extra_info">
                {_p var='total_full_name_change_out_of_allowed' total_full_name_change=$aForms.total_full_name_change allowed=$iTotalFullNameChangesAllowed}
            </div>
            {/if}
		</div>
		{/if}
		{/if}
		{if Phpfox::getUserParam('user.can_change_own_user_name') && !Phpfox::getParam('user.profile_use_id')}
		<div class="form-group">
            <label for="user_name">{required}{_p var='username'}:</label>
            {if $aForms.total_user_change >= $iTotalChangesAllowed && $iTotalChangesAllowed > 0}
            <input class="form-control" type="text" name="val[user_name]" id="user_name" value="{value type='input' id='user_name'}" size="30" readonly="readonly" />
            {else}
            <input class="form-control" type="text" name="val[user_name]" id="user_name" value="{value type='input' id='user_name'}" size="30" />
            {/if}
            {if $iTotalChangesAllowed > 0}
            <div class="extra_info">
                {_p var='total_user_change_out_of_total_user_name_changes' total_user_change=$aForms.total_user_change total=$iTotalChangesAllowed}
            </div>
            {/if}
            <div><input type="hidden" name="val[old_user_name]" id="old_user_name" value="{value type='input' id='user_name'}" size="30" /></div>
		</div>
		{/if}
		{if Phpfox::getUserParam('user.can_change_email') }
            <div class="form-group">
                <label for="email">{if !Phpfox::getParam('core.enable_register_with_phone_number')}{required}{/if}{_p var='email_address'}:</label>
                {if $bEnable2StepVerification}
                    <div class="extra_info">
                        <a href="{url link='user.passcode'}">{_p var='get_new_google_authencator_barcode_when_you_change_email'}</a>
                    </div>
                {/if}
                <input class="form-control" type="text" {if Phpfox::getParam('user.verify_email_at_signup')}onfocus="$('#js_email_warning').show();" {/if}name="val[email]" id="email" value="{value type='input' id='email'}" size="30" />
                {if !empty($iVerifyType) && $iVerifyType === 1}
                    <div class="help-block">
                        <span>{_p var='you_need_to_verify_your_email_to_be_able_to_login_with_email'}</span>
                        <span>
                            <a href="javascript:void(0);" onclick="$.ajaxCall('user.verifySendEmail', 'iUser={$aForms.user_id}'); return false;">{_p var='resend_verification_email'}</a>
                        </span>
                    </div>
                {elseif Phpfox::getParam('user.verify_email_at_signup')}
                    <div class="help-block" style="display:none;" id="js_email_warning">
                        {_p var='changing_your_email_address_requires_you_to_verify_your_new_email'}
                    </div>
                {/if}
            </div>
		{/if}
        {if Phpfox::getParam('core.enable_register_with_phone_number')}
            <div class="item-info">
                <div class="form-group">
                    <label for="phone_number" class="">{_p var='phone_number'}:</label>
                    {if $bEnable2StepVerification}
                        <div class="extra_info">
                            <a href="{url link='user.passcode'}">{_p var='get_new_authenticator_barcode_when_you_change_phone'}</a>
                        </div>
                    {/if}
                    {if Phpfox::getUserParam('user.can_change_phone')}
                        <input class="form-control" type="text" onfocus="$('#js_phone_warning').show();" name="val[phone_number]" id="phone_number" value="{value type='input' id='phone_number'}" size="30" />
                        {if !empty($bIsWaitingVerifyPhone) && !empty($sWaitingVerifyPhone)}
                            <div class="help-block">
                                <a href="{url link='user.sms.send' sent=1 force=1}">{_p var='click_here_to_verify_your_new_phone_number_number' number=$sWaitingVerifyPhone}</a>
                            </div>
                        {/if}
                        <div class="help-block" style="display:none;" id="js_phone_warning">
                            {_p var='changing_your_phone_number_requires_you_to_verify_your_new_one'}
                        </div>
                    {else}
                        <input class="form-control" type="text" readonly onfocus="$('#js_phone_warning').show();" id="phone_number" value="{value type='input' id='phone_number'}" size="30" />
                    {/if}
                </div>
            </div>
            {module name='user.phone-number-country-codes' phone_field_id='#phone_number' default_phone_number=$aForms.full_phone_number}
        {/if}
		{if !Phpfox::getUserBy('fb_user_id')}
			<div class="form-group">
                <label for="password">{required}{_p var='password'}</label>
                <div id="js_password_info" style="padding-top:2px;"><a href="#" onclick="tb_show('{_p var='change_password' phpfox_squote=true}', $.ajaxBox('user.changePassword', 'height=250&amp;width=500')); return false;">{_p var='change_password'}</a></div>
			</div>
		{/if}

		<div class="form-group">
            <label for="language_id">{_p var='primary_language'}</label>
            <select class="form-control" name="val[language_id]" id="language_id">
            {foreach from=$aLanguages item=aLanguage}
                <option value="{$aLanguage.language_id}"{value type='select' id='language_id' default=$aLanguage.language_id}>{$aLanguage.title|clean}</option>
            {/foreach}
            </select>
		</div>

		<div class="form-group js_core_init_selectize_form_group" id="tbl_time_zone">
			<label>{_p var='time_zone'}</label>
            <select class="form-control" name="val[time_zone]" id="time_zone">
                {foreach from=$aTimeZones key=sTimeZoneKey item=sTimeZone}
                <option value="{$sTimeZoneKey}"{if (empty($aForms.time_zone) && $sTimeZoneKey == Phpfox::getParam('core.default_time_zone_offset')) || (!empty($aForms.time_zone) && $aForms.time_zone == $sTimeZoneKey)} selected="selected"{/if}>{$sTimeZone}</option>
                {/foreach}
            </select>
            {if PHPFOX_USE_DATE_TIME != true && Phpfox::getParam('core.identify_dst')}
                <div class="extra_info">
                    <label><input type="checkbox" name="val[dst_check]" value="1" class="v_middle" {if $aForms.dst_check}checked="checked" {/if}/> {_p var='enable_dst_daylight_savings_time'}</label>
                </div>
            {/if}
		</div>
		
		{if Phpfox::getUserParam('user.can_edit_currency')}
			
		<div class="form-group">
			<label>{_p var='preferred_currency'}</label>
            <select  class="form-control" name="val[default_currency]">
                <option value="">{_p var='select'}:</option>
            {foreach from=$aCurrencies key=sCurrency item=aCurrency}
                <option value="{$sCurrency}"{if $aForms.default_currency == $sCurrency} selected="selected"{/if}>{_p var=$aCurrency.name}</option>
            {/foreach}
            </select>
            <p class="help-block">
                {_p var='show_prices_and_make_purchases_in_this_currency'}
            </p>
		</div>
		{/if}

		{plugin call='user.template_controller_setting'}
		
		<div class="from-group">
			<input type="submit" value="{_p var='save'}" class="btn btn-primary" />
		</div>

        <div class="form-group required">
            {_p var='required_fields'}
        </div>

        <h3>{_p var='two_step_verification'}</h3>
        <div class="form-group">
            <div class="profile-edit-radio-holder privacy-block-content">
                <div class="item_label">
                    {_p var='google_2step_verify_description'}
                    {if $bEnable2StepVerification}
                        <p class="help-block">
                            <a href="{url link='user.passcode'}" target="_blank" class="no_ajax">{_p var='setup_authenticator_app'}</a>
                        </p>
                    {/if}
                </div>
                <input type="hidden" value="{if $bEnable2StepVerification}1{else}0{/if}" name="val[current_two_step_verification]">
                <div class="item_is_active_holder">
                    <span class="js_item_active item_is_active">
                        <input type="radio" value="1" name="val[two_step_verification]" {if $bEnable2StepVerification} checked="checked"{/if} class="checkbox" /> {_p var='yes'}
                    </span>
                    <span class="js_item_active item_is_not_active">
                        <input type="radio" value="0" name="val[two_step_verification]" {if !$bEnable2StepVerification} checked="checked"{/if} class="checkbox" /> {_p var='no'}
                    </span>
                </div>
            </div>
        </div>
        <div class="error_message" id="js_warning_disable_tsv" style="display: none">
            {_p var='turning_off_two_step_verification_notice'}
        </div>
        <div id="js_two_step_confirm_password" style="display: none">
            <div class="form-group">
                <label>{required}{_p var='enter_your_password'}</label>
                <input type="password" name="val[two_step_confirm_password]" class="form-control"/>
                <div class="extra_info mb-2">{_p var='you_need_to_enter_your_password_to_confirm_this_change'}</div>
                <div class="error_message" id="js_two_step_confirm_password_error" style="display: none"></div>
                <button type="button" class="btn btn-primary" id="js_confirm_change_tsv">{_p var='save'}</button>
            </div>
        </div>

		{if isset($aGateways) && is_array($aGateways) && count($aGateways)}
		<h3>{_p var='payment_methods'}</h3>
		{foreach from=$aGateways item=aGateway}
        <div class="form-sub-group">
            <div class="sub-group-title">{$aGateway.title}</div>
            <div class="sub-group-desc">{$aGateway.description}</div>
            <div class="sub-group-elems">
			{foreach from=$aGateway.custom key=sFormField item=aCustom}
			<div class="form-group">
				<label>{$aCustom.phrase}</label>
                {if (isset($aCustom.type) && $aCustom.type == 'textarea')}
                    <textarea  class="form-control" name="val[gateway_detail][{$aGateway.gateway_id}][{$sFormField}]" cols="50" rows="8">{if isset($aCustom.user_value)}{$aCustom.user_value|clean}{/if}</textarea>
                {else}
                    <input  class="form-control" type="text" name="val[gateway_detail][{$aGateway.gateway_id}][{$sFormField}]" value="{if isset($aCustom.user_value)}{$aCustom.user_value|clean}{/if}" size="40" />
                {/if}
                {if !empty($aCustom.phrase_info)}
                <div class="extra_info">
                    {$aCustom.phrase_info}
                </div>
                {/if}
			</div>
			{/foreach}
            </div>
        </div>
		{/foreach}

		<div class="form-group">
			<input type="submit" value="{_p var='save'}" class="btn btn-primary" />
		</div>
		{/if}
		{if (Phpfox::getUserParam('user.can_delete_own_account'))}
		<div class="p_top_8">
			<a href="{url link='user.remove'}">{_p var='cancel_account'}</a>
		</div>
		{/if}
        <div class="p_top_8">
            <a href="{url link='user.export'}">{_p var='download_a_copy_of_your_data'}</a>
        </div>
	</form>
</div>
