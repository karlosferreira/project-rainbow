<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php

 

?>
<div class="global_attachment_holder_section" id="global_attachment_photo">
<?php (($sPlugin = Phpfox_Plugin::get('photo.template_block_share_1')) ? eval($sPlugin) : false); ?>
	<div><input type="hidden" name="val[group_id]" value="<?php if (isset ( $this->_aVars['aFeedCallback']['item_id'] )):  echo $this->_aVars['aFeedCallback']['item_id'];  else: ?>0<?php endif; ?>" /></div>
	<div><input type="hidden" name="val[action]" value="upload_photo_via_share" /></div>

<?php Phpfox::getBlock('core.upload-form', array('type' => 'photo_feed')); ?>

<?php (($sPlugin = Phpfox_Plugin::get('photo.template_block_share_2')) ? eval($sPlugin) : false); ?>
</div>
<?php (($sPlugin = Phpfox_Plugin::get('photo.template_block_share_3')) ? eval($sPlugin) : false); ?>
