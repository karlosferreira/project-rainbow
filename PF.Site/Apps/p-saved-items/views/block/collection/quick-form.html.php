<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="p-saveditems-quick-add-form hide js_create_collection_container" data-target="collection_form_{$savedId}" onclick="appSavedItem.stopPropagation(event, this);">
    <div class="alert alert-danger hide js_error"></div>
    <input type="text" name="val[title]" class="p_saveditems_quick_add_collection_input input-sm form-control js_saveditems_collection_title_input" data-id="{$savedId}"/>
    <div class="p-saveditems-quick-add-form-btn">
        <button class="btn btn-primary btn-sm" onclick="appSavedItem.createCollection(this);" data-id="{$savedId}" {if !empty($feedId)}data-feed="{$feedId}"{/if} {if !empty($keepPopup)}data-keeppopup="1"{/if}><span>{_p var='create'}</span></button>
        <button class="btn btn-default btn-sm btn-cancel" onclick="appSavedItem.cancelCreateCollection({$savedId});">
            <span>{_p var='cancel_uppercase'}</span>
        </button>
    </div>
</div>
