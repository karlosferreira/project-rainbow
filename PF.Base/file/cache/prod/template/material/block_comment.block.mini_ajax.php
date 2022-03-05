<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 5, 2022, 2:42 am */ ?>
<?php



?>

<?php if (! empty ( $this->_aVars['aComment']['is_hidden'] ) && ! empty ( $this->_aVars['aComment']['hide_this'] )): ?>
    <div class="js_mini_feed_comment js_hidden_comment_dot comment-item <?php if ($this->_aVars['aComment']['parent_id'] > 0 && empty ( $this->_aVars['bIsViewingComments'] )): ?>comment-item-reply<?php endif; ?> <?php if (isset ( $this->_aVars['aComment']['children'] ) && isset ( $this->_aVars['aComment']['children']['comments'] ) && count ( $this->_aVars['aComment']['children']['comments'] )): ?>has-replies<?php endif; ?> <?php if (! empty ( $this->_aVars['aComment']['is_loaded_more'] )): ?>reply_is_loadmore<?php endif; ?> <?php if (! empty ( $this->_aVars['aComment']['is_added_more'] )): ?>is_added_more<?php endif; ?>">
        <div class="item-outer">
            <div class="item-inner t_center">
                <a href="#" onclick="return $Core.Comment.showHiddenComments(this);" class="js_hover_title" title="" data-hidden-ids="<?php echo $this->_aVars['aComment']['hide_ids']; ?>"><i class="ico ico-dottedmore"></i><span class="js_hover_info"><?php echo _p('total_hidden', array('total' => $this->_aVars['aComment']['total_hidden'])); ?></span></a>
            </div>
        </div>
    </div>
<?php endif; ?>
<div id="js_comment_<?php echo $this->_aVars['aComment']['comment_id']; ?>" class="js_mini_feed_comment comment-item <?php if ($this->_aVars['aComment']['parent_id'] > 0): ?>comment-item-reply<?php endif; ?> js_mini_comment_item_<?php echo $this->_aVars['aComment']['item_id']; ?> <?php if (isset ( $this->_aVars['aComment']['children'] ) && isset ( $this->_aVars['aComment']['children']['comments'] ) && count ( $this->_aVars['aComment']['children']['comments'] )): ?>has-replies<?php endif; ?> <?php if (! empty ( $this->_aVars['aComment']['is_hidden'] )): ?>hide view-hidden<?php endif; ?> <?php if (! empty ( $this->_aVars['aComment']['is_loaded_more'] )): ?>reply_is_loadmore<?php endif; ?> <?php if (! empty ( $this->_aVars['aComment']['is_added_more'] )): ?>is_added_more<?php endif; ?>">
<?php if (( ( ( Phpfox ::getUserParam('comment.delete_own_comment') && Phpfox ::getUserId() == $this->_aVars['aComment']['user_id'] ) || Phpfox ::getUserParam('comment.delete_user_comment') || ( defined ( 'PHPFOX_IS_USER_PROFILE' ) && isset ( $this->_aVars['aUser']['user_id'] ) && $this->_aVars['aUser']['user_id'] == Phpfox ::getUserId() && Phpfox ::getUserParam('comment.can_delete_comments_posted_on_own_profile')) || ( defined ( 'PHPFOX_IS_PAGES_VIEW' ) && defined ( 'PHPFOX_PAGES_ITEM_TYPE' ) && Phpfox ::getService(PHPFOX_PAGES_ITEM_TYPE)->isAdmin('' . $this->_aVars['aPage']['page_id'] . '' ) ) ) || ( Phpfox ::getUserParam('comment.can_delete_comment_on_own_item') && isset ( $this->_aVars['aFeed'] ) && isset ( $this->_aVars['aFeed']['feed_link'] ) && $this->_aVars['aFeed']['user_id'] == Phpfox ::getUserId()) || ( ( Phpfox ::getUserParam('comment.edit_own_comment') && Phpfox ::getUserId() == $this->_aVars['aComment']['user_id'] ) || Phpfox ::getUserParam('comment.edit_user_comment')) || ( Phpfox ::isUser() && $this->_aVars['aComment']['user_id'] != Phpfox ::getUserId() && ( ! isset ( $this->_aVars['aFeed'] ) || $this->_aVars['aFeed']['user_id'] != Phpfox ::getUserId()))) && ( $this->_aVars['aComment']['view_id'] != 1 || ( $this->_aVars['aComment']['view_id'] == 1 && Phpfox ::getUserParam('comment.can_moderate_comments')))): ?>
    <div class="item-comment-options <?php if (! empty ( $this->_aVars['aComment']['is_hidden'] )): ?>hide<?php endif; ?>" id="js_comment_options_<?php echo $this->_aVars['aComment']['comment_id']; ?>">
        <a role="button" data-toggle="dropdown" href="#" class="item-options">
            <span class="ico ico-dottedmore-o"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
