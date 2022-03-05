<?php
/**
 * [PHPFOX_HEADER]
 */

defined('PHPFOX') or exit('NO DICE!');
?>
<form class="form" method="post" action="{url link='admincp.groups.category'}">
    <div><input type="hidden" name="category_id" value="{$iCategoryId}"></div>
    <div><input type="hidden" name="is_sub" value="{$bIsSub}"></div>
    {if $iNumberOfChildren > 0 || $iNumberOfSubCategories > 0}
        {if count($aAllTypes) > 1}
            {_p var='choose_action_to_do_with_child_groups'}
            <div class="form-group">
                <div class="radio">
                    <label>
                        <input type="radio" name="child_action" value="del" onclick="$('#new_category_id').attr('disabled', true);" checked>
                        {if $bIsSub}
                            {_p var="remove_all_groups_belonging_to_this_category"}
                        {else}
                            {_p var="remove_all_groups_and_sub_categories_belonging_to_this_category"}
                        {/if}
                    </label>
                </div>
                <div class="radio">
                    <label>
                        <input type="radio" name="child_action" value="move" onclick="$('#new_category_id').attr('disabled', false);">
                        {if $bIsSub}
                            {_p var="select_another_category_to_move_all_groups_belonging_to_this_category"}
                        {else}
                            {_p var="select_another_category_to_move_all_groups_and_sub_categories_belonging_to_this_category"}
                        {/if}
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="new_category_id">{_p var="move_to"}</label>
                <select name="new_category_id" class="form-control" id="new_category_id" disabled>
                    {foreach from=$aAllTypes item=aType}
                        {if $aType.type_id != $iCategoryId || $bIsSub}
                            <option value="{$aType.type_id}">{_p var=$aType.name}</option>
                        {/if}
                        {if $bIsSub && count($aType.categories)}
                            {foreach from=$aType.categories item=aSubCategory}
                                {if $aSubCategory.category_id != $iCategoryId}
                                    <option value="{$aSubCategory.category_id}_sub">-- {_p var=$aSubCategory.name}</option>
                                {/if}
                            {/foreach}
                        {/if}
                    {/foreach}
                </select>
            </div>
        {else}
            <input type="hidden" name="child_action" value="del">
            <p class="help-block">{_p var='delete_category_notice_groups'}</p>
        {/if}
    {else}
        <p class="help-block">{_p var='are_you_sure'}</p>
    {/if}
    <div class="form-group">
        <input type="submit" value="{_p var='delete'}" class="btn btn-danger" name="delete">
        <input type="button" onclick="return js_box_remove(this);" class="btn" value="{_p var='cancel'}">
    </div>
</form>