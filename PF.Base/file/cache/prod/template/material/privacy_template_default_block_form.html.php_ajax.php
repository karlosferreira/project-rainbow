<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:00 am */ ?>
<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: form.html.php 4854 2012-10-09 05:20:40Z phpFox LLC $
 */

?>

<?php if ($this->_aVars['bIsListType']): ?>
    <div class="<?php if ($this->_aVars['sPrivacyFormType'] == 'mini'): ?>privacy_setting_mini<?php else: ?>privacy_setting<?php endif; ?> privacy_setting_div privacy-list-type">
        <input type="hidden" id="<?php echo $this->_aVars['sPrivacyFormName']; ?>" name="val<?php if (! empty ( $this->_aVars['sPrivacyArray'] )): ?>[<?php echo $this->_aVars['sPrivacyArray']; ?>]<?php endif; ?>[<?php echo $this->_aVars['sPrivacyFormName']; ?>]" value="<?php echo $this->_aVars['aSelectedPrivacyControl']['value']; ?>" />
        <ul class="<?php if (! empty ( $this->_aVars['bSelectInline'] )): ?>privacy_setting_inline<?php endif; ?>">
<?php if (count((array)$this->_aVars['aPrivacyControls'])):  $this->_aPhpfoxVars['iteration']['privacycontrol'] = 0;  foreach ((array) $this->_aVars['aPrivacyControls'] as $this->_aVars['aPrivacyControl']):  $this->_aPhpfoxVars['iteration']['privacycontrol']++; ?>

                <li role="presentation" class="<?php if (( isset ( $this->_aVars['aPrivacyControl']['is_active'] ) ) || ( isset ( $this->_aVars['bNoActive'] ) && $this->_aVars['bNoActive'] && $this->_aPhpfoxVars['iteration']['privacycontrol'] == 1 )): ?>is_active_image<?php endif; ?>">
                    <a <?php if (isset ( $this->_aVars['aPrivacyControl']['onclick'] )): ?> onclick="<?php echo $this->_aVars['aPrivacyControl']['onclick']; ?> return false;"<?php endif; ?> data-toggle="privacy_item" rel="<?php echo $this->_aVars['aPrivacyControl']['value']; ?>"
<?php if (! empty ( $this->_aVars['bSelectInline'] )): ?>data-privacy-inline="true"<?php endif; ?>
                    class="<?php if (( isset ( $this->_aVars['aPrivacyControl']['is_active'] ) ) || ( isset ( $this->_aVars['bNoActive'] ) && $this->_aVars['bNoActive'] && $this->_aPhpfoxVars['iteration']['privacycontrol'] == 1 )): ?>is_active_image<?php endif; ?> <?php if (! empty ( $this->_aVars['bSelectInline'] )): ?>btn btn-icon<?php endif; ?>">
                        <i class="fa fa-privacy fa-privacy-<?php echo $this->_aVars['aPrivacyControl']['value']; ?>"></i>
                        <span class="txt-label"><?php echo $this->_aVars['aPrivacyControl']['phrase']; ?></span>
                    </a>
                </li>
<?php endforeach; endif; ?>
        </ul>
    </div>
<?php if (! empty ( $this->_aVars['sPrivacyFormInfo'] )): ?>
        <p class="help-block">
<?php echo $this->_aVars['sPrivacyFormInfo']; ?>
        </p>
<?php endif;  else: ?>
    <div class="<?php if ($this->_aVars['sPrivacyFormType'] == 'mini'): ?>privacy_setting_mini<?php else: ?>privacy_setting<?php endif; ?> privacy_setting_div">
        <input type="hidden" id="<?php echo $this->_aVars['sPrivacyFormName']; ?>" name="val<?php if (! empty ( $this->_aVars['sPrivacyArray'] )): ?>[<?php echo $this->_aVars['sPrivacyArray']; ?>]<?php endif; ?>[<?php echo $this->_aVars['sPrivacyFormName']; ?>]" value="<?php echo $this->_aVars['aSelectedPrivacyControl']['value']; ?>" />
        <a data-toggle="dropdown" class="privacy_setting_active<?php if ($this->_aVars['sPrivacyFormType'] == 'mini'): ?> js_hover_title<?php endif; ?> btn btn-default btn-icon <?php if (! empty ( $this->_aVars['sBtnSize'] )):  echo $this->_aVars['sBtnSize'];  endif; ?>">
            <i class="fa fa-privacy fa-privacy-<?php echo $this->_aVars['aSelectedPrivacyControl']['value']; ?>"></i>
            <span class="txt-label"><?php echo $this->_aVars['aSelectedPrivacyControl']['phrase']; ?></span>
            <span class="txt-label js_hover_info"><?php echo $this->_aVars['aSelectedPrivacyControl']['phrase']; ?>
            </span>
            <i class="fa fa-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-checkmark">
<?php if (count((array)$this->_aVars['aPrivacyControls'])):  $this->_aPhpfoxVars['iteration']['privacycontrol'] = 0;  foreach ((array) $this->_aVars['aPrivacyControls'] as $this->_aVars['aPrivacyControl']):  $this->_aPhpfoxVars['iteration']['privacycontrol']++; ?>

<?php if (isset ( $this->_aVars['aPrivacyControl']['onclick'] )): ?>
                    <li class="divider"></li>
<?php endif; ?>
                <li role="presentation">
                    <a <?php if (isset ( $this->_aVars['aPrivacyControl']['onclick'] )): ?> onclick="<?php echo $this->_aVars['aPrivacyControl']['onclick']; ?> return false;"<?php endif; ?> data-toggle="privacy_item" rel="<?php echo $this->_aVars['aPrivacyControl']['value']; ?>" <?php if (( isset ( $this->_aVars['aPrivacyControl']['is_active'] ) ) || ( isset ( $this->_aVars['bNoActive'] ) && $this->_aVars['bNoActive'] && $this->_aPhpfoxVars['iteration']['privacycontrol'] == 1 )): ?>class="is_active_image"<?php endif; ?>><?php echo $this->_aVars['aPrivacyControl']['phrase']; ?>
                    </a>
                </li>
<?php endforeach; endif; ?>
        </ul>
    </div>
<?php if (! empty ( $this->_aVars['sPrivacyFormInfo'] )): ?>
        <p class="help-block">
<?php echo $this->_aVars['sPrivacyFormInfo']; ?>
        </p>
<?php endif;  endif; ?>
