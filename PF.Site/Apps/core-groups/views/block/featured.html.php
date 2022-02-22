<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="group-featured-sideblock group-sideblock-container">
    <div class="sticky-label-icon sticky-featured-icon">
        <span class="flag-style-arrow"></span>
        <i class="ico ico-diamond"></i>
    </div>
    {foreach from=$aFeaturedGroups name=pages item=page}
    <div class="page-item">
        <div class="page-cover"
            style="background-image:url(
            {if $page.cover_image_path}
                {img server_id=$page.cover_image_server_id path='photo.url_photo' file=$page.cover_image_path return_url=true}
            {else}
                {img file=$sDefaultCoverPath return_url=true}
            {/if}
        ); cursor: pointer;" data-url="{$page.url}" onclick="$Core.Groups.redirectToDetailGroup(this);">
            <div class="page-shadow">
                <div class="page-avatar">
                    {img user=$page}
                </div>

                <div class="page-like">
                    <b>{$page.total_like}</b>
                    <span>
                        {if $page.total_like == 1}{_p var='member'}{else}{_p var='members'}{/if}
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
