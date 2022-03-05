<?php 
/**
 * [PHPFOX_HEADER]
 *
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{if count($aInvites)}
    <div class="item-event-member-block">
        <div class="item-event-member-list popup-user-total-container popup-user-with-btn-container">
            {foreach from=$aInvites name=friends item=aFriend}
            <div class="mutual-friend-item popup-user-item">
                {module name='user.listing-item' user_id=$aFriend.user_id}
            </div>
            {/foreach}
            {pager}
        </div>
    </div>
{else}
    <div class="help-block p-2">
        {_p var='no_guests_found'}
    </div>
{/if}