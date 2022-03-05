<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 1:06 am */ ?>
<?php

?>
<div id="js_register_step1">
<?php (($sPlugin = Phpfox_Plugin::get('user.template_default_block_register_step1_3')) ? eval($sPlugin) : false); ?>
<?php if (Phpfox ::getParam('user.disable_username_on_sign_up') != 'username'): ?>
<?php if (Phpfox ::getParam('user.split_full_name')): ?>
            <input type="hidden" name="val[full_name]" id="full_name" value="" size="30" />

            <div class="form-group">
                <input class="form-control" placeholder="<?php echo _p('first_name'); ?>" type="text" name="val[first_name]" id="first_name" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['first_name']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['first_name']) : (isset($this->_aVars['aForms']['first_name']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['first_name']) : '')); ?>
" size="30" />
            </div>
            <div class="form-group">
                <input class="form-control" placeholder="<?php echo _p('last_name'); ?>" type="text" name="val[last_name]" id="last_name" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['last_name']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['last_name']) : (isset($this->_aVars['aForms']['last_name']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['last_name']) : '')); ?>
" size="30" />
            </div>
<?php else: ?>
            <div class="form-group">
                <input class="form-control" placeholder="<?php if (Phpfox ::getParam('user.display_or_full_name') == 'full_name'):  echo _p('full_name'); ?> <?php else: ?> <?php echo _p('display_name'); ?> <?php endif; ?>" type="text" name="val[full_name]" id="full_name" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['full_name']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['full_name']) : (isset($this->_aVars['aForms']['full_name']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['full_name']) : '')); ?>
" size="30" />
            </div>
<?php endif; ?>
<?php endif; ?>
<?php if (! Phpfox ::getParam('user.profile_use_id') && ( Phpfox ::getParam('user.disable_username_on_sign_up') != 'full_name' )): ?>
        <div class="form-group">
            <input class="form-control" placeholder="<?php echo _p('choose_a_username'); ?>" type="text" name="val[user_name]" id="user_name" title="<?php echo _p('your_username_is_used_to_easily_connect_to_your_profile'); ?>" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['user_name']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['user_name']) : (isset($this->_aVars['aForms']['user_name']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['user_name']) : '')); ?>
" size="30" autocomplete="off" />
            <div id="js_user_name_error_message"></div>
            <div style="display:none;" id="js_verify_username"></div>
        </div>
<?php endif; ?>
<?php if (Phpfox ::getParam('user.reenter_email_on_signup')): ?>
        <div class="separate"></div>
<?php endif; ?>
    <div class="form-group">
        <input class="form-control <?php if (! empty ( $this->_aVars['sEmailClass'] )):  echo $this->_aVars['sEmailClass'];  endif; ?>" placeholder="<?php if (Phpfox ::getParam('core.enable_register_with_phone_number')):  echo _p('email_or_phone_number');  else:  echo _p('email');  endif; ?>" type="text" name="val[email]" id="email" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['email']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['email']) : (isset($this->_aVars['aForms']['email']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['email']) : '')); ?>
" size="30" />
    </div>

<?php if (Phpfox ::getParam('user.reenter_email_on_signup')): ?>
        <div class="form-group">
            <div class="p_top_8">
                <input class="form-control <?php if (! empty ( $this->_aVars['sConfirmEmailClass'] )):  echo $this->_aVars['sConfirmEmailClass'];  endif; ?>" type="text" name="val[confirm_email]" id="confirm_email" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['confirm_email']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['confirm_email']) : (isset($this->_aVars['aForms']['confirm_email']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['confirm_email']) : '')); ?>
" size="30" placeholder="<?php if (Phpfox ::getParam('core.enable_register_with_phone_number')):  echo _p('reenter_email_or_phone_number');  else:  echo _p('reenter_email');  endif; ?>"/>
            </div>
        </div>
        <div class="separate"></div>
<?php endif; ?>
<?php (($sPlugin = Phpfox_Plugin::get('user.template_default_block_register_step1_5')) ? eval($sPlugin) : false); ?>
    <div class="form-group">
<?php if (isset ( $this->_aVars['bIsPosted'] )): ?>
            <input class="form-control" placeholder="<?php echo _p('password'); ?>" type="password" name="val[password]" id="register_password" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['password']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['password']) : (isset($this->_aVars['aForms']['password']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['password']) : '')); ?>
" size="30" autocomplete="new-password" />
<?php else: ?>
            <input class="form-control" placeholder="<?php echo _p('password'); ?>" type="password" name="val[password]" id="register_password" value="" size="30" autocomplete="new-password" />
<?php endif; ?>
    </div>
<?php if (Phpfox ::getParam('user.signup_repeat_password')): ?>
        <div class="form-group">
            <input class="form-control" placeholder="<?php echo _p('repassword'); ?>" type="password" name="val[repassword]" id="register_repassword" value="" size="30" autocomplete="new-password" />
        </div>
<?php endif; ?>
<?php (($sPlugin = Phpfox_Plugin::get('user.template_default_block_register_step1_4')) ? eval($sPlugin) : false); ?>
</div>

<?php if (Phpfox ::getParam('core.enable_register_with_phone_number')): ?>
<?php if (! empty ( $this->_aVars['sEmailClass'] )): ?>
<?php Phpfox::getBlock('user.phone-number-country-codes', array('init_onchange' => '1','phone_field_id' => $this->_aVars['sEmailClass'])); ?>
<?php else: ?>
<?php Phpfox::getBlock('user.phone-number-country-codes', array('init_onchange' => '1','phone_field_id' => '#email')); ?>
<?php endif; ?>
<?php if (Phpfox ::getParam('user.reenter_email_on_signup')): ?>
<?php if (! empty ( $this->_aVars['sConfirmEmailClass'] )): ?>
<?php Phpfox::getBlock('user.phone-number-country-codes', array('init_onchange' => '1','phone_field_id' => $this->_aVars['sConfirmEmailClass'])); ?>
<?php else: ?>
<?php Phpfox::getBlock('user.phone-number-country-codes', array('init_onchange' => '1','phone_field_id' => '#confirm_email')); ?>
<?php endif; ?>
<?php endif;  endif; ?>
