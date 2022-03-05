<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{$sScript}
{if $aNotifications}
<ul class="panel-items">
	{foreach from=$aNotifications name=notifications item=aNotification}
	<li id="js_notification_{$aNotification.notification_id}" class="js_notification_{$aNotification.notification_id} panel-item {if !$aNotification.is_read} is_new{/if}">
		<div class="panel-item-content">
			<a href="{$aNotification.link}" onclick="$.ajaxCall('notification.markAsRead', 'notification_id={$aNotification.notification_id}');">

				{if isset($aNotification.custom_icon)}
					<i class="fa {$aNotification.custom_icon}"></i>
				{else}
					{img user=$aNotification max_width='50' max_height='50' suffix='_120_square' no_link=true}
				{/if}
				<div class="content">
					<div class="notification-info">
						{$aNotification.message}
						{if isset($aNotification.custom_image)}
							{$aNotification.custom_image}
						{/if}
					</div>
					<div class="time">
						{$aNotification.time_stamp|convert_time}
					</div>
				</div>
			</a>
			
			<div class="notification-delete s-3">
				<a href="#" class="js_hover_title noToggle remove-btn" onclick="$.ajaxCall('notification.delete', 'id={$aNotification.notification_id}'); return false;">
					<span class="ico ico-close"></span>
					<span class="js_hover_info">
						{_p var='delete_this_notification'}
					</span>
				</a>
			</div>
		</div>
	</li>
	{/foreach}
</ul>
{else}
<div class="empty-message">
	<img src="{param var='core.path_actual'}PF.Site/flavors/material/assets/images/empty-notify.svg" alt="">
    {_p var='no_new_notifications'}
</div>
{/if}