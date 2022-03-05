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

<li class="my-subscription-list-wapper">
    <div class="item-title fw-bold mb-2"><a href="{url link='subscribe.view'}?id={$aPurchase.purchase_id}">{$aPurchase.title_parse}</a></div>
    <div class="my-subscription-list-inner">
        <a class="item-media" href="{url link='subscribe.view'}?id={$aPurchase.purchase_id}">
            {if !empty($aPurchase.image_path)}
                {img server_id=$aPurchase.server_id path='subscribe.url_image' file=$aPurchase.image_path suffix='_120' max_width='120' max_height='120'}
            {else}
                {img server_id=0 path='subscribe.app_url' file=$sDefaultPhoto max_width='120' max_height='120'}
            {/if}
        </a>
        <div class="item-body">
            {template file='subscribe.block.entry-info'}
            {if !empty($aPurchase.is_active)}
                {if $aPurchase.status == 'pending'}
                    <button class="btn btn-primary" onclick="tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;purchase_id={$aPurchase.purchase_id}&amp;renew_type={$aPurchase.renew_type}'));">{_p var='pay_now'}</button>
                {elseif (int)$aPurchase.renew_type == 2 && ($aPurchase.status == 'completed') &&  !empty($aPurchase.show_renew)}
                    <button class="btn btn-primary" onclick="tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;purchase_id={$aPurchase.purchase_id}&amp;renew_type=2'));">{_p var='subscribe_renew'}</button>
                {elseif empty($aPurchase.status)}
                    <button class="btn btn-primary" onclick="tb_show('{_p var='select_payment_gateway' phpfox_squote=true}', $.ajaxBox('subscribe.upgrade', 'height=400&amp;width=400&amp;purchase_id={$aPurchase.purchase_id}&amp;renew_type={$aPurchase.renew_type}'));">{_p var='upgrade'}</button>
                {/if}
            {/if}
        </div>
    </div>
</li>