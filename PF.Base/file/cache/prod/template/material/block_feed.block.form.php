<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php



?>

<?php if (! defined ( 'PHPFOX_IS_USER_PROFILE' ) || $this->_aVars['aUser']['user_id'] == Phpfox ::getUserId() || ( Phpfox ::getUserParam('profile.can_post_comment_on_profile') && User_Service_Privacy_Privacy ::instance()->hasAccess('' . $this->_aVars['aUser']['user_id'] . '' , 'feed.share_on_wall' ) )): ?>
<div class="activity-feed-status-form">
  <div class="activity_feed_form_share">
    <div class="activity_feed_form_share_process"><?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => 'ajax/add.gif','class' => 'v_middle')); ?></div>
<?php if (! isset ( $this->_aVars['bSkipShare'] )): ?>
    <ul class="activity_feed_form_attach">
      <li class="share">
        <a role="button"><?php echo _p('share'); ?>:</a>
      </li>
<?php if (isset ( $this->_aVars['aFeedCallback']['module'] )): ?>
      <li><a href="#" rel="global_attachment_status" class="global_attachment_status active">
        <div>
<?php echo _p('post'); ?>
          <span class="activity_feed_link_form_ajax"><?php echo $this->_aVars['aFeedCallback']['ajax_request']; ?></span>
        </div>
        </a>
      </li>
<?php elseif (! isset ( $this->_aVars['bFeedIsParentItem'] ) && ( ! defined ( 'PHPFOX_IS_USER_PROFILE' ) || ( defined ( 'PHPFOX_IS_USER_PROFILE' ) && isset ( $this->_aVars['iUserProfileId'] ) && $this->_aVars['iUserProfileId'] == Phpfox ::getUserId() && empty ( $this->_aVars['mOnOtherUserProfile'] ) ) )): ?>
      <li><a href="#" rel="global_attachment_status" class="global_attachment_status active">
        <div>
<?php echo _p('status'); ?><span class="activity_feed_link_form_ajax">user.updateStatus</span></div><div class="drop"></div></a></li>
<?php else: ?>
      <li><a href="#" rel="global_attachment_status" class="global_attachment_status active"><div><?php echo _p('post'); ?><span class="activity_feed_link_form_ajax">feed.addComment</span></div><div class="drop"></div></a></li>
<?php endif; ?>
<?php if (count((array)$this->_aVars['aFeedStatusLinks'])):  $this->_aPhpfoxVars['iteration']['feedlinks'] = 0;  foreach ((array) $this->_aVars['aFeedStatusLinks'] as $this->_aVars['aFeedStatusLink']):  $this->_aPhpfoxVars['iteration']['feedlinks']++; ?>


<?php if ($this->_aPhpfoxVars['iteration']['feedlinks'] == 3): ?>
      <li><a href="#" rel="view_more_link" class="timeline_view_more js_hover_title"><span class="js_hover_info"><?php echo _p('load_more'); ?></span></a>
        <ul class="view_more_drop">
<?php endif; ?>

<?php if (isset ( $this->_aVars['aFeedCallback']['module'] ) && $this->_aVars['aFeedStatusLink']['no_profile']): ?>
<?php else: ?>
<?php if (( $this->_aVars['aFeedStatusLink']['no_profile'] && ! isset ( $this->_aVars['bFeedIsParentItem'] ) && ( ! defined ( 'PHPFOX_IS_USER_PROFILE' ) || ( defined ( 'PHPFOX_IS_USER_PROFILE' ) && isset ( $this->_aVars['aUser']['user_id'] ) && $this->_aVars['aUser']['user_id'] == Phpfox ::getUserId() && empty ( $this->_aVars['mOnOtherUserProfile'] ) ) ) ) || ! $this->_aVars['aFeedStatusLink']['no_profile']): ?>
          <li>
            <a href="#" rel="global_attachment_<?php echo $this->_aVars['aFeedStatusLink']['module_id']; ?>"<?php if ($this->_aVars['aFeedStatusLink']['no_input']): ?> class="no_text_input"<?php endif; ?>>
            <span class="activity-feed-form-tab"><?php echo Phpfox::getLib('locale')->convert($this->_aVars['aFeedStatusLink']['title']); ?></span>
            <div>
