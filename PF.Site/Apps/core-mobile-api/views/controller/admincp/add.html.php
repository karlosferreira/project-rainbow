<?php
defined('PHPFOX') or exit('NO DICE!');

?>
<link href="{param var='core.path_file'}static/jscript/colorpicker/css/colpick.css" rel="stylesheet">
{literal}
<style type="text/css">
    #core_mobile_add_menu_form div._colorpicker_holder {
        position: inherit;
        width: 30px;
        height: 30px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px;
    }
    #core_mobile_add_menu_form span._colorpicker_holder {
        position: inherit;
        font-size: 30px;
        display: inline-block;
        vertical-align: middle;
        border: none;
        margin-left: 10px;
    }
</style>
{/literal}
<form method="post" id="core_mobile_add_menu_form" action="{url link='admincp.mobile.add' edit=$iEditId}" onsubmit="$Core.onSubmitForm(this, true);">
    <div class="panel panel-default">
        <div class="panel-body">
            {if $bIsEdit}
            <div><input type="hidden" name="val[edit_id]" value="{$iEditId}" /></div>
            <div><input type="hidden" name="val[name]" value="{$aForms.name}" /></div>
            {/if}

            {field_language phrase='name' required=true label='name' field='name' format='val[name_' size=30 maxlength=100}

            <div class="form-group">
                <label>{_p var='icon_color'}</label>
                <input type="hidden" name="val[icon_color]" value="{if isset($aForms.icon_color)}{$aForms.icon_color}{else}#2681d5{/if}" data-rel="colorChooser" class="_colorpicker" />
                {if $bIsEdit && isset($aForms.icon_family) && $aForms.icon_family == 'Lineficon'}
                    <span class="ico ico-{$aForms.icon_name} _colorpicker_holder is_span"></span>
                {else}
                    <div class="_colorpicker_holder"></div>
                {/if}
            </div>
            <hr>
            <div class="form-group">
                <label>{_p var='allow_access'}</label>
                {foreach from=$aUserGroups item=aUserGroup}
                <div class="custom-checkbox-wrapper">
                    <label>
                        <input type="checkbox" name="val[allow_access][]" class="close_warning" value="{$aUserGroup.user_group_id}"{if isset($aAccess) && is_array($aAccess)}{if !in_array($aUserGroup.user_group_id, $aAccess)} checked="checked" {/if}{else} checked="checked" {/if}/>
                        <span class="custom-checkbox"></span>
                        {$aUserGroup.title|convert|clean}
                    </label>
                </div>
                {/foreach}
            </div>
        </div>
        <div class="panel-footer">
            <input type="submit" class="btn btn-primary" value="{if $bIsEdit}{_p var='update'}{else}{_p var='save'}{/if}" />
        </div>
    </div>
</form>