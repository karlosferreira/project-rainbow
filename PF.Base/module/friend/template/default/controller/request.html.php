<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: request.html.php 1129 2009-10-03 12:42:56Z phpFox LLC $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
<div class="main_break"></div>
{_p var='in_order_to_view_this_item_posted_by_user_link_you_need_to_be_on_their_friends_list' user=$aUser}
{if Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'friend.send_request')}
    <ul class="action">
        <li><a href="#?call=friend.request&amp;user_id={$aUser.user_id}&amp;width=420&amp;height=250" class="inlinePopup" title="Add to Friends">{_p var='send_a_friends_request_to_full_name' full_name=$aUser.full_name}</a></li>
    </ul>
{/if}