<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<form class="form" method="post" action="{url link='admincp.ad.placements'}">
    <div><input type="hidden" name="val[placement_id]" value="{$iPlacementId}"></div>
    {if $iAdsCount > 0}
        {if count($aAllPlacements) > 1}
            {_p var='choose_action_to_do_with_ads'}
            <div class="form-group">
                <div class="custom-radio-wrapper">
                    <label>
                        <input type="radio" name="val[child_action]" value="delete" onclick="$('#new_placement_id').attr('disabled', true);" checked>
                        <span class="custom-radio"></span>
                        {_p var="remove_all_ads_belonging_to_this_placement"}
                    </label>
                </div>
                <div class="custom-radio-wrapper">
                    <label>
                        <input type="radio" name="val[child_action]" value="move" onclick="$('#new_placement_id').attr('disabled', false);">
                        <span class="custom-radio"></span>
                        {_p var="select_another_placement_to_move_all_ads_belonging_to_this_placement"}
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label for="new_placement_id">{_p var="move_to"}</label>
                <select name="val[new_placement_id]" class="form-control" id="new_placement_id" disabled>
                    {foreach from=$aAllPlacements item=aPlacement}
                    {if $aPlacement.plan_id != $iPlacementId}
                    <option value="{$aPlacement.plan_id}">{_p var=$aPlacement.title}</option>
                    {/if}
                    {/foreach}
                </select>
            </div>
        {else}
        <p>{_p var='if_you_delete_this_placement_all_ads_belong_to_it_will_be_deleted_also'}</p>
        {/if}
    {else}
        <p>{_p var='are_you_sure_you_want_to_delete_this_placement'}</p>
    {/if}
    <div class="form-group">
        <input type="submit" value="{_p var='delete'}" class="btn btn-danger" name="val[delete]">
        <input type="button" onclick="return js_box_remove(this);" class="btn btn-default" value="{_p var='cancel'}">
    </div>
</form>
