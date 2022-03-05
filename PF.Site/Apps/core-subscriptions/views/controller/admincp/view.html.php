<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: list.html.php 1339 2009-12-19 00:37:55Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="panel panel-default core-subscriptions-admincp-subscribe-view">
    <div class="panel-heading">
        <div class="panel-title">{_p var='subscribe_subscription_detail'}: {$aPurchase.purchase_id}</div>
    </div>
    <div class="panel-body">
        <div class="form-group">
            <span class="title"><b>{_p var='user'}: </b></span><span>{$aPurchase|user}</span>
        </div>
        <div class="form-group">
            <span class="title"><b>{_p var='subscribe_package_title'}: </b></span><span><a href="{url link='admincp.subscribe.add' id={$aPurchase.package_id}" target="_blank">{$aPurchase.title_parsed}</a></span>
        </div>
        <div class="form-group">
            <span class="title"><b>{_p var='subscribe_subscription_status'}: </b></span>
            <span>
                {if $aPurchase.status == 'completed'}
                {_p var='sub_active'}
                {elseif $aPurchase.status == 'cancel'}
                {_p var='canceled'}
                {elseif $aPurchase.status == 'pending'}
                {_p var='pending_payment'}
                {elseif $aPurchase.status == 'expire'}
                {_p var='expired'}
                {else}
                {_p var='pending_action'}
                {/if}
            </span>
        </div>
        <div class="form-group">
            <span class="title"><b>{_p var='subscribe_activation_date'}: </b></span>
            <span>{$aPurchase.time_stamp|convert_time}</span>
        </div>
        {if (int)$aPurchase.recurring_period > 0}
        <div class="form-group">
            <span class="title"><b>{_p var='recurring_period'}: </b></span>
            <span>{$aPurchase.type}</span>
        </div>
        <div class="form-group">
            <span class="title"><b>{_p var='subscribe_next_payment'}: </b></span>
            <span>{$aPurchase.expiry_date|convert_time}</span>
        </div>
        {else}
        <div class="form-group">
            <span class="title"><b>{_p var='subscribe_expiration_date'}: </b></span>
            <span>{_p var='never_expire_subscription'}</span>
        </div>
        {/if}
    </div>
    <div id="admincp-recent-payment" class="table-responsive admincp-recent-payment panel-body">
        <div class="form-group title">{_p var='subscribe_recent_payments'}</div>
        {if count($aRecentPayments)}
        <table class="table table-condensed">
            <thead>
            <tr>
                <th>{_p var='subscribe_purchase_date'}</th>
                <th class="text-center">{_p var='subscribe_amount'}</th>
                <th class="text-center">{_p var='subscribe_payment_method'}</th>
                <th class="text-center">{_p var='subscribe_transaction_id'}</th>
                <th class="text-center">{_p var='subscribe_payment_status'}</th>
            </tr>
            </thead>
            <tbody>
            {foreach from=$aRecentPayments item=aRecentPayment}
            <tr>
                <td>{$aRecentPayment.time_stamp|convert_time}</td>
                <td class="t_center">{$aRecentPayment.total_paid|currency:$aRecentPayment.currency_id}</td>
                <td class="t_center">{$aRecentPayment.payment_method}</td>
                <td class="t_center">{$aRecentPayment.transaction_id}</td>
                {if $aRecentPayment.status == "completed"}
                <td class="completed t_center">{_p var='sub_active'}</td>
                {elseif $aRecentPayment.status == "cancel"}
                <td class="cancel t_center">{_p var='canceled'}</td>
                {elseif $aRecentPayment.status == "expire"}
                <td class="cancel t_center">{_p var='expired'}</td>
                {elseif $aRecentPayment.status == "pending"}
                <td class="pending t_center">{_p var='pending'}</td>
                {/if}
            </tr>
            {/foreach}
            </tbody>
        </table>
        {pager}
        {else}
        <div class="alert alert-danger">
            {_p var='subscribe_recent_payments_not_found'}
        </div>
        {/if}
    </div>
</div>

