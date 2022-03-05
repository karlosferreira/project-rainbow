<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="betterads-migrate-ads">
    <form method="get" action="{url link='admincp.ad.migrate-ads'}" class="form">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">{_p var='search_ad'}</div>
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
                            <label for="name">{_p var='ad_name'}</label>
                            <input name="search[name]" class="form-control" id="name" value="{value type='input' id='name'}">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="creator">{_p var='creator'}</label>
                            <input name="search[creator]" class="form-control" id="creator" value="{value type='input' id='creator'}">
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-6">
                        <label for="status">{_p var='status'}</label>
                        <select name="search[status]" class="form-control" id="status">
                            <option value="0" {value type='select' id='status' default="0"}>{_p var='all_status'}</option>
                            <option value="upcoming" {value type='select' id='status' default='upcoming'}>{_p var='upcoming'}</option>
                            <option value="running" {value type='select' id='status' default='running'}>{_p var='running'}</option>
                            <option value="5" {value type='select' id='status' default='5'}>{_p var='completed'}</option>
                            <option value="ended" {value type='select' id='status' default='ended'}>{_p var='ended'}</option>
                            <option value="2" {value type='select' id='status' default='2'}>{_p var='pending'}</option>
                            <option value="1" {value type='select' id='status' default='1'}>{_p var='unpaid'}</option>
                            <option value="4" {value type='select' id='status' default='4'}>{_p var='denied'}</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="active">{_p var='better_ads_active'}</label>
                        <select name="search[active]" class="form-control" id="active">
                            <option value="-1" {value type='select' id='active' default="-1"}>{_p var='any'}</option>
                            <option value="1" {value type='select' id='active' default='1'}>{_p var='better_ads_active'}</option>
                            <option value="0" {value type='select' id='active' default='0'}>{_p var='better_ads_inactive'}</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <button type="submit" name="search[submit]" class="btn btn-primary"><i class="fa fa-search"></i> {_p var='search'}</button>
                <a role="button" href="{url link='admincp.ad.migrate-ads'}" class="btn btn-default">{_p var='reset'}</a>
            </div>
        </div>
    </form>
    {if count($aAds)}
    <form method="post" action="{url link='admincp.ad.migrate-ads'}" id="migrate_ads_form">
        <div class="panel panel-default">
            <table class="table table-admin">
                <tr>
                    <th class="w20">
                        <div class="custom-checkbox-wrapper">
                            <label>
                                <input type="checkbox" name="val[id][]" value="" id="js_check_box_all" class="main_checkbox" />
                                <span class="custom-checkbox"></span>
                            </label>
                        </div>
                    </th>
                    <th class="w60 t_center">{_p var='better_ads_id'}</th>
                    <th {table_sort asc="a.start_date asc" desc="a.start_date desc" current=$sCurrentSort query="sort"}>{_p var='start_date'}</th>
                    <th>{_p var='better_ads_name'}</th>
                    <th class="w100">{_p var='better_ads_status'}</th>
                    <th class="w300">{_p var='creator'}</th>
                    <th {table_sort class="t_center w100" asc="a.count_view asc" desc="a.count_view desc" current=$sCurrentSort query="sort"}>{_p var='better_ads_views'}</th>
                    <th {table_sort class="t_center w100" asc="a.count_click asc" desc="a.count_click desc" current=$sCurrentSort query="sort"}>{_p var='better_ads_clicks'}</th>
                    <th class="t_center w100">{_p var='better_ads_active'}</th>
                    <th class="t_center w80">{_p var='settings'}</th>
                </tr>
                {foreach from=$aAds key=iKey item=aAd}
                <tr class="{if is_int($iKey/2)} tr{else}{/if}{if $aAd.is_custom && $aAd.is_custom == '2'} is_checked{/if}">
                    <td class="t_center">
                        <div class="custom-checkbox-wrapper">
                            <label>
                                <input type="checkbox" name="val[id][]" class="checkbox" value="{$aAd.ad_id}" id="js_id_row{$aAd.ad_id}" />
                                <span class="custom-checkbox"></span>
                            </label>
                        </div>
                    </td>
                    <td class="t_center">{$aAd.ad_id}</td>
                    <td>{$aAd.start}</td>
                    <td>{$aAd.name|clean|convert}</td>
                    <td>{$aAd.status}</td>
                    <td>{$aAd.user|user}</td>
                    <td class="t_center">{$aAd.count_view|intval}</td>
                    <td class="t_center">{$aAd.count_click|intval}</td>
                    <td class="t_center">{if $aAd.is_active}{_p var='better_ads_active'}{else}{_p var='better_ads_inactive'}{/if}</td>
                    <td class="t_center">
                        <a class="js_drop_down_link" title="{_p var='better_ads_manage'}"></a>
                        <div class="link_menu">
                            <ul class="dropdown-menu dropdown-menu-right">
                                <li><a role="button" onclick="tb_show('', $.ajaxBox('ad.migrateAd', 'height=400&amp;width=600&amp;id={$aAd.ad_id}'))">{_p var='import'}</a></li>
                                <li><a href="{url link='admincp.ad.migrate-ads' delete=$aAd.ad_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_ad_permanently'}">{_p var='delete'}</a></li>
                            </ul>
                        </div>
                    </td>
                </tr>
                {/foreach}
            </table>
        </div>
        <div class="table_hover_action hide">
            <a role="button" class="btn btn-primary sJsCheckBoxButton disabled" disabled="disabled" onclick="$Core.Ads.massImport('#migrate_ads_form');return false;">{_p var='import_selected'}</a>
            <a role="button" class="btn btn-danger sJsCheckBoxButton disabled" disabled="disabled" onclick="$Core.Ads.confirmSubmitForm(this, '#migrate_ads_form')" data-action="delete">{_p var='delete_selected'}</a>
        </div>
    </form>
    {else}
    <div class="alert alert-info">
        {_p var='no_ad_found'}.
    </div>
    {/if}
    {pager}
</div>