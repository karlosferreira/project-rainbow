<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 11:53 pm */ ?>
<?php

?>

<?php if (( isset ( $this->_aVars['showOnlyComments'] ) )): ?>
<?php if (Phpfox ::isModule('comment') && ( isset ( $this->_aVars['aFeed']['comments'] ) && count ( $this->_aVars['aFeed']['comments'] ) )): ?>
<?php if ($this->_aVars['iShownTotal'] < $this->_aVars['aFeed']['total_comment']): ?>
            <div class="core_comment_viemore_holder">
                <div class="comment-viewmore" id="js_feed_comment_pager_<?php echo $this->_aVars['aFeed']['comment_type_id']; ?>_<?php echo $this->_aVars['aFeed']['item_id']; ?>">
                    <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('comment.comments', [], false, false); ?>?type=<?php echo $this->_aVars['aFeed']['comment_type_id']; ?>&id=<?php echo $this->_aVars['aFeed']['item_id']; ?>&page=1<?php if (defined ( 'PHPFOX_FEED_STREAM_MODE' )): ?>&stream-mode=1<?php endif; ?>&time-stamp=<?php echo $this->_aVars['aFeed']['comments']['0']['time_stamp']; ?>&shown-total=<?php echo $this->_aVars['iShownTotal']; ?>&total-comment=<?php echo $this->_aVars['aFeed']['total_comment']; ?>" class="item-viewmore ajax" onclick="$(this).addClass('active');">
<?php if ($this->_aVars['aFeed']['total_comment'] - $this->_aVars['iShownTotal'] == 1): ?>
<?php echo _p('view_one_more_comment'); ?>
<?php elseif (( $this->_aVars['iRemain'] = $this->_aVars['aFeed']['total_comment'] - $this->_aVars['iShownTotal'] ) < 10): ?>
<?php echo _p('view_number_more_comments', array('number' => $this->_aVars['iRemain'])); ?>
<?php else: ?>
<?php echo _p('view_previous_comments'); ?>
<?php endif; ?>
                    </a>
                    <div class="item-number"><?php echo $this->_aVars['iShownTotal']; ?>/<?php echo $this->_aVars['aFeed']['total_comment']; ?></div>
                </div>
            </div>
<?php endif; ?>
<?php if (count((array)$this->_aVars['aFeed']['comments'])):  $this->_aPhpfoxVars['iteration']['comments'] = 0;  foreach ((array) $this->_aVars['aFeed']['comments'] as $this->_aVars['aComment']):  $this->_aPhpfoxVars['iteration']['comments']++; ?>

            <?php
						Phpfox::getLib('template')->getBuiltFile('comment.block.mini');
						?>
<?php endforeach; endif; ?>
<?php endif;  else: ?>
<?php if (isset ( $this->_aVars['bIsViewingComments'] ) && $this->_aVars['bIsViewingComments']): ?>
        <div id="comment-view"><a name="#comment-view"></a></div>
        <div class="message js_feed_comment_border">
