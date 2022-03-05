<?php
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
<div id="js_photo_block_detail" class="js_photo_block page_section_menu_holder">
	<form class="form" method="post" id="js_edit_album_form" action="{url link='photo.edit-album' id=$aForms.album_id}">
        {if Phpfox::isModule('privacy')}
            <div id="js_custom_privacy_input_holder_album">
                {module name='privacy.build' privacy_item_id=$aForms.album_id privacy_module_id='photo_album'}
            </div>
        {/if}
		{template file='photo.block.form-album'}
        <input type="submit" value="{_p var='update'}" class="btn btn-primary" />
	</form>
</div>

<div id="js_photo_block_photo" class="js_photo_block page_section_menu_holder" style="display:none;">
    {if count($aPhotos)}
        <form class="form" id="js_edit_album_photos_form" method="post" action="{url link='photo.edit-album.photo' id=$aForms.album_id}">
            <div class="clearfix item-photo-edit js_core_photos_sortable_album_photos dont-unbind-children" data-id="{$aForms.album_id}">
                {foreach from=$aPhotos item=aForms}
                    {template file='photo.block.edit-photo'}
                {/foreach}
            </div>
            <div class="photo_table_clear">
                <input type="submit" value="{_p var='save_changes'}" class="btn btn-primary" />
            </div>
        </form>
    {else}
        {_p var='no_photos_have_been_added_yet'}
    {/if}
</div>
