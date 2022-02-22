<?php 
defined('PHPFOX') or exit('NO DICE!');

?>
<form method="get" action="{url link='admincp.ad.invoice'}">
	<div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">
                {_p var='search_invoice'}
            </div>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{_p var='date_from'}</label>
                        {select_date start_year='2000' end_year='+100' prefix='from_' name='search'}
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{_p var='date_to'}</label>
                        {select_date start_year='2000' end_year='+100' prefix='to_' name='search'}
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{_p var='user'}</label>
                        <input type="text" class="form-control" name="search[user]" value="{value type='input' id='user'}">
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label>{_p var='better_ads_status'}</label>
                        <select name="search[status]" class="form-control">
                            <option>---</option>
                            <option value="1" {value type='select' id='status' default='1'}>{_p var='paid'}</option>
                            <option value="2" {value type='select' id='status' default='2'}>{_p var='unpaid'}</option>
                            <option value="3" {value type='select' id='status' default='3'}>{_p var='cancelled'}</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <button type="submit" name="search[submit]" value="" class="btn btn-primary"><i class="fa fa-search"></i> {_p var='search'}</button>
            <a href="{url link='admincp.ad.invoice'}" class="btn btn-default">{_p var='reset'}</a>
        </div>
    </div>
</form>
{if !count($aInvoices)}
    <div class="alert alert-info">
        {_p var='better_ads_there_are_no_invoices'}
    </div>
{else}
    <div class="panel panel-default">
        <div class="table-responsive">
            <table class="table table-admin">
                <thead>
                <tr>
                    <th class="w60 t_center">{_p var='better_ads_id'}</th>
                    <th {table_sort asc="ai.time_stamp asc" desc="ai.time_stamp desc" current=$sCurrentSort query="sort"}>{_p var='better_ads_date'}</th>
                    <th>{_p var='user'}</th>
                    <th>{_p var='better_ads_status'}</th>
                    <th>{_p var='better_ads_price'}</th>
                    <th class="w60"></th>
                </tr>
                </thead>
                <tbody>
                {foreach from=$aInvoices key=iKey item=aInvoice}
                <tr {if is_int($iKey/2)} class="tr"{/if}>
                    <td class="t_center">{$aInvoice.invoice_id}</td>
                    <td>{$aInvoice.time_stamp|date}</td>
                    <td>{$aInvoice|user}</td>
                    <td>{$aInvoice.status_phrase}</td>
                    <td>{$aInvoice.price|currency:$aInvoice.currency_id}</td>
                    <td class="t_center">
                        <a href="#" class="js_drop_down_link" title="{_p var='better_ads_manage'}"></a>
                        <div class="link_menu">
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li>
                                    <a href="{url link='admincp.ad.invoice' delete=$aInvoice.invoice_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_invoice_permanently'}">{_p var='better_ads_delete'}</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                {/foreach}
                </tbody>
            </table>
        </div>
    </div>
    {pager}
{/if}
