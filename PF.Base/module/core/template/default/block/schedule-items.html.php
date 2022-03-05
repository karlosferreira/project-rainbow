<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !$bIsPaging}
<div class="js_manage_schedule_items" id="js_manage_schedule_items_container">
{/if}
{if isset($aScheduleItems) && count($aScheduleItems)}
    <div class="core-schedule-feed-manage-wrapper">
        {foreach from=$aScheduleItems item=aScheduleItem}
        <div id="js_schedule_item_holder_{$aScheduleItem.schedule_id}" class="core-schedule-feed-manage-item js_schedule_item">
            {template file='core.block.schedule-item'}
        </div>
        {/foreach}
    </div>
    <div class="help-block hide" id="js_no_schedule_item">{_p var='no_items_found'}</div>
    {pager}
{else}
    <div class="help-block">{_p var='no_items_found'}</div>
{/if}
{if !$bIsPaging}
</div>
{/if}