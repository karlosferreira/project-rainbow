<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:38 am */ ?>
<?php
    
 if ($this->_aVars['aParentFeed']): ?>
    <div class="feed_share_holder feed_share_<?php echo $this->_aVars['aParentFeed']['type_id']; ?>">
        <div class="feed_share_header">
<?php if (! isset ( $this->_aVars['aParentFeed']['no_user_show'] )): ?>
<?php echo '<span class="user_profile_link_span" id="js_user_name_link_' . Phpfox::getService('user')->getUserName($this->_aVars['aParentFeed']['user_id'], $this->_aVars['aParentFeed']['user_name']) . '">' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aParentFeed']['user_id']) ? '' : '<a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile', array($this->_aVars['aParentFeed']['user_name'], ((empty($this->_aVars['aParentFeed']['user_name']) && isset($this->_aVars['aParentFeed']['profile_page_id'])) ? $this->_aVars['aParentFeed']['profile_page_id'] : null))) . '">') . '' . Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->clean(Phpfox::getService('user')->getCurrentName($this->_aVars['aParentFeed']['user_id'], $this->_aVars['aParentFeed']['full_name'])), 50, '...') . '' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aParentFeed']['user_id']) ? '' : '</a>') . '</span>'; ?>
<?php endif; ?>
<?php if (isset ( $this->_aVars['aParentFeed']['parent_user'] )): ?> <span class="ico ico-caret-right"></span> <?php echo '<span class="user_profile_link_span" id="js_user_name_link_' . Phpfox::getService('user')->getUserName($this->_aVars['aParentFeed']['parent_user']['parent_user_id'], $this->_aVars['aParentFeed']['parent_user']['parent_user_name']) . '">' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aParentFeed']['parent_user']['parent_user_id']) ? '' : '<a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile', array($this->_aVars['aParentFeed']['parent_user']['parent_user_name'], ((empty($this->_aVars['aParentFeed']['parent_user']['parent_user_name']) && isset($this->_aVars['aParentFeed']['parent_user']['parent_profile_page_id'])) ? $this->_aVars['aParentFeed']['parent_user']['parent_profile_page_id'] : null))) . '">') . '' . Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->clean(Phpfox::getService('user')->getCurrentName($this->_aVars['aParentFeed']['parent_user']['parent_user_id'], $this->_aVars['aParentFeed']['parent_user']['parent_full_name'])), 50, '...') . '' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aParentFeed']['parent_user']['parent_user_id']) ? '' : '</a>') . '</span>'; ?> <?php endif;  if (! empty ( $this->_aVars['aParentFeed']['feed_info'] )): ?> <?php echo $this->_aVars['aParentFeed']['feed_info'];  endif; ?>
<?php if (Phpfox ::getParam('feed.enable_check_in') && Phpfox ::getParam('core.google_api_key') != '' && ! empty ( $this->_aVars['aParentFeed']['location_name'] )): ?>
                <span class="activity_feed_location_at"><?php echo _p('at'); ?> </span>
                <span class="js_location_name_hover activity_feed_location_name">
                    <span class="ico ico-checkin"></span>
                    <a href="https://maps.google.com/maps?daddr=<?php echo $this->_aVars['aParentFeed']['location_latlng']['latitude']; ?>,<?php echo $this->_aVars['aParentFeed']['location_latlng']['longitude']; ?>" target="_blank"><?php echo $this->_aVars['aParentFeed']['location_name']; ?></a>
                </span>
<?php endif; ?>

<?php if (Phpfox ::getParam('feed.enable_tag_friends') && ! empty ( $this->_aVars['aParentFeed']['friends_tagged'] ) && ! empty ( $this->_aVars['aParentFeed']['total_friends_tagged'] )): ?>
            <?php
                $this->_aVars['aFeed']['friends_tagged'] = $this->_aVars['aParentFeed']['friends_tagged'];
                $this->_aVars['aFeed']['total_friends_tagged'] = $this->_aVars['aParentFeed']['total_friends_tagged'];
                $this->_aVars['aFeed']['temp_type_id'] = $this->_aVars['aFeed']['type_id'];
                $this->_aVars['aFeed']['temp_item_id'] = $this->_aVars['aFeed']['item_id'];
                $this->_aVars['aFeed']['item_id'] = $this->_aVars['aParentFeed']['item_id'];
                $this->_aVars['aFeed']['type_id'] = $this->_aVars['aParentFeed']['type_id'];
            ?>
            <span class="activity_feed_tagged_user"><?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.focus-tagged');
						?></span>
            <?php
                $this->_aVars['aFeed']['item_id'] = $this->_aVars['aFeed']['temp_item_id'];
                $this->_aVars['aFeed']['type_id'] = $this->_aVars['aFeed']['temp_type_id'];
                unset($this->_aVars['aFeed']['temp_type_id']);
                unset($this->_aVars['aFeed']['temp_item_id']);
            ?>
