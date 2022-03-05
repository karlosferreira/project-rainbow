<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !$inDropdown}
<div class="item-addto {if $isListingPage}js_saved_alert_item{/if}" {if $isListingPage}data-target="saved_alert_item_{$savedId}"{/if}>
    <div class="dropdown p-saveditems-dropdown-addto-collection js_add_to_collection_container">
{/if}
        {if !$inDropdown}
        <a role="button" data-toggle="dropdown" class="item-addto-btn js_add_to_collection_btn {if $isListingPage && !$inDropdown}btn btn-default btn-sm{/if}">
            {if $isListingPage}
                <i class="ico ico-folder-plus-o"></i><span class="p-saveditems-btn-text">{_p var='saveditems_collection'}</span>
            {else}
            {_p var='saveditems_add_to_collection'}
            {/if}
        </a>
        {/if}
        <ul class="dropdown-menu dropdown-menu-right">
            <li class="dropdown-header">
                {_p var='saveditems_add_to_collection'}
            </li>
            <li class="p_saveditems_error_add_to_collection_{$savedId} hide"></li>
            <li class="p_saveditems_noti_add_to_collection_{$savedId} hide"></li>
            <li class="p_saveditems_quick_list_collection">
                <div class="p_saveditems_quick_list_collection_wrapper js_quick_list_collection {if empty($collections)}hide{/if}">
                    {foreach from=$collections item=collection}
                    <div class="item-collection-checkbox">
                        <div class="checkbox p-saveitems-checkbox-custom">
                            <label data-id="{$savedId}" data-collection="{$collection.collection_id}" data-feed="{if !empty($feedId)}{$feedId}{/if}" onclick="return appSavedItem.addItemToCollection(this);">
                                <input type="checkbox" {if !empty($collectionIds) && in_array($collection.collection_id,$collectionIds)}checked{/if}/><i class="ico ico-square-o mr-1"></i><div class="item-text">{$collection.name|clean}</div>
                            </label>
                        </div>
                        <a class="item-view" href="{url link='saved.collection.'$collection.collection_id}" target="_blank" onclick="return;">{_p var='view'}</a>
                    </div>
                    {/foreach}
                </div>
                {if empty($collections)}
                <span class="no-collections">{_p var='saveditems_no_collections_found'}</span>
                {/if}
            </li>
            {if Phpfox::getUserParam('saveditems.can_create_collection')}
            <li class="p_saveditems_quick_list_collection-title">
                <a onclick="appSavedItem.processFormAddCollection(event, this); return false;">
                    {_p var='saveditems_add_to_new_collection'}
                    <i class="pull-right ico ico-angle-down" aria-hidden="true"></i>
                </a>
            </li>
            <li class="p_saveditems_quick_list_collection-title-form">
                {template file='saveditems.block.collection.quick-form'}
            </li>
            {/if}
        </ul>
{if !$inDropdown}
    </div>
</div>
{/if}
