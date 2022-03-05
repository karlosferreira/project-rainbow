<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 1:06 am */ ?>
<?php

 echo '
<script type="text/javascript">
    $Behavior.termsAndPrivacy = function()
    {
        $(\'#js_terms_of_use\').click(function() {
            '; ?>

            tb_show('<?php echo _p('terms_of_use', array('phpfox_squote' => true)); ?>', $.ajaxBox('page.view', 'height=410&width=600&id=2&title=terms'));
            <?php echo '
            return false;
        });

        $(\'#js_privacy_policy\').click(function() {
            '; ?>

            tb_show('<?php echo _p('privacy_policy', array('phpfox_squote' => true)); ?>', $.ajaxBox('page.view', 'height=410&width=600&id=1&title=policy'));
            <?php echo '
            return false;
        });
    }
</script>
'; ?>


<?php if (! empty ( $this->_aVars['message'] )): ?>
<div class="extra_info message"><?php echo $this->_aVars['message']; ?></div>
<?php endif; ?>

<?php if (Phpfox_Module ::instance()->getFullControllerName() == 'user.register' && Phpfox ::isModule('invite')): ?>
<div class="block">
    <div class="content">
<?php endif; ?>
<?php if (Phpfox ::isModule('invite') && Invite_Service_Invite ::instance()->isInviteOnly()): ?>
        <div class="sign-up-invitation">
            <img src="<?php echo Phpfox::getParam('core.path_actual'); ?>PF.Site/flavors/material/assets/images/sign-up-invitation.jpg" alt="">

            <p class="help-block">
<?php if (Phpfox ::getParam('core.enable_register_with_phone_number')): ?>
<?php echo _p('ssitetitle_is_an_invite_only_community_enter_your_email_or_phone_number_below_if_you_have_received_an_invitation', array('sSiteTitle' => $this->_aVars['sSiteTitle'])); ?>
<?php else: ?>
<?php echo _p('ssitetitle_is_an_invite_only_community_enter_your_email_below_if_you_have_received_an_invitation', array('sSiteTitle' => $this->_aVars['sSiteTitle'])); ?>
<?php endif; ?>
            </p>

            <form method="post" class="form" action="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.register', [], false, false); ?>">
                <div class="form-group">
                    <div class="input-group">
                        <div class="input-group-addon">
                            <span class="ico ico-activedir-o"></span>
                        </div>
                        <input type="text" id="invite_email" class="form-control" placeholder="<?php if (Phpfox ::getParam('core.enable_register_with_phone_number')):  echo _p('your_email_phone_number');  else:  echo _p('your_email');  endif; ?>" name="val[invite_email]" value="" />
                    </div>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-primary"><?php echo _p('submit_to_sign_up'); ?></button>
                </div>
            
</form>

        </div>
<?php else: ?>
<?php if (isset ( $this->_aVars['sCreateJs'] )): ?>
<?php echo $this->_aVars['sCreateJs']; ?>
<?php endif; ?>
        <div id="js_registration_process" class="t_center" style="display:none;">
            <div class="p_top_8">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => 'ajax/add.gif','alt' => '')); ?>
            </div>
        </div>

        <div id="js_signup_error_message"></div>

<?php if (Phpfox ::getParam('user.allow_user_registration')): ?>
            <div id="js_registration_holder">
                <form method="post" class="form" action="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.register', [], false, false); ?>" id="js_form" enctype="multipart/form-data" data-step="1">
                                        <div id="js_signup_block">
<?php if (isset ( $this->_aVars['bIsPosted'] ) || ! Phpfox ::getParam('user.multi_step_registration_form')): ?>
                            <div>
                                <?php
						Phpfox::getLib('template')->getBuiltFile('user.block.register.step1');
						?>
                                <?php
						Phpfox::getLib('template')->getBuiltFile('user.block.register.step2');
						?>
                            </div>
<?php else: ?>
                            <?php
						Phpfox::getLib('template')->getBuiltFile('user.block.register.step1');
						?>
<?php endif; ?>
                    </div>

