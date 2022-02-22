<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !empty($itemCollections)}
    <span class="p-saveditems-seperate-dot-item p-saveditems-seperate-dot-item-responsive js_collection_item">{_p var='saveditems_saved_to_title'} <a href="{url link='saved.collection.'.$itemCollections.default.collection_id}">{$itemCollections.default.name}</a>{if $itemCollections.count > 0 } {_p var='and'}{/if}
    {if $itemCollections.count > 0}
    <div class="dropdown p-saveditems-view-saved-more js_collection_item">
        <a class="dropdown-toggle" data-toggle="dropdown">{_p var ='saveditems_saved_to_collection_and_more' number=$itemCollections.count}</a>
        <ul class="dropdown-menu dropdown-menu-right">
            {foreach from=$itemCollections.other_collections item=other_collection}
            <li><a href="{url link='saved.collection.'.$other_collection.collection_id}">{$other_collection.name}</a></li>
            {/foreach}
        </ul>
    </div>
    </span>
    {/if}
{/if}
