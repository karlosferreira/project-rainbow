<?php
	defined('PHPFOX') or exit('NO DICE!');
?>

<div id="js_main_mail_thread_holder" class="core-messages__title-conversation">
    <input type="hidden" id="js_core_messages_url_send_message" value="{url link='mail.send-message'}">
	<div class="mail-messages js_mail_messages_content dont-unbind-children" data-id="{$aThread.thread_id}" id="js_conversation_content">
		{foreach from=$aMessages name=messages item=aMail}
			{template file='mail.block.entry'}
		{/foreach}
		<div id="mail_threaded_new_message"></div>
		<div id="mail_threaded_new_message_scroll"></div>
        <div class="js_template_message_is_user">
            <div class="mail_content">
                <div class="mail_text"></div>
            </div>
        </div>
        <input type="hidden" value="{$bCanLoadMore}" id="js_check_load_more_conversation_content">
	</div>
	<div class="mail_thread_form_holder">
		<div class="mail_thread_form_holder_inner">
			{if $bCanReplyThread}
			{$sCreateJs}
			<form method="post" action="{url link='mail.thread' id=$aThread.thread_id}" id="js_form_mail" class="js_ajax_mail_thread" onsubmit="return coreMessages.onSendMessage(this);">
                <div id="js_mail_error"></div>
                <div> <input type="hidden" value="{value type='input' id='message'}" id="message" name="val[message]"></div>
				<div><input type="hidden" name="val[thread_id]" value="{$aThread.thread_id}" /></div>
				<div><input type="hidden" name="val[attachment]" class="js_attachment" value="{value type='input' id='attachment'}" /></div>
			</form>
			{else}
			<div class="message">{_p var='can_not_reply_due_to_privacy_settings'}</div>
			{/if}
		</div>
	</div>
</div>
{literal}
<script type="text/javascript">
    $Behavior.core_messages_conversation_content = function () {
        coreMessages.initConversationContentCustomScroll();
    }
</script>
{/literal}