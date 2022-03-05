<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php
 
 

?>

<?php echo Phpfox::getParam('core.site_copyright'); ?> &middot; <a href="#" id="select_lang_pack"><?php if (Phpfox ::getParam('language.display_language_flag') && ! empty ( $this->_aVars['sLocaleFlagId'] )): ?><img src="<?php echo $this->_aVars['sLocaleFlagId']; ?>" alt="<?php echo $this->_aVars['sLocaleName']; ?>" class="v_middle" /> <?php endif;  echo $this->_aVars['sLocaleName']; ?></a>
<?php if (( defined ( 'PHPFOX_TRIAL_MODE' ) )): ?>
&middot; <a href="https://www.phpfox.com/">Powered by phpFox</a>
<?php endif; ?>
