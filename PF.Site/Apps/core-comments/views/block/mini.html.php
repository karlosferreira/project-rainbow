<?php

defined('PHPFOX') or exit('NO DICE!');

?>

{if !empty($aComment.is_hidden) && !empty($aComment.hide_this)}
    <div class="js_mini_feed_comment js_hidden_comment_dot comment-item {if $aComment.parent_id > 0 && empty($bIsViewingComments)}comment-item-reply{/if} {if isset($aComment.children) && isset($aComment.children.comments) && count($aComment.children.comments)}has-replies{/if} {if !empty($aComment.is_loaded_more)}reply_is_loadmore{/if} {if !empty($aComment.is_added_more)}is_added_more{/if}">
        <div class="item-outer">
            <div class="item-inner t_center">
                <a href="#" onclick="return $Core.Comment.showHiddenComments(this);" class="js_hover_title" title="" data-hidden-ids="{$aComment.hide_ids}"><i class="ico ico-dottedmore"></i><span class="js_hover_info">{_p var='total_hidden' total=$aComment.total_hidden}</span></a>
            </div>
        </div>
    </div>
{/if}
<div id="js_comment_{$aComment.comment_id}" class="js_mini_feed_comment comment-item {if $aComment.parent_id > 0}comment-item-reply{/if} js_mini_comment_item_{$aComment.item_id} {if isset($aComment.children) && isset($aComment.children.comments) && count($aComment.children.comments)}has-replies{/if} {if !empty($aComment.is_hidden)}hide view-hidden{/if} {if !empty($aComment.is_loaded_more)}reply_is_loadmore{/if} {if !empty($aComment.is_added_more)}is_added_more{/if}">
    {if (((Phpfox::getUserParam('comment.delete_own_comment') && Phpfox::getUserId() == $aComment.user_id)
    || Phpfox::getUserParam('comment.delete_user_comment')
    || (defined('PHPFOX_IS_USER_PROFILE') && isset($aUser.user_id) && $aUser.user_id == Phpfox::getUserId() && Phpfox::getUserParam('comment.can_delete_comments_posted_on_own_profile'))
    || (defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->isAdmin('' . $aPage.page_id . '')))
    || (Phpfox::getUserParam('comment.can_delete_comment_on_own_item')
    && isset($aFeed)
    && isset($aFeed.feed_link)
    && $aFeed.user_id == Phpfox::getUserId())
    || ((Phpfox::getUserParam('comment.edit_own_comment') && Phpfox::getUserId() == $aComment.user_id)
    || Phpfox::getUserParam('comment.edit_user_comment')) || ( Phpfox::isUser() && $aComment.user_id != Phpfox::getUserId() && (!isset($aFeed) || $aFeed.user_id != Phpfox::getUserId())))
    && ($aComment.view_id != 1 || ($aComment.view_id == 1 && Phpfox::getUserParam('comment.can_moderate_comments')))
    }
    <div class="item-comment-options {if !empty($aComment.is_hidden)}hide{/if}" id="js_comment_options_{$aComment.comment_id}">
        <a role="button" data-toggle="dropdown" href="#" class="item-options">
            <span class="ico ico-dottedmore-o"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
            {if Phpfox::isUser() && $aComment.user_id != Phpfox::getUserId() && (!isset($aFeed) || $aFeed.user_id != Phpfox::getUserId())}
                <li>
                    <a href="#" onclick="return $Core.Comment.hideComment(this);" data-parent-id="{$aComment.parent_id}" data-comment-id="{$aComment.comment_id}" data-owner-id="{$aComment.user_id}" class="">
                        <span class="ico ico-eye-off-o mr-1"></span>{_p var='hide'}
                    </a>
                </li>
            {/if}
            {if ((Phpfox::getUserParam('comment.edit_own_comment') && Phpfox::getUserId() == $aComment.user_id) || Phpfox::getUserParam('comment.edit_user_comment')) &&
            (($aComment.view_id != 1) || ($aComment.view_id == 1 && Phpfox::getUserParam('comment.can_moderate_comments')))
            }
                <li>
                    <a href="#" onclick="return $Core.Comment.getEditComment({$aComment.comment_id});">
                        <span class="ico ico-pencilline-o mr-1"></span>{_p var='edit'}
                    </a>
                </li>
            {/if}
            {if Phpfox::isModule('report') && Phpfox::getUserParam('report.can_report_comments')}
                {if $aComment.user_id != Phpfox::getUserId() && !Phpfox::getService('user.block')->isBlocked(null, $aComment.user_id)}
                    <li>
                        <a href="#?call=report.add&amp;height=210&amp;width=400&amp;type=comment&amp;id={$aComment.comment_id}" class="inlinePopup" title="{_p var='report_a_comment'}">
                            <span class="ico ico-warning-o mr-1"></span>{_p var='report'}
                        </a>
                    </li>
                {/if}
            {/if}
            {if ((Phpfox::getUserParam('comment.delete_own_comment') && Phpfox::getUserId() == $aComment.user_id) || Phpfox::getUserParam('comment.delete_user_comment') || (defined('PHPFOX_IS_USER_PROFILE') && isset($aUser.user_id) && $aUser.user_id == Phpfox::getUserId() && Phpfox::getUserParam('comment.can_delete_comments_posted_on_own_profile'))
            || (defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && Phpfox::getService(PHPFOX_PAGES_ITEM_TYPE)->isAdmin('' . $aPage.page_id . '')))
            && (($aComment.view_id != 1) || ($aComment.view_id == 1 && Phpfox::getUserParam('comment.can_moderate_comments')))
            }
                <li class="item-delete">
                    <a href="#" onclick="$Core.jsConfirm({left_curly}message:'{_p var='are_you_sure_you_want_to_delete_this_comment_permanently' phpfox_squote=true}'{right_curly}, function(){left_curly}$.ajaxCall('comment.InlineDelete', 'type_id={$aComment.type_id}&amp;comment_id={$aComment.comment_id}{if defined('PHPFOX_IS_THEATER_MODE')}&photo_theater=1{/if}{if !$aComment.parent_id}&item_id={$aComment.item_id}{/if}', 'GET');{right_curly},function(){left_curly}{right_curly}, true); return false;">
                        <span class="ico ico-trash-alt-o  mr-1"></span>{_p var='delete'}
                    </a>
                </li>
            {elseif Phpfox::getUserParam('comment.can_delete_comment_on_own_item') && isset($aFeed) && isset($aFeed.feed_link) && $aFeed.user_id == Phpfox::getUserId()}
                <li>
                    <a href="{$aFeed.feed_link}ownerdeletecmt_{$aComment.comment_id}/" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_comment_permanently'}">
                        <span class="ico ico-trash-alt-o  mr-1"></span>{_p var='delete'}
                    </a>
                </li>
            {/if}
        </ul>
    </div>
    {/if}
    <div class="item-outer">
        <div class="item-media">
            {img user=$aComment suffix='_120_square' max_width=40 max_height=40}
        </div>
        <div class="item-inner js_comment_text_inner_{$aComment.comment_id}">
            <div class="item-name">{$aComment|user:'':'':30}</div>
            <div class="item-comment-content js_comment_text_holder {if $aComment.view_id == '1'}row_moderate{/if}">
                {template file='comment.block.mini-extra'}
            </div>
            <div class="item-action comment_mini_action  {if !empty($aComment.is_hidden)}hide{/if}" id="js_comment_action_{$aComment.comment_id}">
                <div class="action-list">
                    {if $aComment.view_id == '0'}
                        {module name='like.link' like_type_id='feed_mini' like_owner_id=$aComment.user_id like_item_id=$aComment.comment_id like_is_liked=$aComment.is_liked like_is_custom=true}
                        <span class="total-like js_like_link_holder" {if $aComment.total_like == 0}style="display:none"{/if}>
                        <span onclick="return $Core.box('like.browse', 450, 'type_id=feed_mini&amp;item_id={$aComment.comment_id}');">
                                    <span class="js_like_link_holder_info">
                                        {$aComment.total_like}
                                    </span>
                                </span>
                        </span>
                    {/if}

                    {if Phpfox::getUserParam('comment.can_post_comments') && Phpfox::getParam('comment.comment_is_threaded')}
                        {if (isset($bForceNoReply) && $bForceNoReply) || Phpfox::getService('user.block')->isBlocked(null, $aComment.user_id) || $aComment.view_id == '1' || (defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE') && PHPFOX_PAGES_ITEM_TYPE == 'groups' && !Phpfox::isAdmin() && !Phpfox::getService('groups')->isMember('' . $aPage.page_id . ''))}
                        {else}
                            <span class="item-reply"><a href="#" class="js_comment_feed_new_reply" rel="{if !empty($aComment.parent_id)}{$aComment.parent_id}{else}{$aComment.comment_id}{/if}" data-parent-id="{$aComment.parent_id}" data-owner-id="{$aComment.user_id}" data-current-user="<?php echo Phpfox::getUserId(); ?>" data-is-single="{if !empty($bIsViewingComments)}1{else}0{/if}">{_p var='reply'}</a></span>
                        {/if}
                    {/if}

                    {if Phpfox::getUserParam('comment.can_moderate_comments') && ($aComment.view_id == '1' || $aComment.view_id == '9')}
                        <span class="js_comment_action">
                                <a href="#" onclick="$Core.jsConfirm({l}message:'{_p var='are_you_sure_you_want_to_approve_this_comment' phpfox_squote=true}'{r}, function(){l}$('.js_comment_text_inner_{$aComment.comment_id} .js_comment_text_holder').removeClass('row_moderate'); $(this).parent().siblings('.js_comment_action').remove(); $(this).parent().remove(); $.ajaxCall('comment.moderateSpam', 'id={$aComment.comment_id}&amp;action=approve&amp;inacp=0');{r},function(){l}{r}); return false;">{_p var='approve'}</a>
                            </span>
                        <span class="item-reply js_comment_action">
                                <a href="#" onclick="$Core.jsConfirm({l}message:'{_p var='are_you_sure_you_want_to_deny_this_comment' phpfox_squote=true}'{r}, function(){l}$('#js_comment_{$aComment.comment_id}').slideUp(); $.ajaxCall('comment.moderateSpam', 'id={$aComment.comment_id}&amp;action=deny&amp;inacp=0');{r},function(){l}{r}); return false;">{_p var='deny'}</a>
                            </span>
                    {/if}
                    {if !empty($aComment.extra_data) && $aComment.extra_data.extra_type == 'preview' && $aComment.user_id == Phpfox::getUserId() && !Phpfox::getParam('core.disable_all_external_urls')}
                        <span class="item-remove-preview" id="js_remove_preview_action_{$aComment.comment_id}">
                            <a href="#" onclick="$.ajaxCall('comment.removePreview','id={$aComment.comment_id}','post'); return false;" class="comment-remove">{_p var='remove_preview'}</a>
                        </span>
                    {/if}
                    <span class="item-time">{if isset($aComment.unix_time_stamp)}{$aComment.unix_time_stamp|convert_time:'core.global_update_time'}{else}{if $aComment.update_time > 0}{$aComment.update_time|convert_time:'core.global_update_time'}{else}{$aComment.time_stamp|convert_time:'core.global_update_time'}{/if}{/if}</span>

                    {if $aComment.unix_update_time > 0}
                        <span class="item-history" id="js_view_edit_history_action_{$aComment.comment_id}">
                            <a href="#" title="{_p var='show_edit_history'}" class="view-edit-history" onclick="tb_show('{_p var='edit_history'}', $.ajaxBox('comment.showEditHistory', 'id={$aComment.comment_id}&height=400&width=600')); return false;">{_p var='edited'}</a>
                        </span>
                    {/if}
                </div>
            </div>
            {if !empty($aComment.is_hidden)}
                <div class="item-action comment_mini_action " id="js_hide_comment_{$aComment.comment_id}">
                    <div class="action-list">
                            <span class="item-un-hide">
                                <a href="#" onclick="return $Core.Comment.hideComment(this, true);" data-comment-id="{$aComment.comment_id}">{_p var='unhide'}</a>
                            </span>
                        <span class="item-time">{if isset($aComment.unix_time_stamp)}{$aComment.unix_time_stamp|convert_time:'core.global_update_time'}{else}{$aComment.time_stamp|convert_time:'core.global_update_time'}{/if}</span>
                    </div>
                </div>
            {/if}
        </div>
    </div>
    <div class="comment-wrapper-reply">
        <div class="comment-container-reply">
            <div id="js_comment_form_holder_{$aComment.comment_id}" class="js_comment_form_holder"></div>
            {if Phpfox::getParam('comment.thread_comment_total_display') !== null && $aComment.child_total && ((Phpfox::getParam('comment.thread_comment_total_display') && isset($aComment.children.comments) && count($aComment.children.comments) && $aComment.child_total > Phpfox::getParam('comment.thread_comment_total_display')) || (!setting('comment.comment_show_replies_on_comment') && !empty($aComment.last_reply)))}
                <?php
                $this->_aVars['iReplyShowTotal'] = count($this->_aVars['aComment']['children']['comments']);
                $this->_aVars['aLastReply'] = end($this->_aVars['aComment']['children']['comments']);
                ?>
                <div class="js_comment_view_more_reply_holder hide">
                    <div class="js_comment_view_more_reply_wrapper">
                        {if !isset($bIsViewingComments) || !$bIsViewingComments}
                            <div class="comment-viewmore js_comment_view_more_replies_{$aComment.comment_id} js_comment_replies_viewmore_{$aComment.comment_id} {if !setting('comment.comment_show_replies_on_comment')}comment-hide-all{/if}">
                                    <span class="js_link_href hide" data-href="{url link='comment.replies'}?is_feed={if Phpfox::getLib('module')->getFullControllerName() == 'core.index-member'}1{else}0{/if}&comment_type_id={$aComment.type_id}&item_id={$aComment.item_id}&comment_id={$aComment.comment_id}&time-stamp={if isset($aLastReply.time_stamp)}{$aLastReply.time_stamp}{else}0{/if}&max-time=<?php echo PHPFOX_TIME; ?>&shown-total={$iReplyShowTotal}&total-replies={$aComment.child_total}"
                                    >{if !setting('comment.comment_show_replies_on_comment')}{img user=$aComment.last_reply suffix='_120_square' max_width=40 max_height=40 no_link=true}{/if}
                                        {if !setting('comment.comment_show_replies_on_comment')}
                                            {if $aComment.child_total == 1}
                                                {_p var='full_name_replied_one_reply' full_name=$aComment.last_reply.full_name}
                                            {else}
                                                {_p var='full_name_replied_number_replies' full_name=$aComment.last_reply.full_name number=$aComment.child_total}
                                            {/if}
                                        {elseif $aComment.child_total - Phpfox::getParam('comment.thread_comment_total_display') == 1}
                                            {_p var='view_one_more_reply'}
                                        {elseif ($iRemain = $aComment.child_total - Phpfox::getParam('comment.thread_comment_total_display')) < 10}
                                            {_p var='view_span_number_more_replies' number=$iRemain}
                                        {else}
                                            {_p var='view_more_replies'}
                                        {/if}
                                    </span>
                                {if setting('comment.comment_show_replies_on_comment')}
                                    <div class="item-number" >
                                        {$iReplyShowTotal}/{$aComment.child_total}
                                    </div>
                                {/if}
                            </div>
                        {/if}
                    </div>
                </div>
            {/if}
            <div id="js_comment_mini_child_holder_{$aComment.comment_id}" class="comment_mini_child_holder{if isset($aComment.children) && $aComment.children.total > 0} comment_mini_child_holder_padding{/if}">
                <div id="js_comment_children_holder_{$aComment.comment_id}" class="comment_mini_child_content">
                    {if isset($aComment.children) && isset($aComment.children.comments) && count($aComment.children.comments) && setting('comment.comment_show_replies_on_comment')}
                        {foreach from=$aComment.children.comments item=aCommentChilded}
                            {module name='comment.mini' comment_custom=$aCommentChilded}
                        {/foreach}
                    {else}
                        <div id="js_feed_like_holder_{$aComment.type_id}_{$aComment.item_id}"></div>
                    {/if}
                </div>
            </div>
        </div>
    </div>
    {if !empty($bIsAjaxAdd) && (!isset($iParentId) || !$iParentId)}
        <script>
          $Core.Comment.updateCommentCounter('{$aComment.type_id}',{$aComment.item_id}, '+');
        </script>
    {elseif !empty($bIsAjaxAdd) && $iParentId}
        <script>
          $Core.Comment.updateReplyCounter({$iParentId}, '+');
        </script>
    {/if}
</div>
