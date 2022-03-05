<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{if count($aUsers)}
    {foreach from=$aUsers item=aUser}
        <div class="item-user">
            {$aUser.full_name}
        </div>
    {/foreach}
{/if}
{if $iRemainUser}
<div class="item-user">{_p var='and_total_more' total=$iRemainUser|short_number}</div>
{/if}
