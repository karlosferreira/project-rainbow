<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<form action="{url link='admincp.pstatusbg.add-collection'}" method="post" id="js_collection_form" class="p-statusbg-acp-collection">
    <input type="hidden" name="id" id="js_collection_id" value="{if $bIsEdit}{$iEditId}{else}0{/if}">
    {if $bIsEdit}
    <input type="hidden" name="val[title]" value="{$aForms.title}" />
    {/if}
    <input type="hidden" name="time_stamp" value="{$sTimeStamp}">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {$sTitle}
            </div>
        </div>
        <div class="panel-body">
            <div class="form-group">
                {field_language phrase='title' label='collection_name' field='title' format='val[title_' size=30 required=true help_phrase='if_the_name_is_empty_then_its_value_will_have_the_same_value_as_default_language' maxlength=32}
                <div class="help-block">
                    {_p var='the_name_of_collection_maximum_limit_characters' limit=32}
                </div>
            </div>
            <div class="form-group" style="max-width: 200px; {if $bIsEdit && $aForms.is_default}opacity: 0.5;pointer-events: none;{/if}">
                <label for="is_active">{_p var='is_default'}</label>
                <div class="item_is_active_holder" id="js_p_statusbg_is_default">
                    <span class="js_item_active item_is_active">
                        <input class='form-control' type="radio" name="val[is_default]" value="1" {value type='radio' id='is_default' default='1' }/> {_p var='yes'}
                    </span>
                    <span class="js_item_active item_is_not_active">
                        <input class='form-control' type="radio" name="val[is_default]" value="0" {value type='radio' id='is_default' default='0' selected='true'}/> {_p var='no'}
                    </span>
                </div>
            </div>
            <div class="help-block" id="js_p_statusbg_is_default_help" style="color: red; {if !isset($aForms) || !$aForms.is_default || ($bIsEdit && $aForms.is_default)}display: none;{/if}">
                {_p var='if_set_as_default_this_collection_is_activated_and_the_current_default_one_will_be_set_inactive'}
            </div>
            <div class="form-group" style="max-width: 200px; {if $bIsEdit && $aForms.is_default}opacity: 0.5;pointer-events: none;{/if}">
                <label for="is_active">{_p var='active'}</label>
                <div class="item_is_active_holder" id="js_p_statusbg_is_active">
                    <span class="js_item_active item_is_active">
                        <input class='form-control' type="radio" name="val[is_active]" value="1" {value type='radio' id='is_active' default='1' }/> {_p var='yes'}
                    </span>
                    <span class="js_item_active item_is_not_active">
                        <input class='form-control' type="radio" name="val[is_active]" value="0" {value type='radio' id='is_active' default='0' selected='true'}/> {_p var='no'}
                    </span>
                </div>
            </div>
            <div class="help-block" id="js_p_statusbg_is_active_help" style="color: red; display: none;">
                {_p var='you_cannot_activate_this_collection_because_the_maximum_number_of_active_collections_is_2'}
            </div>
            {if !isset($aForms) || !$aForms.view_id}
                <div class="form-group" style="border:1px solid #eee;padding: 8px;">
                    <label for="">{_p var='add_images'}</label>
                    {module name='core.upload-form' type='pstatusbg' params=$aForms.params}
                </div>
            {/if}
            <div id="js_list_backgrounds">
                {template file='pstatusbg.block.admin.list-backgrounds'}
            </div>
            <div class="help-block">
                {if !isset($aForms) || !$aForms.view_id}
                    {_p var='images_should_be_in_width_x_height_ratio_x_y_for_the_best_layout_max_size_of_each_image_is_size' width=1120 height=630 x=16 y=9 size='1Mb'}
                    <br/>
                    {_p var='the_first_image_is_used_as_collection_s_main_image_you_can_drag_drop_to_re_order'}
                {else}
                    {_p var='you_can_not_delete_this_collection_you_can_drag_drop_to_re_order'}
                {/if}
            </div>
        </div>
        <div class="panel-footer">
            <button type="button" class="btn btn-primary" id="js_collection_submit">{_p var='save_change'}</button>
            <a href="{url link='admincp.app' id='P_StatusBg'}" class="btn btn-default" id="js_collection_submit">{_p var='back'}</a>
        </div>
    </div>
</form>
{literal}
<script type="text/javascript">
    $Behavior.onLoadManageCollection = function() {
        $('.toolbar-top .btn-group').find('a.popup').removeClass('popup');
    }
</script>
{/literal}