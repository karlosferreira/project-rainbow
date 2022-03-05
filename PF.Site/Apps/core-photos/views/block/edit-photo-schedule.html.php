<?php

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="photo-app manage-photo" id="js_schedule_manage_photo">
    <div class="block">
        <input type="hidden" name="val[remove_photo]" class="close_warning" id="js_schedule_deleted_photo{$aForms.schedule_id}">
        <input type="hidden" name="val[new_photo]" class="close_warning" id="js_schedule_new_photo{$aForms.schedule_id}">
        {if count($aScheduleImages)}
            <div class="content item-container" id="js_schedule_list_photo">
                {foreach from=$aScheduleImages key=iKey item=aImage}
                    <article class="px-1 mb-2 js_schedule_photo js_schedule_photo_holder_{$aImage.image_id}">
                        <div class="item-outer">
                            <div class="item-media">
                                <a href="javascript:void(0)" style="background-image: url('{img server_id=$aImage.server_id path='photo.url_photo' file=$aImage.image_path suffix='_500' max_width='120' max_height='120' return_url=true}');"></a>
                                <span class="item-photo-delete" title="{_p var='delete_this_image_for_the_listing'}" data-image_id="{$aImage.image_id}" onclick="$Core.Photo.editSchedule.removePhoto('{$aImage.image_id}', {$aForms.schedule_id}); return false;">
                                    <i class="ico ico-close"></i>
                                </span>
                            </div>
                        </div>
                    </article>
                {/foreach}
            </div>
        {else}
            <div class="help-block">{_p var='no_photos_found'}</div>
        {/if}
        <div class="manage-photo-title mb-1">
            <a href="javascript:void(0)" id="js_schedule_upload_photo" onclick="$Core.Photo.editSchedule.toggleScheduleUploadSection(); return false;" class="btn btn-primary btn-sm">
                <i class="ico ico-upload-cloud"></i>&nbsp;{_p var='u_upload_more_photos'}
            </a>
        </div>
        <div class="photo-upload-schedule" style="display: none;">
            {module name='core.upload-form' type='photo_schedule' params=$aForms}
        </div>
    </div>
</div>
