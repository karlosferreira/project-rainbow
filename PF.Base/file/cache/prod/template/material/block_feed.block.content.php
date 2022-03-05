<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php



 if (! isset ( $this->_aVars['aFeed']['feed_mini'] )): ?>
    <div class="activity_feed_header">
        <div class="activity_feed_header_info">
<?php echo '<span class="user_profile_link_span" id="js_user_name_link_' . Phpfox::getService('user')->getUserName($this->_aVars['aFeed']['user_id'], $this->_aVars['aFeed']['user_name']) . '">' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aFeed']['user_id']) ? '' : '<a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile', array($this->_aVars['aFeed']['user_name'], ((empty($this->_aVars['aFeed']['user_name']) && isset($this->_aVars['aFeed']['profile_page_id'])) ? $this->_aVars['aFeed']['profile_page_id'] : null))) . '">') . '' . Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->clean(Phpfox::getService('user')->getCurrentName($this->_aVars['aFeed']['user_id'], $this->_aVars['aFeed']['full_name'])), 50, '...') . '' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aFeed']['user_id']) ? '' : '</a>') . '</span>'; ?>
<?php if (( ! empty ( $this->_aVars['aFeed']['parent_module_id'] ) || isset ( $this->_aVars['aFeed']['parent_is_app'] ) )): ?>
                <span class="feed_info"><?php echo _p('shared'); ?></span>
<?php else: ?>
<?php if (! empty ( $this->_aVars['aFeed']['parent_user'] )): ?>
                    <span class="ico ico-caret-right"></span> <?php echo '<span class="user_profile_link_span" id="js_user_name_link_' . Phpfox::getService('user')->getUserName($this->_aVars['aFeed']['parent_user']['parent_user_id'], $this->_aVars['aFeed']['parent_user']['parent_user_name']) . '">' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aFeed']['parent_user']['parent_user_id']) ? '' : '<a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile', array($this->_aVars['aFeed']['parent_user']['parent_user_name'], ((empty($this->_aVars['aFeed']['parent_user']['parent_user_name']) && isset($this->_aVars['aFeed']['parent_user']['parent_profile_page_id'])) ? $this->_aVars['aFeed']['parent_user']['parent_profile_page_id'] : null))) . '">') . '' . Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->clean(Phpfox::getService('user')->getCurrentName($this->_aVars['aFeed']['parent_user']['parent_user_id'], $this->_aVars['aFeed']['parent_user']['parent_full_name'])), 50, '...') . '' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aFeed']['parent_user']['parent_user_id']) ? '' : '</a>') . '</span>'; ?>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aFeed']['feed_info'] )): ?>
                    <span class="feed_info"><?php echo $this->_aVars['aFeed']['feed_info']; ?></span>
<?php endif; ?>
<?php endif; ?>

<?php if (Phpfox ::getParam('feed.enable_check_in') && Phpfox ::getParam('core.google_api_key') != '' && ! empty ( $this->_aVars['aFeed']['location_name'] )): ?>
                <span class="activity_feed_location_at"> <?php echo _p('at'); ?> </span>
                <span class="js_location_name_hover activity_feed_location_name">
                    <span class="ico ico-checkin"></span>
                    <a href="https://maps.google.com/maps?daddr=<?php echo $this->_aVars['aFeed']['location_latlng']['latitude']; ?>,<?php echo $this->_aVars['aFeed']['location_latlng']['longitude']; ?>" target="_blank"><?php echo $this->_aVars['aFeed']['location_name']; ?></a>
                </span>
<?php endif; ?>

<?php if (Phpfox ::getParam('feed.enable_tag_friends') && ! empty ( $this->_aVars['aFeed']['friends_tagged'] )): ?>
                <span class="activity_feed_tagged_user">
                    <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.focus-tagged');
						?>
                </span>
<?php endif; ?>

            <div class="activity-feed-time-privacy-block">
                <time>
                    <a href="<?php echo $this->_aVars['aFeed']['feed_link']; ?>" class="feed_permalink"><?php echo Phpfox::getLib('date')->convertTime($this->_aVars['aFeed']['time_stamp'], 'feed.feed_display_time_stamp'); ?></a>
<?php if (( isset ( $this->_aVars['sponsor'] ) && $this->_aVars['sponsor'] ) || ( isset ( $this->_aVars['aFeed']['sponsored_feed'] ) && $this->_aVars['aFeed']['sponsored_feed'] )): ?>
                        <span>
                            <b><?php echo _p('sponsored'); ?></b>
                        </span>
<?php endif; ?>
                </time>
<?php if (! empty ( $this->_aVars['aFeed']['privacy_icon_class'] )): ?>
                    <span class="<?php echo $this->_aVars['aFeed']['privacy_icon_class']; ?>"></span>
<?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<div class="activity_feed_content">
<?php if (( isset ( $this->_aVars['aFeed']['focus'] ) )): ?>
        <div data-is-focus="1">
<?php echo $this->_aVars['aFeed']['focus']['html']; ?>
        </div>
<?php else: ?>
		<?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.focus');
						?>
<?php endif; ?>

<?php if (isset ( $this->_aVars['aFeed']['feed_view_comment'] )): ?>
<?php Phpfox::getBlock('feed.comment', array()); ?>
<?php else: ?>
		<?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.comment');
						?>
<?php endif; ?>

<?php if ($this->_aVars['aFeed']['type_id'] != 'friend'): ?>
<?php if (isset ( $this->_aVars['aFeed']['more_feed_rows'] ) && is_array ( $this->_aVars['aFeed']['more_feed_rows'] ) && count ( $this->_aVars['aFeed']['more_feed_rows'] )): ?>
<?php if ($this->_aVars['iTotalExtraFeedsToShow'] = count ( $this->_aVars['aFeed']['more_feed_rows'] )):  endif; ?>
			<a href="#" class="activity_feed_content_view_more" onclick="$(this).parents('.js_feed_view_more_entry_holder:first').find('.js_feed_view_more_entry').show(); $(this).remove(); return false;"><?php echo _p('see_total_more_posts_from_full_name', array('total' => $this->_aVars['iTotalExtraFeedsToShow'],'full_name' => Phpfox::getLib('phpfox.parse.output')->shorten($this->_aVars['aFeed']['full_name'], 40, '...'))); ?></a>
<?php endif; ?>
<?php endif; ?>
</div>
