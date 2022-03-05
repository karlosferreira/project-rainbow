<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="page-featured-sideblock page-sideblock-container">
    <div class="sticky-label-icon sticky-featured-icon">
        <span class="flag-style-arrow"></span>
        <i class="ico ico-diamond"></i>
    </div>
    {foreach from=$aFeaturedPages name=pages item=page}
    <div class="page-item">
        <div class="page-cover"
            style="background-image:url(
            {if $page.cover_image_path}
                {img server_id=$page.cover_image_server_id path='photo.url_photo' file=$page.cover_image_path return_url=true}
            {else}
                {img file=$sDefaultCoverPath return_url=true}
            {/if}
        );cursor: pointer;" data-url="{$page.url}" onclick="Core_Pages.redirectToDetailPage(this);">
            <div class="page-shadow">
                <div class="page-avatar">
                    <a href="{$page.url}" title="{$page.title}">
                        {if !empty($page.image_path)}
                        <div class="img-wrapper">
                            {img server_id=$page.image_server_id title=$page.title path='pages.url_image' file=$page.image_path suffix='_200_square' no_default=false time_stamp=true}
                        </div>
                        {else}
                        {img server_id=$page.image_server_id title=$page.title path='pages.url_image' file=$page.image_path suffix='_200_square' no_default=false max_width=40 max_height=40 time_stamp=true}
                        {/if}
                    </a>
                </div>

                <div class="page-like">
                    <b>{$page.total_like}</b>
                    <span>
                        {if $page.total_like == 1}{_p var='like'}{else}{_p var='likes'}{/if}
                    </span>
                </div>
            </div>
        </div>

        <div class="page-info">
            <div class="page-name">
                {$page|user}
            </div>

            <div class="category-name">
                {_p var=$page.type_name}
                {if $page.category_name}
                    » {_p var=$page.category_name}
                {/if}
            </div>
        </div>
    </div>
    {/foreach}
</div>
