<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 1:06 am */ ?>
<?php 

?>
<div class="welcome-bg-image row_image" >
<div id="welcome_message" class="<?php if (empty ( $this->_aVars['sWelcomeContent'] )): ?>hide<?php endif; ?>">
    <div class="custom_flavor_content" style="white-space: pre-wrap;"><?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['sWelcomeContent'])); ?></div>
</div>
</div>
