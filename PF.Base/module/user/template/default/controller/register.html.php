<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{literal}

<script type="text/javascript">
    $Behavior.termsAndPrivacy = function()
    {
        $('#js_terms_of_use').click(function() {
            {/literal}
            tb_show('{_p var='terms_of_use' phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true}', $.ajaxBox('page.view', 'height=410&width=600&id=2&title=terms'));
            {literal}
            return false;
        });

        $('#js_privacy_policy').click(function() {
            {/literal}
            tb_show('{_p var='privacy_policy' phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true phpfox_squote=true}', $.ajaxBox('page.view', 'height=410&width=600&id=1&title=policy'));
            {literal}
            return false;
        });
    }
</script>
{/literal}
{if !empty($message)}
    <div class="extra_info message">{$message}</div>
{/if}

{if Phpfox::isModule('invite') && Phpfox::getService('invite')->isInviteOnly()}
    <div class="main_break">
        <p class="help-block">
            {if Phpfox::getParam('core.enable_register_with_phone_number')}
                {_p var='ssitetitle_is_an_invite_only_community_enter_your_email_or_phone_number_below_if_you_have_received_an_invitation' sSiteTitle=$sSiteTitle}
            {else}
                {_p var='ssitetitle_is_an_invite_only_community_enter_your_email_below_if_you_have_received_an_invitation' sSiteTitle=$sSiteTitle}
            {/if}
        </p>
        <div class="main_break">
            <form method="post" class="form" action="{url link='user.register'}">
                <div class="form-group">
                    <label class="invite_email">{_p var='email'}</label>
                    <input type="text" id="invite_email" name="val[invite_email]" value="" />
                </div>
                <div class="form-group">
                    <input type="submit" value="{_p var='submit'}" class="btn btn-success" />
                </div>
            </form>
        </div>
    </div>
{else}
    {if isset($sCreateJs)}
        {$sCreateJs}
    {/if}
    <div id="js_registration_process" class="t_center" style="display:none;">
        <div class="p_top_8">
            {img theme='ajax/add.gif' alt=''}
        </div>
    </div>
    <div id="js_signup_error_message" style="width:350px;"></div>
    {if Phpfox::getParam('user.allow_user_registration')}
        <ul class="signin_signup_tab clearfix">
            <li><a class="keepPopup" rel="hide_box_title" href="{url link='login'}">{_p var='sign_in'}</a></li>
            <li class="active"><a rel="hide_box_title" href="javascript:void(0)">{_p var='sign_up'}</a></li>
        </ul>
    <div class="main_break" id="js_registration_holder">
        <form method="post" class="form" action="{url link='user.register'}" id="js_form" enctype="multipart/form-data" data-step="1">
            {token}

            <div id="js_signup_block">
                {if isset($bIsPosted) || !Phpfox::getParam('user.multi_step_registration_form')}
                <div>
                    {template file='user.block.register.step1'}
                    {template file='user.block.register.step2'}
                </div>
                {else}
                    {template file='user.block.register.step1'}
                {/if}
            </div>


            {if Phpfox::getParam('user.new_user_terms_confirmation')}
                <div id="js_register_accept">
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="val[agree]" id="agree" value="1" class="checkbox v_middle" {value type='checkbox' id='agree' default='1'}/> {required}{_p var='i_have_read_and_agree_to_the_a_href_id_js_terms_of_use_terms_of_use_a_and_a_href_id_js_privacy_policy_privacy_policy_a'}
                        </label>
                    </div>
                </div>
            {/if}

            <div class="form-button-group register-form-button-group">
                <div class="form-group">
                    {if isset($bIsPosted) || !Phpfox::getParam('user.multi_step_registration_form')}
                        <button type="submit" class="btn btn-primary text-uppercase" id="js_registration_submit">{_p var='sign_up_button'}</button>
                    {else}
                        <input type="button" value="{_p var='previous_step'}" class="btn btn-default text-uppercase" id="js_registration_back_previous" onclick="$Core.registration.backPreviousForm();" />
                        <input type="button" value="{_p var='next_step'}" class="btn btn-success text-uppercase" id="js_registration_submit" onclick="$Core.registration.submitForm();" />
                    {/if}
                </div>
                <div class="form-group already-member">
                    {_p var='i_m_already_member'}
                    <a class="keepPopup" rel="hide_box_title visitor_form" href="{url link='login'}">{_p var='sign_in_now'}</a>
                </div>
            </div>

            {plugin call='user.template.register_header_set_var'}
            {if isset($bCustomLogin)}
                <div class="custom_signup_fb">
                    <div class="item-or-line"><span>{_p var='or'}</span></div>
                    <div class="p_top_4">
                        {plugin call='user.template_controller_register_block__end'}
                    </div>
                </div>
            {/if}
        </form>
        {if !PHPFOX_IS_AJAX}
            <script type="text/javascript">
                document.getElementById('js_form').getElementsByTagName("input")[0].focus()
            </script>
        {/if}
    </div>
{/if}
{/if}
