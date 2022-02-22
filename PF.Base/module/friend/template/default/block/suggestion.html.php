<?php 
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: suggestion.html.php 3770 2011-12-13 11:34:29Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{if !PHPFOX_IS_AJAX}
<div id="js_friend_suggestion_loader" style="display:none;">{img theme='ajax/small.gif' class='v_middle'} {_p var='finding_another_suggestion'}</div>
<div id="js_friend_suggestion">
{/if}
{if !empty($aSuggestion)}
    {foreach from=$aSuggestion item=aSuggestionUser}
        <div class="row1 row_first row_title hover_action">
            <div class="row_title_image">
                {img user=$aSuggestionUser suffix='_120_square' max_width=50 max_height=50}
            </div>
            <div class="row_title_info">
                {$aSuggestionUser|user}
                <div class="extra_info">
                    {if Phpfox::getService('user.privacy')->hasAccess('' . $aSuggestionUser.user_id . '', 'friend.send_request')}
                        <a href="#?call=friend.request&amp;user_id={$aSuggestionUser.user_id}&amp;width=420&amp;height=250&amp;suggestion=true" class="inlinePopup" title="{_p var='add_to_friends'}">{_p var='add_to_friends'}</a>
                        -
                    {/if}
                    <a href="#" onclick="$('#js_friend_suggestion').hide(); $('#js_friend_suggestion_loader').show(); $.ajaxCall('friend.removeSuggestion', 'user_id={$aSuggestionUser.user_id}&amp;load=true'); return false;" title="{_p var='hide_this_suggestion'}">{_p var='hide'}</a>
                </div>
            </div>
        </div>
    {/foreach}
{/if}
{if !PHPFOX_IS_AJAX}
</div>
{/if}