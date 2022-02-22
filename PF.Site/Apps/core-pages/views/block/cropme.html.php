<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div id="pages_crop_me" class="dont-unbind-children">
    <div class="image-editor">
        <form method="post" id="js_form_pages_crop_me" action="#" onsubmit="return Core_Pages.profilePhoto.submit(this);" dir="ltr">
            {if !empty($bAllowUploadPageProfilePhoto)}
                <input type="hidden" name="val[is_upload]" value="1">
                {module name='core.upload-form' type='pages' id=$aPageCropMe.page_id params=$uploadParams dropzone_class='custom-upload-photo'}
            {/if}
            <div class="cropit-preview"></div>
            <div class="rotate_button {if !empty($bAllowUploadPageProfilePhoto) && empty($aPageCropMe.image_path)}hide{/if}">
                <a class="js-pages-rotate-ccw btn"><i class="fa fa-undo" aria-hidden="true"></i></a>
                <a class="js-pages-rotate-cw btn"><i class="fa fa-repeat" aria-hidden="true"></i></a>
            </div>
            <input type="range" class="cropit-image-zoom-input {if !empty($bAllowUploadPageProfilePhoto) && empty($aPageCropMe.image_path)}hide{/if}"/>
            <input type="hidden" name="image-data" class="hidden-image-data" />
            <div><input type="hidden" name="val[crop-data]" value="" id="crop_it_form_image_data" /></div>
            <div><input type="hidden" name="val[page_id]" value="{$aPageCropMe.page_id}" /></div>
            <div class="rotate_button {if !empty($bAllowUploadPageProfilePhoto) && empty($aPageCropMe.image_path)}hide{/if}">
                {if !empty($bAllowUploadPageProfilePhoto)}
                    <button type="button" class="btn btn-default mr-1 js_submit_btn" onclick="return Core_Pages.profilePhoto.openUploadPopup(this);">{_p var='change_photo'}</button>
                {/if}
                <button type="submit" class="btn btn-primary js_submit_btn">{_p var="save"}</button>
            </div>
        </form>
    </div>
</div>

<script>
  Core_Pages.cropmeImgSrc = '{img server_id=$aPageCropMe.image_server_id path='pages.url_image' file=$aPageCropMe.image_path return_url=true time_stamp=1}';
  {if !empty($bAllowUploadPageProfilePhoto)}
    Core_Pages.profilePhoto.parentContainer = '#pages_crop_me';
      if ($('#' + Core_Pages.profilePhoto.holderId).length) {l}
        $('#' + Core_Pages.profilePhoto.holderId).addClass('no_delete');
      {r}
  {/if}
</script>