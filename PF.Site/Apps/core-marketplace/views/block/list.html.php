<?php 

 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{if !PHPFOX_IS_AJAX}
<div id="js_mp_item_holder" class="item-marketplace-member-list mp_item_holder">
{/if}
{if count($aInvites)}
    {foreach from=$aInvites name=invites item=aUser}
        {template file='user.block.rows'}
    {/foreach}
{else}
    <div class="extra_info px-2">
        {if $iType == 1}
            {_p var='no_visits'}
        {else}
            {_p var='no_results'}
        {/if}
    </div>
{/if}
{pager}
{if !PHPFOX_IS_AJAX}
</div>
{/if}