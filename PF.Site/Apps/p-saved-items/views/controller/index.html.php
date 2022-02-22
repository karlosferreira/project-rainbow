<?php
?>
{if !PHPFOX_IS_AJAX}
    {if $searchByCollection}
    <div class="p-updated-status-container">
        <div>
            {_p var='saveditems_updated_on'} {$searchCollection.updated_time|convert_time}
            {if $searchCollection.total_item > 0}
            <span class="p-updated-status-item">{$searchCollection.total_item} {if $searchCollection.total_item == 1}{_p var='item'}{else}{_p var='items'}{/if}</span>
            {/if}
        </div>
        {if !empty($searchCollection.canAction)}
        <div class="dropdown p-dropdown-action">
            <a class="dropdown-toggle" data-toggle="dropdown"><i class="ico ico-gear-o"></i></a>
            <ul class="dropdown-menu dropdown-menu-right">
                {assign var=isCollectionDetail value=true}
                {template file='saveditems.block.collection.link'}
                {unset var=$isCollectionDetail}
            </ul>
        </div>
        {/if}
    </div>
    {/if}
    <div class="p-saveditems-listing-container">
{/if}
    {if !empty($items)}
        {foreach from=$items item=item}
            {template file='saveditems.block.item-entry'}
        {/foreach}

        {if $canPaging}
        {pager}
        {/if}
    {elseif !PHPFOX_IS_AJAX}
        <div class="extra_info">
            {_p var='saveditems_no_saved_items_found'}
        </div>
    {/if}

{if !PHPFOX_IS_AJAX}
    </div>
{/if}