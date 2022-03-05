<?php
    defined('PHPFOX') or exit('NO DICE!');
?>

<div class="bts-filter-form pt-1">
    <form method="get" action="{url link='current'}" class="ml--1 mr--1">
        <div class="form-group px-1 d-inline-block shink-2">
            <label>{_p var='date_from'}</label>
            {select_date start_year='2000' end_year='+100' prefix='from_' name='search'}
        </div>
        <div class="form-group px-1 d-inline-block shink-2">
            <label>{_p var='date_to'}</label>
            {select_date start_year='2000' end_year='+100' prefix='to_' name='search'}
        </div>
        <div class="form-group px-1 d-inline-block shink-1">
            <label>{_p var='status'}</label>
            <select name="search[is_custom]" class="form-control">
                <option value="0" {value type='select' id='is_custom' default="0"}>{_p var='all_status'}</option>
                <option value="running" {value type='select' id='is_custom' default='running'}>{_p var='running'}</option>
                <option value="upcoming" {value type='select' id='is_custom' default='upcoming'}>{_p var='upcoming'}</option>
                <option value="ended" {value type='select' id='is_custom' default='ended'}>{_p var='ended'}</option>
                <option value="1" {value type='select' id='is_custom' default="1"}>{_p var='unpaid'}</option>
                <option value="2" {value type='select' id='is_custom' default="2"}>{_p var='pending'}</option>
                <option value="4" {value type='select' id='is_custom' default="4"}>{_p var='denied'}</option>
            </select>
        </div>
        <div class="form-group px-1 d-inline-block shink-1">
            <label>{_p var='type'}</label>
            <select name="search[type]" class="form-control">
                <option value="0" {value type='select' id='type' default="0"}>{_p var='all'}</option>
                <option value="1" {value type='select' id='type' default="1"}>{_p var='sponsor_block'}</option>
                <option value="2" {value type='select' id='type' default="2"}>{_p var='in_feed'}</option>
            </select>
        </div>
        <div class="form-group button-wapper px-1">
            <button type="submit" class="btn btn-primary">{_p var='submit'}</button>
        </div>
    </form>
</div>
{if !empty($aAds)}
    <div class="mt-1 table-responsive bts-table">
        <table class="table bts-table-sponsor">
            <thead>
                <tr>
                    <th colspan="2" class="text-uppercase fz-12 text-gray-dark bts-table__ads">{_p var='ad'}</th>
                    <th class="text-uppercase fz-12 text-gray-dark bts-table__sort cursor-point" {table_sort asc="s.start_date asc" desc="s.start_date desc" current=$sCurrentSort query="sort"}>{_p var='start_date'}<span class="bts-table__sort-wapper"><i class="ico ico-caret-up"></i><i class="ico ico-caret-down"></i></span></th>
                    <th class="text-uppercase fz-12 text-gray-dark px-3 bts-table__stt">{_p var='better_ads_status'}</th>
                    <th class="text-uppercase fz-12 text-gray-dark">{_p var='type'}</th>
                    <th class="text-uppercase fz-12 text-gray-dark bts-table__sort bts-table__view cursor-point" {table_sort asc="s.total_view asc" desc="s.total_view desc" current=$sCurrentSort query="sort"}>{_p var='views'}<span class="bts-table__sort-wapper"><i class="ico ico-caret-up"></i><i class="ico ico-caret-down"></i></span></th>
                    <th class="text-uppercase fz-12 text-gray-dark bts-table__sort bts-table__view cursor-point" {table_sort asc="s.total_click asc" desc="s.total_click desc" current=$sCurrentSort query="sort"}>{_p var='better_ads_clicks'}<span class="bts-table__sort-wapper"><i class="ico ico-caret-up"></i><i class="ico ico-caret-down"></i></span></th>
                    <th class="text-uppercase fz-12 text-gray-dark text-center">{_p var='better_ads_active'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$aAds name=ads item=aAd}
                    <tr {if is_int($phpfox.iteration.ads/2)} class="on"{/if}>
                        <td class="w-1 align-middle">
                            <div class="dropdown hidden-sm hidden-xs">
                                <span role="button" data-toggle="dropdown" class="btn s-4 bts-table__setting text-gray-dark"><i class="ico ico-gear-o"></i></span>
                                <ul class="dropdown-menu">
                                    <li><a href="{url link='ad.sponsor' edit=$aAd.sponsor_id}"><span class="ico ico-pencilline-o"></span> {_p var='edit'}</a></li>
                                    <li class="item-delete">
                                        <a href="{url link='ad.manage-sponsor' delete=$aAd.sponsor_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_ad_permanently'}"><span class="ico ico-trash-o"></span> {_p var='delete'}</a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td class="align-middle bts-table__ads-title">
                            <a href="{url link='ad.sponsor' view=$aAd.sponsor_id}"> {$aAd.campaign_name|clean|convert} </a>
                            {if $aAd.is_custom == '1'}<a href="{url link='ad.sponsor' pay=$aAd.invoice_id}">({_p var='better_ads_pay_now'})</a>{/if}
                        </td>
                        <td class="align-middle bts-table__sort-content">{$aAd.start}</td>
                        <td class="align-middle px-3">{$aAd.status}</td>
                        <td class="align-middle">{$aAd.type}</td>
                        <td class="align-middle">{$aAd.total_view|intval}</td>
                        <td class="align-middle">{$aAd.total_click|intval}</td>
                        <td class="text-center align-middle">
                            <div class="privacy-block-content">
                                <div class="item_is_active_holder {if $aAd.is_active}item_selection_active{else}item_selection_not_active{/if}">
                                    <a class="js_item_active item_is_active js_item_active_link" href="#?call=ad.updateSponsorActivity&amp;id={$aAd.sponsor_id}&amp;active=1">{_p var='yes'}</a>
                                    <a class="js_item_active item_is_not_active js_item_active_link" href="#?call=ad.updateSponsorActivity&amp;id={$aAd.sponsor_id}&amp;active=0">{_p var='no'}</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{else}
    <div class="alert alert-info">{_p var='no_sponsorship_found'}</div>
{/if}