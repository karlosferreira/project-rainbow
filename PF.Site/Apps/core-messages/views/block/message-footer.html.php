<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div id="js_compose_new_message" data-form="{$sForm}">
    <div><input type="hidden" name="val[attachment]" class="js_attachment" value=""></div>
    <div class="compose-form">
        {if \Phpfox::getUserParam('mail.can_add_attachment_on_mail')}
        	{editor id='js_compose_message_textarea' enter=true placeholder=_p('mail_send_your_reply')}
        {else}
        	{editor id='js_compose_message_textarea' enter=true can_attach_file=false placeholder=_p('mail_send_your_reply')}
        {/if}
        <button class="btn btn-primary button_not_active btn-compose" id="js_send_message_btn"><i class="ico ico-paperplane mr-1"></i>{_p var='send'}</button>
    </div>
</div>
{literal}
<script type="text/javascript">
    $Behavior.advanced_chat_textarea = function () {
        coreMessagesCustomAttachment.initAttachmentHolder();
    }
</script>
{/literal}