<?php if ($this->_aVars['aFeedStatusLink']['is_frame']): ?>
              <span class="activity_feed_link_form"><?php echo Phpfox::getLib('phpfox.url')->makeUrl(''.$this->_aVars['aFeedStatusLink']['module_id'].'.frame', [], false, false); ?></span>
<?php else: ?>
              <span class="activity_feed_link_form_ajax"><?php echo $this->_aVars['aFeedStatusLink']['module_id']; ?>.<?php echo $this->_aVars['aFeedStatusLink']['ajax_request']; ?></span>
<?php endif; ?>
              <span class="activity_feed_extra_info"><?php echo Phpfox::getLib('locale')->convert($this->_aVars['aFeedStatusLink']['description']); ?></span>
            </div>
            <div class="drop"></div>
            </a>
          </li>
<?php endif; ?>
<?php endif; ?>

<?php if ($this->_aPhpfoxVars['iteration']['feedlinks'] == count ( $this->_aVars['aFeedStatusLinks'] )): ?>
        </ul>
      </li>
<?php endif; ?>

<?php endforeach; endif; ?>
    </ul>
<?php endif; ?>
    <div class="clear"></div>
  </div>

  <div class="activity_feed_form">
    <form method="post" action="#" id="js_activity_feed_form" enctype="multipart/form-data">
      <div id="js_custom_privacy_input_holder"></div>
<?php if (isset ( $this->_aVars['aFeedCallback']['module'] )): ?>
      <div><input type="hidden" name="val[callback_item_id]" value="<?php echo $this->_aVars['aFeedCallback']['item_id']; ?>" /></div>
      <div><input type="hidden" name="val[callback_module]" value="<?php echo $this->_aVars['aFeedCallback']['module']; ?>" /></div>
      <div><input type="hidden" name="val[parent_user_id]" value="<?php echo $this->_aVars['aFeedCallback']['item_id']; ?>" /></div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bFeedIsParentItem'] )): ?>
      <div><input type="hidden" name="val[parent_table_change]" value="<?php echo $this->_aVars['sFeedIsParentItemModule']; ?>" /></div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['aFeedCallback']['module'] )): ?>
<?php elseif (isset ( $this->_aVars['iUserProfileId'] ) && $this->_aVars['iUserProfileId'] && $this->_aVars['iUserProfileId'] != Phpfox ::getUserId()): ?>
        <div><input type="hidden" name="val[parent_user_id]" value="<?php echo $this->_aVars['iUserProfileId']; ?>" /></div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bForceFormOnly'] ) && $this->_aVars['bForceFormOnly']): ?>
      <div><input type="hidden" name="force_form" value="1" /></div>
<?php endif; ?>
      <div class="activity_feed_form_holder">
        <div id="activity_feed_upload_error" style="display:none;"><div class="alert alert-danger" id="activity_feed_upload_error_message"></div></div>
        <div class="global_attachment_holder_section" id="global_attachment_status" style="display:block;">
            <div id="global_attachment_status_value" style="display:none;"></div>
            <textarea cols="60" rows="2" name="val[user_status]" class="close_warning" style="display: none"></textarea>
            <div contenteditable="true" id="<?php if (isset ( $this->_aVars['aPage'] )): ?>pageFeedTextarea<?php elseif (isset ( $this->_aVars['aEvent'] )): ?>eventFeedTextarea<?php elseif (isset ( $this->_aVars['bOwnProfile'] ) && $this->_aVars['bOwnProfile'] == false): ?>profileFeedTextarea<?php endif; ?>"
            class="contenteditable close_warning <?php if (Phpfox ::isAppActive('Core_eGifts')): ?>textarea-has-egift<?php endif; ?>"
            data-text="<?php if (isset ( $this->_aVars['aFeedCallback']['module'] ) || defined ( 'PHPFOX_IS_USER_PROFILE' )):  echo _p('write_something');  else:  echo _p('what_s_on_your_mind');  endif; ?>"
            style="line-height: normal;"></div>
        </div>
