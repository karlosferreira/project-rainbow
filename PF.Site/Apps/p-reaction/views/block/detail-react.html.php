<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if count($aUsersList)}
    {foreach from=$aUsersList name=user item=aUser}
        <div id="js_row_like_{$aUser.user_id}"  class="like-browse-item popup-user-item">
            {template file='preaction.block.user-row'}
        </div>
    {/foreach}
    {if $hasPagingNext}
        {pager}
    {/if}
{/if}