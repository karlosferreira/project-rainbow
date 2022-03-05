<?php
defined('PHPFOX') or exit('NO DICE!');

?>
{foreach from=$aPlans item=aPlan}
{if defined('PHPFOX_NO_WINDOW_CLICK')}
{if $aPlan.sSizes !== false}
<div class="sample">
    {if $aPlan.is_cpm}
    {_p var='better_ads_block_location_cost_cpm_1_000_views' location=$sBlockLocation cost=$aPlan.default_cost|currency}
    {else}
    {_p var='better_ads_block_location_cost_ppc' location=$sBlockLocation cost=$aPlan.default_cost|currency}

    {/if}
    <div class="extra_info">
        ({$aPlan.sSizes})
    </div>
</div>
{/if}
{else}
<div class="extra_info">
    ({$aPlan.sSizes})
</div>
{/if}
{/foreach}