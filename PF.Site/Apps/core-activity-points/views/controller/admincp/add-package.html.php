<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{$sScript}
<div class="js_add_package_error hide"></div>
<div class="core-activitypoint__admincp-add-package" id="js_admincp_add_package">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">{if $bIsEdit}{_p var='activitypoint_edit_package'}{else}{_p var='activitypoint_create_new_package'}{/if}</div>
        </div>
        <div class="panel-body">
            <form id="js_admincp_add_package_form" method="post" action="{url link='admincp.activitypoint.add-package'}" enctype="multipart/form-data" onsubmit="return coreActivityAdmincpIndex.validateFieldForAddingPackage();">
                {if $bIsEdit}
                <div><input type="hidden" name="id" value="{$aForms.package_id}" /></div>
                {/if}
                {field_language phrase='sPhraseTitle' label='title' field='title' format='val[title][' size=40 maxlength=100 required=true}
                <div class="form-group">
                    <label for="image">{_p var='activitypoint_package_image'}</label>
                    {if $bIsEdit && !empty($aForms.image_path)}
                    <div id="js_activitypoint_image_holder">
                        {img server_id=$aForms.server_id title=$aForms.title path='activitypoint.url_image' file=$aForms.image_path suffix='_120' max_width='120' max_height='120'}
                        <p class="help-block">
                            <a href="#" onclick="$Core.jsConfirm({l}message: '{_p var='are_you_sure'}'{r}, function(){l} $('#js_activitypoint_image_holder').remove(); $('#js_activitypoint_upload_image').show(); $.ajaxCall('activitypoint.deleteImage', 'package_id={$aForms.package_id}'); {r}, function(){l}{r}); return false;">{_p var='change_this_image'}</a>
                        </p>
                    </div>
                    {/if}
                    <div id="js_activitypoint_upload_image"{if $bIsEdit && !empty($aForms.image_path)} style="display:none;"{/if}>
                        <input type="file" id="image" name="image" accept="image/*"/>
                        <p class="help-block">
                            {_p var='you_can_upload_a_jpg_gif_or_png_file'}
                        </p>
                    </div>
                </div>
                <div class="form-group">
                    <label for="points">
                        {required}{_p var='activitypoint_number_points'}
                    </label>
                    <input type="number" id="points" name="val[points]" value="{value type='input' id='points'}" class="form-control" min="1">
                </div>
                <div class="form-group js_add_package_currency">
                    <label for="">{required}{_p var='price'}</label>
                    {module name='core.currency' currency_field_name='val[price]'}
                </div>
                <div class="form-group">
                    <label for="is_active">{_p var='activitypoint_package_active'}</label>
                    <div class="item_is_active_holder">
                        <span class="js_item_active item_is_active"><input type="radio" id="is_active" name="val[is_active]" value="1" {value type='radio' id='is_active' default='1' selected='true'}/></span>
                        <span class="js_item_active item_is_not_active"><input type="radio" id="is_active" name="val[is_active]" value="0" {value type='radio' id='is_active' default='0'}/></span>
                    </div>
                </div>
                <div class="form-group">
                    <button class="btn btn-primary" id="js_btn_add_package">{_p var='activitypoint_save'}</button>
                    {if !empty($bIsAjaxPopup)}
                    <button class="btn btn-default" onclick="js_box_remove(this);return false;">{_p var='activitypoint_cancel'}</button>
                    {/if}
                </div>
            </form>
        </div>
    </div>
</div>

{literal}
<script type="text/javascript">
    $Behavior.admincp_add_package = function () {
        
    }
</script>
{/literal}
