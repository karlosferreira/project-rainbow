<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php 

 

?>
<div class="footer-holder">
	<div class="copyright">
        <?php
						Phpfox::getLib('template')->getBuiltFile('core.block.template-copyright');
						?>
	</div>
	<ul class="list-inline footer-menu">
<?php if (count((array)$this->_aVars['aFooterMenu'])):  $this->_aPhpfoxVars['iteration']['footer'] = 0;  foreach ((array) $this->_aVars['aFooterMenu'] as $this->_aVars['iKey'] => $this->_aVars['aMenu']):  $this->_aPhpfoxVars['iteration']['footer']++; ?>

		<li<?php if ($this->_aPhpfoxVars['iteration']['footer'] == 1): ?> class="first"<?php endif; ?>><a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl(''.$this->_aVars['aMenu']['url'].'', [], false, false); ?>" class="ajax_link<?php if ($this->_aVars['aMenu']['url'] == 'mobile'): ?> no_ajax_link<?php endif; ?>"><?php echo _p($this->_aVars['aMenu']['var_name']); ?></a></li>
<?php endforeach; endif; ?>
	</ul>
</div>

