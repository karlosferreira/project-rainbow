<?php

defined('PHPFOX') or exit('NO DICE!');

?>

{if (Phpfox::getParam('marketplace.days_to_expire_listing') > 0) && ( $aListing.time_stamp < (PHPFOX_TIME - (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400)) )}
    <div class="error_message">
        {_p var='listing_expired_and_not_available_main_section'}
    </div>
{/if}
{if $aListing.view_id == '1'}
    {template file='core.block.pending-item-action'}
{/if}
<div class="item_view market-app market-view-detail">
    <div class="market-view-detail-main-content">
        <div class="market-detail-not-right">
            {if $aImages}
                <div class="market-detail-photo-block">
                    <div class="ms-marketplace-detail-showcase dont-unbind market-app">
                        <div class="ms-vertical-template ms-tabs-vertical-template dont-unbind" id="marketplace_slider-detail">
                            {foreach from=$aImages name=images item=aImage}
                            <div class="ms-slide ms-skin-default dont-unbind">
                                <img src="{img theme='misc/blank.gif' return_url=true}" data-src="{img server_id=$aImage.server_id path='marketplace.url_image' file=$aImage.image_path return_url=true}"/>
                                <div class="ms-thumb">
                                    <img class="dont-unbind" src="{img server_id=$aImage.server_id path='marketplace.url_image' file=$aImage.image_path suffix='_120_square' return_url=true}" alt="thumb" />
                                </div>
                            </div>
                            {/foreach}
                        </div>
                    </div>
                    <div class="item-slider-count">
                        <div class="item-count js_market_toggle_thumb dont-unbind">
                            <i class="ico ico-th-large"></i>
                            <div class="item-number">
                                <span class="item-current js_market_current_slide">1</span>/<span class="item-total">{$aImages|count}</span>
                            </div>
                        </div>
                    </div>
                </div>
            {/if}

            {module name='marketplace.info'}
        </div>
        {if !empty($aListing.description)}
            <div class="item-info-long-desc item_view_content">
                <div class="item-label ">{_p var='product_information'}</div>
                <div class="item-text" itemprop="description">
                    {$aListing.description|parse|shorten:200:'view_more':true|split:70}
                    {if $aListing.total_attachment}
                        {module name='attachment.list' sType=marketplace iItemId=$aListing.listing_id}
                    {/if}
                </div>
            </div>
        {/if}
        {plugin call='marketplace.template_default_controller_view_extra_info'}

        <div {if $aListing.view_id != 0}style="display:none;" class="js_moderation_on market-addthis"{/if} class="market-addthis">
            
            {addthis url=$aListing.bookmark_url title=$aListing.title description=$sShareDescription}
            <div class="item-detail-feedcomment">
                {module name='feed.comment'}
            </div>
        </div>
    </div>
    {if (count($aListings))}
        <div class="item-block-detail-listing">
            <div class="item-header">
                {_p var="in_this_category"}
            </div>
            <div class="item-listing">
                <div class="item-container market-app listing">
                    {foreach from=$aListings name=listings item=aListing}
                        {template file='marketplace.block.rows'}
                    {/foreach}
                </div>
            </div>
        </div>
    {/if}
</div>

{literal}
<script type="text/javascript">
    $Behavior.initDetailSlide = function() {
        var ele = $('#marketplace_slider-detail');
        if (ele.prop('built') || !ele.length) return false;
        ele.prop('built', true).addClass('dont-unbind-children');
            var slider = new MasterSlider();

        var mp_direction = 'h';
        var toggle_thumb = $(".js_market_toggle_thumb");

        slider.setup('marketplace_slider-detail' , {
            width: ele.width(),
            height: ele.width(),
            space:5,
            view:'basic',
            dir:mp_direction,
            speed:50,
        });

        slider.control('arrows');
        slider.control('scrollbar' , {dir:'v'});
        slider.control('thumblist' , {autohide:false ,dir:mp_direction});

        if(toggle_thumb.length){
            toggle_thumb.on("click",function(){
                $(this).closest(".market-detail-photo-block").toggleClass("hide-thumb");
            });
        }
        if (window.matchMedia('(max-width: 767px)').matches) {
            $(".market-detail-photo-block").addClass("hide-thumb");
        }

        slider.api.addEventListener(MSSliderEvent.CHANGE_END , function(){
            if ($('.ms-thumbs-cont .ms-thumb-frame').length < 2){
                $('.ms-thumbs-cont').closest('.market-detail-photo-block').addClass('one-slide');
            }
            var width_list_thumb = $('.ms-thumb-list').outerWidth(),
                width_item_thumb = $('.ms-thumb-frame').outerWidth() + 5,
                max_item_thumb = parseInt(width_list_thumb/width_item_thumb),
                count_item_thumb = $('.ms-thumbs-cont .ms-thumb-frame').length ;
            var current_slide = slider.api.index() + 1;
            $(".js_market_current_slide").text(current_slide);
            if(count_item_thumb <= max_item_thumb){
                $('.ms-thumb-list').addClass('not-nav-btn');
            }
        });

      $Behavior.initDetailSlide = function() {}
    };
</script>
{/literal}