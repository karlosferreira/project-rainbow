<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="p-saveditems-dropdown-addto-collection-popup-content js_add_to_collection_container dont-unbind-children js_saved_alert_item" data-target="saved_alert_item_{$savedId}">
    <div class="p_saveditems_error_add_to_collection_{$savedId} hide"></div>
    <div class="p_saveditems_noti_add_to_collection_{$savedId} hide"></div>
   <div class="p-saveditems-detail-saved-alert">
       <div>
           {_p var ='saveditems_your_item_has_been_successfully_saved'}
       </div>
       <div class="js_p_saveditems_action_collection_toggle item-action-toggle">
           {_p var ='saveditems_add_this_item_to_your_collections'} <i class="ico ico-caret-down"></i>
       </div>
   </div>
   <div class="js_p_saveditems_wrapper_collection_toggle hide">
        <div class="p_saveditems_quick_list_collection">
            <div class="p_saveditems_quick_list_collection_wrapper js_quick_list_collection {if empty($collections)}hide{/if}">
                {foreach from=$collections item=collection}
                <div class="item-collection-checkbox">
                    <div class="checkbox p-saveitems-checkbox-custom">
                        <label data-id="{$savedId}" data-collection="{$collection.collection_id}" onclick="appSavedItem.addItemToCollection(this);">
                            <input type="checkbox"/><i class="ico ico-square-o mr-1"></i><div class="item-text">{$collection.name|clean}</div>
                        </label>
                    </div>
                    <a class="item-view" href="{url link='saved.collection.'$collection.collection_id}" target="_blank" onclick="return;">{_p var='view'}</a>
                </div>
                {/foreach}
            </div>
            {if empty($collections)}
            <span class="no-collections">{_p var='saveditems_no_collections_found'}</span>
            {/if}
        </div>
        {if Phpfox::getUserParam('saveditems.can_create_collection')}
        <div class="p_saveditems_quick_list_collection-title">
            <a onclick="appSavedItem.processFormAddCollection(event, this); return false;">
                {_p var='saveditems_add_to_new_collection'}
                <i class="pull-right ico ico-angle-down" aria-hidden="true"></i>
            </a>
        </div>
        <div class="p_saveditems_quick_list_collection-title-form">
            {template file='saveditems.block.collection.quick-form'}
        </div>
        {/if}
    </div>
    <div class="p_saveditems_recent_saved_form">
        <a class="item-recent-saved" href="{url link='saved'}" title="{_p var='saveditems_recent_saved_items'}" target="_blank" onclick="return;">{_p var='saveditems_recent_saved_items'}</a>
        <button class="btn btn-default btn-sm" onclick="js_box_remove(this);">{_p var='close'}</button>
    </div>
</div>

{literal}
<script>
    $Ready(function() {
        if($('.p-saveditems-dropdown-addto-collection-popup-content').length > 0){
            $('.p-saveditems-dropdown-addto-collection-popup-content').closest('.js_box').addClass('p-saveditems-dropdown-addto-collection-popup');
        }
    });
</script>
{/literal}