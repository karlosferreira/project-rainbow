<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: list.html.php 5382 2013-02-18 09:48:39Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="my-subscription-search">
    <form id="list-package-filter" method="post" action="{url link='subscribe.list'}">
        <div class="form-group">
            <label>{_p var='subscribe_package_title'}</label>
            <select class="form-control" name="val[title]">
                <option value="" {if empty($aSearchData.title)}selected="true"{/if}>{_p var='any'}</option>
                {foreach from=$aPurchases item=aPurchase}
                    <option value="{$aPurchase.title}" {if !empty($aSearchData.title) && $aSearchData.title == $aPurchase.title}selected="true"{/if} >{$aPurchase.title_parse}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <label>{_p var='subscribe_subscription_status'}</label>
            <select class="form-control" name="val[status]">
                <option value="" {if empty($aSearchData.status)}selected="true"{/if}>{_p var='any'}</option>
                {foreach from=$aStatuses item=statustext key=statusvalue}
                    <option value="{$statusvalue}" {if !empty($aSearchData.status) && $aSearchData.status == $statusvalue}selected="true"{/if}>{$statustext}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group">
            <button class="btn btn-primary" type="submit" value="submit" name="submit">{_p var='search'}</button>
        </div>
    </form>
</div>
{plugin call='subscribe.template_controller_list__1'}
{if count($aFilters)}
    {plugin call='subscribe.template_controller_list__2'}
        <div class="my-subscription-items">
            <ul class="my-subscription-list">
                {foreach from=$aFilters item=aPurchase name=purchases}
                    {plugin call='subscribe.template_controller_list__3'}
                        {template file='subscribe.block.advance-entry'}
                    {plugin call='subscribe.template_controller_list__4'}
                {/foreach}
            </ul>
        </div>
    {pager}
{else}
    {plugin call='subscribe.template_controller_list__5'}
    <div class="extra_info">
        {_p var='no_subscriptions_found'}
    </div>
    {plugin call='subscribe.template_controller_list__6'}
{/if}
{plugin call='subscribe.template_controller_list__7'}