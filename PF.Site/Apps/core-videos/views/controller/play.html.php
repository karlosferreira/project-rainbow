<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !empty($aItem.duration)}
<meta itemprop="duration" content="{$aItem.duration}" />
{/if}

<div class="video-view ">
    {if $aItem.in_process > 0}
        <div class="alert alert-info">
            {_p('video_is_being_processed')}
        </div>
    {else}
        {if $aItem.view_id == 2}
            {template file='core.block.pending-item-action'}
        {/if}
    {/if}
    <div class="pf_video_wrapper_container">
        <div class="t_center pf_video_wrapper {if $aItem.is_stream}{if empty($aItem.is_facebook_embed)}pf_video_wrapper_iframe{elseif !empty($aItem.is_new_embed)}pf_video_facebook_new_iframe{else}pf_video_facebook_iframe{/if}{/if} {if $aItem.destination}pf_video_wrapper_uploader{/if}">
            {$aItem.embed_code}
            {if PHPFOX_IS_AJAX_PAGE}
                <span class="_a_back"><i class="ico ico-arrow-left"></i>{_p var='back'}</span>
            {/if}
        </div>
    </div>
    <div class="core-videos-view-title header-page-title item-title {if isset($aTitleLabel.total_label) && $aTitleLabel.total_label > 0}header-has-label-{$aTitleLabel.total_label}{/if}">
        <a href="{$aItem.link}" class="ajax_link">{$aItem.title|clean}</a>
        <div class="video-icon">
            {if (isset($sView) && $sView == 'my' || isset($bIsDetail)) && $aItem.view_id == 1}
            <div class="sticky-label-icon sticky-pending-icon">
                <span class="flag-style-arrow"></span>
                <i class="ico ico-clock-o"></i>
            </div>
            {/if}
            {if $aItem.is_sponsor}
            <div class="sticky-label-icon sticky-sponsored-icon">
                <span class="flag-style-arrow"></span>
                <i class="ico ico-sponsor"></i>
            </div>
            {/if}
            {if $aItem.is_featured}
            <div class="sticky-label-icon sticky-featured-icon">
                <span class="flag-style-arrow"></span>
                <i class="ico ico-diamond"></i>
            </div>
            {/if}
        </div>
    </div>
    <div class="video-info-wrapper">
        <div class="video-info">
            <div class="video-info-image">{img user=$aItem suffix='_120_square'}</div>
            <div class="video-info-main">
                <span class="video-author">{$aItem|user:'':'':50:'':'author'} {_p var='on'} {$aItem.time_stamp|convert_time:'core.global_update_time'}</span>
                <span class="video-view">{$aItem.total_view|number_format} {if $aItem.total_view == 1}{_p var='view_lowercase'}{else}{_p var='views_lowercase'}{/if}</span>
            </div>
        </div>
        
        {if $aItem.hasPermission}
        <div class="item_bar video-button-option">
            <div class="item_bar_action_holder">
                <a href="#" class="item_bar_action" data-toggle="dropdown" role="button"><span>{_p('Actions')}</span><i class="ico ico-gear-o"></i></a>
                <ul class="dropdown-menu dropdown-menu-right">
                    {template file='v.block.menu'}
                </ul>
            </div>
        </div>
        {/if}  
    </div>
    <div class="core-videos-view-content-collapse-container">  
        <div class="core-videos-view-content-collapse js_core_videos_view_content_collapse">
            {if !empty($aItem.text)}
                <div class="video-content item_view_content">{$aItem.text|parse}</div>
            {/if}
            {if $aItem.sHtmlCategories}
                <div class="video_category">
                    <span>{_p var='categories'}:</span>
                    {$aItem.sHtmlCategories}
                </div>
            {/if}
        </div>
        <div class="core-videos-view-action-collapse js-core-video-action-collapse">
            <a class="item-viewmore-btn js-item-btn-toggle-collapse">{_p var="view_more"} <i class="ico ico-caret-down"></i></a>
            <a class="item-viewless-btn js-item-btn-toggle-collapse">{_p var="view_less"} <i class="ico ico-caret-up"></i></a>
        </div>
    </div>
    <div {if $aItem.view_id}style="display:none;" class="js_moderation_on"{/if}>
    {if Phpfox::isModule('feed') &&  Phpfox::getParam('feed.enable_check_in') && Phpfox::getParam('core.google_api_key') != '' && !empty($aItem.location_name)}
        <div class="activity_feed_location">
            <span class="activity_feed_location_at">{_p('at')} </span>
            <span class="js_location_name_hover activity_feed_location_name" {if isset($aItem.location_latlng) && isset($aItem.location_latlng.latitude)}onmouseover="$Core.Feed.showHoverMap('{$aItem.location_latlng.latitude}','{$aItem.location_latlng.longitude}', this);"{/if}>
                <span class="ico ico-checkin"></span>
                <a href="https://maps.google.com/maps?daddr={$aItem.location_latlng.latitude},{$aItem.location_latlng.longitude}" target="_blank">{$aItem.location_name}</a>
            </span>
        </div>
    {/if}
    <div class="addthis_share pf_video_addthis mb-3 pt-2">
        <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid={$sAddThisPubId}" data-title="{$aItem.title|clean}"></script>
        {plugin call='video.template_controller_play_addthis_start'}
        {addthis url=$aItem.link title=$aItem.title description=$sShareDescription}
        {plugin call='video.template_controller_play_addthis_end'}
    </div>
    <div class="item-detail-feedcomment">
    {module name='feed.comment'}
    </div>
    </div>
</div>

{if $bLoadCheckin}
<script type="text/javascript">
    var bCheckinInit = false;
    $Behavior.prepareInit = function()
    {l}
        if($Core.Feed !== undefined)
        {l}
            $Core.Feed.sIPInfoDbKey = '';
            $Core.Feed.sGoogleKey = '{param var="core.google_api_key"}';

            {if isset($aVisitorLocation)}
                $Core.Feed.setVisitorLocation({$aVisitorLocation.latitude}, {$aVisitorLocation.longitude} );
            {else}

            {/if}
            $Core.Feed.googleReady('{param var="core.google_api_key"}');
        {r}
    {r}
</script>
{/if}