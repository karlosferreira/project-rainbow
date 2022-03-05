<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{if (isset($showOnlyComments))}
    {if Phpfox::isModule('comment') && (isset($aFeed.comments) && count($aFeed.comments))}
        {if $iShownTotal < $aFeed.total_comment}
            <div class="core_comment_viemore_holder">
                <div class="comment-viewmore" id="js_feed_comment_pager_{$aFeed.comment_type_id}_{$aFeed.item_id}">
                    <a href="{url link='comment.comments'}?type={$aFeed.comment_type_id}&id={$aFeed.item_id}&page=1{if defined('PHPFOX_FEED_STREAM_MODE')}&stream-mode=1{/if}&time-stamp={$aFeed.comments.0.time_stamp}&shown-total={$iShownTotal}&total-comment={$aFeed.total_comment}" class="item-viewmore ajax" onclick="$(this).addClass('active');">
                        {if $aFeed.total_comment - $iShownTotal == 1}
                            {_p var='view_one_more_comment'}
                        {elseif ($iRemain = $aFeed.total_comment - $iShownTotal) < 10}
                            {_p var='view_number_more_comments' number=$iRemain}
                        {else}
                            {_p var='view_previous_comments'}
                        {/if}
                    </a>
                    <div class="item-number">{$iShownTotal}/{$aFeed.total_comment}</div>
                </div>
            </div>
        {/if}
        {foreach from=$aFeed.comments name=comments item=aComment}
            {template file='comment.block.mini'}
        {/foreach}
    {/if}
{else}
    {if isset($bIsViewingComments) && $bIsViewingComments}
        <div id="comment-view"><a name="#comment-view"></a></div>
        <div class="message js_feed_comment_border">
            {_p var='viewing_a_single_comment'}
            <a href="{$aFeed.feed_link}">{_p var='view_all_comments'}</a>
        </div>
        <script>
            {literal}
            $Ready(function() {
                var c = $('#comment-view');
                if (c.length && !c.hasClass('completed') && c.is(':visible')) {
                    c.addClass('completed');
                    $("html, body").animate({ scrollTop: (c.offset().top - 80) });
                }
            });
            {/literal}
        </script>
    {/if}

    {if isset($sFeedType)}
    <div class="js_parent_feed_entry parent_item_feed">
    {/if}
        <div class="js_feed_comment_border comment-content" {if isset($sFeedType) &&  $sFeedType == 'view'}id="js_item_feed_{$aFeed.feed_id}"{/if}>
            {plugin call='feed.template_block_comment_border'}
            <div id="js_feed_like_holder_{if isset($aFeed.like_type_id) && !isset($aFeed.is_app)}{$aFeed.like_type_id}{else}{$aFeed.comment_type_id}{/if}_{if isset($aFeed.like_item_id) && !isset($aFeed.is_app)}{$aFeed.like_item_id}{else}{$aFeed.item_id}{/if}" class="comment_mini_content_holder{if (isset($aFeed.is_app) && $aFeed.is_app && isset($aFeed.app_object))} _is_app{/if}"{if (isset($aFeed.is_app) && $aFeed.is_app && isset($aFeed.app_object))} data-app-id="{$aFeed.app_object}"{/if}>
                <div class="comment_mini_content_holder_icon"{if isset($aFeed.marks) || (isset($aFeed.likes) && is_array($aFeed.likes)) || (isset($aFeed.total_comment) && $aFeed.total_comment > 0)}{else}{/if}></div>
                <div class="comment_mini_content_border">
                    {if !isset($aFeed.feed_mini)}
                        <div class="feed-options-holder item-options-holder hide" data-component="feed-options">
                            <a role="button" data-toggle="dropdown" href="#" class="feed-options item-options">
                                <span class="ico ico-dottedmore-o"></span>
                            </a>
                            {template file='feed.block.link'}
                        </div>
                    {/if}

                    <div class="comment-mini-content-commands">
                        <div class="button-like-share-block {if isset($aFeed.total_action)}comment-has-{$aFeed.total_action}-actions{/if}">
                            {if $aFeed.can_like}
                                <div class="feed-like-link">
                                    {if isset($aFeed.like_item_id)}
                                        {module name='like.link' like_type_id=$aFeed.like_type_id like_item_id=$aFeed.like_item_id like_is_liked=$aFeed.feed_is_liked}
                                    {else}
                                        {module name='like.link' like_type_id=$aFeed.like_type_id like_item_id=$aFeed.item_id like_is_liked=$aFeed.feed_is_liked}
                                    {/if}
                                    <span class="counter" onclick="return $Core.box('like.browse', 450, 'type_id={if isset($aFeed.like_type_id)}{$aFeed.like_type_id}{else}{$aFeed.comment_type_id}{/if}&amp;item_id={$aFeed.item_id}');">{if !empty($aFeed.feed_total_like)}{$aFeed.feed_total_like}{/if}</span>
                                </div>
                            {/if}
                            {if (!isset($sFeedType) ||  $sFeedType != 'mini') && $aFeed.can_comment}
                                <div class="feed-comment-link">
                                    <a href="#" onclick="$('#js_feed_comment_form_textarea_{$aFeed.feed_id}').focus();return false;"><span class="ico ico-comment-o"></span></a>
                                    <span class="counter">{if !empty($aFeed.total_comment)}{$aFeed.total_comment}{/if}</span>
                                </div>
                            {/if}

                            {if $aFeed.can_share}
                                <div class="feed-comment-share-holder">
                                    {assign var=empty value=false}
                                    {if $aFeed.privacy == '0' || $aFeed.privacy == '1' || $aFeed.privacy == '2'}
                                        {if isset($aFeed.share_type_id)}
                                            {module name='share.link' type='feed' display='menu_btn' url=$aFeed.feed_link title=$aFeed.feed_title sharefeedid=$aFeed.item_id sharemodule=$aFeed.share_type_id}
                                        {else}
                                            {module name='share.link' type='feed' display='menu_btn' url=$aFeed.feed_link title=$aFeed.feed_title sharefeedid=$aFeed.item_id sharemodule=$aFeed.type_id}
                                        {/if}
                                    {else}
                                        {module name='share.link' type='feed' display='menu_btn' url=$aFeed.feed_link title=$aFeed.feed_title}
                                    {/if}
                                    <span class="counter">{if !empty($aFeed.total_share)}{$aFeed.total_share}{/if}</span>
                                </div>
                            {/if}

                            {plugin call='feed.template_block_comment_commands_1'}
                        </div>

                        {if isset($aFeed.like_type_id) && !(isset($aFeed.disable_like_function) && $aFeed.disable_like_function)}
                        <div class="js_comment_like_holder" id="js_feed_like_holder_{$aFeed.comment_type_id}_{$aFeed.item_id}">
                            <div id="js_like_body_{$aFeed.feed_id}">
                                {template file='like.block.display'}
                            </div>
                        </div>
                        {/if}

                        {plugin call='feed.template_block_comment_commands_2'}

                        {plugin call='feed.template_block_comment_commands_3'}
                    </div>
                    <div class="comment-wrapper"> <!--comment-wrapper-->
                        {if  Phpfox::isModule('comment')}
                            {if !isset($bIsViewingComments) || !$bIsViewingComments}
                                <div id="js_feed_comment_post_{$aFeed.feed_id}" class="js_feed_comment_view_more_holder">
                                    {if isset($aFeed.comments) && $iShownTotal = count($aFeed.comments)}
                                            {if Phpfox::getParam('comment.comment_page_limit') != null && Phpfox::isModule('comment') && $aFeed.total_comment > Phpfox::getParam('comment.comment_page_limit')}
                                                <div class="comment-viewmore" id="js_feed_comment_pager_{$aFeed.comment_type_id}_{$aFeed.item_id}">
                                                    <a href="{url link='comment.comments'}?type={$aFeed.comment_type_id}&id={$aFeed.item_id}&page=1{if defined('PHPFOX_FEED_STREAM_MODE')}&stream-mode=1{/if}&time-stamp={$aFeed.comments.0.time_stamp}&shown-total={$iShownTotal}&total-comment={$aFeed.total_comment}" class="ajax item-viewmore"  onclick="$(this).addClass('active');">
                                                        {if $aFeed.total_comment - Phpfox::getParam('comment.comment_page_limit') == 1}
                                                            {_p var='view_one_more_comment'}
                                                        {elseif ($iRemain = $aFeed.total_comment - Phpfox::getParam('comment.comment_page_limit')) < 10}
                                                            {_p var='view_number_more_comments' number=$iRemain}
                                                        {else}
                                                            {_p var='view_previous_comments'}
                                                        {/if}
                                                    </a>
                                                    <div class="item-number">{$iShownTotal}/{$aFeed.total_comment}</div>
                                                </div>
                                            {/if}
                                            <div class="comment-container">
                                                <div id="js_feed_comment_view_more_{$aFeed.feed_id}"{if isset($sFeedType) &&  $sFeedType == 'view'}class="js_comment_items"{else} class="js_comment_limit js_comment_items" data-limit="{if ($thisLimit = setting('comment.comment_page_limit'))}{$thisLimit}{/if}"{/if}>
                                                    {foreach from=$aFeed.comments name=comments item=aComment}
                                                        {template file='comment.block.mini'}
                                                    {/foreach}
                                                </div><!-- // #js_feed_comment_view_more_{$aFeed.feed_id} -->
                                            </div>

                                    {else}
                                        <div class="comment-container">
                                            <div id="js_feed_comment_view_more_{$aFeed.feed_id}"></div><!-- // #js_feed_comment_view_more_{$aFeed.feed_id} -->
                                        </div>
                                    {/if}
                                </div><!-- // #js_feed_comment_post_{$aFeed.feed_id} -->
                            {else}
                                {if isset($aFeed.comments.0) && ($aFeed.comments.0.view_id == '0' || (Phpfox::isUser() && (Phpfox::getUserParam('comment.can_moderate_comments') || $aFeed.comments.0.user_id == Phpfox::getUserId())))}
                                    <div class="comment-container">
                                        <div id="js_feed_comment_view_more_{$aFeed.feed_id}"{if isset($sFeedType) &&  $sFeedType == 'view'}class="js_comment_items"{else} class="js_comment_limit js_comment_items" data-limit="{if ($thisLimit = setting('comment.comment_page_limit'))}{$thisLimit}{/if}"{/if}>
                                            {foreach from=$aFeed.comments name=comments item=aComment}
                                                {template file='comment.block.mini-single'}
                                            {/foreach}
                                        </div><!-- // #js_feed_comment_view_more_{$aFeed.feed_id} -->
                                    </div>
                                {else}
                                    <div class="comment-container">
                                        <div class="comment-item">
                                            <div class="error_message" style="margin-bottom: 0;">
                                                {_p var='you_do_not_have_permission_to_view_this_comment'}
                                            </div>
                                        </div>
                                    </div>
                                {/if}
                            {/if}
                        {/if}

                        {if isset($sFeedType) &&  $sFeedType == 'mini'}

                        {else}
                            {if Phpfox::isModule('comment')
                                && isset($aFeed.comment_type_id)
                                && Phpfox::getUserParam('comment.can_post_comments')
                                && Phpfox::isUser()
                                && $aFeed.can_post_comment
                                && (!isset($bIsGroupMember) || $bIsGroupMember)
                            }
                                {if Phpfox::isModule('captcha') && Phpfox::getUserParam('captcha.captcha_on_comment')}
                                    {module name='captcha.form' sType='comment' captcha_popup=true}
                                {/if}
                                <div class="comment-footer js_feed_comment_form_holder">
                                    <div class="comment-box-container">
                                        <div class="js_feed_core_comment_form" {if isset($sFeedType) &&  $sFeedType == 'view'} id="js_feed_comment_form_{$aFeed.feed_id}"{/if}>
                                            <div class="js_app_comment_feed_textarea_browse"></div>
                                            <div class="{if isset($sFeedType) &&  $sFeedType == 'view'} feed_item_view{/if}">
                                                <form method="post" action="#" class="js_app_comment_feed_form form" id="js_app_comment_feed_form_{$aFeed.feed_id}">
                                                    {if (isset($aFeed.is_app) && $aFeed.is_app && isset($aFeed.app_object))}
                                                        <input type="hidden" name="val[app_object]" value="{$aFeed.app_object}" />
                                                    {/if}
                                                    <input type="hidden" name="val[table_prefix]" value="{if isset($aFeed.feed_table_prefix)}{$aFeed.feed_table_prefix}{/if}" />
                                                    <input type="hidden" name="val[type]" value="{$aFeed.comment_type_id}" />
                                                    <input type="hidden" name="val[item_id]" value="{$aFeed.item_id}" />
                                                    <input type="hidden" name="val[parent_id]" value="0" class="js_feed_comment_parent_id" />
                                                    <input type="hidden" name="val[is_single]" value="{if !empty($bIsViewingComments) && isset($aFeed.comments.0) && $aFeed.comments.0.parent_id}1{else}0{/if}" class="js_feed_comment_is_single" />
                                                    <input type="hidden" name="val[photo_id]" value="0" class="js_feed_comment_photo_id" />
                                                    <input type="hidden" name="val[sticker_id]" value="0" class="js_feed_comment_sticker_id" />
                                                    <input type="hidden" name="val[is_via_feed]" value="{$aFeed.feed_id}" class="js_feed_comment_feed_id"/>
                                                    {if defined('PHPFOX_IS_THEATER_MODE')}
                                                        <input type="hidden" name="ajax_post_photo_theater" value="1" />
                                                    {/if}
                                                    <div class="">
                                                        <input type="hidden" name="val[default_feed_value]" value="{_p var='write_a_comment'}" />
                                                        <div class="js_comment_feed_value">{_p var='write_a_comment'}</div>
                                                        <div class="item-outer">
                                                            <div class="item-media">
                                                                {if Phpfox::isUser()}
                                                                    {img user=$aGlobalUser suffix='_120_square' max_width='40' max_height='40'}
                                                                {/if}
                                                            </div>
                                                            <div class="item-inner">
                                                                <div class="comment-box js_comment_box">
                                                                    <div class="item-edit-content">
                                                                        <div class="item-box-input">
                                                                        <div ondragover="return false;" ondrop="return false;" class="p-comment-pseudo-firefox-prevent-drop" style="position: absolute;top: -16px;left: 0;right: 0;bottom: -48px;z-index: 0;display: none;"></div>
                                                                            <textarea rows="1" name="val[text]"  id="js_feed_comment_form_textarea_{$aFeed.feed_id}" class="form-control comment-textarea-edit js_app_comment_feed_textarea" placeholder="{_p var='write_a_comment'}" autocomplete="off" style="display: none"></textarea>
                                                                            <div contenteditable="true" class="form-control contenteditable comment-textarea-edit js_app_comment_feed_textarea" data-text="{_p var='write_a_comment'}" ondragover="return false;" ondrop="return false;"></div>
                                                                            <button class="mobile-sent-btn" style="display: none;"><span class="ico ico-paperplane"></span></button>
                                                                            <div class="js_feed_comment_process_form"><i class="fa fa-spin fa-circle-o-notch"></i></div>
                                                                            <div class="comment-group-icon dropup js_comment_group_icon">
                                                                                {if Phpfox::getParam('comment.comment_enable_photo')}
                                                                                    <div title="" class="item-icon icon-photo js_comment_attach_photo js_hover_title" data-feed-id="{$aFeed.feed_id}">
                                                                                        <i class="ico ico-camera-o"></i>
                                                                                        <span class="js_hover_info">{_p var='attach_a_photo'}</span>
                                                                                        <input type="file" style="display: none;" class="js_attach_photo_input_file" accept="image/*" data-feed-id="{$aFeed.feed_id}">
                                                                                    </div>
                                                                                {/if}
                                                                                {if !empty(Phpfox::getService('comment.stickers')->countActiveStickerSet()) && Phpfox::getParam('comment.comment_enable_sticker')}
                                                                                    <div title="" class="item-icon icon-sticker js_comment_attach_sticker js_comment_icon_sticker_{$aFeed.feed_id} js_hover_title" data-sticker_next="0" data-feed-id="{$aFeed.feed_id}">
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
                                                                                        <span class="js_hover_info">{_p var='post_a_sticker'}</span>
                                                                                    </div>
                                                                                {/if}
                                                                                {if Phpfox::getParam('comment.comment_enable_emoticon')}
                                                                                    <div title="" class="item-icon icon-emoji js_comment_attach_emoticon js_hover_title" data-feed-id="{$aFeed.feed_id}"><i class="ico ico-smile-o"></i><span class="js_hover_info">{_p var='insert_an_emoji'}</span></div>
                                                                                {/if}
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
                            {else}
                                {if isset($aFeed.comments) && count($aFeed.comments)}
                                    <div class="feed_comments_end"></div>
                                {/if}
                            {/if}
                        {/if}
                    </div> <!--comment-wrapper-->
                </div><!-- // .comment_mini_content_border -->
            </div><!-- // .comment_mini_content_holder -->
        </div>
    {if isset($sFeedType)}
    </div>
    {/if}
{/if}

<script type="text/javascript">
    {literal}
    $Behavior.hideEmptyFeedOptions = function() {
        $('[data-component="feed-options"] ul.dropdown-menu').each(function() {
            if ($(this).children().length !== 0) {
                var dropdownMenu = $(this).closest('[data-component="feed-options"]');
                dropdownMenu.removeClass('hide');
                dropdownMenu.closest('.js_feed_view_more_entry_holder').
                find('.activity_feed_header_info').
                addClass('feed-has-dropdown-menu');
            } else {
                var commentHolder = $(this).closest('.comment_mini_content_border');
                commentHolder.find('.js_comment_like_holder .activity_like_holder').css('padding-right', 0);
            }
        });
    };
    {/literal}
</script>