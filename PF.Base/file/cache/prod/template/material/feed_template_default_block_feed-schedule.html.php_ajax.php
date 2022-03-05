<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 11:53 pm */ ?>
<?php

?>
<div class="feed-table-schedule">
    <div class="js_feed_compose_extra feed_compose_extra js_feed_compose_schedule dont-unbind-children" style="display: none;">
        <div class="feed-box-outer">
            <div class="feed-box">
                <div class="feed-box-inner">
                    <div class="feed-with"><?php echo _p('at'); ?></div>
                    <div class="feed-schedule-input-box">
                        <div id="js_add_schedule">
                            <div><input type="hidden" id="val_schedule_confirmed" name="val[confirm_scheduled]" class="close_warning val_schedule_confirmed" value='0'></div>
                            <div><input type="hidden" id="val_schedule_time_year" name="val[schedule_time][year]" class="close_warning val_schedule_time_year"></div>
                            <div><input type="hidden" id="val_schedule_time_month" name="val[schedule_time][month]" class="close_warning val_schedule_time_month"></div>
                            <div><input type="hidden" id="val_schedule_time_day" name="val[schedule_time][day]" class="close_warning val_schedule_time_day"></div>
                            <div><input type="hidden" id="val_schedule_time_hour" name="val[schedule_time][hour]" class="close_warning val_schedule_time_hour"></div>
                            <div><input type="hidden" id="val_schedule_time_minute" name="val[schedule_time][minute]" class="close_warning val_schedule_time_minute"></div>
                        </div>
                        <div class="form-inline select_date"><div class="js_datepicker_core_schedule"><span class="js_datepicker_holder"><div style="display:none;"><?php $schedule_default_picker_time = PHPFOX_TIME; $schedule_default_picker_time += 3600; ?> <select  name="val[schedule_month]" id="schedule_month" class="form-control js_datepicker_month">
			<option value="1" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '1')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '1') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('1', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('1' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'January' : _p('january')); ?></option>
			<option value="2" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '2')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '2') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('2', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('2' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'February' : _p('february')); ?></option>
			<option value="3" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '3')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '3') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('3', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('3' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'March' : _p('march')); ?></option>
			<option value="4" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '4')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '4') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('4', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('4' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'April' : _p('april')); ?></option>
			<option value="5" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '5')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '5') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('5', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('5' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'May' : _p('may')); ?></option>
			<option value="6" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '6')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '6') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('6', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('6' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'June' : _p('june')); ?></option>
			<option value="7" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '7')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '7') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('7', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('7' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'July' : _p('july')); ?></option>
			<option value="8" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '8')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '8') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('8', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('8' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'August' : _p('august')); ?></option>
			<option value="9" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '9')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '9') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('9', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('9' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'September' : _p('september')); ?></option>
			<option value="10" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '10')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '10') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('10', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('10' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'October' : _p('october')); ?></option>
			<option value="11" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '11')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '11') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('11', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('11' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'November' : _p('november')); ?></option>
			<option value="12" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_month') && in_array('schedule_month', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_month'])
								&& $aParams['schedule_month'] == '12')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_month'])
									&& !isset($aParams['schedule_month'])
									&& (($this->_aVars['aForms']['schedule_month'] == '12') || (is_array($this->_aVars['aForms']['schedule_month']) && in_array('12', $this->_aVars['aForms']['schedule_month']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_month']) ? ('12' == Phpfox::getTime('n', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>><?php echo (defined('PHPFOX_INSTALLER') ? 'December' : _p('december')); ?></option>
		</select>
<span class="field_separator"> / </span>		<select name="val[schedule_day]" id="schedule_day" class="form-control js_datepicker_day">
			<option value="1" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '1')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '1') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('1', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('1' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>1</option>
			<option value="2" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '2')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '2') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('2', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('2' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>2</option>
			<option value="3" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '3')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '3') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('3', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('3' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>3</option>
			<option value="4" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '4')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '4') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('4', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('4' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>4</option>
			<option value="5" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '5')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '5') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('5', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('5' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>5</option>
			<option value="6" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '6')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '6') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('6', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('6' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>6</option>
			<option value="7" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '7')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '7') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('7', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('7' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>7</option>
			<option value="8" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '8')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '8') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('8', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('8' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>8</option>
			<option value="9" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '9')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '9') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('9', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('9' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>9</option>
			<option value="10" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '10')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '10') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('10', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('10' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>10</option>
			<option value="11" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '11')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '11') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('11', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('11' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>11</option>
			<option value="12" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '12')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '12') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('12', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('12' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>12</option>
			<option value="13" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '13')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '13') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('13', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('13' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>13</option>
			<option value="14" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '14')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '14') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('14', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('14' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>14</option>
			<option value="15" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '15')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '15') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('15', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('15' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>15</option>
			<option value="16" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '16')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '16') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('16', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('16' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>16</option>
			<option value="17" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '17')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '17') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('17', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('17' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>17</option>
			<option value="18" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '18')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '18') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('18', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('18' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>18</option>
			<option value="19" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '19')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '19') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('19', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('19' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>19</option>
			<option value="20" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '20')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '20') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('20', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('20' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>20</option>
			<option value="21" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '21')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '21') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('21', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('21' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>21</option>
			<option value="22" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '22')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '22') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('22', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('22' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>22</option>
			<option value="23" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '23')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '23') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('23', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('23' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>23</option>
			<option value="24" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '24')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '24') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('24', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('24' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>24</option>
			<option value="25" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '25')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '25') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('25', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('25' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>25</option>
			<option value="26" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '26')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '26') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('26', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('26' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>26</option>
			<option value="27" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '27')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '27') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('27', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('27' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>27</option>
			<option value="28" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '28')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '28') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('28', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('28' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>28</option>
			<option value="29" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '29')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '29') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('29', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('29' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>29</option>
			<option value="30" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '30')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '30') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('30', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('30' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>30</option>
			<option value="31" <?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_day') && in_array('schedule_day', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_day'])
								&& $aParams['schedule_day'] == '31')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_day'])
									&& !isset($aParams['schedule_day'])
									&& (($this->_aVars['aForms']['schedule_day'] == '31') || (is_array($this->_aVars['aForms']['schedule_day']) && in_array('31', $this->_aVars['aForms']['schedule_day']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_day']) ? ('31' == Phpfox::getTime('j', $schedule_default_picker_time) ? ' selected="selected"' : '') : ''); ?>>31</option>
		</select>
<span class="field_separator"> / </span><?php $aYears = range(2022, 2023);   $bSetEmptyAll = 0;   $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val')); ?>		<select name="val[schedule_year]" id="schedule_year" class="form-control js_datepicker_year">
		<option value=""><?php echo _p('year'); ?>:</option>
<?php foreach ($aYears as $iYear): ?>			<option value="<?php echo $iYear; ?>"<?php echo ($bSetEmptyAll ? ' ': ((isset($aParams['schedule_year']) && $aParams['schedule_year'] == $iYear) ? ' selected="selected"' : (!isset($this->_aVars['aForms']['schedule_year']) ? ($iYear == Phpfox::getTime('Y', $schedule_default_picker_time) ? ' selected="selected"' : '') : ($this->_aVars['aForms']['schedule_year'] == $iYear ? ' selected="selected"' : '')))); ?>><?php echo $iYear; ?></option>
<?php endforeach; ?>		</select>
</div><input type="text" name="js_schedule__datepicker" value="<?php if (isset($aParams['schedule_month'])):  switch(Phpfox::getParam("core.date_field_order")){  case "DMY":  echo $aParams['schedule_day'] . '/';  echo $aParams['schedule_month'] . '/';  echo $aParams['schedule_year'];  break;  case "MDY":  echo $aParams['schedule_month'] . '/';  echo $aParams['schedule_day'] . '/';  echo $aParams['schedule_year'];  break;  case "YMD":  echo $aParams['schedule_year'] . '/';  echo $aParams['schedule_month'] . '/';  echo $aParams['schedule_day'];  break;  }  elseif (isset($this->_aVars['aForms'])):  if (isset($this->_aVars['aForms']['schedule_month'])):  switch(Phpfox::getParam("core.date_field_order")){  case "DMY":  echo $this->_aVars['aForms']['schedule_day'] . '/';  echo $this->_aVars['aForms']['schedule_month'] . '/';  echo $this->_aVars['aForms']['schedule_year'];  break;  case "MDY":  echo $this->_aVars['aForms']['schedule_month'] . '/';  echo $this->_aVars['aForms']['schedule_day'] . '/';  echo $this->_aVars['aForms']['schedule_year'];  break;  case "YMD":  echo $this->_aVars['aForms']['schedule_year'] . '/';  echo $this->_aVars['aForms']['schedule_month'] . '/';  echo $this->_aVars['aForms']['schedule_day'];  break;  }  endif;  else:  switch(Phpfox::getParam("core.date_field_order")){	case "DMY": echo Phpfox::getTime('j', $schedule_default_picker_time) . '/' . Phpfox::getTime('n', $schedule_default_picker_time) . '/' . Phpfox::getTime('Y', $schedule_default_picker_time); break;	case "MDY": echo Phpfox::getTime('n', $schedule_default_picker_time) . '/' . Phpfox::getTime('j', $schedule_default_picker_time) . '/' . Phpfox::getTime('Y', $schedule_default_picker_time); break;	case "YMD": echo Phpfox::getTime('Y', $schedule_default_picker_time) . '/' . Phpfox::getTime('n', $schedule_default_picker_time) . '/' . Phpfox::getTime('j', $schedule_default_picker_time); break;} endif; ?>" class="form-control js_date_picker" /><div class="js_datepicker_image"></div></span> <span class="form-inline js_datepicker_selects">		<select class="form-control" name="val[schedule_hour]" id="schedule_hour">
			<option value="00"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '00')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '00') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('00', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('00' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>00</option>
			<option value="01"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '01')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '01') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('01', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('01' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>01</option>
			<option value="02"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '02')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '02') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('02', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('02' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>02</option>
			<option value="03"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '03')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '03') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('03', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('03' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>03</option>
			<option value="04"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '04')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '04') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('04', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('04' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>04</option>
			<option value="05"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '05')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '05') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('05', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('05' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>05</option>
			<option value="06"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '06')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '06') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('06', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('06' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>06</option>
			<option value="07"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '07')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '07') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('07', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('07' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>07</option>
			<option value="08"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '08')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '08') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('08', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('08' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>08</option>
			<option value="09"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '09')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '09') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('09', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('09' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>09</option>
			<option value="10"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '10')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '10') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('10', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('10' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>10</option>
			<option value="11"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '11')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '11') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('11', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('11' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>11</option>
			<option value="12"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '12')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '12') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('12', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('12' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>12</option>
			<option value="13"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '13')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '13') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('13', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('13' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>13</option>
			<option value="14"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '14')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '14') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('14', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('14' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>14</option>
			<option value="15"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '15')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '15') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('15', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('15' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>15</option>
			<option value="16"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '16')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '16') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('16', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('16' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>16</option>
			<option value="17"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '17')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '17') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('17', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('17' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>17</option>
			<option value="18"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '18')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '18') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('18', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('18' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>18</option>
			<option value="19"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '19')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '19') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('19', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('19' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>19</option>
			<option value="20"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '20')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '20') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('20', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('20' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>20</option>
			<option value="21"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '21')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '21') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('21', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('21' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>21</option>
			<option value="22"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '22')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '22') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('22', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('22' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>22</option>
			<option value="23"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_hour') && in_array('schedule_hour', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_hour'])
								&& $aParams['schedule_hour'] == '23')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_hour'])
									&& !isset($aParams['schedule_hour'])
									&& (($this->_aVars['aForms']['schedule_hour'] == '23') || (is_array($this->_aVars['aForms']['schedule_hour']) && in_array('23', $this->_aVars['aForms']['schedule_hour']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							 echo (!isset($this->_aVars['aForms']['schedule_hour']) ? ('23' == (Phpfox::getLib('date')->modifyHours('+1')) ? ' selected="selected"' : '') : ''); ?>>23</option>
		</select><span class="select-date-separator">:</span>
		<select class="form-control" name="val[schedule_minute]" id="schedule_minute">
			<option value="00"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '00')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '00') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('00', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>00</option>
			<option value="01"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '01')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '01') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('01', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>01</option>
			<option value="02"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '02')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '02') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('02', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>02</option>
			<option value="03"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '03')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '03') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('03', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>03</option>
			<option value="04"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '04')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '04') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('04', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>04</option>
			<option value="05"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '05')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '05') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('05', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>05</option>
			<option value="06"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '06')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '06') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('06', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>06</option>
			<option value="07"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '07')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '07') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('07', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>07</option>
			<option value="08"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '08')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '08') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('08', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>08</option>
			<option value="09"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '09')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '09') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('09', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>09</option>
			<option value="10"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '10')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '10') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('10', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="11"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '11')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '11') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('11', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="12"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '12')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '12') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('12', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="13"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '13')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '13') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('13', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="14"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '14')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '14') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('14', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="15"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '15')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '15') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('15', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="16"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '16')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '16') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('16', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="17"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '17')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '17') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('17', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="18"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '18')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '18') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('18', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="19"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '19')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '19') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('19', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="20"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '20')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '20') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('20', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="21"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '21')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '21') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('21', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="22"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '22')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '22') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('22', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="23"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '23')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '23') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('23', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="24"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '24')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '24') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('24', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="25"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '25')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '25') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('25', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="26"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '26')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '26') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('26', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="27"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '27')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '27') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('27', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="28"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '28')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '28') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('28', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="29"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '29')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '29') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('29', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="30"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '30')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '30') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('30', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="31"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '31')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '31') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('31', $this->_aVars['aForms']['schedule_minute']))))
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
			<option value="32"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '32')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '32') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('32', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>32</option>
			<option value="33"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '33')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '33') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('33', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>33</option>
			<option value="34"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '34')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '34') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('34', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>34</option>
			<option value="35"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '35')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '35') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('35', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>35</option>
			<option value="36"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '36')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '36') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('36', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>36</option>
			<option value="37"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '37')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '37') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('37', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>37</option>
			<option value="38"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '38')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '38') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('38', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>38</option>
			<option value="39"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '39')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '39') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('39', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>39</option>
			<option value="40"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '40')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '40') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('40', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>40</option>
			<option value="41"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '41')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '41') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('41', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>41</option>
			<option value="42"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '42')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '42') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('42', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>42</option>
			<option value="43"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '43')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '43') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('43', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>43</option>
			<option value="44"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '44')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '44') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('44', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>44</option>
			<option value="45"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '45')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '45') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('45', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>45</option>
			<option value="46"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '46')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '46') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('46', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>46</option>
			<option value="47"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '47')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '47') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('47', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>47</option>
			<option value="48"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '48')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '48') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('48', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>48</option>
			<option value="49"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '49')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '49') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('49', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>49</option>
			<option value="50"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '50')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '50') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('50', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>50</option>
			<option value="51"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '51')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '51') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('51', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>51</option>
			<option value="52"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '52')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '52') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('52', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>52</option>
			<option value="53"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '53')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '53') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('53', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>53</option>
			<option value="54"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '54')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '54') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('54', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>54</option>
			<option value="55"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '55')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '55') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('55', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>55</option>
			<option value="56"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '56')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '56') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('56', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>56</option>
			<option value="57"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '57')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '57') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('57', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>57</option>
			<option value="58"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '58')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '58') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('58', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>58</option>
			<option value="59"<?php $aParams = (isset($aParams) ? $aParams : Phpfox::getLib('phpfox.request')->getArray('val'));


if (isset($this->_aVars['aField']) && isset($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]) && !is_array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]))
							{
								$this->_aVars['aForms'][$this->_aVars['aField']['field_id']] = array($this->_aVars['aForms'][$this->_aVars['aField']['field_id']]);
							}

