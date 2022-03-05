{if count($aFriends)}
<div>
    {if !$bIsPaging}
    <div class="js_friend_mutual_container popup-user-total-container popup-user-with-btn-container">
        {/if}
        {foreach from=$aFriends name=friends item=aFriend}
        <div class="mutual-friend-item popup-user-item" id="js_collection_{$aFriend.collection_id}_friend_{$aFriend.user_id}">
            {module name='saveditems.listing-user' user_id=$aFriend.user_id}
        </div>
        {/foreach}
        {if $hasPagingNext}
        {pager}
        {/if}
        {if !$bIsPaging}
    </div>
    {/if}
</div>
{else}
<div>
    <p>{_p var='saveditems_there_is_no_friend_in_this_collection'}</p>
</div>
{/if}