<?php endif; ?>
            <div class="activity-feed-time-privacy-block">
                <time>
                    <a href="<?php echo $this->_aVars['aParentFeed']['feed_link']; ?>" class="feed_permalink"><?php echo Phpfox::getLib('date')->convertTime($this->_aVars['aParentFeed']['time_stamp'], 'feed.feed_display_time_stamp'); ?></a>
                </time>
<?php if (! empty ( $this->_aVars['aParentFeed']['privacy_icon_class'] )): ?>
                    <span class="<?php echo $this->_aVars['aParentFeed']['privacy_icon_class']; ?>"></span>
<?php endif; ?>
            </div>

<?php if (! empty ( $this->_aVars['aParentFeed']['feed_mini_content'] )): ?>
                <div class="activity_feed_content_status">
                    <div class="activity_feed_content_status_left">
                        <img src="<?php echo $this->_aVars['aParentFeed']['feed_icon']; ?>" alt="" class="v_middle" /> <?php echo $this->_aVars['aParentFeed']['feed_mini_content']; ?>
                    </div>
                    <div class="activity_feed_content_status_right">
                        <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.link');
						?>
                    </div>
                    <div class="clear"></div>
                </div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['aParentFeed']['feed_status'] ) && ( ! empty ( $this->_aVars['aParentFeed']['feed_status'] ) || $this->_aVars['aParentFeed']['feed_status'] == '0' )): ?>
<?php if (! empty ( $this->_aVars['aParentFeed']['status_background'] )): ?>
                <div class="p-statusbg-feed" style="background-image: url('<?php echo $this->_aVars['aParentFeed']['status_background']; ?>');">
<?php endif; ?>
                    <div class="activity_feed_content_status">
<?php echo Phpfox::getLib('phpfox.parse.output')->split(Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('parse.output')->feedStrip($this->_aVars['aParentFeed']['feed_status'], false), 200, 'feed.view_more', true), 55); ?>
                    </div>
<?php if (! empty ( $this->_aVars['aParentFeed']['status_background'] )): ?>
                </div>
<?php endif; ?>
<?php endif; ?>
        </div>
<?php if (isset ( $this->_aVars['aParentFeed']['load_block'] )): ?>
<?php Phpfox::getBlock($this->_aVars['aParentFeed']['load_block'], array('this_feed_id' => $this->_aVars['aParentFeed']['feed_id'])); ?>
<?php else: ?>
            <div class="activity_feed_content_link"<?php if (isset ( $this->_aVars['aParentFeed']['no_user_show'] )): ?> style="margin-top:0px;"<?php endif; ?>>
<?php if ($this->_aVars['aParentFeed']['type_id'] == 'friend' && isset ( $this->_aVars['aParentFeed']['more_feed_rows'] ) && is_array ( $this->_aVars['aParentFeed']['more_feed_rows'] ) && count ( $this->_aVars['aParentFeed']['more_feed_rows'] )): ?>
<?php if (count((array)$this->_aVars['aParentFeed']['more_feed_rows'])):  foreach ((array) $this->_aVars['aParentFeed']['more_feed_rows'] as $this->_aVars['aFriends']): ?>
<?php echo $this->_aVars['aFriends']['feed_image']; ?>
<?php endforeach; endif; ?>
<?php echo $this->_aVars['aParentFeed']['feed_image']; ?>
<?php else: ?>
<?php if (! empty ( $this->_aVars['aParentFeed']['feed_image'] )): ?>
                        <div class="activity_feed_content_image"<?php if (isset ( $this->_aVars['aParentFeed']['feed_custom_width'] )): ?> style="width:<?php echo $this->_aVars['aParentFeed']['feed_custom_width']; ?>;"<?php endif; ?>>
<?php if (is_array ( $this->_aVars['aParentFeed']['feed_image'] )): ?>
                                <div class="activity_feed_multiple_image feed-img-stage-<?php echo $this->_aVars['aParentFeed']['total_image']; ?>">
<?php if (count((array)$this->_aVars['aParentFeed']['feed_image'])):  $this->_aPhpfoxVars['iteration']['image'] = 0;  foreach ((array) $this->_aVars['aParentFeed']['feed_image'] as $this->_aVars['sFeedImage']):  $this->_aPhpfoxVars['iteration']['image']++; ?>

                                    <div class="img-<?php echo $this->_aPhpfoxVars['iteration']['image']; ?>">
<?php echo $this->_aVars['sFeedImage']; ?>
                                    </div>
<?php endforeach; endif; ?>
                                </div>
                                <div class="clear"></div>
