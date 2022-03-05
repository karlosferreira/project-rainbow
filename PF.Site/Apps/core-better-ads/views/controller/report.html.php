<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{literal}
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script type="text/javascript">
    $Behavior.betterads_draw_chart = function() {
        google.charts.load('current', {'packages': ['corechart', 'line']});
        google.charts.setOnLoadCallback(drawChart);
    }
    function drawChart() {
        var data = google.visualization.arrayToDataTable([
            [oTranslations['better_ads_period'], oTranslations['better_ads_click'], oTranslations['better_ads_view']],
            {/literal}
                {foreach from=$aReports value=aReport}
                    ['{$aReport.day_string}',{$aReport.total_click},{$aReport.total_view}],
                {/foreach}
            {literal}
        ]);

        var options = {
            title: '{/literal}{$aAds.name}{literal}',
            hAxis: {title: oTranslations['better_ads_period']},
            vAxis: {minValue: 0}
        };

        var chart = new google.charts.Line(document.getElementById('chart_div'));
        chart.draw(data, options);
    }
</script>
{/literal}

<div class="dropdown bts-detail-setting">
    <span role="button" data-toggle="dropdown" class="btn s-4 text-gray-dark"><i class="ico ico-gear-o"></i></span>
    <ul class="dropdown-menu dropdown-menu-right">
        <li><a href="{url link='ad.add' id=$aAds.ads_id}"><span class="ico ico-pencilline-o"></span> {_p var='edit'}</a></li>
        {if $aAds.is_custom ==1}
        <li><a href="{url link='ad.add.completed' id=$aAds.ads_id}"><span class="ico ico-credit-card-o"></span> {_p var="better_ads_pay_now"}</a></li>
        {/if}
        <li class="divider"></li>
        <li class="item-delete"><a href="{url link='ad.manage' delete=$aAds.ads_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_ad_permanently'}"><span class="ico ico-trash-o"></span> {_p var='delete'}</a></li>
    </ul>
</div>

{if $aAds.is_custom ==1}
<div class="alert alert-warning">
    {_p var="better_ads_pending_payment"}
