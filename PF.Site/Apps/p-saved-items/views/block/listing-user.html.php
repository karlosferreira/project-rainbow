<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="user-listing-item">
    <div class="item-outer">
        <div class="item-media">
            {img user=$aUser suffix='_120_square' max_width=50 max_height=50}
        </div>
        <div class="item-name">
            {$aUser|user}
        </div>
        {if \Phpfox::getUserId() != $aUser.user_id && !Phpfox::getUserBy('profile_page_id') && !$aUser.profile_page_id}
        <div class="item-actions">
            {if $bIsOwner && $aFriend.user_id != Phpfox::getUserId()}
            <button class="btn btn-danger btn-xs btn-round"
                    onclick="$Core.jsConfirm({l}{r}, function(){l} $.ajaxCall('saveditems.removeFriendFromCollection', 'friend_id={$aFriend.user_id}&amp;collection_id={$aFriend.collection_id}'); {r}, function(){l}{r}); return false;"
            >{_p var='saveditems_remove_friend'}</button>
            {/if}
        </div>
        {/if}
    </div>
</div>