<?php if (count((array)$this->_aVars['aFeedStatusLinks'])):  foreach ((array) $this->_aVars['aFeedStatusLinks'] as $this->_aVars['aFeedStatusLink']): ?>
<?php if (! empty ( $this->_aVars['aFeedStatusLink']['module_block'] )): ?>
<?php Phpfox::getBlock($this->_aVars['aFeedStatusLink']['module_block'], array()); ?>
<?php endif; ?>
<?php endforeach; endif; ?>
<?php if (Phpfox ::isModule('egift')): ?>
<?php Phpfox::getBlock('egift.display', array()); ?>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bLoadTagFriends'] ) && $this->_aVars['bLoadTagFriends'] == true): ?>
            <script type="text/javascript">
                oTranslations['with_name_and_name'] = "<?php echo _p('with_name_and_name'); ?>";
                oTranslations['with_name'] = "<?php echo _p('with_name'); ?>";
                oTranslations['with_name_and_number_others'] = "<?php echo _p('with_name_and_number_others'); ?>";
                oTranslations['number_others'] = "<?php echo _p('number_others'); ?>";
            </script>
            <div class="js_tagged_review tagged_review"></div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bLoadCheckIn'] ) && $this->_aVars['bLoadCheckIn'] == true): ?>
          <script type="text/javascript">
            oTranslations['at_location'] = "<?php echo _p('at_location'); ?>";
          </script>
          <div id="js_location_feedback" class="js_location_feedback feed-location-info"></div>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['bLoadSchedule'] )): ?>
          <script type="text/javascript">
              oTranslations['will_send_on_time'] = "<?php echo _p('will_send_on_time'); ?>";
          </script>
<?php endif; ?>
        <div class="js_schedule_review tagged_review"></div>
      </div>
      <div class="activity_feed_form_button">
        <div class="activity_feed_form_button_status_info">
            <textarea cols="60" rows="8" name="val[status_info]" style="display: none"></textarea>
            <div contenteditable="true" id="activity_feed_textarea_status_info" class="contenteditable" style="min-height: 40px; line-height: normal;"></div>
<?php if (isset ( $this->_aVars['bLoadTagFriends'] ) && $this->_aVars['bLoadTagFriends'] == true): ?>
                <div class="js_tagged_review tagged_review"></div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bLoadCheckIn'] ) && $this->_aVars['bLoadCheckIn'] == true): ?>
                <div id="js_location_feedback" class="js_location_feedback feed-location-info"></div>
<?php endif; ?>
            <div class="js_schedule_review tagged_review"></div>
        </div>
<?php if ($this->_aVars['bLoadCheckIn']): ?>
              <div id="js_location_input">
                  <a class="pr-4" href="#" title="<?php echo _p('close'); ?>" onclick="$Core.FeedPlace.cancelCheckIn('', true); return false;"><i class="fa fa-eye-slash"></i></a>
                  <a class="" href="#" title="<?php echo _p('remove_checkin'); ?>" onclick="$Core.FeedPlace.cancelCheckIn(''); return false;">
                      <span class="ico ico-close"></span>
                  </a>
                  <input type="text" id="hdn_location_name" autocomplete="off">
              </div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['bLoadTagFriends'] ) && $this->_aVars['bLoadTagFriends'] == true): ?>
          <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.tagged');
						?>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['bLoadSchedule'] )): ?>
            <div class="js_feed_schedule_container">
                <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.feed-schedule');
						?>
            </div>
<?php endif; ?>
        <div class="activity_feed_form_button_position">
<?php if (( defined ( 'PHPFOX_IS_PAGES_VIEW' ) && $this->_aVars['aPage']['is_admin'] )): ?>

          <div id="activity_feed_share_this_one">
            <div class="activity-posting-as">
