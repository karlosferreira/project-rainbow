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
{literal}
<style rel="stylesheet">
    .cancel-reason
    {
        padding: 0 20px;
    }
    .row
    {
        margin-bottom: 5px;
    }
</style>
{/literal}
<div class="cancel-reason">
    <div class="row">
        <span style="margin-right: 5px;"><b>{_p var='subscribe_subscription_id'}: </b></span> <span>{$aPurchase.purchase_id}</span>
    </div>
    <div class="row">
        <span style="margin-right: 5px;"><b>{_p var='subscribe_cancel_on'}: </b></span> <span>{$aPurchase.time_stamp|convert_time}</span>
    </div>
    <div class="row" style="word-break: keep-all; word-wrap: break-word">
        <span style="margin-right: 5px;"><b>{_p var='reason'}: </b></span> <span>{$aReason.title_parsed}</span>
    </div>
    <div class="row t_center">
        <button class="btn btn-success" onclick="js_box_remove(this)" style="width: 80px;">{_p var='subscribe_ok'}</button>
    </div>
</div>
