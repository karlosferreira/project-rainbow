<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:00 am */ ?>
<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: share.html.php 7024 2014-01-07 14:54:37Z Fern $
 */
 
 

?>
<script type="text/javascript">
<?php echo '
	function sendFeed(oObj)
	{
		$(\'#btnShareFeed\').attr(\'disabled\', \'disabled\');
		$(\'#imgShareFeedLoading\').show();
		$(oObj).ajaxCall(\'feed.share\');
		
		return false;
	}
'; ?>

</script>
<div class="activity_feed_share_form">
	<form class="form" method="post" action="#" onsubmit="return sendFeed(this);">
		<div><input type="hidden" name="val[parent_feed_id]" value="<?php echo $this->_aVars['iFeedId']; ?>" /></div>
		<div><input type="hidden" name="val[parent_module_id]" value="<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['sShareModule'])); ?>" /></div>
		<select class="form-control" name="val[post_type]" onchange="if (this.value == '1') { $('#js_feed_share_friend_holder').hide(); $('#js_feed_share_privacy_button').show(); } else { $('#js_feed_share_friend_holder').show(); $('#js_feed_share_privacy_button').hide(); }">
			<option value="1"><?php echo _p('on_your_wall'); ?></option>
			<option value="2"><?php echo _p('on_a_friend_s_wall'); ?></option>
		</select>
		<div class="p_top_8" id="js_feed_share_friend_holder" style="display:none;">
<?php Phpfox::getBlock('friend.search-small', array('input_name' => 'val[friends]')); ?>
		</div>
		<div class="p_top_8">
			<textarea class="form-control" rows="4" name="val[post_content]"></textarea>
<?php if (isset ( $this->_aVars['bLoadTagFriends'] ) && $this->_aVars['bLoadTagFriends'] == true): ?>
                <script type="text/javascript">
                  oTranslations['with_name_and_name'] = "<?php echo _p('with_name_and_name'); ?>";
                  oTranslations['with_name'] = "<?php echo _p('with_name'); ?>";
                  oTranslations['with_name_and_number_others'] = "<?php echo _p('with_name_and_number_others'); ?>";
                  oTranslations['number_others'] = "<?php echo _p('number_others'); ?>";
                </script>
                <div class="js_tagged_review"></div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bLoadTagFriends'] ) && $this->_aVars['bLoadTagFriends'] == true): ?>
                <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.tagged');
						?>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bLoadCheckIn'] ) && $this->_aVars['bLoadCheckIn'] == true): ?>
                <script type="text/javascript">
                  oTranslations['at_location'] = "<?php echo _p('at_location'); ?>";
                </script>
                <div id="js_location_feedback" class="js_location_feedback">
<?php if (! empty ( $this->_aVars['aForms']['location_name'] )): ?>
<?php echo _p('at_location', array('location' => $this->_aVars['aForms']['location_name'])); ?>
<?php endif; ?>
                </div>
<?php endif; ?>
            <script type="text/javascript">
                oTranslations['will_send_on_time'] = "<?php echo _p('will_send_on_time'); ?>";
            </script>
            <div class="js_schedule_review"></div>
		</div>
<?php if (Phpfox ::isModule('privacy')): ?>
        <div id="js_custom_privacy_input_holder">
<?php Phpfox::getBlock('privacy.build', array('privacy_item_id' => $this->_aVars['aForms']['item_id'],'privacy_module_id' => $this->_aVars['aForms']['type_id'])); ?>
        </div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bLoadCheckIn'] ) && $this->_aVars['bLoadCheckIn'] == true): ?>
            <div id="js_location_input">
                <a class="btn btn-danger toggle-checkin" href="#" title="<?php echo _p('close'); ?>" onclick="$Core.FeedPlace.cancelCheckIn(<?php echo $this->_aVars['iFeedId']; ?>, true); return false;"><i class="fa fa-eye-slash"></i></a>
                <a class="btn btn-danger" href="#" title="<?php echo _p('remove_checkin'); ?>" onclick="$Core.FeedPlace.cancelCheckIn(<?php echo $this->_aVars['iFeedId']; ?>); return false;"><i class="fa fa-times"></i></a>
                <input type="text" id="hdn_location_name" class="close_warning" <?php if (! empty ( $this->_aVars['aForms']['location_name'] )): ?>value="<?php echo $this->_aVars['aForms']['location_name']; ?>"<?php endif; ?> autocomplete="off">
            </div>
<?php endif; ?>
        <div class="activity_feed_share_form_button_position">
            <div id="activity_feed_share_this_one" class="activity_feed_checkin">
<?php $this->assign('iFeedId', 0); ?>
<?php if (isset ( $this->_aVars['bLoadTagFriends'] ) && $this->_aVars['bLoadTagFriends'] == true): ?>
                    <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.with-friend');
						?>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bLoadCheckIn'] ) && $this->_aVars['bLoadCheckIn'] == true): ?>
                    <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.checkin');
						?>
<?php endif; ?>
            </div>
            <div class="activity_feed_share_form_button_position_button">
                <input type="submit" id="btnShareFeed" value="<?php echo _p('post'); ?>" class="btn btn-primary" />
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => 'ajax/small.gif','style' => "display:none",'id' => "imgShareFeedLoading")); ?>
            </div>
            <div id="js_feed_share_privacy_button">
<?php Phpfox::getBlock('privacy.form', array('privacy_name' => 'privacy','privacy_type' => 'mini','btn_size' => 'normal','default_privacy' => 'feed.default_privacy_setting')); ?>
            </div>
        </div>
	
</form>

</div>
<script type="text/javascript">
	$Core.loadInit();
</script>
