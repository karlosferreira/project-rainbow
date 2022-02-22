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

<div class="item">
    <span class=status>{_p var='subscribe_subscription_id'}:</span>
    <span class="value">{$aPurchase.purchase_id}</span>
</div>
<div class="item">
    <span class=status>{_p var='subscribe_cost'}:</span>
    <span class="value">
        {if isset($aPurchase.default_cost) && $aPurchase.default_cost != 0}
            <span class="fw-bold price">
                {$aPurchase.default_cost|currency:$aPurchase.default_currency_id}
            </span>
            <span class="mb-0 recurring">
                {if !empty($aPurchase.default_recurring_cost)}
                    {$aPurchase.default_recurring_cost}
                {else}
                    {_p var='subscribe_one_time'}
                {/if}
            </span>
        {else}
            <span class="fw-bold price">
                {_p var='free'}
            </span>
            <span class="mb-0 recurring">
                {if !empty($aPurchase.default_recurring_cost)}
                    {$aPurchase.default_recurring_cost}
                {/if}
            </span>
        {/if}
    </span>
</div>
<div class="item">
    <span class=status>{_p var='membership'}:</span>
    <span class="value">
        {if $aPurchase.status == "completed"}
            {$aPurchase.s_title|convert|clean}
        {else}
            {$aPurchase.f_title|convert|clean}
        {/if}
    </span>
</div>
<div class="item">
    <span class=status>{_p var='status'}:</span>
    <span class="value">
        {if $aPurchase.status == 'completed'}
            <span class="active">{_p var='sub_active'}</span>
        {elseif $aPurchase.status == 'cancel'}
            <span class="cancel">{_p var='canceled'}</span>
        {elseif $aPurchase.status == 'pending'}
            <span class="pending-payment">{_p var='pending_payment'}</span>
        {elseif $aPurchase.status == 'expire'}
            <span class="expire">{_p var='expired'}</span>
        {else}
            <span class="pending-action">{_p var='pending_action'}</span>
        {/if}
    </span>
</div>
<div class="item">
    <span class=status>{_p var='subscribe_activation_date'}:</span>
    <span class="value">{$aPurchase.time_purchased|convert_time}</span>
</div>
<div class="item">
    <span class=status>{_p var='subscribe_expiration_date'}:</span>
    <span class="value">
        {if $aPurchase.recurring_period == 0}
            {_p var='no_expiration_date'}
        {else}
            {if !empty($aPurchase.expiry_date)}
                {$aPurchase.expiry_date|convert_time}
            {/if}
        {/if}
    </span>
</div>