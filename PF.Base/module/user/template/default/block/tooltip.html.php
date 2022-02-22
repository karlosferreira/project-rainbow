<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="user_tooltip_image">
	{img user=$aUser suffix='_120_square' max_width=50 max_height=50}
</div>
<div class="user_tooltip_info">
	{plugin call='user.template_block_tooltip_1'}
  <a href="{$aUser.profile_link}" class="user_tooltip_info_user">{$aUser.full_name|clean}</a>
	{plugin call='user.template_block_tooltip_3'}

	{if $bIsPage}
		<ul>
			<li>{$aUser.page.category_name|convert}</li>
			<li>
				{if $aUser.page.page_type == '1'}
					{if $aUser.page.total_like == 1}
						{_p var='1_member'}
					{elseif $aUser.page.total_like > 1}
						{_p var='total_members' total=$aUser.page.total_like|number_format}{/if}
				{else}
					{if $aUser.page.total_like == 1}
						{_p var='1_person_likes_this'}
					{elseif $aUser.page.total_like > 1}
						{_p var='total_people_like_this' total=$aUser.page.total_like|number_format}
					{/if}
				{/if}
			</li>
		</ul>
	{else}
		<ul>
			{if Phpfox::getParam('user.display_user_online_status') && $aUser.is_online}
			<li class="user_is_online" title="{_p var='online'}"><i class="fa fa-circle js_hover_title"></i></li>
			{/if}
			{if $aUser.gender_name}
			<li>{$aUser.gender_name}</li>
			{/if}
            {if !empty($aUser.birthdate_display) }
			<li>
			{foreach from=$aUser.birthdate_display key=sAgeType item=sBirthDisplay}
				{if $aUser.dob_setting == '2'}
                    {if $sBirthDisplay == 1}
                        {_p var='1_year_old'}
                    {else}
                        {_p var='age_years_old' age=$sBirthDisplay}
                    {/if}
				{else}
					{if $aUser.dob_setting != '3'}
						{$sBirthDisplay}
					{/if}
				{/if}
			{/foreach}
			</li>
            {/if}
			{if $aUser.location}
			<li>{$aUser.location}</li>
			{/if}
		</ul>
		{if $iMutualTotal > 0}
		<div class="user_tooltip_mutual">
			<a href="#" onclick="$Core.box('friend.getMutualFriends', 300, 'user_id={$aUser.user_id}'); return false;">{_p var='mutual_friends_total' total=$iMutualTotal}</a>
			<div class="block_listing_inline">
				<ul>			
				{foreach from=$aMutualFriends item=aMutual}
					<li class="js_hover_title">{img user=$aMutual suffix='_120_square' max_width=32 max_height=32}
                        <span class="js_hover_info">{$aMutual.full_name}</span>
                    </li>
				{/foreach}
				</ul>
				<div class="clear"></div>
			</div>
		</div>
		{/if}
		{plugin call='user.template_block_tooltip_5'}
	{/if}
	
	{plugin call='user.template_block_tooltip_2'}
	
</div>
{if $aUser.user_id != Phpfox::getUserId() && !$bIsPage}
    <div class="user_tooltip_action">
        <ul>
            {if empty($aUser.is_ignore_request) && !$aUser.is_friend && !$bLoginAsPage && Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'friend.send_request')}
                <li><a href="#" onclick="$(this).closest('.js_user_tool_tip_holder').hide();return $Core.addAsFriend('{$aUser.user_id}');" title="{_p var='add_to_friends'}">{_p var='add_as_friend'}</a></li>
            {/if}
            {if $bCanSendMessage}
                <li><a href="#" onclick="$Core.composeMessage({left_curly}user_id: {$aUser.user_id}{right_curly});$(this).closest('.js_user_tool_tip_holder').hide(); return false;">{_p var='send_message'}</a></li>
            {/if}
            {if $bShowBDay == true}
                <li><a href="{url link=$aUser.user_name}">{_p var='birthday_wishes'}</a></li>
            {/if}
        </ul>
    </div>
{/if}
