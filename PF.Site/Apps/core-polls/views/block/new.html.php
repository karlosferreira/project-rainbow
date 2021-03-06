<?php
/**
 *
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package  		Poll
 *
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{if !count($aPolls)}
<div class="extra_info">
	{_p var='no_polls_have_been_added_yet'}
	<ul class="action">
		<li><a href="{url link='poll.add'}">{_p var='be_the_first_to_create_a_poll'}</a></li>
	</ul>
</div>
{else}
{foreach from=$aPolls name=polls item=aPoll}
<div class="{if is_int($phpfox.iteration.polls/2)}row1{else}row2{/if}{if $phpfox.iteration.polls == 1} row_first{/if}"{if $phpfox.iteration.polls == 1} style="padding-top:0px;"{/if}>
	{img user=$aPoll max_width=50 max_height=50 suffix='_50' class='v_middle'} <a href="{url link=''$aPoll.user_name'.poll.'$aPoll.question_url''}">{$aPoll.question|clean}</a>
	<div class="extra_info">
		{_p var='poll_created_on_time_stamp_by_user_info' time_stamp=$aPoll.time_stamp|date:'core.global_update_time' user_info=$aPoll|user}
	</div>
</div>
{/foreach}
{/if}