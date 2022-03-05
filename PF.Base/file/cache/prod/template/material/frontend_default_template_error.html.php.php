<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<div class="_block_error">
<?php if (isset ( $this->_aVars['aPageSectionMenu'] ) && count ( $this->_aVars['aPageSectionMenu'] )): ?>
	<div class="page_section_menu page_section_menu_header">
		<div class="">
			<ul class="nav nav-tabs nav-justified">
<?php if (count((array)$this->_aVars['aPageSectionMenu'])):  $this->_aPhpfoxVars['iteration']['pagesectionmenu'] = 0;  foreach ((array) $this->_aVars['aPageSectionMenu'] as $this->_aVars['sPageSectionKey'] => $this->_aVars['sPageSectionMenu']):  $this->_aPhpfoxVars['iteration']['pagesectionmenu']++; ?>

				<li <?php if (( $this->_aVars['sPageSectionKey'] == $this->_aVars['sActiveTab'] )): ?> class="active"<?php endif; ?>><a href="<?php if ($this->_aVars['bPageIsFullLink']):  echo $this->_aVars['sPageSectionKey'];  else: ?>#<?php echo $this->_aVars['sPageSectionKey'];  endif; ?>" <?php if (! $this->_aVars['bPageIsFullLink']): ?>rel="<?php echo $this->_aVars['sPageSectionMenuName']; ?>_<?php echo $this->_aVars['sPageSectionKey']; ?>"<?php endif; ?>><?php echo $this->_aVars['sPageSectionMenu']; ?></a></li>
<?php endforeach; endif; ?>
			</ul>
		</div>
		<div class="clear"></div>
	</div>
<?php endif; ?>
<?php echo Phpfox::getLib('template')->getSectionMenuJavaScript(); ?>
</div>

<?php if (isset ( $this->_aVars['sPublicMessage'] ) && $this->_aVars['sPublicMessage'] && ! is_bool ( $this->_aVars['sPublicMessage'] )): ?>
<div class="public_message <?php if ($this->_aVars['sPublicMessageType'] != 'success'): ?>public_message_<?php echo $this->_aVars['sPublicMessageType'];  endif; ?>" id="public_message" data-auto-close="<?php echo $this->_aVars['sPublicMessageAutoClose']; ?>">
    <span><?php echo $this->_aVars['sPublicMessage']; ?></span>
    <span class="ico ico-close-circle-o" onclick="$Core.publicMessageSlideDown();"></span>
</div>
<script type="text/javascript">
	$Behavior.template_error = function()
	{
	$('#public_message').show();
	};
</script>
<?php else: ?>
<div class="public_message" id="public_message"></div>
<?php endif; ?>
<div id="pem"><a href="#"></a></div>
<div id="core_js_messages">
<?php if (isset ( $this->_aVars['aErrors'] ) && count ( $this->_aVars['aErrors'] )): ?>
<?php if (count((array)$this->_aVars['aErrors'])):  foreach ((array) $this->_aVars['aErrors'] as $this->_aVars['sErrorMessage']): ?>
	<div class="error_message <?php if (defined ( 'PHPFOX_ERROR_AS_WARNING' ) && PHPFOX_ERROR_AS_WARNING): ?>warning_message<?php endif; ?>"><?php echo $this->_aVars['sErrorMessage']; ?>
<?php if (defined ( 'PHPFOX_ERROR_FORCE_LOGOUT' ) && PHPFOX_ERROR_FORCE_LOGOUT): ?>
    <button class="button btn-default" onclick="$Core.reloadPage();"><?php echo _p("ok"); ?></button>
<?php endif; ?>
    </div>
<?php endforeach; endif; ?>
<?php unset($this->_aVars['sErrorMessage'], $this->_aVars['sample']); ?>

<?php endif; ?>
</div>

<?php if (defined ( 'PHPFOX_TRIAL_MODE' )): ?>
    <?php
						Phpfox::getLib('template')->getBuiltFile('core.block.template-trial-mode');
						 endif; ?>

<?php if (setting ( 'core.site_is_offline' )): ?>
<?php Phpfox::getBlock('core.template-site-offline', array());  endif; ?>
