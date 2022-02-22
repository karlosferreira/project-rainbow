<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: upgrade.html.php 7107 2014-02-11 19:46:17Z Fern $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{if isset($bIsFree)}
<div class="extra_info">
    {_p var='your_membership_has_successfully_been_upgraded'}
    <ul class="action">
        <li><a href="{url link='subscribe.view' id=$iPurchaseId}">{_p var='view_your_subscription'}</a></li>
    </ul>
</div>
{elseif !empty($bIsFirstFree)}
<div class="extra_info">
    {_p var='subscribe_purchase_package_first_free_then_recurring' date=$sDateTitle}
    <ul class="action">
        <li><a href="{url link='subscribe.view' id=$iPurchaseId}">{_p var='view_your_subscription'}</a></li>
    </ul>
</div>
{else}
{module name='api.gateway.form' bIsThickBox=$bIsThickBox}
{/if}