<?php echo _p('viewing_a_single_comment'); ?>
            <a href="<?php echo $this->_aVars['aFeed']['feed_link']; ?>"><?php echo _p('view_all_comments'); ?></a>
        </div>
        <script>
            <?php echo '
            $Ready(function() {
                var c = $(\'#comment-view\');
                if (c.length && !c.hasClass(\'completed\') && c.is(\':visible\')) {
                    c.addClass(\'completed\');
                    $("html, body").animate({ scrollTop: (c.offset().top - 80) });
                }
            });
            '; ?>

        </script>
<?php endif; ?>

<?php if (isset ( $this->_aVars['sFeedType'] )): ?>
    <div class="js_parent_feed_entry parent_item_feed">
<?php endif; ?>
        <div class="js_feed_comment_border comment-content" <?php if (isset ( $this->_aVars['sFeedType'] ) && $this->_aVars['sFeedType'] == 'view'): ?>id="js_item_feed_<?php echo $this->_aVars['aFeed']['feed_id']; ?>"<?php endif; ?>>
<?php (($sPlugin = Phpfox_Plugin::get('feed.template_block_comment_border')) ? eval($sPlugin) : false); ?>
            <div id="js_feed_like_holder_<?php if (isset ( $this->_aVars['aFeed']['like_type_id'] ) && ! isset ( $this->_aVars['aFeed']['is_app'] )):  echo $this->_aVars['aFeed']['like_type_id'];  else:  echo $this->_aVars['aFeed']['comment_type_id'];  endif; ?>_<?php if (isset ( $this->_aVars['aFeed']['like_item_id'] ) && ! isset ( $this->_aVars['aFeed']['is_app'] )):  echo $this->_aVars['aFeed']['like_item_id'];  else:  echo $this->_aVars['aFeed']['item_id'];  endif; ?>" class="comment_mini_content_holder<?php if (( isset ( $this->_aVars['aFeed']['is_app'] ) && $this->_aVars['aFeed']['is_app'] && isset ( $this->_aVars['aFeed']['app_object'] ) )): ?> _is_app<?php endif; ?>"<?php if (( isset ( $this->_aVars['aFeed']['is_app'] ) && $this->_aVars['aFeed']['is_app'] && isset ( $this->_aVars['aFeed']['app_object'] ) )): ?> data-app-id="<?php echo $this->_aVars['aFeed']['app_object']; ?>"<?php endif; ?>>
                <div class="comment_mini_content_holder_icon"<?php if (isset ( $this->_aVars['aFeed']['marks'] ) || ( isset ( $this->_aVars['aFeed']['likes'] ) && is_array ( $this->_aVars['aFeed']['likes'] ) ) || ( isset ( $this->_aVars['aFeed']['total_comment'] ) && $this->_aVars['aFeed']['total_comment'] > 0 )):  else:  endif; ?>></div>
                <div class="comment_mini_content_border">
<?php if (! isset ( $this->_aVars['aFeed']['feed_mini'] )): ?>
                        <div class="feed-options-holder item-options-holder hide" data-component="feed-options">
                            <a role="button" data-toggle="dropdown" href="#" class="feed-options item-options">
                                <span class="ico ico-dottedmore-o"></span>
                            </a>
                            <?php
						Phpfox::getLib('template')->getBuiltFile('feed.block.link');
						?>
                        </div>
<?php endif; ?>

                    <div class="comment-mini-content-commands">
                        <div class="button-like-share-block <?php if (isset ( $this->_aVars['aFeed']['total_action'] )): ?>comment-has-<?php echo $this->_aVars['aFeed']['total_action']; ?>-actions<?php endif; ?>">
<?php if ($this->_aVars['aFeed']['can_like']): ?>
                                <div class="feed-like-link">
<?php if (isset ( $this->_aVars['aFeed']['like_item_id'] )): ?>
<?php Phpfox::getBlock('like.link', array('like_type_id' => $this->_aVars['aFeed']['like_type_id'],'like_item_id' => $this->_aVars['aFeed']['like_item_id'],'like_is_liked' => $this->_aVars['aFeed']['feed_is_liked'])); ?>
<?php else: ?>
<?php Phpfox::getBlock('like.link', array('like_type_id' => $this->_aVars['aFeed']['like_type_id'],'like_item_id' => $this->_aVars['aFeed']['item_id'],'like_is_liked' => $this->_aVars['aFeed']['feed_is_liked'])); ?>
<?php endif; ?>
                                    <span class="counter" onclick="return $Core.box('like.browse', 450, 'type_id=<?php if (isset ( $this->_aVars['aFeed']['like_type_id'] )):  echo $this->_aVars['aFeed']['like_type_id'];  else:  echo $this->_aVars['aFeed']['comment_type_id'];  endif; ?>&amp;item_id=<?php echo $this->_aVars['aFeed']['item_id']; ?>');"><?php if (! empty ( $this->_aVars['aFeed']['feed_total_like'] )):  echo $this->_aVars['aFeed']['feed_total_like'];  endif; ?></span>
                                </div>
<?php endif; ?>
<?php if (( ! isset ( $this->_aVars['sFeedType'] ) || $this->_aVars['sFeedType'] != 'mini' ) && $this->_aVars['aFeed']['can_comment']): ?>
                                <div class="feed-comment-link">
                                    <a href="#" onclick="$('#js_feed_comment_form_textarea_<?php echo $this->_aVars['aFeed']['feed_id']; ?>').focus();return false;"><span class="ico ico-comment-o"></span></a>
                                    <span class="counter"><?php if (! empty ( $this->_aVars['aFeed']['total_comment'] )):  echo $this->_aVars['aFeed']['total_comment'];  endif; ?></span>
                                </div>
<?php endif; ?>

<?php if ($this->_aVars['aFeed']['can_share']): ?>
                                <div class="feed-comment-share-holder">
<?php $this->assign('empty', false); ?>
<?php if ($this->_aVars['aFeed']['privacy'] == '0' || $this->_aVars['aFeed']['privacy'] == '1' || $this->_aVars['aFeed']['privacy'] == '2'): ?>
<?php if (isset ( $this->_aVars['aFeed']['share_type_id'] )): ?>
<?php Phpfox::getBlock('share.link', array('type' => 'feed','display' => 'menu_btn','url' => $this->_aVars['aFeed']['feed_link'],'title' => $this->_aVars['aFeed']['feed_title'],'sharefeedid' => $this->_aVars['aFeed']['item_id'],'sharemodule' => $this->_aVars['aFeed']['share_type_id'])); ?>
<?php else: ?>
<?php Phpfox::getBlock('share.link', array('type' => 'feed','display' => 'menu_btn','url' => $this->_aVars['aFeed']['feed_link'],'title' => $this->_aVars['aFeed']['feed_title'],'sharefeedid' => $this->_aVars['aFeed']['item_id'],'sharemodule' => $this->_aVars['aFeed']['type_id'])); ?>
<?php endif; ?>
<?php else: ?>
<?php Phpfox::getBlock('share.link', array('type' => 'feed','display' => 'menu_btn','url' => $this->_aVars['aFeed']['feed_link'],'title' => $this->_aVars['aFeed']['feed_title'])); ?>
<?php endif; ?>
                                    <span class="counter"><?php if (! empty ( $this->_aVars['aFeed']['total_share'] )):  echo $this->_aVars['aFeed']['total_share'];  endif; ?></span>
                                </div>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('feed.template_block_comment_commands_1')) ? eval($sPlugin) : false); ?>
                        </div>

<?php if (isset ( $this->_aVars['aFeed']['like_type_id'] ) && ! ( isset ( $this->_aVars['aFeed']['disable_like_function'] ) && $this->_aVars['aFeed']['disable_like_function'] )): ?>
                        <div class="js_comment_like_holder" id="js_feed_like_holder_<?php echo $this->_aVars['aFeed']['comment_type_id']; ?>_<?php echo $this->_aVars['aFeed']['item_id']; ?>">
                            <div id="js_like_body_<?php echo $this->_aVars['aFeed']['feed_id']; ?>">
                                <?php
						Phpfox::getLib('template')->getBuiltFile('like.block.display');
						?>
                            </div>
                        </div>
<?php endif; ?>

<?php (($sPlugin = Phpfox_Plugin::get('feed.template_block_comment_commands_2')) ? eval($sPlugin) : false); ?>

<?php (($sPlugin = Phpfox_Plugin::get('feed.template_block_comment_commands_3')) ? eval($sPlugin) : false); ?>
                    </div>
                    <div class="comment-wrapper"> <!--comment-wrapper-->
<?php if (Phpfox ::isModule('comment')): ?>
<?php if (! isset ( $this->_aVars['bIsViewingComments'] ) || ! $this->_aVars['bIsViewingComments']): ?>
                                <div id="js_feed_comment_post_<?php echo $this->_aVars['aFeed']['feed_id']; ?>" class="js_feed_comment_view_more_holder">
<?php if (isset ( $this->_aVars['aFeed']['comments'] ) && $this->_aVars['iShownTotal'] = count ( $this->_aVars['aFeed']['comments'] )): ?>
<?php if (Phpfox ::getParam('comment.comment_page_limit') != null && Phpfox ::isModule('comment') && $this->_aVars['aFeed']['total_comment'] > Phpfox ::getParam('comment.comment_page_limit')): ?>
                                                <div class="comment-viewmore" id="js_feed_comment_pager_<?php echo $this->_aVars['aFeed']['comment_type_id']; ?>_<?php echo $this->_aVars['aFeed']['item_id']; ?>">
                                                    <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('comment.comments', [], false, false); ?>?type=<?php echo $this->_aVars['aFeed']['comment_type_id']; ?>&id=<?php echo $this->_aVars['aFeed']['item_id']; ?>&page=1<?php if (defined ( 'PHPFOX_FEED_STREAM_MODE' )): ?>&stream-mode=1<?php endif; ?>&time-stamp=<?php echo $this->_aVars['aFeed']['comments']['0']['time_stamp']; ?>&shown-total=<?php echo $this->_aVars['iShownTotal']; ?>&total-comment=<?php echo $this->_aVars['aFeed']['total_comment']; ?>" class="ajax item-viewmore"  onclick="$(this).addClass('active');">
<?php if ($this->_aVars['aFeed']['total_comment'] - Phpfox ::getParam('comment.comment_page_limit') == 1): ?>
<?php echo _p('view_one_more_comment'); ?>
<?php elseif (( $this->_aVars['iRemain'] = $this->_aVars['aFeed']['total_comment'] - Phpfox ::getParam('comment.comment_page_limit')) < 10): ?>
<?php echo _p('view_number_more_comments', array('number' => $this->_aVars['iRemain'])); ?>
<?php else: ?>
<?php echo _p('view_previous_comments'); ?>
<?php endif; ?>
                                                    </a>
                                                    <div class="item-number"><?php echo $this->_aVars['iShownTotal']; ?>/<?php echo $this->_aVars['aFeed']['total_comment']; ?></div>
                                                </div>
<?php endif; ?>
                                            <div class="comment-container">
                                                <div id="js_feed_comment_view_more_<?php echo $this->_aVars['aFeed']['feed_id']; ?>"<?php if (isset ( $this->_aVars['sFeedType'] ) && $this->_aVars['sFeedType'] == 'view'): ?>class="js_comment_items"<?php else: ?> class="js_comment_limit js_comment_items" data-limit="<?php if (( $this->_aVars['thisLimit'] = setting ( 'comment.comment_page_limit' ) )):  echo $this->_aVars['thisLimit'];  endif; ?>"<?php endif; ?>>
<?php if (count((array)$this->_aVars['aFeed']['comments'])):  $this->_aPhpfoxVars['iteration']['comments'] = 0;  foreach ((array) $this->_aVars['aFeed']['comments'] as $this->_aVars['aComment']):  $this->_aPhpfoxVars['iteration']['comments']++; ?>

                                                        <?php
						Phpfox::getLib('template')->getBuiltFile('comment.block.mini');
						?>
<?php endforeach; endif; ?>
                                                </div><!-- // #js_feed_comment_view_more_<?php echo $this->_aVars['aFeed']['feed_id']; ?> -->
                                            </div>

<?php else: ?>
                                        <div class="comment-container">
                                            <div id="js_feed_comment_view_more_<?php echo $this->_aVars['aFeed']['feed_id']; ?>"></div><!-- // #js_feed_comment_view_more_<?php echo $this->_aVars['aFeed']['feed_id']; ?> -->
                                        </div>
<?php endif; ?>
                                </div><!-- // #js_feed_comment_post_<?php echo $this->_aVars['aFeed']['feed_id']; ?> -->
<?php else: ?>
<?php if (isset ( $this->_aVars['aFeed']['comments']['0'] ) && ( $this->_aVars['aFeed']['comments']['0']['view_id'] == '0' || ( Phpfox ::isUser() && ( Phpfox ::getUserParam('comment.can_moderate_comments') || $this->_aVars['aFeed']['comments']['0']['user_id'] == Phpfox ::getUserId())))): ?>
                                    <div class="comment-container">
                                        <div id="js_feed_comment_view_more_<?php echo $this->_aVars['aFeed']['feed_id']; ?>"<?php if (isset ( $this->_aVars['sFeedType'] ) && $this->_aVars['sFeedType'] == 'view'): ?>class="js_comment_items"<?php else: ?> class="js_comment_limit js_comment_items" data-limit="<?php if (( $this->_aVars['thisLimit'] = setting ( 'comment.comment_page_limit' ) )):  echo $this->_aVars['thisLimit'];  endif; ?>"<?php endif; ?>>
<?php if (count((array)$this->_aVars['aFeed']['comments'])):  $this->_aPhpfoxVars['iteration']['comments'] = 0;  foreach ((array) $this->_aVars['aFeed']['comments'] as $this->_aVars['aComment']):  $this->_aPhpfoxVars['iteration']['comments']++; ?>

                                                <?php
						Phpfox::getLib('template')->getBuiltFile('comment.block.mini-single');
						?>
<?php endforeach; endif; ?>
                                        </div><!-- // #js_feed_comment_view_more_<?php echo $this->_aVars['aFeed']['feed_id']; ?> -->
                                    </div>
<?php else: ?>
                                    <div class="comment-container">
                                        <div class="comment-item">
                                            <div class="error_message" style="margin-bottom: 0;">
<?php echo _p('you_do_not_have_permission_to_view_this_comment'); ?>
                                            </div>
                                        </div>
                                    </div>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>

<?php if (isset ( $this->_aVars['sFeedType'] ) && $this->_aVars['sFeedType'] == 'mini'): ?>

<?php else: ?>
<?php if (Phpfox ::isModule('comment') && isset ( $this->_aVars['aFeed']['comment_type_id'] ) && Phpfox ::getUserParam('comment.can_post_comments') && Phpfox ::isUser() && $this->_aVars['aFeed']['can_post_comment'] && ( ! isset ( $this->_aVars['bIsGroupMember'] ) || $this->_aVars['bIsGroupMember'] )): ?>
<?php if (Phpfox ::isModule('captcha') && Phpfox ::getUserParam('captcha.captcha_on_comment')): ?>
<?php Phpfox::getBlock('captcha.form', array('sType' => 'comment','captcha_popup' => true)); ?>
<?php endif; ?>
                                <div class="comment-footer js_feed_comment_form_holder">
                                    <div class="comment-box-container">
                                        <div class="js_feed_core_comment_form" <?php if (isset ( $this->_aVars['sFeedType'] ) && $this->_aVars['sFeedType'] == 'view'): ?> id="js_feed_comment_form_<?php echo $this->_aVars['aFeed']['feed_id']; ?>"<?php endif; ?>>
                                            <div class="js_app_comment_feed_textarea_browse"></div>
                                            <div class="<?php if (isset ( $this->_aVars['sFeedType'] ) && $this->_aVars['sFeedType'] == 'view'): ?> feed_item_view<?php endif; ?>">
                                                <form method="post" action="#" class="js_app_comment_feed_form form" id="js_app_comment_feed_form_<?php echo $this->_aVars['aFeed']['feed_id']; ?>">
<?php if (( isset ( $this->_aVars['aFeed']['is_app'] ) && $this->_aVars['aFeed']['is_app'] && isset ( $this->_aVars['aFeed']['app_object'] ) )): ?>
                                                        <input type="hidden" name="val[app_object]" value="<?php echo $this->_aVars['aFeed']['app_object']; ?>" />
<?php endif; ?>
                                                    <input type="hidden" name="val[table_prefix]" value="<?php if (isset ( $this->_aVars['aFeed']['feed_table_prefix'] )):  echo $this->_aVars['aFeed']['feed_table_prefix'];  endif; ?>" />
                                                    <input type="hidden" name="val[type]" value="<?php echo $this->_aVars['aFeed']['comment_type_id']; ?>" />
                                                    <input type="hidden" name="val[item_id]" value="<?php echo $this->_aVars['aFeed']['item_id']; ?>" />
                                                    <input type="hidden" name="val[parent_id]" value="0" class="js_feed_comment_parent_id" />
                                                    <input type="hidden" name="val[is_single]" value="<?php if (! empty ( $this->_aVars['bIsViewingComments'] ) && isset ( $this->_aVars['aFeed']['comments']['0'] ) && $this->_aVars['aFeed']['comments']['0']['parent_id']): ?>1<?php else: ?>0<?php endif; ?>" class="js_feed_comment_is_single" />
                                                    <input type="hidden" name="val[photo_id]" value="0" class="js_feed_comment_photo_id" />
                                                    <input type="hidden" name="val[sticker_id]" value="0" class="js_feed_comment_sticker_id" />
                                                    <input type="hidden" name="val[is_via_feed]" value="<?php echo $this->_aVars['aFeed']['feed_id']; ?>" class="js_feed_comment_feed_id"/>
<?php if (defined ( 'PHPFOX_IS_THEATER_MODE' )): ?>
                                                        <input type="hidden" name="ajax_post_photo_theater" value="1" />
<?php endif; ?>
                                                    <div class="">
                                                        <input type="hidden" name="val[default_feed_value]" value="<?php echo _p('write_a_comment'); ?>" />
                                                        <div class="js_comment_feed_value"><?php echo _p('write_a_comment'); ?></div>
                                                        <div class="item-outer">
                                                            <div class="item-media">
<?php if (Phpfox ::isUser()): ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aGlobalUser'],'suffix' => '_120_square','max_width' => '40','max_height' => '40')); ?>
<?php endif; ?>
                                                            </div>
                                                            <div class="item-inner">
                                                                <div class="comment-box js_comment_box">
                                                                    <div class="item-edit-content">
                                                                        <div class="item-box-input">
                                                                        <div ondragover="return false;" ondrop="return false;" class="p-comment-pseudo-firefox-prevent-drop" style="position: absolute;top: -16px;left: 0;right: 0;bottom: -48px;z-index: 0;display: none;"></div>
                                                                            <textarea rows="1" name="val[text]"  id="js_feed_comment_form_textarea_<?php echo $this->_aVars['aFeed']['feed_id']; ?>" class="form-control comment-textarea-edit js_app_comment_feed_textarea" placeholder="<?php echo _p('write_a_comment'); ?>" autocomplete="off" style="display: none"></textarea>
                                                                            <div contenteditable="true" class="form-control contenteditable comment-textarea-edit js_app_comment_feed_textarea" data-text="<?php echo _p('write_a_comment'); ?>" ondragover="return false;" ondrop="return false;"></div>
                                                                            <button class="mobile-sent-btn" style="display: none;"><span class="ico ico-paperplane"></span></button>
                                                                            <div class="js_feed_comment_process_form"><i class="fa fa-spin fa-circle-o-notch"></i></div>
                                                                            <div class="comment-group-icon dropup js_comment_group_icon">
<?php if (Phpfox ::getParam('comment.comment_enable_photo')): ?>
                                                                                    <div title="" class="item-icon icon-photo js_comment_attach_photo js_hover_title" data-feed-id="<?php echo $this->_aVars['aFeed']['feed_id']; ?>">
                                                                                        <i class="ico ico-camera-o"></i>
                                                                                        <span class="js_hover_info"><?php echo _p('attach_a_photo'); ?></span>
                                                                                        <input type="file" style="display: none;" class="js_attach_photo_input_file" accept="image/*" data-feed-id="<?php echo $this->_aVars['aFeed']['feed_id']; ?>">
                                                                                    </div>
<?php endif; ?>
<?php if (! empty ( Phpfox ::getService('comment.stickers')->countActiveStickerSet()) && Phpfox ::getParam('comment.comment_enable_sticker')): ?>
                                                                                    <div title="" class="item-icon icon-sticker js_comment_attach_sticker js_comment_icon_sticker_<?php echo $this->_aVars['aFeed']['feed_id']; ?> js_hover_title" data-sticker_next="0" data-feed-id="<?php echo $this->_aVars['aFeed']['feed_id']; ?>">
                                                                                        <i class="ico">
                                                                                            <svg class="sticker-o" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px" viewBox="0 0 24 24" style="enable-background:new 0 0 24 24;" xml:space="preserve">
                                                                                                <g>
                                                                                                    <path  d="M12,24C5.4,24,0,18.6,0,12S5.4,0,12,0h0.4L24,11.6l0,0.4C24,18.6,18.6,24,12,24z M10.9,2.1C5.8,2.6,2,6.9,2,12
                                                                                                        c0,5.5,4.5,10,10,10c5.1,0,9.4-3.8,9.9-8.9c-0.2,0-0.4,0-0.5,0c-3.4,0-6-0.9-7.8-2.6C11.7,8.6,10.8,5.8,10.9,2.1z M13,3.4
                                                                                                        c0.1,2.5,0.8,4.3,2,5.6c1.2,1.2,3.1,1.9,5.6,2L13,3.4z"/>
                                                                                                    <g>
                                                                                                        <path d="M10.2,12.3c-0.5,0.3-1.1,0.1-1.4-0.4c-0.3-0.5-0.9-0.7-1.4-0.4c-0.5,0.3-0.7,0.9-0.4,1.4c0.3,0.5,0.1,1.1-0.4,1.4
                                                                                                            c-0.5,0.3-1.1,0.1-1.4-0.4c-0.8-1.5-0.2-3.3,1.3-4.1s3.3-0.2,4.1,1.3C10.9,11.5,10.7,12.1,10.2,12.3z"/>
                                                                                                        <path d="M16.6,13.8c0.8,1.5,0.2,3.3-1.3,4.1c-1.5,0.8-3.3,0.2-4.1-1.3S15.9,12.3,16.6,13.8z"/>
                                                                                                    </g>
                                                                                                </g>
                                                                                            </svg>
                                                                                            <svg class="sticker" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                                                                                 viewBox="0 0 24 24" style="enable-background:new 0 0 24 24;" xml:space="preserve">
                                                                                                <g>
                                                                                                    <path d="M23.7,12.7c-0.1,0-1.3,0.3-2.9,0.3c-2.9,0-5.3-0.9-7.1-2.6c-0.6-0.5-3.5-3.5-2.5-10L11.2,0C5,0.4,0,5.6,0,12
                                                                                                        c0,6.6,5.4,12,12,12c6.4,0,11.6-5,12-11.4L23.7,12.7z M10.2,12.3c-0.5,0.3-1.1,0.1-1.4-0.4c-0.3-0.5-0.9-0.7-1.4-0.4
                                                                                                        s-0.7,0.9-0.4,1.4c0.3,0.5,0.1,1.1-0.4,1.4c-0.5,0.3-1.1,0.1-1.4-0.4c-0.8-1.5-0.2-3.3,1.3-4.1s3.3-0.2,4.1,1.3
                                                                                                        C10.9,11.5,10.7,12.1,10.2,12.3z M15.4,17.8c-1.5,0.8-3.3,0.2-4.1-1.3c-0.8-1.5,4.5-4.3,5.3-2.8S16.8,17,15.4,17.8z"/>
                                                                                                    <path d="M15,9c3.1,3,8.2,1.8,8.2,1.8s-8.6-8.6-10-10C12.2,6.6,15,9,15,9z"/>
                                                                                                </g>
                                                                                            </svg>
                                                                                        </i>
                                                                                        <span class="js_hover_info"><?php echo _p('post_a_sticker'); ?></span>
                                                                                    </div>
<?php endif; ?>
<?php if (Phpfox ::getParam('comment.comment_enable_emoticon')): ?>
                                                                                    <div title="" class="item-icon icon-emoji js_comment_attach_emoticon js_hover_title" data-feed-id="<?php echo $this->_aVars['aFeed']['feed_id']; ?>"><i class="ico ico-smile-o"></i><span class="js_hover_info"><?php echo _p('insert_an_emoji'); ?></span></div>
<?php endif; ?>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                
</form>

                                            </div>
                                        </div>
                                    </div>
                                    <div class="comment-group-btn-icon-empty" style="display: none">
                                    </div>
                                </div>
<?php else: ?>
<?php if (isset ( $this->_aVars['aFeed']['comments'] ) && count ( $this->_aVars['aFeed']['comments'] )): ?>
                                    <div class="feed_comments_end"></div>
<?php endif; ?>
<?php endif; ?>
<?php endif; ?>
                    </div> <!--comment-wrapper-->
                </div><!-- // .comment_mini_content_border -->
            </div><!-- // .comment_mini_content_holder -->
        </div>
<?php if (isset ( $this->_aVars['sFeedType'] )): ?>
    </div>
<?php endif;  endif; ?>

<script type="text/javascript">
    <?php echo '
    $Behavior.hideEmptyFeedOptions = function() {
        $(\'[data-component="feed-options"] ul.dropdown-menu\').each(function() {
            if ($(this).children().length !== 0) {
                var dropdownMenu = $(this).closest(\'[data-component="feed-options"]\');
                dropdownMenu.removeClass(\'hide\');
                dropdownMenu.closest(\'.js_feed_view_more_entry_holder\').
                find(\'.activity_feed_header_info\').
                addClass(\'feed-has-dropdown-menu\');
            } else {
                var commentHolder = $(this).closest(\'.comment_mini_content_border\');
                commentHolder.find(\'.js_comment_like_holder .activity_like_holder\').css(\'padding-right\', 0);
            }
        });
    };
    '; ?>

</script>