<?php if (Phpfox ::isUser() && $this->_aVars['aComment']['user_id'] != Phpfox ::getUserId() && ( ! isset ( $this->_aVars['aFeed'] ) || $this->_aVars['aFeed']['user_id'] != Phpfox ::getUserId())): ?>
                <li>
                    <a href="#" onclick="return $Core.Comment.hideComment(this);" data-parent-id="<?php echo $this->_aVars['aComment']['parent_id']; ?>" data-comment-id="<?php echo $this->_aVars['aComment']['comment_id']; ?>" data-owner-id="<?php echo $this->_aVars['aComment']['user_id']; ?>" class="">
                        <span class="ico ico-eye-off-o mr-1"></span><?php echo _p('hide'); ?>
                    </a>
                </li>
<?php endif; ?>
<?php if (( ( Phpfox ::getUserParam('comment.edit_own_comment') && Phpfox ::getUserId() == $this->_aVars['aComment']['user_id'] ) || Phpfox ::getUserParam('comment.edit_user_comment')) && ( ( $this->_aVars['aComment']['view_id'] != 1 ) || ( $this->_aVars['aComment']['view_id'] == 1 && Phpfox ::getUserParam('comment.can_moderate_comments')))): ?>
                <li>
                    <a href="#" onclick="return $Core.Comment.getEditComment(<?php echo $this->_aVars['aComment']['comment_id']; ?>);">
                        <span class="ico ico-pencilline-o mr-1"></span><?php echo _p('edit'); ?>
                    </a>
                </li>
<?php endif; ?>
<?php if (Phpfox ::isModule('report') && Phpfox ::getUserParam('report.can_report_comments')): ?>
<?php if ($this->_aVars['aComment']['user_id'] != Phpfox ::getUserId() && ! Phpfox ::getService('user.block')->isBlocked(null, $this->_aVars['aComment']['user_id'] )): ?>
                    <li>
                        <a href="#?call=report.add&amp;height=210&amp;width=400&amp;type=comment&amp;id=<?php echo $this->_aVars['aComment']['comment_id']; ?>" class="inlinePopup" title="<?php echo _p('report_a_comment'); ?>">
                            <span class="ico ico-warning-o mr-1"></span><?php echo _p('report'); ?>
                        </a>
                    </li>
<?php endif; ?>
<?php endif; ?>
<?php if (( ( Phpfox ::getUserParam('comment.delete_own_comment') && Phpfox ::getUserId() == $this->_aVars['aComment']['user_id'] ) || Phpfox ::getUserParam('comment.delete_user_comment') || ( defined ( 'PHPFOX_IS_USER_PROFILE' ) && isset ( $this->_aVars['aUser']['user_id'] ) && $this->_aVars['aUser']['user_id'] == Phpfox ::getUserId() && Phpfox ::getUserParam('comment.can_delete_comments_posted_on_own_profile')) || ( defined ( 'PHPFOX_IS_PAGES_VIEW' ) && defined ( 'PHPFOX_PAGES_ITEM_TYPE' ) && Phpfox ::getService(PHPFOX_PAGES_ITEM_TYPE)->isAdmin('' . $this->_aVars['aPage']['page_id'] . '' ) ) ) && ( ( $this->_aVars['aComment']['view_id'] != 1 ) || ( $this->_aVars['aComment']['view_id'] == 1 && Phpfox ::getUserParam('comment.can_moderate_comments')))): ?>
                <li class="item-delete">
                    <a href="#" onclick="$Core.jsConfirm({message:'<?php echo _p('are_you_sure_you_want_to_delete_this_comment_permanently', array('phpfox_squote' => true)); ?>'}, function(){$.ajaxCall('comment.InlineDelete', 'type_id=<?php echo $this->_aVars['aComment']['type_id']; ?>&amp;comment_id=<?php echo $this->_aVars['aComment']['comment_id'];  if (defined ( 'PHPFOX_IS_THEATER_MODE' )): ?>&photo_theater=1<?php endif;  if (! $this->_aVars['aComment']['parent_id']): ?>&item_id=<?php echo $this->_aVars['aComment']['item_id'];  endif; ?>', 'GET');},function(){}, true); return false;">
                        <span class="ico ico-trash-alt-o  mr-1"></span><?php echo _p('delete'); ?>
                    </a>
                </li>
