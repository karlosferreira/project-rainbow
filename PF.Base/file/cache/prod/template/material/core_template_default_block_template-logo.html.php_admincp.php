<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: February 19, 2022, 2:44 am */ ?>
<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author           phpFox LLC
 * @package          Phpfox
 * @version          $Id: template-logo.html.php 7042 2014-01-14 12:42:41Z Fern $
 */



?>
<div class="site-logo">
    <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('', [], false, false); ?>" class="site-logo-link">
        <span class="site-logo-icon"><i<?php if (isset ( $this->_aVars['logo'] )): ?> style="background-image:url(<?php echo $this->_aVars['logo']; ?>)"<?php endif; ?>></i></span>
<?php if (( isset ( $this->_aVars['site_name'] ) )): ?>
	    <span class="site-logo-name" style="display:none;"><?php echo $this->_aVars['site_name']; ?></span>
<?php endif; ?>
    </a>
</div>

