<?php 

defined('PHPFOX') or exit('NO DICE!'); 

?>
<div class="footer-holder">
	<div class="copyright">
        {template file='core.block.template-copyright'}
	</div>
	<ul class="list-inline footer-menu">
		{foreach from=$aFooterMenu key=iKey item=aMenu name=footer}
		<li{if $phpfox.iteration.footer == 1} class="first"{/if}><a href="{url link=''$aMenu.url''}" class="ajax_link{if $aMenu.url == 'mobile'} no_ajax_link{/if}">{_p var=$aMenu.var_name}</a></li>
		{/foreach}
	</ul>
</div>
