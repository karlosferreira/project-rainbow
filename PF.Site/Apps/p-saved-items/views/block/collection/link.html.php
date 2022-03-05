<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !empty($searchCollection)}
{assign var=collection value=$searchCollection}
{/if}
{if $collection.isOwner}
    {if $collection.canEdit}
    <li>
       <a class="action" href="javascript:void(0);" onclick="tb_show('{_p var='saveditems_new_collection'}', $.ajaxBox('saveditems.showCreateCollectionPopup','collection_id={$collection.collection_id}{if $isCollectionDetail}&detail=1{/if}'));">
           <span class="ico ico-pencilline-o mr-1"></span>{_p var='edit'}
       </a>
    </li>
    {/if}

    {if $collection.canDelete}
    <li class="item-delete">
        <a class="action" href="javascript:void(0);" onclick="return appSavedItem.deleteCollection({$collection.collection_id});">
            <span class="ico ico-trash-o mr-1"></span>{_p var='delete'}
        </a>
    </li>
    {/if}

    <li>
        <a class="action" href="javascript:void(0);" onclick="tb_show('{_p var='saveditems_add_friend'}', $.ajaxBox('saveditems.addFriend', 'collection_id={$collection.collection_id}')); return false;">
            <span class="ico ico-user2-plus-o mr-1"></span>{_p var='saveditems_add_friend'}
        </a>
    </li>
{/if}

<li>
    <a class="action" href="javascript:void(0);" onclick="tb_show('{_p var='saveditems_list_friend'}', $.ajaxBox('saveditems.showFriendListPopup', 'collection_id={$collection.collection_id}')); return false;">
        <span class="ico ico-list-o mr-1"></span>{_p var='saveditems_view_member_list'}
    </a>
</li>

{if !$collection.isOwner}
<li>
    <a class="action" href="javascript:void(0);"
       onclick="$Core.jsConfirm({l}{r}, function(){l} $.ajaxCall('saveditems.removeFriendFromCollection', 'friend_id={$aGlobalUser.user_id}&amp;collection_id={$collection.collection_id}&amp;is_leave=1'); {r}, function(){l}{r}); return false;">
        <span class="ico ico-close mr-1"></span>{_p var='saveditems_leave_collection'}
    </a>
</li>
{/if}

{if !empty($searchCollection)}
{unset var=$collection}
{/if}
