<?php 
    defined('PHPFOX') or exit('NO DICE!');
?>

<div class="betterads-sponsor-item">
    {if !isset($iInvoice)}
        {if !empty($bIsEdit)}
        <form method="post" action="{url link='ad.sponsor'}">
            <div><input type="hidden" name="val[sponsor_id]" value="{value type='input' id='sponsor_id'}"></div>
            <div class="form-group">
                <label for="name" class="required">{_p var='ad_name'}</label>
                <input type="text" name="val[name]" value="{value type='input' id='campaign_name'}" size="25" id="name" class="form-control" />
            </div>
            {template file='ad.block.targetting'}
            <div class="form-group">
                <input type="submit" value="{_p var='update'}" class="btn btn-primary">
            </div>
        </form>
        {else}
        <form action="{permalink module='ad.sponsor' id=$iId}section_{if isset($sFormerModule)}{$sFormerModule}{else}{$sModule}{/if}/{if isset($aForms.where)}where_{$aForms.where}/{/if}{if isset($aForms.item_id)}item_{$aForms.item_id}/{/if}" name="js_form" method="post">
            <div class="bts-block">
                <div class="bts-block__item only-html">
                    <div class="bts-block__html sponsor-block">
                        {if !empty($aForms.image) || !empty($aForms.image_path)}
                            <a href="{$aForms.link}" class="bts-block__thumb--img">
                                {if isset($aForms.image) && isset($aForms.image_dir) && isset($aForms.server_id)}
                                    {img server_id=$aForms.server_id path=$aForms.image_dir file=$aForms.image suffix='_500' title=$aForms.title|clean}
                                {else if isset($aForms.image_path)}
                                    <img src="{$aForms.image_path}" alt="{$aForms.title|clean}" class="_image__500 image_deferred has_image">
                                {/if}
                            </a>
                        {/if}
                        <div class="bts-block__info">
                            <a href="{$aForms.link}" class="bts-block__title">{$aForms.title|clean}</a>
                            {if isset($aForms.extra)}
                                <div class="extra_info item_view_content">
                                    {$aForms.extra}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            </div>

            <h2>{_p var='details'}</h2>

            <div class="form-group">
                <label for="name" class="required">{_p var='ad_name'}</label>
                <input type="text" name="val[name]" value="{value type='input' id='name'}" size="25" id="name" class="form-control" />
            </div>

            {template file='ad.block.targetting'}

            <div class="form-group bts-add-startday">
                <label>{_p var='better_ads_start_date'}</label>
                {select_date prefix='start_' start_year='current_year' end_year='+10' field_separator=' / ' field_order='MDY' default_all=true add_time=true time_separator='core.time_separator'}
                <p class="help-block">
                    {_p var='better_ads_note_the_time_is_set_to_your_registered_time_zone'}
                </p>
                {if $bWithoutPaying}
                    <div class="custom-checkbox-wrapper">
                        <label>
                            <input type="checkbox" name="val[end_option]" id="end_option" value="1" {if !empty($aForms.end_date)}checked{/if}>
                            <span class="custom-checkbox"></span>
                            {_p var='better_ads_end_date'}
                        </label>
                        {select_date prefix='end_' start_year='current_year' end_year='+10' field_separator=' / '
                        field_order='MDY' default_all=true add_time=true time_separator='core.time_separator'}
                    </div>
                {/if}
            </div>

            <div class="form-group">
                <div class="form-inline ml--1 mr--1">
                    {if !$bWithoutPaying}
                        <input type="hidden" name="val[has_total_view]" value="1">
                        <div class="form-group px-1">
                            <label for="total_view" class="d-block">{_p var='number_of_views'}</label>
                            <input type="number" name="val[total_view]" value="{value type='input' name='impressions' id='impressions' default='1000'}" size="15" id="total_view" class="form-control" min="1000"/>
                        </div>
                        <div class="form-group px-1">
                            <label class="d-block">{_p var='total_cost'} ({$currency_code})</label>
                            <input type="text" name="val[ad_cost]" value="{value type='input' id='ad_cost'}" size="15" id="total_cost" class="form-control" readonly data-cost="{$aForms.ad_cost}"/>
                        </div>
                    {else}
                        <div class="form-group px-1">
                            <label class="d-block">
                                <input type="checkbox" name="val[has_total_view]" value="1" id="set_total_view"> {_p var='total_views'}
                            </label>
                            <input type="number" class="form-control" name="val[total_view]" value="{value type='input' name='impressions' id='impressions' default='1000'}" min="1000" id="total_view" readonly>
                        </div>
                    {/if}
                </div>
            </div>

            <div class="form-group">
                <input type="submit" value="{_p var='better_ads_submit'}" class="btn btn-primary" />
            </div>
        </form>
        {/if}
    {elseif $sStatus == ''}
        <h3>{_p var='better_ads_payment_methods'}</h3>
        {module name='api.gateway.form'}
    {else}
        {_p var='better_ads_your_order_has_been_processed'}
    {/if}
</div>