<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 1:06 am */ ?>
<?php

 if (! empty ( $this->_aVars['sCreateJs'] )): ?>
<?php echo $this->_aVars['sCreateJs'];  endif;  if (! empty ( $this->_aVars['bVerifyTwoStepLogin'] )): ?>
    <form method="post" action="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('login', array('token' => $this->_aVars['sCurrentToken']), false, false); ?>" id="js_login_form">
        <div class="form-group">
            <p class="mb-1"><strong><?php echo _p('two_step_verification_explain_in_login_process'); ?></strong></p>
            <p class="mb-2"><strong><?php echo _p('please_enter_your_authenticator_six_digit_code'); ?></strong></p>
            <input class="form-control" required placeholder="<?php echo _p('enter_passcode'); ?>" type="text" name="val[passcode]" id="passcode" value="" size="40" />
            <input type="hidden" name="val[login]" value="<?php echo $this->_aVars['sCurrentLogin']; ?>">
            <input type="hidden" name="val[password]" value="<?php echo $this->_aVars['sCurrentPassword']; ?>">
            <input type="hidden" name="val[token]" value="<?php echo $this->_aVars['sCurrentToken']; ?>">
<?php if (! empty ( $this->_aVars['sCurrentRemember'] )): ?>
                <input type="hidden" name="val[remember_me]" value="1">
<?php endif; ?>
            <p class="help-block"><?php echo _p('get_a_verification_code_from_the_authenticator_app'); ?></p>
            <a id="js_login_passcode_note" class="login-passcode-note" href="javascript:void(0)" onclick="tb_show('<?php echo _p('try_another_way_to_authenticate'); ?>', $.ajaxBox('user.getAuthMethods', 'user_id=<?php echo $this->_aVars['sCurrentLoginUser']; ?>&amp;width=500&amp;height=300'));return false;"><?php echo _p('try_another_way_to_authenticate'); ?></a><span id="js_login_passcode_waiting_time" class="pl-1 text text-danger"></span>
        </div>
        <div class="form-button-group">
            <button id="_submit" type="submit" class="btn btn-primary mr-1">
<?php echo _p('verify_passcode'); ?>
            </button>
            <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('', [], false, false); ?>" class="btn btn-default"><?php echo _p('cancel'); ?></a>
        </div>
    
</form>

<?php else: ?>
<?php (($sPlugin = Phpfox_Plugin::get('user.template_controller_login_block__start')) ? eval($sPlugin) : false); ?>
    <form method="post" action="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.login', [], false, false); ?>" id="js_login_form" <?php if (! empty ( $this->_aVars['sGetJsForm'] )): ?>onsubmit="<?php echo $this->_aVars['sGetJsForm']; ?>"<?php endif; ?>>
        <div class="form-group">
<?php if (! Phpfox ::getParam('core.enable_register_with_phone_number')): ?>
                <input class="form-control" placeholder="<?php if (Phpfox ::getParam('user.login_type') == 'user_name'):  echo _p('user_name');  elseif (Phpfox ::getParam('user.login_type') == 'email'):  echo _p('email');  else:  echo _p('email_or_user_name');  endif; ?>" type="<?php if (Phpfox ::getParam('user.login_type') == 'email'): ?>email<?php else: ?>text<?php endif; ?>" name="val[login]" id="login" value="<?php echo $this->_aVars['sDefaultEmailInfo']; ?>" size="40" autofocus/>
<?php else: ?>
                <input class="form-control" placeholder="<?php if (Phpfox ::getParam('user.login_type') == 'user_name'):  echo _p('user_name');  elseif (Phpfox ::getParam('user.login_type') == 'email'):  echo _p('email_or_phone_number');  else:  echo _p('email_or_user_name_or_phone_number');  endif; ?>" type="text" name="val[login]" id="login" value="<?php echo $this->_aVars['sDefaultEmailInfo']; ?>" size="40" autofocus/>
<?php if (Phpfox ::getParam('user.login_type') != 'user_name'): ?>
<?php Phpfox::getBlock('user.phone-number-country-codes', array('init_onchange' => '1','phone_field_id' => '#login')); ?>
<?php endif; ?>
<?php endif; ?>
        </div>

        <div class="form-group">
            <input class="form-control" placeholder="<?php echo _p('password'); ?>" type="password" name="val[password]" id="login_password" value="" size="40" autocomplete="off" />
        </div>

<?php if (Phpfox ::isModule('captcha') && Phpfox ::getParam('user.captcha_on_login') && ( $this->_aVars['sCaptchaType'] = Phpfox ::getParam('captcha.captcha_type'))): ?>
            <div id="js_register_capthca_image" class="<?php echo $this->_aVars['sCaptchaType']; ?>">
<?php Phpfox::getBlock('captcha.form', array()); ?>
            </div>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('user.template_controller_login_end')) ? eval($sPlugin) : false); ?>

        <div class="form-group remember-box">
            <div class="checkbox">
                <label>
                    <input type="checkbox" class="checkbox" name="val[remember_me]" value="" />
<?php echo _p('remember'); ?>
                </label>
            </div>

            <div>
                <a class="no_ajax" href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.password.request', [], false, false); ?>"><?php echo _p('forgot_your_password'); ?></a>
            </div>
        </div>

        <div class="form-button-group">
            <button id="_submit" type="submit" class="btn btn-primary">
<?php echo _p('sign_in'); ?>
            </button>

<?php (($sPlugin = Phpfox_Plugin::get('user.template.login_header_set_var')) ? eval($sPlugin) : false); ?>

<?php if (Phpfox ::getParam('user.allow_user_registration')): ?>
                <div class="form-group new-member">
<?php echo _p('need_an_account'); ?>
<?php if (! empty ( $this->_aVars['bSlideForm'] )): ?>
                        <a href="javascript:void(0);" class="js-slide-btn"><?php echo _p('sign_up_now'); ?></a>
<?php else: ?>
                        <a class="keepPopup" rel="hide_box_title visitor_form" href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.register', [], false, false); ?>"><?php echo _p('sign_up'); ?></a>
<?php endif; ?>
                </div>
<?php endif; ?>

            <input type="hidden" name="val[parent_refresh]" value="1" />
<?php if (isset ( $this->_aVars['sMainUrl'] )): ?>
                <input type="hidden" name="val[redirect_url]" value="<?php echo $this->_aVars['sMainUrl']; ?>">
<?php endif; ?>
        </div>

<?php if (isset ( $this->_aVars['bCustomLogin'] )): ?>
            <div class="form-button-group form-login-custom-fb">
                <div class="custom-fb-or"><span><?php echo _p('or'); ?></span></div>
                <div class="custom_fb">
<?php (($sPlugin = Phpfox_Plugin::get('user.template_controller_login_block__end')) ? eval($sPlugin) : false); ?>
                </div>
            </div>
<?php endif; ?>
    
</form>

<?php endif; ?>

