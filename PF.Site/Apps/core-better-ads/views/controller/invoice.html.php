<?php 
    defined('PHPFOX') or exit('NO DICE!');
?>

<div class="filter-invoices bts-filter-form pt-1">
    <form action="{url link='current'}" method="get" class="ml--1 mr--1">
        <div class="form-group px-1 d-inline-block shink-2">
            <label>{_p var='better_ads_from'}</label>
            {select_date start_year='2000' end_year='+100' prefix='from_' name='search'}
        </div>
        <div class="form-group px-1 d-inline-block shink-2">
            <label>{_p var='To'}</label>
            {select_date start_year='2000' end_year='+100' prefix='to_' name='search'}
        </div>
        <div class="form-group px-1 d-inline-block shink-1">
            <label for="status">{_p var='status'}</label>
            <select name="search[status]" id="status" class="form-control">
                <option value="0" {value id="status" type="select" default="0"}>{_p var='all_status'}</option>
                <option value="completed" {value type="select" id="status" default="completed"}>{_p var='paid'}</option>
                <option value="pending" {value type="select" id="status" default="pending"}>{_p var='unpaid'}</option>
                <option value="cancel" {value type="select" id="status" default="cancel"}>{_p var='cancelled'}</option>
            </select>
        </div>
        <div class="form-group button-wapper px-1">
            <button class="btn btn-primary">{_p var='submit'}</button>
        </div>
    </form>
</div>
{if !count($aInvoices)}
    <div class="alert alert-info">
        {_p var='no_invoice_found'}.
    </div>
{else}
    <div class="mt-1 table-responsive bts-table">
        <table class="table bts-table-invoice">
            <thead>
                <tr>
                    <th class="w-1 text-uppercase fz-12 text-gray-dark">{_p var='better_ads_id'}</th>
                    <th class="text-uppercase fz-12 text-gray-dark px-3 bts-table__sort cursor-point" {table_sort asc="ai.time_stamp asc" desc="ai.time_stamp desc" current=$sCurrentSort query="sort"}>{_p var='better_ads_date'}<span class="bts-table__sort-wapper"><i class="ico ico-caret-up"></i><i class="ico ico-caret-down"></i></span></th>
                    <th class="text-uppercase fz-12 text-gray-dark px-3">{_p var='better_ads_status'}</th>
                    <th class="text-uppercase fz-12 text-gray-dark px-3">{_p var='better_ads_price'}</th>
                    <th class="text-uppercase fz-12 text-gray-dark px-3">{_p var='action'}</th>
                </tr>
            </thead>
            <tbody>
                {foreach from=$aInvoices item=aInvoice}
                    <tr>
                        <td class="w-1 align-middle">{$aInvoice.invoice_id}</td>
                        <td class="align-middle px-3 bts-table__sort-content">{$aInvoice.time_stamp|date}</td>
                        <td class="betterads-invoice-status align-middle px-3">{$aInvoice.status_phrase}</td>
                        <td class="px-3 align-middle">{if $aInvoice.price > 0}{$aInvoice.price|currency:$aInvoice.currency_id}{else}{_p var='free'}{/if}</td>
                        <td class="px-3 align-middle">
                            {if $aInvoice.status === null}
                                <a class="btn btn-primary btn-sm" href="{if $aInvoice.is_sponsor != 1}{url link='ad.add.completed' id=$aInvoice.ads_id}{else}{url link='ad.sponsor' pay=$aInvoice.invoice_id}{/if}">{_p var='better_ads_pay_now'}</a>
                                <a role="button" data-cmd="cancel_invoice" data-invoice-id="{$aInvoice.invoice_id}" class="btn btn-default btn-sm">{_p var='cancel'}</a>
                            {else}
                                {_p var='n_a'}
                            {/if}
                        </td>
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
{/if}