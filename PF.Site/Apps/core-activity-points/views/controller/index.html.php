<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="core-activitypoint-addpackage"><a class="btn btn-primary btn-sm popup" href="{url link='activitypoint.package'}"><i class="ico ico-money-bag-o"></i><span class="item-text">{_p var='activitypoint_purchase_packages'}</span></a></div>
<div class="core-activitypoint__index" id="js_core_activitypoint_index">
    <div class="core-activitypoint-statistic">
        <div class="statistic-wrapper-left">
            <div class="item-statistic-with-icon">
                <div class="item-icon"><i class="ico ico-star-circle-o"></i></div>
                <div class="item-holder">
                    <div class="item-inner">
                        <div class="item-title">{$aStatistics.current_points.title}<span class="ico ico-question-circle " data-toggle="tooltip" data-placement="top" title="{_p var=$aStatistics.current_points.information}"></span></div>
                        <div class="item-number">{$aStatistics.current_points.points|number_format}</div>
                    </div>
                    {if isset($aStatistics.points_to_currency)}
                        <div class="item-inner pt-1">
                            <div class="item-title">{$aStatistics.points_to_currency.title}<span class="ico ico-question-circle " data-toggle="tooltip" data-placement="top" title="{_p var=$aStatistics.points_to_currency.information}"></span></div>
                            <div class="item-number">{$aStatistics.points_to_currency.points|number_format:2}</div>
                            <div class="help-block">{$aStatistics.points_to_currency.sub_information}</div>
                        </div>
                    {/if}
                </div>
            </div>
            <div class="statistic-list-mini">
                <div class="item-list-title">
                    <div class="item-title">{$aStatistics.earned_points.title}<span class="ico ico-question-circle" data-toggle="tooltip" data-placement="top" title="{_p var=$aStatistics.earned_points.information}"></span></div>
                    <div class="item-title">{$aStatistics.bought_points.title}<span class="ico ico-question-circle" data-toggle="tooltip" data-placement="top" title="{_p var=$aStatistics.bought_points.information}"></span></div>
                    <div class="item-title">{$aStatistics.received_points.title}<span class="ico ico-question-circle" data-toggle="tooltip" data-placement="top" title="{_p var=$aStatistics.received_points.information}"></span></div>
                    <div class="item-title">{$aStatistics.retrieved_points.title}<span class="ico ico-question-circle" data-toggle="tooltip" data-placement="top" title="{_p var=$aStatistics.retrieved_points.information}"></span></div>
                </div>
                <div class="item-list-number">
                    <div class="item-number">{$aStatistics.earned_points.points|number_format}</div>
                    <div class="item-number">{$aStatistics.bought_points.points|number_format}</div>
                    <div class="item-number">{$aStatistics.received_points.points|number_format}</div>
                    <div class="item-number">{$aStatistics.retrieved_points.points|number_format}</div>
                </div>
            </div>
        </div>
        <div class="statistic-wrapper-right">
            <div class="item-statistic-with-icon">
                <div class="item-icon"><i class="ico ico-dollar-circle-o"></i></div>
                <div class="item-inner">
                    <div class="item-title">{$aStatistics.spent_points.title}<span class="ico ico-question-circle" data-toggle="tooltip" data-placement="top" title="{_p var=$aStatistics.spent_points.information}"></span></div>
                    <div class="item-number">{$aStatistics.spent_points.points|number_format}</div>
                </div>
            </div>
            <div class="item-statistic-with-icon">
                <div class="item-icon"><i class="ico ico-share-alt-o"></i></div>
                <div class="item-inner">
                    <div class="item-title">{$aStatistics.sent_points.title}<span class="ico ico-question-circle" data-toggle="tooltip" data-placement="top" title="{_p var=$aStatistics.sent_points.information}"></span></div>
                    <div class="item-number">{$aStatistics.sent_points.points|number_format}</div>
                </div>
            </div>
        </div>
    </div>
    <div class="core-activitypoint__index-search">
        <div class="core-activitypoint__index-search-content">
            <input type="hidden" id="js_date_from_default" value="{$sFromDate}">
            <input type="hidden" id="js_date_to_default" value="{$sToDate}">
            <form id="js_form_transaction_index" method="get" action="{url link='activitypoint'}">
                <div class="search-item-wrapper">
                    <div class="search-item form-group">
                        <label>{_p var='from'}</label>
                        <div style="position: relative;" class="">
                            {select_date prefix='from_' id='_start' start_year=$sFromDateYear end_year='+1' field_separator=' / ' default_all=true field_order='MDY'}
                        </div>
                    </div>
                    <div class="search-item form-group">
                        <label>{_p var='activitypoint_to'}</label>
                        <div style="position: relative;" class="">
                            {select_date prefix='to_' id='_start' start_year='current_year' end_year='+1' field_separator=' / ' default_all=true field_order='MDY'}
                        </div>
                    </div>
                    <div class="search-item form-group">
                        <span class="form-item">
                            <label>{_p var='activitypoint_type'}</label>
                            <select name="val[type]" class="form-control">
                                <option value="">{_p var='activitypoint_all'}</option>
                                {foreach from=$aPointTypes key=sPointTypeKey item=sPointTypeTitle}
                                    <option value="{$sPointTypeKey}" {value type='select' id='type' default=$sPointTypeKey}>{$sPointTypeTitle}</option>
                                {/foreach}
                            </select>
                        </span>
                    </div>
                    <div class="item-btn-search form-group">
                        <button class="btn btn-primary">{_p var='activitypoint_search'}</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    <div class="core-activitypoint__index-list">
        {if !$bBeginer}
            {if !empty($aTransactions)}
            <div class="table-responsive core-activitypoint-table transaction-table">
                <table class="table" cellpadding="0" cellspacing="0">
                    <thead>
                    <tr>
                        <th {table_sort class="" asc="t.transaction_id asc" desc="t.transaction_id desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_transaction_id'}<span class="item-table-sort-wapper"><i class="ico ico-caret-up item-up"></i><i class="ico ico-caret-down item-down"></i></span></th>
                        <th {table_sort class="" asc="t.time_stamp asc" desc="t.time_stamp desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_date'}<span class="item-table-sort-wapper"><i class="ico ico-caret-up item-up"></i><i class="ico ico-caret-down item-down"></i></span></th>
                        <th class="">{_p var='activitypoint_point_type'}</th>
                        <th class="">{_p var='activitypoint_app'}</th>
                        <th class="">{_p var='activitypoint_action'}</th>
                        <th {table_sort class="t_center" asc="t.points asc" desc="t.points desc" query="sort" current="{$sCurrent}"}>{_p var='activitypoint_points'}<span class="item-table-sort-wapper"><i class="ico ico-caret-up item-up"></i><i class="ico ico-caret-down item-down"></i></span></th>
                    </tr>
                    </thead>
                    <tbody>
                        {foreach from=$aTransactions item=aTransaction}
                        <tr>
                            <td class=""><div class="td-content">{$aTransaction.transaction_id|number_format}</div></td>
                            <td class=""><div class="td-content">{$aTransaction.time_stamp|convert_time}</div></td>
                            <td class=""><div class="td-content">{_p var=$aTransaction.type}</div></td>
                            <td class=""><div class="td-content">{_p var=$aTransaction.module_text}</div></td>
                            <td class=""><div class="td-content"><div class="w120">{$aTransaction.phrase}</div></div></td>
                            <td class="t_center"><div class="td-content center {$aTransaction.custom_class}">{if $aTransaction.custom_class == 'minus'}-{/if}{$aTransaction.points|number_format}</div></td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
            </div>
            {pager}
            {else}
            <div class="help-block">{_p var='activitypoint_transactions_not_found'}</div>
            {/if}
        {else}
        <a class="" href="{url link='activitypoint.information'}">{_p var='activitypoint_introduce_how_to_earn_title'}</a>
        {/if}
    </div>
</div>