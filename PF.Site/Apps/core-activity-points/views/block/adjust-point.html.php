<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="core-activitypoint__adjust-point-block" id="core-activitypoint__adjust_member_points_block">

    {if !$bDisableAll && Phpfox::getUserParam('activitypoint.can_admin_adjust_activity_points')}
        <input type="hidden" id="js_member_list" value="{$sUserId}">
        <input type="hidden" id="js_maximum_point_for_reduce" value="{$sMaximumPointsForReduce}">
        <input type="hidden" id="js_maximum_point_for_send" value="{$iPointsCanSent}">

        <div class="form-group hide_it" id="js_adjust_point_block_error"></div>
        <div class="form-group">
            {_p var='activitypoint_choose_action_method'}
        </div>
        <div class="form-group">
            {if !empty($iSentMaximumPoints) && !empty($iPointsCanSent)}
            <label>
                <input type="radio" name="point-action" value="send" class="mr-3" data-phrase="{_p var='activitypoint_send_point'}" checked>
                {_p var='activitypoint_send_point'}
            </label>
            {/if}
            {if !empty($sMaximumPointsForReduce)}
            <label>
                <input type="radio" name="point-action" value="reduce" data-phrase="{_p var='activitypoint_reduce_point'}" {if empty($iSentMaximumPoints) || empty($iPointsCanSent)}checked{/if} >
                {_p var='activitypoint_reduce_point'}
            </label>
            {/if}
        </div>
        <div class="form-group">
            <div class="title">
                {_p var='activitypoint_how_many_points'}
            </div>
            <div class="points">
                <input type="number" class="form-control" id="js_point_number" value="1" min="1" step="1" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
            </div>
            <p class="js_maximum_points">
                {if !empty($iSentMaximumPoints) && !empty($iPointsCanSent)}
                    <span class="js_point_title">{_p var='activitypoint_notify_maximum_point_for_send'}</span> <span id="point-number">{$iPointsCanSent|number_format}.</span>
                {elseif !empty($sMaximumPointsForReduce)}
                    <span class="js_point_title">{_p var='activitypoint_notify_maximum_point_for_reduce'}</span> <span id="point-number">{$sMaximumPointsForReduce|number_format}.</span>
                {/if}
            </p>
        </div>
        <div class="form-group js_selected_members">
            <div class="title">
                {_p var='activitypoint_to'}
            </div>
            <div class="members">
                {foreach from=$aUsers item=aUser}
                    <div class="core-activitypoint__adjust-point-block-members js_member_item" id="js_member_{$aUser.user_id}" data-id="{$aUser.user_id}">
                        <div class="core-activitypoint__adjust-point-block-members-avatar">
                            {img user=$aUser suffix='_120_square' max_width=50 max_height=50}
                            <a href="javascript:void(0)" class="js_delete_member"><span class="ico ico-close"></span></a>
                        </div>
                        <div class="core-activitypoint__adjust-point-block-members-name">
                            {$aUser.full_name}
                        </div>
                    </div>
                {/foreach}
            </div>
        </div>
        <div class="form-group action-button">
            <button class="btn btn-primary" id="js_adjust_point_button">{if !empty($iSentMaximumPoints) && !empty($iPointsCanSent)}{_p var='activitypoint_send_point'}{elseif !empty($sMaximumPointsForReduce)}{_p var='Reduce'}{/if}</button>
            <button class="btn btn-default" onclick="js_box_remove(this);">{_p var='activitypoint_cancel'}</button>
        </div>
    {else}
        <div class="alert alert-empty">{_p var='activitypoint_can_not_adjust_points'}</div>
    {/if}
</div>
{literal}
<script type="text/javascript">
    $Behavior.core_activitypoint_adjust_point_block = function(){
        coreActivityPointActionsBlock.init();
    }
</script>
{/literal}