</div>
{/if}
<div class="bts-detail">
    <div class="mx--1 bts-detail__preview {if !empty($aAds.image_tooltip_text)}hidden-info{/if}">
        <div class="pl-1 pr-1 bts-detail__preview__media">
            <div class="bts-detail__preview__temp">
                {img file=$aAds.image_path path='ad.url_image' server_id=$aAds.server_id}
            </div>
        </div>
        <div class="pl-1 pr-1 bts-detail__preview__info">
            {if $aAds.type_id == 1}
                <!-- Type Image -->
                <p class="fw-bold mb-0 bts-detail__preview__title only-img">{$aAds.image_tooltip_text}</p>
            {else}
                <!-- Type HTML -->
                <p class="fw-bold mb-0 bts-detail__preview__title">{$aAds.name}</p>
                <p class="bts-detail__preview__content mb-0 mt-1">{$aAds.body}</p>
            {/if}
        </div>
    </div>
    <div class="bts-detail__body mt-3">
        <div class="bts-detail__body__outer mx--1 d-flex flex-wrap">
            <div class="col-md-4 col-sm-6 col-xs-6 col-xsxs-12 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                <div class="bts-detail__body__inner pb-1">
                    <div class="bts-detail__body__title fw-bold mb-1">{_p var='placement'}</div>
                    <div class="bts-detail__body__text">{$aPlacement.title}: {_p var='block_location_x' x=$aPlacement.block_id} - {if $aPlacement.is_cpm}{_p var='better_ads_cpm_cost_per_mille'}{else}{_p var='better_ads_ppc_pay_per_click'}{/if}</div>
                </div>
            </div>
            <div class="col-md-4 col-sm-2 col-xs-6 col-xsxs-12 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                <div class="bts-detail__body__inner pb-1">
                    <div class="bts-detail__body__title fw-bold mb-1">{_p var='status'}</div>
                    <div class="bts-detail__body__text">{$aAds.status}</div>
                </div>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-6 col-xsxs-12 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                <div class="bts-detail__body__inner pb-1">
                    <div class="bts-detail__body__title fw-bold mb-1">{_p var='duration'}</div>
                    <div class="bts-detail__body__text"><small>{_p var='from'}</small> {$aAds.start} {if $aAds.end}<small>{_p var='To'}</small> {$aAds.end}{/if}</div>
                </div>
            </div>
            {if $bAdvancedAdFilters}
                <div class="col-md-4 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                    <div class="bts-detail__body__inner pb-1">
                        <div class="bts-detail__body__title fw-bold mb-1">{_p var='postal_code'}</div>
                        <div class="bts-detail__body__text">
                            {assign var=infoItems value=$aAds.postal_code}
                            {template file='ad.block.detail-info-body'}
                        </div>
                    </div>
                </div>
                <div class="col-md-4 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                    <div class="bts-detail__body__inner pb-1">
                        <div class="bts-detail__body__title fw-bold mb-1">{_p var="country"}</div>
                        <div class="bts-detail__body__text">
                            {assign var=infoItems value=$aAds.country}
                            {template file='ad.block.detail-info-body'}
                        </div>
                    </div>
                </div>
                <div class="col-md-4 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                    <div class="bts-detail__body__inner pb-1">
                        <div class="bts-detail__body__title fw-bold mb-1">{_p var='city'}</div>
                        <div class="bts-detail__body__text">
                            {assign var=infoItems value=$aAds.city_location}
                            {template file='ad.block.detail-info-body'}
                        </div>
                    </div>
                </div>
            {/if}
            <div class="col-md-4 col-sm-7 col-xs-6 col-xsxs-12 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                <div class="bts-detail__body__inner pb-1">
                    <div class="bts-detail__body__title fw-bold mb-1">{_p var='destination_url'}</div>
                    <div class="bts-detail__body__text bts-text-break-all"><a href="{$aAds.url_link}" target="_blank">{$aAds.url_link}</a></div>
                </div>
            </div>
            <div class="col-md-4 col-sm-2 col-xs-6 col-xsxs-12 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                <div class="bts-detail__body__inner pb-1">
                    <div class="bts-detail__body__title fw-bold mb-1">{_p var='gender'}</div>
                    <div class="bts-detail__body__text">
                        {assign var=infoItems value=$aAds.gender}
                        {template file='ad.block.detail-info-body'}
                    </div>
                </div>
            </div>
            {if !empty($aAds.age_from) || !empty($aAds.age_to)}
            <div class="col-md-4 col-sm-3 col-xs-6 col-xsxs-12 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                <div class="bts-detail__body__inner pb-1">
                    <div class="bts-detail__body__title fw-bold mb-1">{_p var='age_between'}</div>
                    <div class="bts-detail__body__text"><small>{_p var='from'}</small> {if $aAds.age_from}{$aAds.age_from}{else}{_p var='any'}{/if} <small>{_p var='To'}</small> {if $aAds.age_to}{$aAds.age_to}{else}{_p var='any'}{/if}</div>
                </div>
            </div>
            {/if}

            <div class="col-md-4 col-sm-2 col-xs-6 col-xsxs-12 pl-1 pr-1 mb-3 flex-column d-inline-flex">
                <div class="bts-detail__body__inner pb-1">
                    <div class="bts-detail__body__title fw-bold mb-1">{_p var='language'}</div>
                    <div class="bts-detail__body__text">
                        {assign var=infoItems value=$aAds.languages}
                        {template file='ad.block.detail-info-body'}
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="bts-filter-form px-2 pt-2 mt-1">
        <form class="ml--1 mr--1" enctype="multipart/form-data" action="{url link='ad.report' ads_id=$aAds.ads_id}" method="post">
            <div class="form-group px-1 d-inline-block">
                <label>{_p var="from"}</label>
                {select_date prefix='from_' start_year=$iFilterFromYear end_year=$iFilterToYear field_separator=' / ' field_order='MDY' default_all=true}
            </div>
            <div class="form-group px-1 d-inline-block">
                <label>{_p var="To"}</label>
                {select_date prefix='to_' start_year=$iFilterFromYear end_year=$iFilterToYear field_separator=' / ' field_order='MDY' default_all=true}
            </div>
            <div class="form-group px-1 d-inline-block">
                <label for="type">{_p var='sort_by'}</label>
                <select name="val[type]" id="type" class="form-control">
                    <option value="1" {value type='select' id='type' default="1"}>{_p var="better_ads_view_day"}</option>
                    <option value="2" {value type='select' id='type' default="2"}>{_p var="better_ads_view_week"}</option>
                    <option value="3" {value type='select' id='type' default="3"}>{_p var="better_ads_view_month"}</option>
                </select>
            </div>
            <div class="form-group button-wapper px-1 d-inline-block">
                <button type="submit" class="btn btn-primary mr-1" name="val[submit]">{_p var="submit"}</button><a class="no_ajax btn btn-default" target="_blank" href="{url link='ad.report' ads_id=$aAds.ads_id export=true}"><span class="ico ico-text-file-download"></span>&nbsp;{_p var='better_ads_export'}</a>
            </div>
        </form>
    </div>
    <div id="chart_div" class="mt-2" style="width: auto; height: 464px"></div>
    <div class="bts-detail__reports mt-2">
        {module name='ad.daily-reports' ad_id=$aAds.ads_id}
    </div>
</div>