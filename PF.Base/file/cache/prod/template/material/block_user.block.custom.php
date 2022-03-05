<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 1:06 am */ ?>
<?php

?>

<?php if (count((array)$this->_aVars['aSettings'])):  foreach ((array) $this->_aVars['aSettings'] as $this->_aVars['aSetting']): ?>
<div class="table js_custom_groups<?php if (isset ( $this->_aVars['aSetting']['group_id'] )): ?> js_custom_group_<?php echo $this->_aVars['aSetting']['group_id'];  endif; ?>">
    <label>
<?php if ($this->_aVars['aSetting']['is_required'] && ! Phpfox ::isAdminPanel()): ?>*<?php endif;  echo _p($this->_aVars['aSetting']['phrase_var_name']); ?>:
    </label>
        <?php
						Phpfox::getLib('template')->getBuiltFile('custom.block.form');
						?>
</div>
<?php endforeach; endif;  (($sPlugin = Phpfox_Plugin::get('user.template_controller_profile_form')) ? eval($sPlugin) : false); ?>
