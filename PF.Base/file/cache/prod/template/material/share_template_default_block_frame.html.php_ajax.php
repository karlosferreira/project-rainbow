<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:00 am */ ?>
<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package  		Module_Share
 * @version 		$Id: frame.html.php 6769 2013-10-11 09:08:02Z phpFox LLC $
 */
 
 

 if (Phpfox ::isUser() && $this->_aVars['iFeedId'] > 0): ?>
<?php Phpfox::getBlock('feed.share', array('type' => $this->_aVars['sBookmarkType'],'url' => $this->_aVars['sBookmarkUrl']));  else: ?>
<?php Phpfox::getBlock('share.friend', array('type' => $this->_aVars['sBookmarkType'],'url' => $this->_aVars['sBookmarkUrl'],'title' => $this->_aVars['sBookmarkTitle'])); ?>
    <script type="text/javascript">$Core.loadInit();</script>
<?php endif; ?>
