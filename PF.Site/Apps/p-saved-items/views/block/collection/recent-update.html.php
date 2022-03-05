<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="p-saveditems-collection-recently-container">
    <div class="p-saveditems-collection-recently-wrapper ">
        <div class="p-saveditems-collection-recently-listing">
            {foreach from=$collections item=collection}
            <div class="item-collection">
                <a href="{url link='saved.collection.'.$collection.collection_id}"><i class="ico ico-folder-alt"></i>
                    <div class="item-name">{$collection.name|clean}</div>
                    <div class="total-count">{$collection.total_item|short_number}</div>
                </a>
            </div>
            {/foreach}
        </div>
    </div>
</div>