if (isset($this->_aVars['aForms'])
 && is_numeric('schedule_minute') && in_array('schedule_minute', $this->_aVars['aForms']))
							
{
								echo ' selected="selected" ';
							}

							if (isset($aParams['schedule_minute'])
								&& $aParams['schedule_minute'] == '59')

							{

								echo ' selected="selected" ';

							}

							else

							{

								if (isset($this->_aVars['aForms']['schedule_minute'])
									&& !isset($aParams['schedule_minute'])
									&& (($this->_aVars['aForms']['schedule_minute'] == '59') || (is_array($this->_aVars['aForms']['schedule_minute']) && in_array('59', $this->_aVars['aForms']['schedule_minute']))))
								{
								 echo ' selected="selected" ';
								}
								else
								{
									echo "";
								}
							}
							?>
>59</option>
		</select>
</span></div></div>
                    </div>
                </div>
<?php if (empty ( $this->_aVars['bIsEdit'] )): ?>
                    <span class="js_btn_clear_schedule_wrapper" style="display: none;">
                        <a class="btn btn-danger btn-sm btn_clear_schedule" id="btn_clear_schedule"><?php echo _p('clear'); ?></a>
                    </span>
<?php endif; ?>
                <span class="js_btn_confirm_schedule_wrapper">
                    <a class="btn btn-success btn-sm btn_confirm_schedule" data-is_edit="<?php if (! empty ( $this->_aVars['bIsEdit'] )): ?>1<?php endif; ?>" id="btn_confirm_schedule"><?php if (! empty ( $this->_aVars['bIsEdit'] )):  echo _p('change_time');  else:  echo _p('confirm');  endif; ?></a>
                </span>
            </div>
            <div class="js_schedule_invalid_time text-danger pb-1" style="display: none"><?php echo _p('you_cant_schedule_in_the_past'); ?></div>
        </div>
    </div>
</div>
