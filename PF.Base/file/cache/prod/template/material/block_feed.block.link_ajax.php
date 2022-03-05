<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 11:53 pm */ ?>
<?php

?>
<ul class="dropdown-menu dropdown-menu-right">
<?php if ($this->_aVars['aFeed']['type_id'] == "user_status" && ( ( Phpfox ::getUserParam('feed.can_edit_own_user_status') && $this->_aVars['aFeed']['user_id'] == Phpfox ::getUserId()) || Phpfox ::getUserParam('feed.can_edit_other_user_status'))): ?>
        <li class=""><a href="#" class="" data-privacy-editable="1" data-id="<?php echo $this->_aVars['aFeed']['feed_id']; ?>" onclick="tb_show('<?php echo _p('edit_your_post'); ?>', $.ajaxBox('feed.editUserStatus', 'height=400&amp;width=600&amp;id=<?php echo $this->_aVars['aFeed']['feed_id']; ?>')); return false;">
            <span class="ico ico-pencilline-o"></span> <?php echo _p('edit'); ?></a>
        </li>
<?php endif; ?>
<?php if (( $this->_aVars['aFeed']['type_id'] == 'pages_comment' || $this->_aVars['aFeed']['type_id'] == 'groups_comment' ) && $this->_aVars['aFeed']['parent_user_id'] != 0 && ( $this->_aVars['aFeed']['user_id'] == Phpfox ::getUserId() || ( defined ( 'PHPFOX_PAGES_ITEM_TYPE' ) && Phpfox ::getService(PHPFOX_PAGES_ITEM_TYPE)->isAdmin($this->_aVars['aFeed']['parent_user_id'])))): ?>
        <li class=""><a href="#" class="" onclick="tb_show('<?php echo _p('edit_your_post'); ?>', $.ajaxBox('feed.editUserStatus', 'height=400&amp;width=600&amp;id=<?php echo $this->_aVars['aFeed']['feed_id']; ?>&amp;module=pages')); return false;">
            <span class="ico ico-pencilline-o"></span> <?php echo _p('edit'); ?></a>
        </li>
<?php endif; ?>
<?php if ($this->_aVars['aFeed']['type_id'] == 'feed_comment' && ( $this->_aVars['aFeed']['user_id'] == Phpfox ::getUserId() || Phpfox ::isAdmin())): ?>
        <li class=""><a href="#" class="" onclick="tb_show('<?php echo _p('edit_your_post'); ?>', $.ajaxBox('feed.editUserStatus', 'height=400&amp;width=600&amp;id=<?php echo $this->_aVars['aFeed']['feed_id']; ?>')); return false;">
            <span class="ico ico-pencilline-o"></span> <?php echo _p('edit'); ?></a>
        </li>
<?php endif; ?>
<?php if ($this->_aVars['aFeed']['type_id'] == 'event_comment' && $this->_aVars['aFeed']['user_id'] == Phpfox ::getUserId()): ?>
        <li class=""><a href="#" class="" onclick="tb_show('<?php echo _p('edit_your_post'); ?>', $.ajaxBox('feed.editUserStatus', 'height=400&amp;width=600&amp;id=<?php echo $this->_aVars['aFeed']['feed_id']; ?>&amp;module=event')); return false;">
            <span class="ico ico-pencilline-o"></span> <?php echo _p('edit'); ?></a>
        </li>
<?php endif; ?>
<?php if (Phpfox ::isModule('report') && $this->_aVars['aFeed']['type_id'] == 'user_status' && $this->_aVars['aFeed']['user_id'] != Phpfox ::getUserId() && ! User_Service_Block_Block ::instance()->isBlocked(null, $this->_aVars['aFeed']['user_id'] )): ?>
        <li class=""><a href="#?call=report.add&height=210&width=400&type=user_status&id=<?php echo $this->_aVars['aFeed']['item_id']; ?>" class="inlinePopup" title="<?php echo _p('report'); ?>">
                <span class="ico ico-warning-o"></span> <?php echo _p('report'); ?></a></li>
<?php endif; ?>

<?php $this->assign('empty', true); ?>
	
<?php if (Phpfox ::isUser() && Phpfox ::isModule('report') && isset ( $this->_aVars['sFeedType'] ) && isset ( $this->_aVars['aFeed']['report_module'] ) && ! User_Service_Block_Block ::instance()->isBlocked(null, $this->_aVars['aFeed']['user_id'] )): ?>
<?php $this->assign('empty', false); ?>
		<li><a href="#?call=report.add&amp;height=100&amp;width=400&amp;type=<?php echo $this->_aVars['aFeed']['report_module']; ?>&amp;id=<?php echo $this->_aVars['aFeed']['item_id']; ?>" class="inlinePopup activity_feed_report" title="<?php echo $this->_aVars['aFeed']['report_phrase']; ?>">
				<span class="ico ico-warning-o"></span>
<?php echo _p('report'); ?></a>
		</li>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('feed.template_block_entry_2')) ? eval($sPlugin) : false); ?>

<?php if (! empty ( $this->_aVars['feed_entry_be'] ) && ( defined ( 'PHPFOX_FEED_CAN_DELETE' ) || ( Phpfox ::getUserParam('feed.can_delete_own_feed') && $this->_aVars['aFeed']['user_id'] == Phpfox ::getUserId()) || Phpfox ::getUserParam('feed.can_delete_other_feeds') || ( ! defined ( 'PHPFOX_IS_PAGES_VIEW' ) && ! empty ( $this->_aVars['aFeed']['parent_user_id'] ) && ( int ) $this->_aVars['aFeed']['parent_user_id'] === Phpfox ::getUserId()))): ?>
        <li class="item-delete"><a href="#" class="" onclick="$Core.jsConfirm({message: '<?php echo _p('are_you_sure_you_want_to_delete_this_feed_permanently'); ?>'}, function(){$.ajaxCall('feed.delete', 'height=400&amp;width=600&amp;TB_inline=1&amp;call=feed.delete&amp;type=delete&amp;id=<?php echo $this->_aVars['aFeed']['feed_id'];  if (isset ( $this->_aVars['aFeedCallback']['module'] )): ?>&amp;module=<?php echo $this->_aVars['aFeedCallback']['module']; ?>&amp;item=<?php echo $this->_aVars['aFeedCallback']['item_id'];  endif; ?>&amp;type_id=<?php echo $this->_aVars['aFeed']['type_id']; ?>')}, function(){}); return false;">
                <span class="ico ico-trash-alt-o"></span> <?php echo _p('delete'); ?></a></li>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('core.template_block_comment_border_new')) ? eval($sPlugin) : false); ?>

</ul>
<?php if ($this->_aVars['empty']): ?>
<input type="hidden" class="comment_mini_link_like_empty" value="1" />
<?php endif; ?>