<?php elseif (Phpfox ::getUserParam('comment.can_delete_comment_on_own_item') && isset ( $this->_aVars['aFeed'] ) && isset ( $this->_aVars['aFeed']['feed_link'] ) && $this->_aVars['aFeed']['user_id'] == Phpfox ::getUserId()): ?>
                <li>
                    <a href="<?php echo $this->_aVars['aFeed']['feed_link']; ?>ownerdeletecmt_<?php echo $this->_aVars['aComment']['comment_id']; ?>/" class="sJsConfirm" data-message="<?php echo _p('are_you_sure_you_want_to_delete_this_comment_permanently'); ?>">
                        <span class="ico ico-trash-alt-o  mr-1"></span><?php echo _p('delete'); ?>
                    </a>
                </li>
<?php endif; ?>
        </ul>
    </div>
<?php endif; ?>
    <div class="item-outer">
        <div class="item-media">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aComment'],'suffix' => '_120_square','max_width' => 40,'max_height' => 40)); ?>
        </div>
        <div class="item-inner js_comment_text_inner_<?php echo $this->_aVars['aComment']['comment_id']; ?>">
            <div class="item-name"><?php echo '<span class="user_profile_link_span" id="js_user_name_link_' . Phpfox::getService('user')->getUserName($this->_aVars['aComment']['user_id'], $this->_aVars['aComment']['user_name']) . '">' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aComment']['user_id']) ? '' : '<a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile', array($this->_aVars['aComment']['user_name'], ((empty($this->_aVars['aComment']['user_name']) && isset($this->_aVars['aComment']['profile_page_id'])) ? $this->_aVars['aComment']['profile_page_id'] : null))) . '">') . '' . Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->clean(Phpfox::getService('user')->getCurrentName($this->_aVars['aComment']['user_id'], $this->_aVars['aComment']['full_name'])), 30, '...') . '' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aComment']['user_id']) ? '' : '</a>') . '</span>'; ?></div>
            <div class="item-comment-content js_comment_text_holder <?php if ($this->_aVars['aComment']['view_id'] == '1'): ?>row_moderate<?php endif; ?>">
                <?php
						Phpfox::getLib('template')->getBuiltFile('comment.block.mini-extra');
						?>
            </div>
            <div class="item-action comment_mini_action  <?php if (! empty ( $this->_aVars['aComment']['is_hidden'] )): ?>hide<?php endif; ?>" id="js_comment_action_<?php echo $this->_aVars['aComment']['comment_id']; ?>">
                <div class="action-list">
<?php if ($this->_aVars['aComment']['view_id'] == '0'): ?>
<?php Phpfox::getBlock('like.link', array('like_type_id' => 'feed_mini','like_owner_id' => $this->_aVars['aComment']['user_id'],'like_item_id' => $this->_aVars['aComment']['comment_id'],'like_is_liked' => $this->_aVars['aComment']['is_liked'],'like_is_custom' => true)); ?>
                        <span class="total-like js_like_link_holder" <?php if ($this->_aVars['aComment']['total_like'] == 0): ?>style="display:none"<?php endif; ?>>
                        <span onclick="return $Core.box('like.browse', 450, 'type_id=feed_mini&amp;item_id=<?php echo $this->_aVars['aComment']['comment_id']; ?>');">
                                    <span class="js_like_link_holder_info">
<?php echo $this->_aVars['aComment']['total_like']; ?>
                                    </span>
                                </span>
                        </span>
