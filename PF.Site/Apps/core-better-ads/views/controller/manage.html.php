<?php 
    defined('PHPFOX') or exit('NO DICE!');
?>

{if $bNewPurchase && user('better_ad_campaigns_must_be_approved_first')}
    <div class="message">
    	{_p var='better_ads_thank_you_for_your_purchase_your_ad_is_currently_pending_approval'}
    </div>
{/if}

<div class="bts-filter-form pt-1">
    <form method="get" action="{url link='current'}" class="ml--1 mr--1">
        <div class="form-group px-1 d-inline-block shink-2">
            <label>{_p var='better_ads_from'}</label>
            {select_date start_year='2000' end_year='+100' prefix='from_' name='search'}
        </div>
        <div class="form-group px-1 d-inline-block shink-2">
            <label>{_p var='To'}</label>
            {select_date start_year='2000' end_year='+100' prefix='to_' name='search'}
        </div>
        <div class="form-group px-1 d-inline-block shink-1">
            <label>{_p var='placement'}</label>
            <select name="search[location]" class="form-control">
                <option value="0" {value type='select' id='location' default="0"}>{_p var='all_placements'}</option>
                {foreach from=$aPlacements item=aPlacement}
                    <option value="{$aPlacement.plan_id}" {value type='select' id='location' default="{$aPlacement.plan_id}"}>{$aPlacement.title}</option>
                {/foreach}
            </select>
        </div>
        <div class="form-group px-1 d-inline-block shink-1">
            <label>{_p var='status'}</label>
            <select name="search[is_custom]" class="form-control">
                <option value="0" {value type='select' id='is_custom' default="0"}>{_p var='all_status'}</option>
                <option value="upcoming" {value type='select' id='is_custom' default='upcoming'}>{_p var='upcoming'}</option>
                <option value="running" {value type='select' id='is_custom' default='running'}>{_p var='running'}</option>
                <option value="5" {value type='select' id='is_custom' default='5'}>{_p var='completed'}</option>
                <option value="ended" {value type='select' id='is_custom' default='ended'}>{_p var='ended'}</option>
                <option value="1" {value type='select' id='is_custom' default="1"}>{_p var='unpaid'}</option>
                <option value="2" {value type='select' id='is_custom' default="2"}>{_p var='pending'}</option>
                <option value="4" {value type='select' id='is_custom' default="4"}>{_p var='denied'}</option>
            </select>
        </div>
        <div class="form-group button-wapper px-1">
            <button type="submit" class="btn btn-primary">{_p var='submit'}</button>
        </div>
    </form>
</div>

<div class="mt-1 table-responsive bts-table">
    <table class="table bts-table-manage">
        <thead>
            <tr>
                <th colspan="2" class="text-uppercase fz-12 text-gray-dark bts-table__ads">{_p var='ads'}</th>
                <th class="text-uppercase fz-12 text-gray-dark text-center">{_p var='placement'}</th>
                <th class="text-uppercase fz-12 text-gray-dark bts-table__sort cursor-point" {table_sort asc="ads.start_date asc" desc="ads.start_date desc" current=$sCurrentSort query="sort"}>{_p var='start_date'}<span class="bts-table__sort-wapper"><i class="ico ico-caret-up"></i><i class="ico ico-caret-down"></i></span></th>
                <th class="text-uppercase fz-12 text-gray-dark px-3 bts-table__stt">{_p var='better_ads_status'}</th>
                <th class="text-uppercase fz-12 text-gray-dark bts-table__sort bts-table__view cursor-point" {table_sort asc="ads.count_view asc" desc="ads.count_view desc" current=$sCurrentSort query="sort"}>{_p var='view'}<span class="bts-table__sort-wapper"><i class="ico ico-caret-up"></i><i class="ico ico-caret-down"></i></span></th>
                <th class="text-uppercase fz-12 text-gray-dark bts-table__sort bts-table__view cursor-point" {table_sort asc="ads.count_click asc" desc="ads.count_click desc" current=$sCurrentSort query="sort"}>{_p var='better_ads_clicks'}<span class="bts-table__sort-wapper"><i class="ico ico-caret-up"></i><i class="ico ico-caret-down"></i></span></th>
                <th class="text-uppercase fz-12 text-gray-dark text-center">{_p var='better_ads_active'}</th>
            </tr>
        </thead>
        <tbody>
        {foreach from=$aAllAds name=ads item=aAd}
            <tr>
                <td class="w-1 align-middle">
                    <div class="dropdown hidden-sm hidden-xs">
                        <span role="button" data-toggle="dropdown" class="btn s-4 bts-table__setting text-gray-dark"><i class="ico ico-gear-o"></i></span>
                        <ul class="dropdown-menu">
                            <li><a href="{url link='ad.add' id=$aAd.ads_id}"><i class="ico ico-pencilline-o"></i>{_p var='edit'}</a></li>
                            <li class="item-delete"><a href="{url link='ad.manage' delete=$aAd.ads_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_ad_permanently'}"><i class="ico ico-trash-o"></i> {_p var='delete'}</a></li>
                        </ul>
                    </div>
                </td>
                <td class="align-middle bts-table__ads-title">
                    <a href="{url link='ad.report' ads_id=$aAd.ads_id}">{$aAd.name|clean|convert}</a>
                </td>
                <td class="align-middle text-center">{$aAd.location_name}</td>
                <td class="align-middle bts-table__sort-content">{$aAd.date}</td>
                <td class="align-middle px-3">{$aAd.status}</td>
                <td class="align-middle">{$aAd.count_view|intval}</td>
                <td class="align-middle">{$aAd.count_click|intval}</td>
                <td class="text-center align-middle">
                    <div class="privacy-block-content">
                        <div class="item_is_active_holder {if $aAd.is_active}item_selection_active{else}item_selection_not_active{/if}">
                            <a class="js_item_active item_is_active js_item_active_link" href="#?call=ad.updateAdActivityUser&amp;id={$aAd.ads_id}&amp;active=1" title="{_p var='better_ads_continue_this_campaign'}">{_p var='yes'}</a>
                            <a class="js_item_active item_is_not_active js_item_active_link" href="#?call=ad.updateAdActivityUser&amp;id={$aAd.ads_id}&amp;active=0" title="{_p var='better_ads_pause_this_campaign'}">{_p var='no'}</a>
                        </div>
                    </div>
                </td>
            </tr>
        {foreachelse}
        <tr>
            <td colspan="5">
                <div class="extra_info">
                    {_p var='better_ads_no_ads_found'}
                </div>
            </td>
        </tr>
        {/foreach}
        </tbody>
    </table>
</div>
{pager}