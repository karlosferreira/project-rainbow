<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div id="groups_crop_me" class="dont-unbind-children">
    <div class="image-editor">
        <form method="post" id="js_form_groups_crop_me" action="#" onsubmit="return $Core.Groups.profilePhoto.submit(this);" dir="ltr">
            {if !empty($bAllowUploadGroupProfilePhoto)}
                <input type="hidden" name="val[is_upload]" value="1">
                {module name='core.upload-form' type='groups' id=$aGroupCropMe.page_id params=$uploadParams dropzone_class='custom-upload-photo'}
            {/if}
            <div class="cropit-preview"></div>
            <div class="rotate_button {if !empty($bAllowUploadGroupProfilePhoto) && empty($aGroupCropMe.image_path)}hide{/if}">
                <a class="js-groups-rotate-ccw btn"><i class="fa fa-undo" aria-hidden="true"></i></a>
                <a class="js-groups-rotate-cw btn"><i class="fa fa-repeat" aria-hidden="true"></i></a>
            </div>
            <input type="range" class="cropit-image-zoom-input {if !empty($bAllowUploadGroupProfilePhoto) && empty($aGroupCropMe.image_path)}hide{/if}"/>
            <input type="hidden" name="image-data" class="hidden-image-data" />
            <div><input type="hidden" name="val[crop-data]" value="" id="crop_it_form_image_data" /></div>
            <div><input type="hidden" name="val[page_id]" value="{$aGroupCropMe.page_id}" /></div>
            <div class="rotate_button {if !empty($bAllowUploadGroupProfilePhoto) && empty($aGroupCropMe.image_path)}hide{/if}">
                {if !empty($bAllowUploadGroupProfilePhoto)}
                    <button type="button" class="btn btn-default mr-1 js_submit_btn" onclick="return $Core.Groups.profilePhoto.openUploadPopup(this);">{_p var='change_photo'}</button>
                {/if}
                <button type="submit" class="btn btn-primary js_submit_btn">{_p var="Save"}</button>
            </div>
        </form>
    </div>
</div>

<script>
  {if !empty($aGroupCropMe.image_path)}
    $Core.Groups.cropmeImgSrc = '{img server_id=$aGroupCropMe.image_server_id path='pages.url_image' file=$aGroupCropMe.image_path return_url=true time_stamp=1}';
    {literal}
        $Behavior.crop_groups_image_photo = function() {
            $Core.Groups.initCropMe();
        };
    {/literal}
  {/if}
   {if !empty($bAllowUploadGroupProfilePhoto)}
     $Core.Groups.profilePhoto.parentContainer = '#groups_crop_me';
    if ($('#' + $Core.Groups.profilePhoto.holderId).length) {l}
      $('#' + $Core.Groups.profilePhoto.holderId).addClass('no_delete');
    {r}
   {/if}
</script>