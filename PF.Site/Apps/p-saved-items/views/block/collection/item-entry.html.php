<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<article class="p-saveditems js_saved_collection" id="js_saved_collection_{$collection.collection_id}">
    <div class="p-saveditems-outer">
        <!-- image -->
        <div class="p-saveditems-media-wrapper">
            <a class="p-saveditems-media-link" href="{url link='saved.collection.'.$collection.collection_id}">
                <span class="p-saveditems-media-src" style="background-image: url({if !empty($collection.image_path)}{img server_id=$collection.image_server_id path='saveditems.url_pic' file=$collection.image_path return_url=true}{else}{$defaultPhoto}{/if})"></span>
            </a>
        </div>
        <div class="p-saveditems-inner">
            <!-- title -->
            <div class="p-saveditems-title">
                <a href="{url link='saved.collection.'.$collection.collection_id}" title="{$collection.name|clean}" class="js_collection_title">
                    {$collection.name|clean}
                    {if !empty($collection.privacy_icon_class)}
                    <span class="{$collection.privacy_icon_class}"></span>
                    {/if}
                </a>
                {if !empty($collection.canAction)}
                <div class="dropdown">
                    <span role="button" class="row_edit_bar_action" data-toggle="dropdown">
                        <i class="ico ico-gear-o"></i>
                    </span>
                    <ul class="dropdown-menu dropdown-menu-right">
                        {template file='saveditems.block.collection.link'}
                    </ul>
                </div>
                {/if}
            </div>
            <!-- minor info -->
            <div class="p-saveditems-minor-info">
                <p class="item-minor-info">{_p var='saveditems_created_by'} {$collection|user}</p>
                <p class="item-date">{_p var='saveditems_updated_on'} {$collection.updated_time|convert_time}</p>
                <p class="item-quantity">{$collection.total_item} {if $collection.total_item == 1}{_p var='item'}{else}{_p var='items'}{/if}</p>
            </div>
        </div>
    </div>
</article>
