<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{literal}
<style type="text/css">
    #p_reaction_add_reaction ._colorpicker_holder {
        position: inherit;
        width: 64px;
        height: 32px;
        border: 1px solid rgba(0, 0, 0, 0.1);
        display: inline-block;
        vertical-align: middle;
        margin-left: 10px;
    }
    #p_reaction_add_reaction .mb-1{
        margin-bottom: 8px;
    }
</style>
{/literal}
<form action="{url link='current'}" enctype="multipart/form-data" method="post" id="p_reaction_add_reaction" onsubmit="$Core.onSubmitForm(this, true);">
    {if $bIsEdit}
        <input type="hidden" name="val[title]" value="{$aForms.title}" />
        <input type="hidden" name="id" value="{$iEditId}" />
    {/if}
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {$sTitle}
            </div>
        </div>
        <div class="panel-body">
            <div class="form-group">
                {field_language phrase='title' label='react_title' field='title' format='val[title_' size=30 required=true help_phrase='if_the_title_is_empty_then_its_value_will_have_the_same_value_as_default_language' maxlength=12}
                <div class="help-block">
                    {_p var='limit_12_characters'}
                </div>
            </div>
            <div class="form-group">
                <label>{required}{_p var='react_color'}</label>
                <input type="hidden" name="val[color]" value="{if isset($aForms.color)}{$aForms.color}{else}2681D5{/if}" data-rel="colorChooser" class="_colorpicker" />
                <div class="_colorpicker_holder"></div>
            </div>
            <div class="form-group">
                <label for="">{required}{_p var='icon'}</label>
                {if !empty($aForms.full_path)}
                <div class="mb-1">
                    <img src="{$aForms.full_path}" alt="" width="64px">
                </div>
                {/if}
                <input type="file" class="form-control" id="icon" name="icon"/>
                <div class="help-block">
                    {_p var='choose_an_icon_for_this_react_suggested_image_should_be_round_and_64_64_pixels_in_resolution'}
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button name="val[submit]" class="btn btn-primary">{_p var='save__u'}</button>
            <a class="btn btn-default" href="{url link='admincp.app' id='P_Reaction'}">{_p var='back'}</a>
        </div>
    </div>
</form>

{literal}
<script type="text/javascript">
    $Behavior.onLoadAddReaction = function() {
        var btnAdd = $('.toolbar-top .btn-group').find('a.popup');
        {/literal}
        {if $iTotalReaction >= 12}
        btnAdd.hide();
        {/if}
        {literal}
        btnAdd.removeClass('popup');
    }
</script>
{/literal}