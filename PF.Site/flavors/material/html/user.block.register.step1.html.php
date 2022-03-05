<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div id="js_register_step1">
    {plugin call='user.template_default_block_register_step1_3'}
    {if Phpfox::getParam('user.disable_username_on_sign_up') != 'username'}
        {if Phpfox::getParam('user.split_full_name')}
            <input type="hidden" name="val[full_name]" id="full_name" value="" size="30" />

            <div class="form-group">
                <input class="form-control" placeholder="{_p var='first_name'}" type="text" name="val[first_name]" id="first_name" value="{value type='input' id='first_name'}" size="30" />
            </div>
            <div class="form-group">
                <input class="form-control" placeholder="{_p var='last_name'}" type="text" name="val[last_name]" id="last_name" value="{value type='input' id='last_name'}" size="30" />
            </div>
        {else}
            <div class="form-group">
                <input class="form-control" placeholder="{if Phpfox::getParam('user.display_or_full_name') == 'full_name'}{_p var='full_name'} {else} {_p var='display_name'} {/if}" type="text" name="val[full_name]" id="full_name" value="{value type='input' id='full_name'}" size="30" />
            </div>
        {/if}
    {/if}
    {if !Phpfox::getParam('user.profile_use_id') && (Phpfox::getParam('user.disable_username_on_sign_up') != 'full_name')}
        <div class="form-group">
            <input class="form-control" placeholder="{_p var='choose_a_username'}" type="text" name="val[user_name]" id="user_name" title="{_p var='your_username_is_used_to_easily_connect_to_your_profile'}" value="{value type='input' id='user_name'}" size="30" autocomplete="off" />
            <div id="js_user_name_error_message"></div>
            <div style="display:none;" id="js_verify_username"></div>
        </div>
    {/if}
    {if Phpfox::getParam('user.reenter_email_on_signup')}
        <div class="separate"></div>
    {/if}
    <div class="form-group">
        <input class="form-control {if !empty($sEmailClass)}{$sEmailClass}{/if}" placeholder="{if Phpfox::getParam('core.enable_register_with_phone_number')}{_p var='email_or_phone_number'}{else}{_p var='email'}{/if}" type="text" name="val[email]" id="email" value="{value type='input' id='email'}" size="30" />
    </div>

    {if Phpfox::getParam('user.reenter_email_on_signup')}
        <div class="form-group">
            <div class="p_top_8">
                <input class="form-control {if !empty($sConfirmEmailClass)}{$sConfirmEmailClass}{/if}" type="text" name="val[confirm_email]" id="confirm_email" value="{value type='input' id='confirm_email'}" size="30" placeholder="{if Phpfox::getParam('core.enable_register_with_phone_number')}{_p var='reenter_email_or_phone_number'}{else}{_p var='reenter_email'}{/if}"/>
            </div>
        </div>
        <div class="separate"></div>
    {/if}
    {plugin call='user.template_default_block_register_step1_5'}
    <div class="form-group">
        {if isset($bIsPosted)}
            <input class="form-control" placeholder="{_p var='password'}" type="password" name="val[password]" id="register_password" value="{value type='input' id='password'}" size="30" autocomplete="new-password" />
        {else}
            <input class="form-control" placeholder="{_p var='password'}" type="password" name="val[password]" id="register_password" value="" size="30" autocomplete="new-password" />
        {/if}
    </div>
    {if Phpfox::getParam('user.signup_repeat_password')}
        <div class="form-group">
            <input class="form-control" placeholder="{_p var='repassword'}" type="password" name="val[repassword]" id="register_repassword" value="" size="30" autocomplete="new-password" />
        </div>
    {/if}
    {plugin call='user.template_default_block_register_step1_4'}
</div>

{if Phpfox::getParam('core.enable_register_with_phone_number')}
    {if !empty($sEmailClass)}
        {module name='user.phone-number-country-codes' init_onchange=1 phone_field_id=$sEmailClass}
    {else}
        {module name='user.phone-number-country-codes' init_onchange=1 phone_field_id='#email'}
    {/if}
    {if Phpfox::getParam('user.reenter_email_on_signup')}
        {if !empty($sConfirmEmailClass)}
            {module name='user.phone-number-country-codes' init_onchange=1 phone_field_id=$sConfirmEmailClass}
        {else}
            {module name='user.phone-number-country-codes' init_onchange=1 phone_field_id='#confirm_email'}
        {/if}
    {/if}
{/if}