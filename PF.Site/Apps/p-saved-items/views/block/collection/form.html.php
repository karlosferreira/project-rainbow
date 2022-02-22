<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="p-saveditems-create-collection-popup js_create_collection_container">
    <div class="alert alert-danger hide js_error"></div>
    <div class="item-collection-form">
        <div class="form-group">
            <label for="">{_p var='saveditems_collection_name'}</label>
            <input type="text" class="form-control js_saveditems_collection_title_input" placeholder="{_p var='saveditems_maximum_128_characters'}" maxlength="128" value="{value type='input' id='name'}" data-id="{$savedId}" {if !empty($aForms.collection_id)}data-collection="{$aForms.collection_id}"{/if} {if $isCollectionDetail}data-detail="1"{/if}>
        </div>
        {if Phpfox::isModule('privacy')}
        <div id="js_custom_privacy_input_holder">
            {module name='privacy.build' privacy_item_id=$aForms.collection_id privacy_module_id='saveditems_collection'}
        </div>
        {/if}
        <div class="form-group">
            <label>{_p var='privacy'}</label>
            {module name='privacy.form' privacy_name='privacy' saved_app=1}
        </div>
    </div>
    <div class="item-collection-action">
        <div class="item-buttons-wrapper">
            <button class="btn btn-default btn-sm" onclick="js_box_remove(this);">{_p var='cancel_uppercase'}</button>
            <button class="btn btn-primary btn-sm" onclick="appSavedItem.createCollection(this);" {if !empty($aForms.collection_id)} data-collection="{$aForms.collection_id}"{/if} {if $isCollectionDetail}data-detail="1"{/if}>{if !empty($aForms)}{_p var='edit'}{else}{_p var='create'}{/if}</button>
        </div>
    </div>
</div>