<?php (($sPlugin = Phpfox_Plugin::get('user.template_controller_register_pre_captcha')) ? eval($sPlugin) : false); ?>

<?php if (Phpfox ::isModule('captcha') && Phpfox ::getParam('user.captcha_on_signup') && ( $this->_aVars['sCaptchaType'] = Phpfox ::getParam('captcha.captcha_type'))): ?>
                    <div id="js_register_capthca_image"<?php if (Phpfox ::getParam('user.multi_step_registration_form') && ! isset ( $this->_aVars['bIsPosted'] )): ?> style="display:none;"<?php endif; ?> class="<?php echo $this->_aVars['sCaptchaType']; ?>">
<?php Phpfox::getBlock('captcha.form', array()); ?>
                    </div>
<?php endif; ?>

<?php if (Phpfox ::getParam('user.new_user_terms_confirmation')): ?>
                    <div id="js_register_accept" class="register-accept-block form-group">
                        <div class="checkbox">
                            <label>
                                <input type="checkbox" name="val[agree]" id="agree" value="1" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('agree') && in_array('agree', $this->_aVars['aForms']))
							
{
								echo ' checked="checked" ';
							}

							if (isset($aParams['agree'])
								&& $aParams['agree'] == '1')

							{

								echo ' checked="checked" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['agree'])
									&& !isset($aParams['agree'])
									&& (($this->_aVars['aForms']['agree'] == '1') || (is_array($this->_aVars['aForms']['agree']) && in_array('1', $this->_aVars['aForms']['agree']))))
								{
								 echo ' checked="checked" ';
								}
								else
								{
									echo "";
								}
							}
							?>
/>
<?php echo _p('i_have_read_and_agree_to_the_a_href_id_js_terms_of_use_terms_of_use_a_and_a_href_id_js_privacy_policy_privacy_policy_a'); ?>
                            </label>
                        </div>
                    </div>
<?php endif; ?>

                    <div class="form-button-group register-form-button-group">
                        <div class="form-group">
<?php if (isset ( $this->_aVars['bIsPosted'] ) || ! Phpfox ::getParam('user.multi_step_registration_form')): ?>
                                <button type="submit" class="btn btn-primary text-uppercase" id="js_registration_submit"><?php echo _p('sign_up_button'); ?></button>
<?php else: ?>
                                <input type="button" value="<?php echo _p('previous_step'); ?>" class="btn btn-default text-uppercase" id="js_registration_back_previous" onclick="$Core.registration.backPreviousForm();" />
                                <input type="button" value="<?php echo _p('next_step'); ?>" class="btn btn-success text-uppercase" id="js_registration_submit" onclick="$Core.registration.submitForm();" />
<?php endif; ?>
                        </div>
                        <div class="form-group already-member">
<?php echo _p('i_m_already_member'); ?>
<?php if (! empty ( $this->_aVars['bSlideForm'] )): ?>
                                <a href="javascript:void(0);" class="js-slide-btn"><?php echo _p('sign_in_now'); ?></a>
<?php else: ?>
                                <a class="keepPopup" rel="hide_box_title visitor_form" href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('login', [], false, false); ?>"><?php echo _p('sign_in_now'); ?></a>
<?php endif; ?>
                        </div>
                    </div>

<?php (($sPlugin = Phpfox_Plugin::get('user.template.register_header_set_var')) ? eval($sPlugin) : false); ?>
<?php if (isset ( $this->_aVars['bCustomLogin'] )): ?>
                    <div class="form-button-group form-login-custom-fb">
                        <div class="custom-fb-or"><span><?php echo _p('or'); ?></span></div>
                        <div class="custom_fb">
<?php (($sPlugin = Phpfox_Plugin::get('user.template_controller_register_block__end')) ? eval($sPlugin) : false); ?>
                        </div>
                    </div>
<?php endif; ?>
                
</form>

            </div>
<?php endif; ?>
<?php endif; ?>

<?php if (Phpfox_Module ::instance()->getFullControllerName() == 'user.register'): ?>
    </div>
</div>
<?php endif; ?>

