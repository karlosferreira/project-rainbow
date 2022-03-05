<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 11:53 pm */ ?>
<?php

 if (Phpfox ::getUserId() && ( Phpfox ::getUserId() != $this->_aVars['aFeed']['user_id'] ) && empty ( $this->_aVars['aFeed']['feed_display'] ) && ! defined ( 'PHPFOX_IS_USER_PROFILE' ) && ! defined ( 'PHPFOX_IS_PAGES_VIEW' ) && ! defined ( 'PHPFOX_IS_EVENT_VIEW' )): ?>
    <li class="js_hide_feed" id="hide_feed_<?php echo $this->_aVars['aFeed']['feed_id']; ?>" data-user-id="<?php echo $this->_aVars['aFeed']['user_id']; ?>" data-user-full_name="<?php echo $this->_aVars['aFeed']['full_name']; ?>">
        <a href="javascript:void(0);" class="feed_hide" title="<?php echo _p('hide_feed'); ?>" onclick="$Core.feed.prepareHideFeed([<?php echo $this->_aVars['aFeed']['feed_id']; ?>], []); $.ajaxCall('feed.hideFeed', 'id=' + <?php echo $this->_aVars['aFeed']['feed_id']; ?>); return false;">
            <span class="ico ico-eye-alt-blocked" aria-hidden="true"></span> <?php echo _p('hide'); ?>
        </a>
    </li>

<?php if (Phpfox ::getUserBy('profile_page_id') == 0): ?>
        <li class="">
            <a href="javascript:void(0);" class="feed_hide_all" title="<?php echo _p('hide_all_from_full_name_regular', array('full_name' => $this->_aVars['aFeed']['full_name'])); ?>" onclick="$Core.feed.prepareHideFeed([], [<?php echo $this->_aVars['aFeed']['user_id']; ?>]); $.ajaxCall('feed.hideAllFromUser', 'id=' + <?php echo $this->_aVars['aFeed']['user_id']; ?>); return false;">
                <span class="ico ico-eye-alt-blocked" aria-hidden="true"></span> <span><?php echo _p('hide_all_from_full_name', array('full_name' => $this->_aVars['aFeed']['full_name'])); ?></span>
            </a>
        </li>
<?php endif;  endif; ?>
