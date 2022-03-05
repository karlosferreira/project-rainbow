<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 1:06 am */ ?>
<?php

?>

<div id="js_register_step2">
<?php (($sPlugin = Phpfox_Plugin::get('user.template_default_block_register_step2_6')) ? eval($sPlugin) : false); ?>
<?php if (! isset ( $this->_aVars['bIsPosted'] ) && Phpfox ::getParam('user.multi_step_registration_form')): ?>
		<div class="p_bottom_10"><?php echo _p('complete_this_step_to_setup_your_personal_profile'); ?></div>
<?php endif; ?>

<?php if (Phpfox ::getParam('core.registration_enable_dob')): ?>
	<div class="form-group">
		*<label class=""><?php echo _p('birthday'); ?></label>
		<div class="form-inline select_date"><?php $default_picker_time = PHPFOX_TIME; ?> <select  name="val[month]" id="month" class="form-control js_datepicker_month">
		<option value=""><?php echo _p('month'); ?>:</option>
			<option value="1" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '1')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '1') || (is_array($this->_aVars['aForms']['month']) && in_array('1', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'January' : _p('january')); ?></option>
			<option value="2" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '2')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '2') || (is_array($this->_aVars['aForms']['month']) && in_array('2', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'February' : _p('february')); ?></option>
			<option value="3" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '3')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '3') || (is_array($this->_aVars['aForms']['month']) && in_array('3', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'March' : _p('march')); ?></option>
			<option value="4" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '4')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '4') || (is_array($this->_aVars['aForms']['month']) && in_array('4', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'April' : _p('april')); ?></option>
			<option value="5" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '5')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '5') || (is_array($this->_aVars['aForms']['month']) && in_array('5', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'May' : _p('may')); ?></option>
			<option value="6" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '6')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '6') || (is_array($this->_aVars['aForms']['month']) && in_array('6', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'June' : _p('june')); ?></option>
			<option value="7" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '7')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '7') || (is_array($this->_aVars['aForms']['month']) && in_array('7', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'July' : _p('july')); ?></option>
			<option value="8" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '8')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '8') || (is_array($this->_aVars['aForms']['month']) && in_array('8', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'August' : _p('august')); ?></option>
			<option value="9" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '9')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '9') || (is_array($this->_aVars['aForms']['month']) && in_array('9', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'September' : _p('september')); ?></option>
			<option value="10" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '10')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '10') || (is_array($this->_aVars['aForms']['month']) && in_array('10', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'October' : _p('october')); ?></option>
			<option value="11" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '11')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '11') || (is_array($this->_aVars['aForms']['month']) && in_array('11', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'November' : _p('november')); ?></option>
			<option value="12" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('month') && in_array('month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['month'])
								&& $aParams['month'] == '12')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['month'])
									&& !isset($aParams['month'])
									&& (($this->_aVars['aForms']['month'] == '12') || (is_array($this->_aVars['aForms']['month']) && in_array('12', $this->_aVars['aForms']['month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo (defined('PHPFOX_INSTALLER') ? 'December' : _p('december')); ?></option>
		</select>
<span class="field_separator"> / </span>		<select name="val[day]" id="day" class="form-control js_datepicker_day">
		<option value=""><?php echo _p('day'); ?>:</option>
			<option value="1" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '1')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '1') || (is_array($this->_aVars['aForms']['day']) && in_array('1', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>1</option>
			<option value="2" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '2')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '2') || (is_array($this->_aVars['aForms']['day']) && in_array('2', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>2</option>
			<option value="3" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '3')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '3') || (is_array($this->_aVars['aForms']['day']) && in_array('3', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>3</option>
			<option value="4" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '4')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '4') || (is_array($this->_aVars['aForms']['day']) && in_array('4', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>4</option>
			<option value="5" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '5')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '5') || (is_array($this->_aVars['aForms']['day']) && in_array('5', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>5</option>
			<option value="6" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '6')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '6') || (is_array($this->_aVars['aForms']['day']) && in_array('6', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>6</option>
			<option value="7" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '7')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '7') || (is_array($this->_aVars['aForms']['day']) && in_array('7', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>7</option>
			<option value="8" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '8')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '8') || (is_array($this->_aVars['aForms']['day']) && in_array('8', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>8</option>
			<option value="9" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '9')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '9') || (is_array($this->_aVars['aForms']['day']) && in_array('9', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>9</option>
			<option value="10" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '10')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '10') || (is_array($this->_aVars['aForms']['day']) && in_array('10', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>10</option>
			<option value="11" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '11')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '11') || (is_array($this->_aVars['aForms']['day']) && in_array('11', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>11</option>
			<option value="12" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '12')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '12') || (is_array($this->_aVars['aForms']['day']) && in_array('12', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>12</option>
			<option value="13" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '13')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '13') || (is_array($this->_aVars['aForms']['day']) && in_array('13', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>13</option>
			<option value="14" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '14')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '14') || (is_array($this->_aVars['aForms']['day']) && in_array('14', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>14</option>
			<option value="15" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '15')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '15') || (is_array($this->_aVars['aForms']['day']) && in_array('15', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>15</option>
			<option value="16" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '16')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '16') || (is_array($this->_aVars['aForms']['day']) && in_array('16', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>16</option>
			<option value="17" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '17')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '17') || (is_array($this->_aVars['aForms']['day']) && in_array('17', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>17</option>
			<option value="18" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '18')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '18') || (is_array($this->_aVars['aForms']['day']) && in_array('18', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>18</option>
			<option value="19" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '19')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '19') || (is_array($this->_aVars['aForms']['day']) && in_array('19', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>19</option>
			<option value="20" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '20')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '20') || (is_array($this->_aVars['aForms']['day']) && in_array('20', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>20</option>
			<option value="21" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '21')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '21') || (is_array($this->_aVars['aForms']['day']) && in_array('21', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>21</option>
			<option value="22" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '22')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '22') || (is_array($this->_aVars['aForms']['day']) && in_array('22', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>22</option>
			<option value="23" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '23')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '23') || (is_array($this->_aVars['aForms']['day']) && in_array('23', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>23</option>
			<option value="24" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '24')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '24') || (is_array($this->_aVars['aForms']['day']) && in_array('24', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>24</option>
			<option value="25" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '25')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '25') || (is_array($this->_aVars['aForms']['day']) && in_array('25', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>25</option>
			<option value="26" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '26')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '26') || (is_array($this->_aVars['aForms']['day']) && in_array('26', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>26</option>
			<option value="27" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '27')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '27') || (is_array($this->_aVars['aForms']['day']) && in_array('27', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>27</option>
			<option value="28" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '28')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '28') || (is_array($this->_aVars['aForms']['day']) && in_array('28', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>28</option>
			<option value="29" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '29')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '29') || (is_array($this->_aVars['aForms']['day']) && in_array('29', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>29</option>
			<option value="30" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '30')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '30') || (is_array($this->_aVars['aForms']['day']) && in_array('30', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>30</option>
			<option value="31" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('day') && in_array('day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['day'])
								&& $aParams['day'] == '31')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['day'])
									&& !isset($aParams['day'])
									&& (($this->_aVars['aForms']['day'] == '31') || (is_array($this->_aVars['aForms']['day']) && in_array('31', $this->_aVars['aForms']['day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>31</option>
		</select>
<span class="field_separator"> / </span><?php $aYears = range($this->_aVars['sDobEnd'], $this->_aVars['sDobStart']);   $bSetEmptyAll = 0;   $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); ?>		<select name="val[year]" id="year" class="form-control js_datepicker_year">
		<option value=""><?php echo _p('year'); ?>:</option>
<?php foreach ($aYears as $iYear): ?>			<option value="<?php echo $iYear; ?>"<?php echo ($bSetEmptyAll ? ' ': ((isset($aParams['year']) && $aParams['year'] == $iYear) ? ' selected="selected"' : (!isset($this->_aVars['aForms']['year']) ? ($iYear == Phpfox::getTime('Y', $default_picker_time) ? ' selected="selected"' : '') : ($this->_aVars['aForms']['year'] == $iYear ? ' selected="selected"' : '')))); ?>><?php echo $iYear; ?></option>
<?php endforeach; ?>		</select>
<script>var pf_select_date_sort_desc = true;</script></div>
	</div>
<?php endif; ?>

<?php if (Phpfox ::getParam('core.registration_enable_gender')): ?>
	<div class="form-group">
		*<label for="gender" class=""><?php echo _p('i_am'); ?></label>
		<select class="form-control" name="val[gender]" id="gender">
		<option value=""><?php echo _p('select'); ?>:</option>
			<option value="1"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('gender') && in_array('gender', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['gender'])
								&& $aParams['gender'] == '1')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['gender'])
									&& !isset($aParams['gender'])
									&& (($this->_aVars['aForms']['gender'] == '1') || (is_array($this->_aVars['aForms']['gender']) && in_array('1', $this->_aVars['aForms']['gender']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo _p('profile.male'); ?></option>
			<option value="2"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('gender') && in_array('gender', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['gender'])
								&& $aParams['gender'] == '2')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['gender'])
									&& !isset($aParams['gender'])
									&& (($this->_aVars['aForms']['gender'] == '2') || (is_array($this->_aVars['aForms']['gender']) && in_array('2', $this->_aVars['aForms']['gender']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo _p('profile.female'); ?></option>
		</select>
	</div>
<?php endif; ?>

<?php if (Phpfox ::getParam('core.registration_enable_location')): ?>
	<div class="form-group js_core_init_selectize_form_group selectize-mcustomscroll">
		*<label for="country_iso" class=""><?php echo _p('location'); ?>:</label>
		<?php Phpfox::getBlock('core.country-build', array('param'=> array (
))); ?>
<?php Phpfox::getBlock('core.country-child', array('country_force_div' => true)); ?>
	</div>
<?php endif; ?>

<?php if (Phpfox ::getParam('core.city_in_registration')): ?>
	<div class="form-group">
		<label for="city_location"><?php echo _p('city'); ?></label>
		<input class="form-control" type="text" name="val[city_location]" id="city_location" value="<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); echo (isset($aParams['city_location']) ? Phpfox::getLib('phpfox.parse.output')->clean($aParams['city_location']) : (isset($this->_aVars['aForms']['city_location']) ? Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aForms']['city_location']) : '')); ?>
" size="30" />
	</div>
<?php endif; ?>

<?php if (Phpfox ::getParam('core.registration_enable_timezone')): ?>
	<div class="form-group js_core_init_selectize_form_group">
		<label><?php echo _p('time_zone'); ?></label>
		<select class="form-control" name="val[time_zone]">
<?php if (count((array)$this->_aVars['aTimeZones'])):  foreach ((array) $this->_aVars['aTimeZones'] as $this->_aVars['sTimeZoneKey'] => $this->_aVars['sTimeZone']): ?>
                <option value="<?php echo $this->_aVars['sTimeZoneKey']; ?>"<?php if (( Phpfox ::getTimeZone() == $this->_aVars['sTimeZoneKey'] && ! isset ( $this->_aVars['iTimeZonePosted'] ) ) || ( isset ( $this->_aVars['iTimeZonePosted'] ) && $this->_aVars['iTimeZonePosted'] == $this->_aVars['sTimeZoneKey'] ) || ( Phpfox ::getParam('core.default_time_zone_offset') == $this->_aVars['sTimeZoneKey'] )): ?> selected="selected"<?php endif; ?>><?php echo $this->_aVars['sTimeZone']; ?></option>
<?php endforeach; endif; ?>
		</select>
	</div>
<?php endif; ?>
	
<?php (($sPlugin = Phpfox_Plugin::get('user.template_default_block_register_step2_7')) ? eval($sPlugin) : false); ?>
	<?php
						Phpfox::getLib('template')->getBuiltFile('user.block.custom');
						?>
<?php (($sPlugin = Phpfox_Plugin::get('user.template_default_block_register_step2_8')) ? eval($sPlugin) : false); ?>
<?php if (Phpfox ::isAppActive('Core_Subscriptions') && Phpfox ::getParam('subscribe.enable_subscription_packages') && count ( $this->_aVars['aPackages'] )): ?>
	<div class="form-group">
		<label>
<?php if (Phpfox ::getParam('subscribe.subscribe_is_required_on_sign_up')): ?>*<?php endif; ?>
<?php echo _p('membership'); ?>
		</label>
		<select class="form-control" name="val[package_id]" id="js_subscribe_package_id">
<?php if (Phpfox ::getParam('subscribe.subscribe_is_required_on_sign_up')): ?>
			<option value=""<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('package_id') && in_array('package_id', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['package_id'])
								&& $aParams['package_id'] == '0')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['package_id'])
									&& !isset($aParams['package_id'])
									&& (($this->_aVars['aForms']['package_id'] == '0') || (is_array($this->_aVars['aForms']['package_id']) && in_array('0', $this->_aVars['aForms']['package_id']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo _p('select'); ?>:</option>
<?php else: ?>
			<option value=""<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('package_id') && in_array('package_id', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['package_id'])
								&& $aParams['package_id'] == '0')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['package_id'])
									&& !isset($aParams['package_id'])
									&& (($this->_aVars['aForms']['package_id'] == '0') || (is_array($this->_aVars['aForms']['package_id']) && in_array('0', $this->_aVars['aForms']['package_id']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php echo _p('free_normal'); ?></option>
<?php endif; ?>
<?php if (count((array)$this->_aVars['aPackages'])):  foreach ((array) $this->_aVars['aPackages'] as $this->_aVars['aPackage']): ?>
			<option value="<?php echo $this->_aVars['aPackage']['package_id']; ?>"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('package_id') && in_array('package_id', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['package_id'])
								&& $aParams['package_id'] == $this->_aVars['aPackage']['package_id'])

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['package_id'])
									&& !isset($aParams['package_id'])
									&& (($this->_aVars['aForms']['package_id'] == $this->_aVars['aPackage']['package_id']) || (is_array($this->_aVars['aForms']['package_id']) && in_array($this->_aVars['aPackage']['package_id'], $this->_aVars['aForms']['package_id']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
><?php if ($this->_aVars['aPackage']['show_price']): ?>(<?php if ($this->_aVars['aPackage']['default_cost'] == '0.00'):  echo _p('free');  else:  echo Phpfox::getService('core.currency')->getSymbol($this->_aVars['aPackage']['default_currency_id']);  echo $this->_aVars['aPackage']['default_cost'];  endif; ?>) <?php endif;  echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean(Phpfox::getLib('locale')->convert($this->_aVars['aPackage']['title']))); ?></option>
<?php endforeach; endif; ?>
		</select>
		<div class="help-block">
			<a href="#" onclick="tb_show('<?php echo _p('membership_upgrades', array('phpfox_squote' => true)); ?>', $.ajaxBox('subscribe.listUpgradesOnSignup', 'height=400&width=500')); return false;"><?php echo _p('click_here_to_learn_more_about_our_membership_upgrades'); ?></a>
		</div>
	</div>
<?php endif; ?>
</div>

<?php Phpfox::getBlock('user.showspamquestion', array()); ?>

<?php if (Phpfox ::getParam('user.force_user_to_upload_on_sign_up')): ?>
<?php Phpfox::getBlock('core.upload-form', array('type' => 'user_registration','unique_id' => true,'force_convert_file' => true));  endif; ?>
