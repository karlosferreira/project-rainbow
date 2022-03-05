<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !empty($sCreateJs)}
    {$sCreateJs}
{/if}
{if !empty($bVerifyTwoStepLogin)}
    <form method="post" action="{url link='login' token=$sCurrentToken}" id="js_login_form">
        <div class="form-group">
            <p class="mb-1"><strong>{_p var='two_step_verification_explain_in_login_process'}</strong></p>
            <p class="mb-2"><strong>{_p var='please_enter_your_authenticator_six_digit_code'}</strong></p>
            <input class="form-control" required placeholder="{_p var='enter_passcode'}" type="text" name="val[passcode]" id="passcode" value="" size="40" />
            <input type="hidden" name="val[login]" value="{$sCurrentLogin}">
            <input type="hidden" name="val[password]" value="{$sCurrentPassword}">
            <input type="hidden" name="val[token]" value="{$sCurrentToken}">
            {if !empty($sCurrentRemember)}
                <input type="hidden" name="val[remember_me]" value="1">
            {/if}
            <p class="help-block">{_p var='get_a_verification_code_from_the_authenticator_app'}</p>
            <a id="js_login_passcode_note" class="login-passcode-note" href="javascript:void(0)" onclick="tb_show('{_p var='try_another_way_to_authenticate'}', $.ajaxBox('user.getAuthMethods', 'user_id={$sCurrentLoginUser}&amp;width=500&amp;height=300'));return false;">{_p var='try_another_way_to_authenticate'}</a><span id="js_login_passcode_waiting_time" class="pl-1 text text-danger"></span>
        </div>
        <div class="form-button-group">
            <button id="_submit" type="submit" class="btn btn-primary mr-1">
                {_p var='verify_passcode'}
            </button>
            <a href="{url link=''}" class="btn btn-default">{_p var='cancel'}</a>
        </div>
    </form>
{else}
    {plugin call='user.template_controller_login_block__start'}
    <form method="post" action="{url link='user.login'}" id="js_login_form" {if !empty($sGetJsForm)}onsubmit="{$sGetJsForm}"{/if}>
        <div class="form-group">
            {if !Phpfox::getParam('core.enable_register_with_phone_number')}
                <input class="form-control" placeholder="{if Phpfox::getParam('user.login_type') == 'user_name'}{_p var='user_name'}{elseif Phpfox::getParam('user.login_type') == 'email'}{_p var='email'}{else}{_p var='email_or_user_name'}{/if}" type="{if Phpfox::getParam('user.login_type') == 'email'}email{else}text{/if}" name="val[login]" id="login" value="{$sDefaultEmailInfo}" size="40" autofocus/>
            {else}
                <input class="form-control" placeholder="{if Phpfox::getParam('user.login_type') == 'user_name'}{_p var='user_name'}{elseif Phpfox::getParam('user.login_type') == 'email'}{_p var='email_or_phone_number'}{else}{_p var='email_or_user_name_or_phone_number'}{/if}" type="text" name="val[login]" id="login" value="{$sDefaultEmailInfo}" size="40" autofocus/>
                {if Phpfox::getParam('user.login_type') != 'user_name'}
                    {module name='user.phone-number-country-codes' init_onchange=1 phone_field_id='#login'}
                {/if}
            {/if}
        </div>

        <div class="form-group">
            <input class="form-control" placeholder="{_p var='password'}" type="password" name="val[password]" id="login_password" value="" size="40" autocomplete="off" />
        </div>

        {if Phpfox::isModule('captcha') && Phpfox::getParam('user.captcha_on_login') && ($sCaptchaType = Phpfox::getParam('captcha.captcha_type'))}
            <div id="js_register_capthca_image" class="{$sCaptchaType}">
                {module name='captcha.form'}
            </div>
        {/if}

        {plugin call='user.template_controller_login_end'}

        <div class="form-group remember-box">
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="checkbox" name="val[remember_me]" value="" />
                    {_p var='remember'}
                </label>
            </div>

            <div>
                <a class="no_ajax" href="{url link='user.password.request'}">{_p var='forgot_your_password'}</a>
            </div>
        </div>

        <div class="form-button-group">
            <button id="_submit" type="submit" class="btn btn-primary">
                {_p var='sign_in'}
            </button>

            {plugin call='user.template.login_header_set_var'}

            {if Phpfox::getParam('user.allow_user_registration')}
                <div class="form-group new-member">
                    {_p var='need_an_account'}
                    {if !empty($bSlideForm)}
                        <a href="javascript:void(0);" class="js-slide-btn">{_p var='sign_up_now'}</a>
                    {else}
                        <a class="keepPopup" rel="hide_box_title visitor_form" href="{url link='user.register'}">{_p var='sign_up'}</a>
                    {/if}
                </div>
            {/if}

            <input type="hidden" name="val[parent_refresh]" value="1" />
            {if isset($sMainUrl)}
                <input type="hidden" name="val[redirect_url]" value="{$sMainUrl}">
            {/if}
        </div>

        {if isset($bCustomLogin)}
            <div class="form-button-group form-login-custom-fb">
                <div class="custom-fb-or"><span>{_p var='or'}</span></div>
                <div class="custom_fb">
                    {plugin call='user.template_controller_login_block__end'}
                </div>
            </div>
        {/if}
    </form>
{/if}
