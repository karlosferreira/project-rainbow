
<div class="js_poll_feed_item core-feed-item poll-app feed {if empty($aPoll.image_path)}no-photo{/if} {if !Phpfox::getUserParam('poll.poll_can_change_own_vote') && $aPoll.user_voted_this_poll}cannot-change-answer{/if}" id="js_poll_feed_item_{$aPoll.poll_id}">
	<div class="item-wrapper">
		<div class="item-outer-info">
			<div class="item-inner">
				<a href="{if $bIsSponsorFeed && $iSponsorId}{url link='ad.sponsor' view=$iSponsorId}{else}{permalink id=$aPoll.poll_id module='poll' title=$aPoll.question}{/if}" class="core-feed-title line-1">{$aPoll.question|clean}</a>
				
				<div class="core-feed-description line-3 item_view_content">{$aPoll.description|stripbb|feed_strip|split:55}</div>
			</div>
			{if !empty($aPoll.image_path)}
			<div class="item-media">
		            <a class="item-media-bg" href="{if $bIsSponsorFeed && $iSponsorId}{url link='ad.sponsor' view=$iSponsorId}{else}{permalink id=$aPoll.poll_id module='poll' title=$aPoll.question}{/if}"
		               style="background-image: url({img server_id=$aPoll.server_id path='poll.url_image' file=$aPoll.image_path suffix='' return_url=true})">
		            </a>
			</div>
	        {/if}
		</div>
		<div class="item-outer-action">
            <form class="poll_form" method="post" action="{url link='current'}" id="js_poll_form_{$aPoll.poll_id}">
                <input type="hidden" name="val[is_feed]" value="true">
                <input type="hidden" name="val[poll_id]" value="{$aPoll.poll_id}">
                <div class="action-vote-list">
                    {foreach from=$aPoll.answer item=aAnswer}
                        {if $aPoll.is_multiple}
                            <div class="checkbox corepoll-checkbox-custom item-answer {if $aAnswer.voted}chosen{/if}">
                                <label >
                                    {if (Phpfox::getUserId() != $aPoll.user_id) || (Phpfox::getUserId() == $aPoll.user_id && Phpfox::getUserParam('poll.can_vote_in_own_poll'))}
                                        <input type="checkbox" name="val[poll_{$aPoll.poll_id}_answer][]" value="{$aAnswer.answer_id}" {if $aAnswer.voted}checked="true"{/if} {if $aAnswer.voted && !Phpfox::getUserParam('poll.poll_can_change_own_vote')}disabled="true"{/if}>
                                        <i class="ico ico-square-o mr-1"></i>
                                    {/if}
                                    <span>{$aAnswer.answer}</span>
                                </label>
                            </div>
                        {else}
                            <div class="radio corepoll-radio-custom item-answer {if $aAnswer.voted}chosen{/if}">
                                <label >
                                    {if (Phpfox::getUserId() != $aPoll.user_id) || (Phpfox::getUserId() == $aPoll.user_id && Phpfox::getUserParam('poll.can_vote_in_own_poll'))}
                                        <input type="radio" name="val[poll_{$aPoll.poll_id}_answer][]" value="{$aAnswer.answer_id}" {if $aAnswer.voted}checked{/if} {if $aAnswer.voted && !Phpfox::getUserParam('poll.poll_can_change_own_vote')}disabled="true"{/if}>
                                        <i class="ico ico-circle-o mr-1"></i>
                                    {/if}
                                    <span>{$aAnswer.answer}</span>
                                </label>
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <div class="action-vote-bottom">
                    {if ($iVotedByUser > 3 && $aPoll.user_voted_this_poll) || (count($aPoll.answer) > 3 && (!$aPoll.user_voted_this_poll || ($aPoll.user_voted_this_poll && Phpfox::getUserParam('poll.poll_can_change_own_vote'))))}
                    <a class="item-show-more js_feed_poll_more_answer">
                        {_p var='poll_show_all_options'} <i class="ico ico-angle-down"></i>
                    </a>
                    {/if}
                    <div class="poll-group-submit">
                        <a href="javascript:void(0);" class="item-vote-number" {if $bCanViewVotes}onclick="$Core.box('poll.pageVotes', 900, 'poll_id={$aPoll.poll_id}'); return false;"{/if}>{$aPoll.total_votes|short_number} {if $aPoll.total_votes == 1}{_p('poll_total_vote')}{else}{_p('poll_total_votes')}{/if}</a>
                        {if (Phpfox::getUserId() != $aPoll.user_id) || (Phpfox::getUserId() == $aPoll.user_id && Phpfox::getUserParam('poll.can_vote_in_own_poll'))}
                            {if !$aPoll.user_voted_this_poll || ($aPoll.user_voted_this_poll && Phpfox::getUserParam('poll.poll_can_change_own_vote'))}
                            <div class="item-submit">
                                <button href="javascript:void(0);" class="btn btn-sm btn-default" onclick="$Core.poll.submitPoll({if !Phpfox::getUserParam('poll.poll_can_change_own_vote')}true{else}false{/if}, {$aPoll.poll_id}); return false;">{if !$aPoll.user_voted_this_poll}{_p var='poll_vote_now'}{else}{_p var='poll_vote_again'}{/if}</button>
                            </div>
                            {/if}
                        {/if}
                    </div>
                </div>
            </form>
		</div>
	</div>
</div>