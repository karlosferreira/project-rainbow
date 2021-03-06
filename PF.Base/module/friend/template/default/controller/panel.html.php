<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{$sScript}
{if $aFriends}
<ul class="panel_rows">
	{foreach from=$aFriends name=notifications item=aNotification}
	<li id="drop_down_{$aNotification.request_id}">
		<div href="{url link=$aNotification.user_name}" class="panel_row {if !$aNotification.is_seen} is_new{/if}">
			<div class="panel_rows_image">
				{img user=$aNotification max_width='50' max_height='50' suffix='_120_square'}
			</div>
			<div class="panel_rows_content">
				<div class="panel_focus">{$aNotification|user}</div>
				<div class="panel_rows_time">
					{if $aNotification.relation_data_id > 0}
                        <div class="extra_info_link">
                            <i class="fa fa-heart"></i> {_p var='relationship_request_for'} "{$aNotification.relation_name}"
                        </div>
					{else}
					    {$aNotification.mutual_friends.total} <span class="to-lower">{_p var='mutual_friends'}</span>
					{/if}
				</div>
				<div class="panel_action">
					{if $aNotification.relation_data_id > 0}
						<span onclick="$(this).parents('.drop_data_action').find('.js_drop_data_add').show(); {if $aNotification.relation_data_id > 0} $.ajaxCall('custom.processRelationship', 'relation_data_id={$aNotification.relation_data_id}&amp;type=accept&amp;request_id={$aNotification.request_id}'); {else} $.ajaxCall('friend.processRequest', 'type=yes&amp;user_id={$aNotification.user_id}&amp;request_id={$aNotification.request_id}&amp;inline=true'); {/if}event.stopPropagation();">{_p var='accept'}</span>
						<span class="deny" onclick="$(this).parents('.drop_data_action').find('.js_drop_data_add').show(); {if $aNotification.relation_data_id > 0} $.ajaxCall('custom.processRelationship', 'relation_data_id={$aNotification.relation_data_id}&amp;type=deny&amp;request_id={$aNotification.request_id}'); {else} $.ajaxCall('friend.processRequest', 'type=no&amp;user_id={$aNotification.user_id}&amp;request_id={$aNotification.request_id}&amp;inline=true'); {/if}event.stopPropagation();">{_p var='deny'}</span>
					{else}
                        <span onclick="$.ajaxCall('friend.processRequest', 'type=yes&amp;user_id={$aNotification.user_id}&amp;request_id={$aNotification.request_id}&amp;inline=true'); event.stopPropagation();">{_p var='accept'}</span>
                        <span onclick="$.ajaxCall('friend.processRequest', 'type=no&amp;user_id={$aNotification.user_id}&amp;request_id={$aNotification.request_id}&amp;inline=true'); event.stopPropagation();" class="deny">{_p var='deny'}</span>
					{/if}
				</div>
			</div>
		</div>
	</li>
	{/foreach}
</ul>
<div class="panel_actions clearfix">

</div>
{else}
<div class="message">
	{_p var='no_new_friend_requests'}
</div>
{/if}