<?php if (defined ( 'PHPFOX_IS_PAGES_VIEW' ) && $this->_aVars['aPage']['is_admin'] && $this->_aVars['aPage']['page_id'] != Phpfox ::getUserBy('profile_page_id') && ( $this->_aVars['aPage']['item_type'] == 0 )): ?>
                <input type="hidden" name="custom_pages_post_as_page" value="<?php echo $this->_aVars['aPage']['page_id']; ?>">
                <a data-toggle="dropdown" role="button" class="">
                  <span class="txt-prefix"><?php echo _p('posting_as'); ?>: </span>
                  <span class="txt-label"><?php echo Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aPage']['full_name'])), 15, '...'); ?></span>
                  <span class="ico ico-caret-down ml-1"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-checkmark">
                  <li>
                    <a class="is_active_image" data-toggle="privacy_item" role="button" rel="<?php echo $this->_aVars['aPage']['page_id']; ?>"><?php echo Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aPage']['full_name'])), 20, '...'); ?></a>
                  </li>
                  <li>
                    <a data-toggle="privacy_item" role="button" rel="0"><?php echo Phpfox::getLib('phpfox.parse.output')->shorten($this->_aVars['sGlobalUserFullName'], 20, '...'); ?></a>
                  </li>
                </ul>
<?php endif; ?>
<?php if ($this->_aVars['bLoadTagFriends']): ?>
                <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.with-friend');
						?>
<?php endif; ?>
<?php if ($this->_aVars['bLoadCheckIn']): ?>
                <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.checkin');
						?>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['bLoadSchedule'] )): ?>
                <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.with-schedule');
						?>
<?php endif; ?>
            </div>
          </div>

<?php else: ?>
            <div id="activity_feed_share_this_one" class="activity_feed_checkin">
<?php if ($this->_aVars['bLoadTagFriends']): ?>
                <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.with-friend');
						?>
<?php endif; ?>
<?php if ($this->_aVars['bLoadCheckIn']): ?>
                  <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.checkin');
						?>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['bLoadSchedule'] )): ?>
                <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.with-schedule');
						?>
<?php endif; ?>
            </div>
<?php endif; ?>

          <div class="activity_feed_form_button_position_button">
            <button type="submit" value="<?php echo _p('share'); ?>"  id="activity_feed_submit" class="button btn btn-gradient btn-primary"><span class="ico ico-paperplane hide"></span><span><?php echo _p('share'); ?></span></button>
          </div>
<?php if (isset ( $this->_aVars['aFeedCallback']['module'] )): ?>
<?php else: ?>
            <div class="special_close_warning">
<?php if (! isset ( $this->_aVars['bFeedIsParentItem'] ) && ( ! defined ( 'PHPFOX_IS_USER_PROFILE' ) || ( defined ( 'PHPFOX_IS_USER_PROFILE' ) && isset ( $this->_aVars['aUser']['user_id'] ) && $this->_aVars['aUser']['user_id'] == Phpfox ::getUserId() && empty ( $this->_aVars['mOnOtherUserProfile'] ) ) )): ?>
<?php Phpfox::getBlock('privacy.form', array('privacy_name' => 'privacy','privacy_type' => 'mini','btn_size' => 'normal','default_privacy' => 'feed.default_privacy_setting')); ?>
<?php endif; ?>
            </div>
<?php endif; ?>
        </div>

<?php if (Phpfox ::getParam('feed.enable_check_in') && Phpfox ::getParam('core.google_api_key') != ''): ?>
        <div id="js_add_location">
          <div><input type="hidden" id="val_location_latlng" name="val[location][latlng]" class="close_warning"></div>
          <div><input type="hidden" id="val_location_name" name="val[location][name]"></div>
          <div id="js_add_location_suggestions" style="overflow-y: auto;"></div>
          <div id="js_feed_check_in_map"></div>
        </div>
<?php endif; ?>

      </div>
    
</form>

    <div class="activity_feed_form_iframe"></div>
  </div>
</div>
<?php endif; ?>