<?php endif; ?>

<?php if (Phpfox ::getUserParam('comment.can_post_comments') && Phpfox ::getParam('comment.comment_is_threaded')): ?>
<?php if (( isset ( $this->_aVars['bForceNoReply'] ) && $this->_aVars['bForceNoReply'] ) || Phpfox ::getService('user.block')->isBlocked(null, $this->_aVars['aComment']['user_id'] ) || $this->_aVars['aComment']['view_id'] == '1' || ( defined ( 'PHPFOX_IS_PAGES_VIEW' ) && defined ( 'PHPFOX_PAGES_ITEM_TYPE' ) && PHPFOX_PAGES_ITEM_TYPE == 'groups' && ! Phpfox ::isAdmin() && ! Phpfox ::getService('groups')->isMember('' . $this->_aVars['aPage']['page_id'] . '' ) )): ?>
<?php else: ?>
                            <span class="item-reply"><a href="#" class="js_comment_feed_new_reply" rel="<?php if (! empty ( $this->_aVars['aComment']['parent_id'] )):  echo $this->_aVars['aComment']['parent_id'];  else:  echo $this->_aVars['aComment']['comment_id'];  endif; ?>" data-parent-id="<?php echo $this->_aVars['aComment']['parent_id']; ?>" data-owner-id="<?php echo $this->_aVars['aComment']['user_id']; ?>" data-current-user="<?php echo Phpfox::getUserId(); ?>" data-is-single="<?php if (! empty ( $this->_aVars['bIsViewingComments'] )): ?>1<?php else: ?>0<?php endif; ?>"><?php echo _p('reply'); ?></a></span>
<?php endif; ?>
<?php endif; ?>

<?php if (Phpfox ::getUserParam('comment.can_moderate_comments') && ( $this->_aVars['aComment']['view_id'] == '1' || $this->_aVars['aComment']['view_id'] == '9' )): ?>
                        <span class="js_comment_action">
                                <a href="#" onclick="$Core.jsConfirm({message:'<?php echo _p('are_you_sure_you_want_to_approve_this_comment', array('phpfox_squote' => true)); ?>'}, function(){$('.js_comment_text_inner_<?php echo $this->_aVars['aComment']['comment_id']; ?> .js_comment_text_holder').removeClass('row_moderate'); $(this).parent().siblings('.js_comment_action').remove(); $(this).parent().remove(); $.ajaxCall('comment.moderateSpam', 'id=<?php echo $this->_aVars['aComment']['comment_id']; ?>&amp;action=approve&amp;inacp=0');},function(){}); return false;"><?php echo _p('approve'); ?></a>
                            </span>
                        <span class="item-reply js_comment_action">
                                <a href="#" onclick="$Core.jsConfirm({message:'<?php echo _p('are_you_sure_you_want_to_deny_this_comment', array('phpfox_squote' => true)); ?>'}, function(){$('#js_comment_<?php echo $this->_aVars['aComment']['comment_id']; ?>').slideUp(); $.ajaxCall('comment.moderateSpam', 'id=<?php echo $this->_aVars['aComment']['comment_id']; ?>&amp;action=deny&amp;inacp=0');},function(){}); return false;"><?php echo _p('deny'); ?></a>
                            </span>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aComment']['extra_data'] ) && $this->_aVars['aComment']['extra_data']['extra_type'] == 'preview' && $this->_aVars['aComment']['user_id'] == Phpfox ::getUserId() && ! Phpfox ::getParam('core.disable_all_external_urls')): ?>
                        <span class="item-remove-preview" id="js_remove_preview_action_<?php echo $this->_aVars['aComment']['comment_id']; ?>">
                            <a href="#" onclick="$.ajaxCall('comment.removePreview','id=<?php echo $this->_aVars['aComment']['comment_id']; ?>','post'); return false;" class="comment-remove"><?php echo _p('remove_preview'); ?></a>
                        </span>
<?php endif; ?>
                    <span class="item-time"><?php if (isset ( $this->_aVars['aComment']['unix_time_stamp'] )):  echo Phpfox::getLib('date')->convertTime($this->_aVars['aComment']['unix_time_stamp'], 'core.global_update_time');  else:  if ($this->_aVars['aComment']['update_time'] > 0):  echo Phpfox::getLib('date')->convertTime($this->_aVars['aComment']['update_time'], 'core.global_update_time');  else:  echo Phpfox::getLib('date')->convertTime($this->_aVars['aComment']['time_stamp'], 'core.global_update_time');  endif;  endif; ?></span>

