<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<form action="{url link='admincp.comment.add-sticker-set'}" method="post" id="js_sticker_set_form" class="comment-acp-sticker">
    <input type="hidden" name="id" id="js_sticker_set_id" value="{if $bIsEdit}{$iEditId}{else}0{/if}">
    <div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {$sTitle}
            </div>
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label for="">{required}{_p var='sticker_set_title'}</label>
                {if !isset($aForms) || !$aForms.view_only}
                    <input class="form-control" type="text" id="js_sticker_set_title" name="val[title]" value="{value type='input' id='title'}">
                {else}
                    &nbsp;{$aForms.title|clean}
                {/if}
            </div>
            {if !isset($aForms) || !$aForms.view_only}
                <div class="form-group" style="border:1px solid #eee;padding: 8px;">
                    <label for="">{_p var='add_stickers'}</label>
                    <div class="pt-2">
                        {module name='core.upload-form' type='comment' params=$aForms.params}
                    </div>
                </div>
            {/if}
            <div id="js_list_stickers">
                {template file='comment.block.admin.list-stickers'}
            </div>
            <div class="help-block">
                {if !isset($aForms) || !$aForms.view_only}
                    {_p var='maximum_48_stickers_recommend_size_80_80_the_first_one_is_displayes_as_thumbnail_of_sticker_set_you_can_drag_drop_to_re_order'}
                {else}
                    {_p var='you_can_not_edit_or_delete_this_sticker_you_can_drag_drop_to_re_order'}
                {/if}
            </div>
        </div>
        {if !isset($aForms) || !$aForms.view_only}
            <div class="panel-footer">
                <button type="submit" class="btn btn-primary" id="js_sticker_set_submit">{_p var='save_change'}</button>
            </div>
        {/if}
    </div>
</form>
<div class="panel panel-default">

</div>
