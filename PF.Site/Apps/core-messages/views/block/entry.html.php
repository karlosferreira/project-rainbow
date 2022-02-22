<?php
	defined('PHPFOX') or exit('NO DICE!');
?>
{if !empty($aMail.message_time_list)}
	<div class="message-time-list" id="js_message_time_{$aMail.message_time_list.0}"><span class="d-inline-block">{$aMail.message_time_list.1}</span></div>
{/if}
<div class="mail_thread_holder{if $aMail.user_id == Phpfox::getUserId()} is_user{/if} {$aMail.class_with_same_user}" data-order="{$aMail.message_with_order}" data-id="{$aMail.message_id}" data-user="{$aMail.user_id}" data-date="{$aMail.message_with_same_date}"  id="js_mail_thread_message_{$aMail.message_id}">
	{if Phpfox::getUserId() != $aMail.user_id && empty($aMail.remove_avatar_with_same_user)}
	<div class="mail_user_image" data-toggle="tooltip" data-container="body" data-placement="left" title="{$aMail.full_name}">
		{img user=$aMail suffix='_120_square' max_width=50 max_height=50}
	</div>
	{/if}
	<div class="mail_content {if !empty($aMail.is_only_image_text) && empty($aMail.total_attachment)}is_only_image_text{/if} {if !empty($aMail.is_mixed_text)}is_mixed_text{/if} {if (int)$aMail.total_attachment > 0}has_attachment{/if}" data-toggle="tooltip" data-placement="{if $aMail.user_id == Phpfox::getUserId()}left{else}right{/if}" title="{$aMail.timestamp_parsed}">
		<div class="mail_text">
			<div class="mail_ie_support">
				{$aMail.text|parse}
			</div>
		</div>

        {if $aMail.total_attachment}
        	{module name='attachment.list' sType=mail iItemId=$aMail.message_id}
        {/if}
	</div>
</div>