<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="edit-shoutbox-message {if isset($aShoutbox.quoted_text)}has-quote{/if}" id="js_edit_shoutbox_message_content" data-error-quote-message="{_p var='shoutbox_you_only_quote_once'}">
    <input type="hidden" value="{$aShoutbox.shoutbox_id}" id="js_edit_shoutbox_id">
    <div class="error_message hide"></div>
    <div class="js_edit_shoutbox_message" id="js_compose_new_message">
        <form onsubmit="return false;">
            {if isset($aShoutbox.quoted_text)}
            <div class="shoutbox-container js_quoted_message_container">
                <a href="javascript:void(0);" role="button" onclick="appShoutbox.deleteQuotedMessage(this);" class="btn-delete-quoted js_btn_delete_quoted hide" title=""><i class="ico ico-close" aria-hidden="true"></i></a>
                <div class="shoutbox-item">
                    <div class="item_view_content">
                        <div class="item-quote-content edit-message">
                            <div class="quote-user">{$aShoutbox.quoted_full_name}</div>
                            <div class="quote-message">{$aShoutbox.quoted_text|parse}</div>
                        </div>
                    </div>
                </div>
            </div>
            {/if}
            <div class="input-group shoutbox-input-wrapper">
                <textarea rows='1' maxlength="255" id="shoutbox_edit_message_input" type="text" class="form-control chat_input" placeholder="{_p var='write_message'}"/></textarea>
            </div>
            <div class="attachment-holder">
                <div class="global_attachment">
                    <span class="item-count"><span id="pf_shoutbox_edit_text_counter">0</span>/255</span>
                    <ul class="global_attachment_list" data-id="shoutbox_edit_message_input"></ul>
                </div>
            </div>
        </form>
    </div>
    <div class="item-footer-sent">
        <button class="btn btn-default btn-sm" onclick="js_box_remove(this);">{_p var='cancel'}</button>
        <button class="btn btn-primary btn-sm" id="js_shoutbox_edit_message" data-error-message="{_p var='type_something_to_chat'}">{_p var='edit'}</button>
    </div>
</div>

<script type="text/javascript">
    {literal}
        $Behavior.edit_shoutbox_message = function(){
            var oInput = $('#shoutbox_edit_message_input');
            if(!oInput.hasClass('is_built')) {
                var originText = '{/literal}{$textEncrypt}{literal}';
                var textDescrypt = $Core.b64DecodeUnicode(originText);
                oInput.val(textDescrypt);
                oInput.addClass('is_built');
            }
            if(oInput.length) {
                $('.js_edit_shoutbox_message').find('#pf_shoutbox_edit_text_counter').html(oInput.val().length)
            }
            oInput.on('input', function() {
                appShoutbox.processQuote("edit");
            });
            $('.js_edit_shoutbox_message .emoji-list li').on('click', function() {
                oInput.trigger('input');
            });
            $('#js_shoutbox_edit_message').off('click').on('click', function() {
                var currentText = oInput.val().replace(/\s/g,'');
                if(currentText.length == 0 && !($(this).closest('#js_edit_shoutbox_message_content').find('.js_quoted_message_container').length)) {
                    $('#js_edit_shoutbox_message_content').find('.error_message').html($(this).data('error-message')).removeClass('hide');
                    return false;
                }
                $.ajaxCall('shoutbox.updateMessage', 'keep_quoted=' + $(this).closest('#js_edit_shoutbox_message_content').find('.js_quoted_message_container').length +'&shoutbox_id=' + $('#js_edit_shoutbox_id').val() + '&text=' + encodeURIComponent(oInput.val()));
            });
        }
    {/literal}
</script>
