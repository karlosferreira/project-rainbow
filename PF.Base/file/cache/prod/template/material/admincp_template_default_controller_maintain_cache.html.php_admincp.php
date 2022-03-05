<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: February 19, 2022, 2:44 am */ ?>
<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package  		Module_Admincp
 * @version 		$Id: cache.html.php 5332 2013-02-11 08:27:54Z phpFox LLC $
 */
 
 

 if ($this->_aVars['bCacheLocked']): ?>
<div class="alert alert-warning" role="alert">
    <h4><?php echo _p('cache_system_is_locked'); ?></h4>
    <p>
<?php echo _p('the_cache_system_is_locked_during_an_operation_that_requires_all_cache_files_to_be_kept_in_place', array('link' => $this->_aVars['sUnlockCache'])); ?>
    </p>
</div>

<?php else: ?>
<?php if ($this->_aVars['iCacheCnt'] > 0): ?>
<?php if (! defined ( 'PHPFOX_IS_HOSTED_SCRIPT' )): ?>
        <div class="alert alert-info" role="alert">
<?php echo _p('total_objects'); ?>: <?php echo $this->_aVars['aStats']['total']; ?>
        </div>
        <div class="alert alert-info" role="alert">
<?php echo _p('cache_size'); ?>: <?php echo Phpfox::getLib('phpfox.file')->filesize($this->_aVars['aStats']['size']); ?>
        </div>
        <div class="alert alert-info" role="alert">
<?php echo $this->_aVars['aStats']['info']; ?>
        </div>
        <div class="alert alert-info" role="alert">
<?php echo _p('Driver'); ?>: <?php echo $this->_aVars['aStats']['driver']; ?>
        </div>
<?php endif; ?>
<?php else: ?>
    <div class="alert alert-info" role="alert">
<?php echo _p('no_cache_data_found'); ?>
    </div>
<?php endif;  endif; ?>
