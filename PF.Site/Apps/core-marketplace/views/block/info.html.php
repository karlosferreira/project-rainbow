<?php 
	defined('PHPFOX') or exit('NO DICE!'); 
?>

<div class="market-app detail-extra-info">
    <div class="item-info-price">
        <span class="" itemprop="price">
            {if $aListing.price == '0.00'}
                <span class="free">{_p var='free'}</span>
            {else}
                <span class="{if $aListing.view_id == '2'}sold{/if}">
                    {$aListing.price|currency:$aListing.currency_id}
                </span>
            {/if}
        </span>
    </div>
    <div class="item-info-statistic">
        <div class="item-stat">
           <span>{$aListing.total_like|number_format}</span> {if $aListing.total_like == 1}{_p('like_lowercase')}{else}{_p('likes_lowercase')}{/if}
        </div>
        <div class="item-stat">
           <span>{$aListing.total_view|number_format}</span> {if $aListing.total_view == 1}{_p('view_lowercase')}{else}{_p('views_lowercase')}{/if}
        </div>
    </div>
    {if Phpfox::isUser() && $aListing.user_id != Phpfox::getUserId()}
       <div class="item-action-contact">
            <div class="item-action-list">
                {if $aListing.view_id == '2'}
                    <div class="btn item-soldout">
                        {_p var='sold'}
                    </div>
                {/if}
                {if (($aListing.is_sell && $bHaveGateway) || $aListing.allow_point_payment ) && $aListing.view_id != '2' && $aListing.price != '0.00'}
                    <form method="post" action="{url link='marketplace.purchase'}" class="form">
                        <div><input type="hidden" name="id" value="{$aListing.listing_id}" /></div>
                        <button type="submit" value="{_p var='buy_it_now'}" class="btn btn-primary fw-bold item-buynow">
                            {_p var='buy_now'}</button>
                    </form>
                {/if}
                {if $aListing.canContactSeller}
                    <button class="btn btn-default" onclick="$Core.marketplace.contactSeller({l}id: {$aListing.user_id}, message: '{$sMessage}', listing_id: {$aListing.listing_id}, module_id: 'marketplace'{r}); return false;">
                        <i class="ico ico-user3-next-o mr-1"></i>{_p var='contact_seller'}
                    </button>
                {/if}
            </div>
       </div>
    {/if}
    <div class="item-info-author">
        {img user=$aListing suffix='_120_square'}
        <div class="item-detail-author">
            <div>{_p var="By"} {$aListing|user:'':'':50}</div>
            <div>{_p var="posted_on"} {$aListing.time_stamp|convert_time}</div>
        </div>
        {if $aListing.hasPermission}
        <div class="item-detail-main-action">
            <div class="dropdown">
                <span role="button" data-toggle="dropdown" class="item_bar_action">
                    <i class="ico ico-gear-o"></i>
                </span>
                <ul class="dropdown-menu dropdown-menu-right">
                    {template file='marketplace.block.menu'}
                </ul>
            </div>
        </div>
        {/if}
    </div>
    {if !empty($aListing.mini_description)}
        <div class="item-info-short-desc">
            {$aListing.mini_description|clean}
        </div>
    {/if}
    {if !empty($aListing.location)}
        <div class="item-info-location">
            <span class="item-label">{_p var="location"}:</span>
            <span>
                <a href="https://maps.google.com/?q={$aListing.location}" target="_blank">{$aListing.location}</a>
            </span>
        </div>
    {/if}
    {if is_array($aListing.categories) && count($aListing.categories)}
        <div class="item-info-categories">
            <span class="item-label">{_p var="Categories"}:</span>
            {$aListing.categories|category_display}
        </div>
    {/if}
</div>