<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: register.html.php 5382 2013-02-18 09:48:39Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{if !empty($bIsRegister)}
    {if $bSignUpRequired }
        <a class="btn btn-primary" id="subscription_change_pack" href="{$sPathSubscribe}">{_p var='change_package'}</a>
    {else}
        <a class="btn btn-primary" id="subscription_change_free" href="#">{_p var='free_package'}</a>
        <a class="btn btn-primary" id="subscription_change_pack" href="{$sPathSubscribe}">{_p var='change_package'}</a>
    {/if}
{/if}

{plugin call='subscribe.template_controller_register__1'}
{if empty($aPurchase.status)}
{plugin call='subscribe.template_controller_register__2'}
{module name='api.gateway.form'}
{plugin call='subscribe.template_controller_register__3'}
{else}
{plugin call='subscribe.template_controller_register__4'}
{if $aPurchase.status == 'pending'}
{plugin call='subscribe.template_controller_register__5'}
<div class="extra_info">
    {_p var='thank_you_for_your_purchase_your_payment_is_currently_pending_approval'}
</div>
{plugin call='subscribe.template_controller_register__6'}
{/if}
{/if}
{plugin call='subscribe.template_controller_register__7'}