<?php else: ?>
                                <a href="<?php echo $this->_aVars['aParentFeed']['feed_link']; ?>" target="_blank" class="<?php if (isset ( $this->_aVars['aParentFeed']['custom_css'] )): ?> <?php echo $this->_aVars['aParentFeed']['custom_css']; ?> <?php endif;  if (! empty ( $this->_aVars['aParentFeed']['feed_image_onclick'] )):  if (! isset ( $this->_aVars['aParentFeed']['feed_image_onclick_no_image'] )): ?>play_link <?php endif; ?> no_ajax_link<?php endif; ?>"<?php if (! empty ( $this->_aVars['aParentFeed']['feed_image_onclick'] )): ?> onclick="<?php echo $this->_aVars['aParentFeed']['feed_image_onclick']; ?>"<?php endif;  if (! empty ( $this->_aVars['aParentFeed']['custom_rel'] )): ?> rel="<?php echo $this->_aVars['aParentFeed']['custom_rel']; ?>"<?php endif;  if (isset ( $this->_aVars['aParentFeed']['custom_js'] )): ?> <?php echo $this->_aVars['aParentFeed']['custom_js']; ?> <?php endif; ?>><?php if (! empty ( $this->_aVars['aParentFeed']['feed_image_onclick'] )):  if (! isset ( $this->_aVars['aParentFeed']['feed_image_onclick_no_image'] )): ?><span class="play_link_img"><?php echo _p('play'); ?></span><?php endif;  endif;  echo $this->_aVars['aParentFeed']['feed_image']; ?></a>
<?php endif; ?>
                        </div>
<?php endif; ?>
                    <div class="<?php if (( ! empty ( $this->_aVars['aParentFeed']['feed_content'] ) || ! empty ( $this->_aVars['aParentFeed']['feed_custom_html'] ) ) && empty ( $this->_aVars['aParentFeed']['feed_image'] )): ?> activity_feed_content_no_image<?php endif;  if (! empty ( $this->_aVars['aParentFeed']['feed_image'] )): ?> activity_feed_content_float<?php endif; ?>"<?php if (isset ( $this->_aVars['aParentFeed']['feed_custom_width'] )): ?> style="margin-left:<?php echo $this->_aVars['aParentFeed']['feed_custom_width']; ?>;"<?php endif; ?>>
<?php if (! empty ( $this->_aVars['aParentFeed']['feed_title'] )): ?>
                            <a href="<?php echo $this->_aVars['aParentFeed']['feed_link']; ?>" class="activity_feed_content_link_title<?php if (isset ( $this->_aVars['aParentFeed']['custom_css'] )): ?> <?php echo $this->_aVars['aParentFeed']['custom_css'];  endif; ?>"<?php if (isset ( $this->_aVars['aParentFeed']['feed_title_extra_link'] )): ?> target="_blank"<?php endif; ?>><?php echo Phpfox::getLib('phpfox.parse.output')->split(Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aParentFeed']['feed_title'])), 30); ?></a>
<?php if (! empty ( $this->_aVars['aParentFeed']['feed_title_extra'] )): ?>
                                <div class="activity_feed_content_link_title_link">
                                    <a href="<?php echo $this->_aVars['aParentFeed']['feed_title_extra_link']; ?>" class="<?php if (isset ( $this->_aVars['aParentFeed']['custom_css'] )):  echo $this->_aVars['aParentFeed']['custom_css'];  endif; ?>" target="_blank"><?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aParentFeed']['feed_title_extra'])); ?></a>
                                </div>
<?php endif; ?>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aParentFeed']['feed_content'] )): ?>
                            <div class="activity_feed_content_display">
<?php echo Phpfox::getLib('phpfox.parse.output')->split(Phpfox::getLib('parse.output')->feedStrip($this->_aVars['aParentFeed']['feed_content'], false), 55); ?>
                            </div>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aParentFeed']['feed_custom_html'] )): ?>
                            <div class="activity_feed_content_display_custom">
<?php echo $this->_aVars['aParentFeed']['feed_custom_html']; ?>
                            </div>
<?php endif; ?>

<?php if (! empty ( $this->_aVars['aParentFeed']['app_content'] )): ?>
<?php echo $this->_aVars['aParentFeed']['app_content']; ?>
<?php endif; ?>

                    </div>
<?php if (! empty ( $this->_aVars['aParentFeed']['feed_image'] )): ?>
                        <div class="clear"></div>
<?php endif; ?>
<?php endif; ?>

<?php if ($this->_aVars['showMap']): ?>
                    <div class="activity_feed_location">
                        <div id="feed_<?php echo $this->_aVars['aFeed']['feed_id']; ?>_share_<?php echo $this->_aVars['aParentFeed']['feed_id']; ?>" class="pf-feed-map" data-component="pf_map" data-lat="<?php echo $this->_aVars['aParentFeed']['location_latlng']['latitude']; ?>" data-lng="<?php echo $this->_aVars['aParentFeed']['location_latlng']['longitude']; ?>" data-id="feed_<?php echo $this->_aVars['aFeed']['feed_id']; ?>_share_<?php echo $this->_aVars['aParentFeed']['feed_id']; ?>"></div>
                    </div>
<?php endif; ?>
            </div>
<?php endif; ?>
    </div>
<?php else: ?>
    <div class="alert alert-warning m_bottom_0 mt-1" role="alert">
        <h4 class="alert-heading mb-1"><?php echo _p('this_content_is_not_available_at_the_moment'); ?></h4>
        <p><?php echo _p('when_this_happens_its_usually_because_the_owner_only_shared_it_with_a_small_group_of_people_or_changed_who_can_see_it_or_its_been_deleted'); ?></p>
    </div>
<?php endif; ?>
			
