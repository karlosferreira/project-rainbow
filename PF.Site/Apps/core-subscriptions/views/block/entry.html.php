<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: entry.html.php 1339 2009-12-19 00:37:55Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>

<div class="my-subscription-items">
    <ul class="my-subscription-list">
        <li class="my-subscription-list-wapper">
            <div class="my-subscription-list-inner">
                <a class="item-media" href="javascript:void(0);">
                    {if !empty($aPurchase.image_path)}
                        {img server_id=$aPurchase.server_id path='subscribe.url_image' file=$aPurchase.image_path suffix='_120' max_width='120' max_height='120'}
                    {else}
                        {img server_id=0 path='subscribe.app_url' file=$sDefaultPhoto max_width='120' max_height='120'}
                    {/if}
                </a>
                <div class="item-body">
                    {template file='subscribe.block.entry-info'}
                    <div class="item">
                        {if !empty($aPurchase.is_active)}
                            {if $aPurchase.status == 'pending'}
                                <button class="btn btn-primary mr-1" onclick="tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;purchase_id={$aPurchase.purchase_id}&amp;renew_type={$aPurchase.renew_type}'));">{_p var='pay_now'}</button>
                            {elseif (int)$aPurchase.renew_type == 2 && ($aPurchase.status == 'completed') &&  !empty($aPurchase.show_renew)}
                                <button class="btn btn-primary mr-1" onclick="tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;purchase_id={$aPurchase.purchase_id}&amp;renew_type=2'));">{_p var='subscribe_renew'}</button>
                            {elseif empty($aPurchase.status)}
                                <button class="btn btn-primary mr-1" onclick="tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;purchase_id={$aPurchase.purchase_id}&amp;renew_type={$aPurchase.renew_type}'));">{_p var='upgrade'}</button>
                            {/if}
                        {/if}
                        {if $aPurchase.status == 'completed'}
                            <button class="btn btn-danger" onclick="tb_show('{_p var='subscribe_cancel_subscription' phpfox_squote=true}', $.ajaxBox('subscribe.showPopupCancelSubscription', 'height=400&amp;width=600&amp;purchase_id={$aPurchase.purchase_id}'));">{_p var='cancel'}</button>
                        {/if}
                    </div>
                </div>
            </div>
        </li>
    </ul>
</div>

<div id="recent-payment" class="table-responsive recent-payment">
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
                    <td>{$aRecentPayment.total_paid|currency:$aRecentPayment.currency_id}</td>
                    <td>{$aRecentPayment.payment_method}</td>
                    <td>{$aRecentPayment.transaction_id}</td>
                    {if $aRecentPayment.status == "completed"}
                        <td class="completed">{_p var='sub_active'}</td>
                    {elseif $aRecentPayment.status == "cancel"}
                        <td class="cancel">{_p var='canceled'}</td>
                    {elseif $aRecentPayment.status == "pending"}
                        <td class="pending">{_p var='pending'}</td>
                    {/if}
                </tr>
            {/foreach}
        </tbody>
    </table>
    {pager}
</div>