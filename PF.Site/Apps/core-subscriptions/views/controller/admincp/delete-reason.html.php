<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: add.html.php 4554 2012-07-23 08:44:50Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="panel panel-default core-subscriptions-admincp-delete-reason">
    <div class="panel-heading">
        <div class="panel-title">{_p var='subscribe_delete_reason'}</div>
    </div>
    <div class="panel-body">
        <form id="core-subscriptions-admincp-delete-reason" method="post" action="{url link='admincp.subscribe.delete-reason'}">
            <div><input type="hidden" name="val[reason_id]" value="{$iReasonId}"></div>

            <div class="form-group alert alert-empty">
                {_p var='subscribe_are_you_sure_to_delete_reason'}
            </div>
            <div class="form-group">
                <div class="title mb-1">
                    {_p var='subscribe_select_an_action_to_all_cancel_subscriptions_of_this_reason'}
                </div>
                <div class="selection">
                    <div class="delete-option">
                        <label>
                            <input type="radio" name="val[option]" value="1" checked="checked">
                            {_p var='subscribe_move_all_cancel_subscriptions_to_default_reason'}
                        </label>

                    </div>
                    {if count($aReasonOptions)}
                    <div class="delete-option">
                        <label>
                            <input type="radio" name="val[option]" value="2">
                            {_p var='subscribe_select_another_reason_for_all_cancel_subscriptions'}
                        </label>

                    </div>
                    {/if}
                </div>
            </div>
            {if count($aReasonOptions)}
            <div class="form-group extra-option">
                <select name="val[extra_option]" class="form-control">
                    {foreach from=$aReasonOptions item=aOption}
                    <option value="{$aOption.reason_id}">{$aOption.title_parsed}</option>
                    {/foreach}
                </select>
            </div>
            {/if}
            <div class="form-group">
                <button type="submit" class="btn btn-success">{_p var='delete'}</button>
                {if !empty($isAjaxPopup)}
                <button class="btn btn-default" onclick="js_box_remove(this); return false;">{_p var='cancel'}</button>
                {/if}
            </div>
        </form>
    </div>
</div>