<?php if ($this->_aVars['aComment']['unix_update_time'] > 0): ?>
                        <span class="item-history" id="js_view_edit_history_action_<?php echo $this->_aVars['aComment']['comment_id']; ?>">
                            <a href="#" title="<?php echo _p('show_edit_history'); ?>" class="view-edit-history" onclick="tb_show('<?php echo _p('edit_history'); ?>', $.ajaxBox('comment.showEditHistory', 'id=<?php echo $this->_aVars['aComment']['comment_id']; ?>&height=400&width=600')); return false;"><?php echo _p('edited'); ?></a>
                        </span>
<?php endif; ?>
                </div>
            </div>
<?php if (! empty ( $this->_aVars['aComment']['is_hidden'] )): ?>
                <div class="item-action comment_mini_action " id="js_hide_comment_<?php echo $this->_aVars['aComment']['comment_id']; ?>">
                    <div class="action-list">
                            <span class="item-un-hide">
                                <a href="#" onclick="return $Core.Comment.hideComment(this, true);" data-comment-id="<?php echo $this->_aVars['aComment']['comment_id']; ?>"><?php echo _p('unhide'); ?></a>
                            </span>
                        <span class="item-time"><?php if (isset ( $this->_aVars['aComment']['unix_time_stamp'] )):  echo Phpfox::getLib('date')->convertTime($this->_aVars['aComment']['unix_time_stamp'], 'core.global_update_time');  else:  echo Phpfox::getLib('date')->convertTime($this->_aVars['aComment']['time_stamp'], 'core.global_update_time');  endif; ?></span>
                    </div>
                </div>
<?php endif; ?>
        </div>
    </div>
    <div class="comment-wrapper-reply">
        <div class="comment-container-reply">
            <div id="js_comment_form_holder_<?php echo $this->_aVars['aComment']['comment_id']; ?>" class="js_comment_form_holder"></div>
<?php if (Phpfox ::getParam('comment.thread_comment_total_display') !== null && $this->_aVars['aComment']['child_total'] && ( ( Phpfox ::getParam('comment.thread_comment_total_display') && isset ( $this->_aVars['aComment']['children']['comments'] ) && count ( $this->_aVars['aComment']['children']['comments'] ) && $this->_aVars['aComment']['child_total'] > Phpfox ::getParam('comment.thread_comment_total_display')) || ( ! setting ( 'comment.comment_show_replies_on_comment' ) && ! empty ( $this->_aVars['aComment']['last_reply'] ) ) )): ?>
                <?php
                $this->_aVars['iReplyShowTotal'] = count($this->_aVars['aComment']['children']['comments']);
                $this->_aVars['aLastReply'] = end($this->_aVars['aComment']['children']['comments']);
                ?>
                <div class="js_comment_view_more_reply_holder hide">
                    <div class="js_comment_view_more_reply_wrapper">
<?php if (! isset ( $this->_aVars['bIsViewingComments'] ) || ! $this->_aVars['bIsViewingComments']): ?>
                            <div class="comment-viewmore js_comment_view_more_replies_<?php echo $this->_aVars['aComment']['comment_id']; ?> js_comment_replies_viewmore_<?php echo $this->_aVars['aComment']['comment_id']; ?> <?php if (! setting ( 'comment.comment_show_replies_on_comment' )): ?>comment-hide-all<?php endif; ?>">
                                    <span class="js_link_href hide" data-href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('comment.replies', [], false, false); ?>?is_feed=<?php if (Phpfox ::getLib('module')->getFullControllerName() == 'core.index-member'): ?>1<?php else: ?>0<?php endif; ?>&comment_type_id=<?php echo $this->_aVars['aComment']['type_id']; ?>&item_id=<?php echo $this->_aVars['aComment']['item_id']; ?>&comment_id=<?php echo $this->_aVars['aComment']['comment_id']; ?>&time-stamp=<?php if (isset ( $this->_aVars['aLastReply']['time_stamp'] )):  echo $this->_aVars['aLastReply']['time_stamp'];  else: ?>0<?php endif; ?>&max-time=<?php echo PHPFOX_TIME; ?>&shown-total=<?php echo $this->_aVars['iReplyShowTotal']; ?>&total-replies=<?php echo $this->_aVars['aComment']['child_total']; ?>"
                                    ><?php if (! setting ( 'comment.comment_show_replies_on_comment' )):  echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aComment']['last_reply'],'suffix' => '_120_square','max_width' => 40,'max_height' => 40,'no_link' => true));  endif; ?>
