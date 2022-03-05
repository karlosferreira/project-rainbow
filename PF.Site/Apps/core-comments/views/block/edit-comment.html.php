<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="comment-box-edit js_edit_comment_holder js_comment_quick_edit_holder_{$aComment.comment_id} {if !empty($sType) && $sType != 'preview'}has-photo-sticker{/if}">
    <form method="post" action="#" class="js_app_comment_feed_form form" data-edit-id="{$aComment.comment_id}">
        <div class="item-edit-content">
            <div class="item-box-input">
                <input type="hidden" name="val[parent_id]" value="{$aComment.parent_id}" class="js_feed_comment_parent_id" />
                <input type="hidden" name="val[comment_id]" value="{$aComment.comment_id}" class="js_feed_comment_id" />
                <input type="hidden" name="val[photo_id]" value="0" class="js_feed_comment_photo_id" />
                <input type="hidden" name="val[attach_changed]" value="0" class="js_feed_comment_attach_change" />
                <input type="hidden" name="val[sticker_id]" value="0" class="js_feed_comment_sticker_id" />
                <input type="hidden" name="val[default_feed_value]" value="{_p var='write_a_comment'}" />
                <textarea rows="1" name="val[text]" class="form-control comment-textarea-edit js_comment_textarea_edit" id="js_comment_quick_edit_{$aComment.comment_id}" placeholder="{if $aComment.parent_id}{_p var='write_a_reply'}{else}{_p var='write_a_comment'}{/if}" style="display: none">{$aComment.text}</textarea>
                <div contenteditable="true" class="form-control contenteditable comment-textarea-edit js_comment_textarea_edit" data-text="{_p var='write_a_comment'}" ondragover="return false;" ondrop="return false;">{$generatedValue|divcontenteditable_comment_parse_emojis}</div>
                <div class="js_feed_comment_process_form"><i class="fa fa-spin fa-circle-o-notch"></i></div>
                <div class="comment-group-icon dropup js_comment_group_icon">
                    {if Phpfox::getParam('comment.comment_enable_photo')}
                        <div title="" class="item-icon icon-photo js_comment_attach_photo js_hover_title" data-feed-id="0" data-edit-id="{$aComment.comment_id}">
                            <input type="file" style="display: none;" class="js_attach_photo_input_file" accept="image/*" data-feed-id="0" data-edit-id="{$aComment.comment_id}">
                            <i class="ico ico-camera-o"></i>
                            <span class="js_hover_info">{_p var='attach_a_photo'}</span>
                        </div>
                    {/if}
                    {if !empty(Phpfox::getService('comment.stickers')->countActiveStickerSet()) && Phpfox::getParam('comment.comment_enable_sticker')}
                        <div title="" class="item-icon icon-sticker js_comment_attach_sticker js_comment_icon_sticker_{$aComment.comment_id} js_hover_title" data-sticker_next="0" data-feed-id="0" data-edit-id="{$aComment.comment_id}"><i class="ico "><svg class="sticker-o" version="1.1" id="Layer_1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" x="0px" y="0px"
                                 viewBox="0 0 24 24" style="enable-background:new 0 0 24 24;" xml:space="preserve">
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
                        </i><span class="js_hover_info">{_p var='post_a_sticker'}</span></div>
                    {/if}
                    {if Phpfox::getParam('comment.comment_enable_emoticon')}
                        <div title="" class="item-icon icon-emoji js_comment_attach_emoticon js_hover_title" data-feed-id="0" data-edit-id="{$aComment.comment_id}"><i class="ico ico-smile-o"></i><span class="js_hover_info">{_p var='insert_an_emoji'}</span></div>
                    {/if}
                </div>
            </div>
            {if !empty($aForms)}
                {template file='comment.block.preview-attach'}
            {/if}
        </div>
    </form>
    <div class="comment-group-btn-icon">
        <div class="comment-btn">
            <button class="btn btn-default btn-xs" onclick="return $Core.Comment.unsetAllEditComment({$aComment.comment_id});">{_p var='cancel'}</button>
            <button class="btn btn-primary btn-xs" onclick="$(this).closest('.js_edit_comment_holder').find('form:first').submit(); return false;">{_p var='submit'}</button>
        </div>
        <div class="item-edit-cancel">
            <span class="js_comment_focus_edit_comment">
                {_p var='press_esc_to'} <a href="#" onclick="return $Core.Comment.unsetAllEditComment({$aComment.comment_id});" class="item-cancel">{_p var='cancel__l'}</a> {_p var='edit__l'}
            </span>
            <span class="js_comment_not_focus_edit_comment hide">
                <a href="#" onclick="return $Core.Comment.unsetAllEditComment({$aComment.comment_id});" class="item-cancel">{_p var='cancel__l'}</a>
            </span>
        </div>
    </div>
</div>
<script type="text/javascript">
    $Behavior.onLoadEditCommentForm = function() {l}
        $Core.attachFunctionTagger('.js_comment_quick_edit_holder_{$aComment.comment_id} .js_comment_textarea_edit');
        $Core.Comment.initFocusTextarea('.js_comment_textarea_edit');
        $Core.Comment.editFormStateInit();
    {r}
</script>