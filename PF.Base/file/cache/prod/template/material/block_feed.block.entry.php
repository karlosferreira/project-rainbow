<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php 

 

 $this->assign('feed_entry_be', true); ?>
<div data-feed-id="<?php echo $this->_aVars['aFeed']['feed_id']; ?>" data-feed-update="<?php echo $this->_aVars['aFeed']['time_update']; ?>" class="feed-stream-content
<?php if (( isset ( $this->_aVars['sponsor'] ) && $this->_aVars['sponsor'] ) || ( isset ( $this->_aVars['aFeed']['sponsored_feed'] ) && $this->_aVars['aFeed']['sponsored_feed'] )): ?>sponsor<?php endif; ?> _app_<?php echo $this->_aVars['aFeed']['type_id']; ?> <?php if (isset ( $this->_aVars['aFeed']['custom_class'] )):  echo $this->_aVars['aFeed']['custom_class'];  endif; ?> js_parent_feed_entry js_user_feed" id="js_item_feed_<?php echo $this->_aVars['aFeed']['feed_id']; ?>" <?php if (! empty ( $this->_aVars['bForceFlavor'] )): ?>data-force-flavor="material"<?php endif; ?>>

<?php (($sPlugin = Phpfox_Plugin::get('feed.template_block_entry_1')) ? eval($sPlugin) : false); ?>
	<div class="activity_feed_image">
<?php if (! isset ( $this->_aVars['aFeed']['feed_mini'] )): ?>
<?php if (isset ( $this->_aVars['aFeed']['is_custom_app'] ) && $this->_aVars['aFeed']['is_custom_app'] && ( ( isset ( $this->_aVars['aFeed']['view_id'] ) && $this->_aVars['aFeed']['view_id'] == 7 ) || ( isset ( $this->_aVars['aFeed']['gender'] ) && $this->_aVars['aFeed']['gender'] < 1 ) )): ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('server_id' => 0,'path' => 'app.url_image','file' => $this->_aVars['aFeed']['app_image_path'],'suffix' => '_square','max_width' => 50,'max_height' => 50)); ?>
<?php else: ?>
<?php if (isset ( $this->_aVars['aFeed']['user_name'] ) && ! empty ( $this->_aVars['aFeed']['user_name'] )): ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aFeed'],'suffix' => '_120_square','max_width' => 50,'max_height' => 50)); ?>
<?php else: ?>
<?php if (! empty ( $this->_aVars['aFeed']['parent_user_name'] )): ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aFeed'],'suffix' => '_120_square','max_width' => 50,'max_height' => 50,'href' => $this->_aVars['aFeed']['parent_user_name'])); ?>
<?php else: ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aFeed'],'suffix' => '_120_square','max_width' => 50,'max_height' => 50,'href' => '')); ?>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
	</div>
	
	<?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.content');
						?>
	
<?php (($sPlugin = Phpfox_Plugin::get('feed.template_block_entry_3')) ? eval($sPlugin) : false); ?>
</div>
