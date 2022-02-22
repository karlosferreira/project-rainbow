<?php
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{if isset($aForms.server_id)}
    <div><input type="hidden" name="val{if isset($aForms.photo_id)}[{$aForms.photo_id}]{/if}[server_id]" value="{$aForms.server_id}" /></div>
{/if}

<div class="form-group {if !Phpfox::getParam('photo.photo_show_title')}hide{/if}">
    <label for="title">{required}{_p var='title'}</label>
    <input id="title" type="{if Phpfox::getParam('photo.photo_show_title')}text{else}hidden{/if}" name="val{if isset($aForms.photo_id)}[{$aForms.photo_id}]{/if}[title]" value="{if isset($aForms.title)}{$aForms.title|clean}{else}{value type='input' id='title'}{/if}" size="30" maxlength="150" onfocus="this.select();" class="form-control close_warning" required/>
</div>
<div class="form-group item-description">
    <label for="{if isset($aForms.photo_id)}{$aForms.photo_id}{/if}description">{_p var='description'}</label>
    <textarea rows="4" id="{if isset($aForms.photo_id)}{$aForms.photo_id}{/if}description" name="val{if isset($aForms.photo_id)}[{$aForms.photo_id}]{/if}[description]" class="form-control close_warning">{if isset($aForms.description)}{$aForms.description|clean}{else}{value type='input' id='description'}{/if}</textarea>
</div>
<div class="item-categories-inner">
    {if (!$aForms.module_id
    || ($aForms.module_id == 'groups' && Phpfox::getParam('photo.display_photo_album_created_in_group'))
    || ($aForms.module_id == 'pages' && Phpfox::getParam('photo.display_photo_album_created_in_page')))}
        {if Phpfox::getService('photo.category')->hasCategories()}
            <div class="form-group  js_core_init_selectize_form_group">
                <label for="">{_p var='category'}</label>
                <div class="table_right js_category_list_holder">
                    {if isset($aForms.photo_id)}<div class="js_photo_item_id" style="display:none;">{$aForms.photo_id}</div>{/if}
                    {if isset($aForms.category_list)}<div class="js_photo_active_items" style="display:none;">{$aForms.category_list}</div>{/if}
                    {module name='photo.drop-down'}
                </div>
            </div>
        {/if}
    {/if}
</div>
{if Phpfox::isModule('tag') && Phpfox::getParam('tag.enable_tag_support')}
    {if isset($aForms.photo_id)}
        {module name='tag.add' sType='photo' separate=false tag_id=$aForms.photo_id bCloseWarning=true}
    {else}
        {module name='tag.add' sType='photo' separate=false bCloseWarning=true}
    {/if}
{/if}
{if $aForms.can_add_mature}
    <div class="form-group item-mature">
        <label for="">{_p var='mature_content'}</label>
        <div>
            <label class="mr-3">
                <input type="radio" class="close_warning" name="val{if isset($aForms.photo_id)}[{$aForms.photo_id}]{/if}[mature]" value="2" {if isset($aForms) && $aForms.mature == 2} checked {/if}>
                <i class="ico ico-circle-o mr-1"></i>
                {_p var='yes_strict'}
            </label>
            <label class="mr-3">
                <input type="radio" class="close_warning" name="val{if isset($aForms.photo_id)}[{$aForms.photo_id}]{/if}[mature]" value="1" {if isset($aForms) && $aForms.mature == 1} checked {/if}>
                <i class="ico ico-circle-o mr-1"></i>
                {_p var='yes_warning'}
            </label>
            <label >
                <input type="radio" class="close_warning" name="val{if isset($aForms.photo_id)}[{$aForms.photo_id}]{/if}[mature]" value="0" {if isset($aForms) && $aForms.mature == 0} checked {/if}>
                <i class="ico ico-circle-o mr-1"></i>
                {_p var='no'}
            </label>
        </div>
    </div>
{/if}
<div class="form-group">
    <div class="checkbox">
        <label><input type="checkbox" class="close_warning" name="val[{$aForms.photo_id}][allow_download]" value="1" {value type='checkbox' id='allow_download' default=1}/> {_p var='download_enabled'}</label>
    </div>
    <p class="help-block" style="padding-left: 0; padding-right: 0">
        {_p var='enabling_this_option_will_allow_others_the_rights_to_download_this_photo'}
    </p>
</div>