<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
{if !PHPFOX_IS_AJAX}
<div id="js_friend_suggestion_loader" style="display:none;"><i class="fa fa-spinner fa-spin"></i> {_p var='finding_another_suggestion'}</div>
<div id="js_friend_suggestion">
{/if}
{if !empty($aSuggestion)}
    <div class="user_rows_mini core-friend-block">
    {foreach from=$aSuggestion item=aSuggestionUser}
        <div class="user_rows">
            <div class="user_rows_image">
                {img user=$aSuggestionUser suffix='_120_square'}
            </div>
            <div class="user_rows_inner">
                {assign var='bInSuggestionsBlock' value=1}
                {$aSuggestionUser|user}
                {assign var=aUser value=$aSuggestionUser}
                {module name='user.friendship' friend_user_id=$aSuggestionUser.user_id type='icon' extra_info=true mutual_list=true}
                {unset var=$bInSuggestionsBlock}
            </div>
            <a class="item-hide" role="button" onclick="$('#js_friend_suggestion').hide(); $('#js_friend_suggestion_loader').show(); $.ajaxCall('friend.removeSuggestion', 'user_id={$aSuggestionUser.user_id}&amp;load=true'); return false;" title="{_p var='hide_this_suggestion'}"><span class="ico ico-close"></span></a>
        </div>
    {/foreach}
    </div>
{/if}
{if !PHPFOX_IS_AJAX}
</div>
{/if}