<?php if (! setting ( 'comment.comment_show_replies_on_comment' )): ?>
<?php if ($this->_aVars['aComment']['child_total'] == 1): ?>
<?php echo _p('full_name_replied_one_reply', array('full_name' => $this->_aVars['aComment']['last_reply']['full_name'])); ?>
<?php else: ?>
<?php echo _p('full_name_replied_number_replies', array('full_name' => $this->_aVars['aComment']['last_reply']['full_name'],'number' => $this->_aVars['aComment']['child_total'])); ?>
<?php endif; ?>
<?php elseif ($this->_aVars['aComment']['child_total'] - Phpfox ::getParam('comment.thread_comment_total_display') == 1): ?>
<?php echo _p('view_one_more_reply'); ?>
<?php elseif (( $this->_aVars['iRemain'] = $this->_aVars['aComment']['child_total'] - Phpfox ::getParam('comment.thread_comment_total_display')) < 10): ?>
<?php echo _p('view_span_number_more_replies', array('number' => $this->_aVars['iRemain'])); ?>
<?php else: ?>
<?php echo _p('view_more_replies'); ?>
<?php endif; ?>
                                    </span>
<?php if (setting ( 'comment.comment_show_replies_on_comment' )): ?>
                                    <div class="item-number" >
<?php echo $this->_aVars['iReplyShowTotal']; ?>/<?php echo $this->_aVars['aComment']['child_total']; ?>
                                    </div>
<?php endif; ?>
                            </div>
<?php endif; ?>
                    </div>
                </div>
<?php endif; ?>
            <div id="js_comment_mini_child_holder_<?php echo $this->_aVars['aComment']['comment_id']; ?>" class="comment_mini_child_holder<?php if (isset ( $this->_aVars['aComment']['children'] ) && $this->_aVars['aComment']['children']['total'] > 0): ?> comment_mini_child_holder_padding<?php endif; ?>">
                <div id="js_comment_children_holder_<?php echo $this->_aVars['aComment']['comment_id']; ?>" class="comment_mini_child_content">
<?php if (isset ( $this->_aVars['aComment']['children'] ) && isset ( $this->_aVars['aComment']['children']['comments'] ) && count ( $this->_aVars['aComment']['children']['comments'] ) && setting ( 'comment.comment_show_replies_on_comment' )): ?>
<?php if (count((array)$this->_aVars['aComment']['children']['comments'])):  foreach ((array) $this->_aVars['aComment']['children']['comments'] as $this->_aVars['aCommentChilded']): ?>
<?php Phpfox::getBlock('comment.mini', array('comment_custom' => $this->_aVars['aCommentChilded'])); ?>
<?php endforeach; endif; ?>
<?php else: ?>
                        <div id="js_feed_like_holder_<?php echo $this->_aVars['aComment']['type_id']; ?>_<?php echo $this->_aVars['aComment']['item_id']; ?>"></div>
<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php if (! empty ( $this->_aVars['bIsAjaxAdd'] ) && ( ! isset ( $this->_aVars['iParentId'] ) || ! $this->_aVars['iParentId'] )): ?>
        <script>
          $Core.Comment.updateCommentCounter('<?php echo $this->_aVars['aComment']['type_id']; ?>',<?php echo $this->_aVars['aComment']['item_id']; ?>, '+');
        </script>
<?php elseif (! empty ( $this->_aVars['bIsAjaxAdd'] ) && $this->_aVars['iParentId']): ?>
        <script>
          $Core.Comment.updateReplyCounter(<?php echo $this->_aVars['iParentId']; ?>, '+');
        </script>
<?php endif; ?>
</div>

