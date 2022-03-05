<?php
	defined('PHPFOX') or exit('NO DICE!');
?>
{plugin call='ad.template_block_display__start'}

<div class="js_ad_space_parent">
    <div id="js_ads_space_{$iBlockId}" class="bts-block">
        {foreach from=$aBlockAds item=aAd}
	        <div id="ads_item_{$aAd.ads_id}" class="bts-block__item multi_ad_holder {if $aAd.type_id == 1}only-img{else}only-html{/if}">
	            {if $bCanHideAds}
		            <a href="javascript:void(0)" role="button" onclick="$.ajaxCall('ad.hideAds', 'id={$aAd.ads_id}');" class="bts-block__del" title="{_p var='hide_this_ad'}"><i class="ico ico-close" aria-hidden="true"></i></a>
	            {/if}
	            {if $aAd.type_id == 1}
		            <div class="bts-block__img">
			            <!-- Image type -->
			            <a {if !empty($aAd.url_link)}href="{$aAd.url_link}" target="_blank"{else}role="button"{/if} title="{$aAd.image_tooltip_text}" class="bts-block__thumb--img {if !empty($aAd.url_link)}no_ajax_link{/if}">
			            	{img file=$aAd.image_path path='ad.url_image' server_id=$aAd.server_id}
			            </a>
			        </div>
	            {else}
		            <!-- HTML type -->
		            <div class="bts-block__html ad_unit_multi_ad">
                        {if !empty($aAd.image_path)}
			                <a {if !empty($aAd.url_link)}href="{$aAd.url_link}" target="_blank"{else}role="button"{/if} title="{$aAd.image_tooltip_text}" class="bts-block__thumb--img ad_unit_multi_ad_image {if !empty($aAd.url_link)}no_ajax_link{/if}">
			                	{img file=$aAd.image_path path='ad.url_image' server_id=$aAd.server_id}
			                </a>
                        {/if}
		                <div class="bts-block__info ad_unit_multi_ad_content">
			                <a href="{$aAd.url_link}" class="bts-block__title ad_unit_multi_ad_title no_ajax_link" target="_blank">{$aAd.title}</a>
			                <a class="ad_unit_multi_ad_url bts-block__url no_ajax_link" href="{$aAd.trimmed_url}" target="_blank">{$aAd.trimmed_url}</a>
			                <p class="bts-block__desc ad_unit_multi_ad_text mb-0">{$aAd.body}</p>
		                </div>
		            </div>
	            {/if}
	        </div>
        {/foreach}
    </div>
</div>

{plugin call='ad.template_block_display__end'}