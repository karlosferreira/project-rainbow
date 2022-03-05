<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{if isset($aParentFeed) && $aParentFeed.type_id == 'v'}
    <div class="v-feed-video feed_block_title_content {if $aParentFeed.is_stream}feed-video-url{else}feed-video-upload{/if}">
        <div class="item-media-outer">
            <div class="p-video-overlay-bg" {if !empty($aParentFeed.embed_poster)}style="background-image:url({$aParentFeed.embed_poster});"{/if}></div>
            <div class="{if $aParentFeed.is_stream}fb_video_iframe{else}fb_video_player{/if} v-feed-video {if !empty($aParentFeed.is_facebook_embed)}fb_video_embed{/if}" data-video-id="{$aParentFeed.video_id}">
                {if isset($aParentFeed.embed_code)}{$aParentFeed.embed_code}{/if}
            </div>
        </div>
        <div class="v-feed-inner">
        <a href="{$aParentFeed.feed_link}" class="v-feed-title activity_feed_content_link_title">{$aParentFeed.title|clean}</a>
        <!-- please show view number -->
            <div class="v-feed-view"><span>{$aParentFeed.video_total_view < 100 ? $aParentFeed.video_total_view : '99+'} {if $aParentFeed.video_total_view > 1}{_p var='views_lowercase'}{else}{_p var='view_lowercase'}{/if}</span></div>
            <div class="v-feed-description item_view_content">{$aParentFeed.feed_content|feed_strip|split:55|stripbb}</div>
        </div>
    </div>
    {unset var=$aParentFeed}
{else}
    <div class="v-feed-video feed_block_title_content {if $aFeed.is_stream}feed-video-url{else}feed-video-upload{/if}">
        <div class="item-media-outer">
            <div class="p-video-overlay-bg"  {if !empty($aFeed.embed_poster)}style="background-image:url({$aFeed.embed_poster});"{/if}></div>
            <div class="{if $aFeed.is_stream}fb_video_iframe{else}fb_video_player{/if} {if !empty($aFeed.is_facebook_embed)}fb_video_embed{/if}" data-video-id="{$aFeed.video_id}" id="{$aFeed.feed_id}_{$aFeed.video_id}">
                {if isset($aFeed.embed_code)}{$aFeed.embed_code}{/if}
            </div>
        </div>
        <div class="v-feed-inner">
            <a href="{$aFeed.feed_link}" class="v-feed-title activity_feed_content_link_title">{$aFeed.feed_title|clean}</a>
            <!-- please show view number -->
            <div class="v-feed-view"><span>{$aFeed.video_total_view < 100 ? $aFeed.video_total_view : '99+'} {if $aFeed.video_total_view > 1}{_p var='views_lowercase'}{else}{_p var='view_lowercase'}{/if}</span></div>
            <div class="v-feed-description activity_feed_content_display">{$aFeed.feed_content|feed_strip|split:55|stripbb}</div>
        </div>
    </div>
{/if}