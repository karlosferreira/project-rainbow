<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="profile-liked-pages">
    {foreach from=$aPagesList name=pages item=user}
    <div class="page-item">
        <div class="page-cover"
            style="background-image:url(
            {if $user.cover_image_path}
                {img server_id=$user.cover_image_server_id path='photo.url_photo' file=$user.cover_image_path return_url=true}
            {else}
                {img file=$sDefaultCoverPath return_url=true}
            {/if}
        );cursor: pointer;" data-url="{$user.url}" onclick="Core_Pages.redirectToDetailPage(this);">
            <div class="page-shadow">
                <div class="page-avatar">
                    <a href="{$user.url}" title="{$user.title}">
                        {if !empty($user.image_path)}
                        <div class="img-wrapper">
                            {img server_id=$user.image_server_id title=$user.title path='pages.url_image' file=$user.image_path suffix='_200_square' no_default=false max_width=50 max_height=50 time_stamp=true}
                        </div>
                        {else}
                        {img server_id=$user.image_server_id title=$user.title path='pages.url_image' file=$user.image_path suffix='_200_square' no_default=false max_width=50 max_height=50 time_stamp=true}
                        {/if}
                    </a>
                </div>

                <div class="page-like">
                    <b>{$user.total_like}</b>
                    <span>
                        {if $user.total_like == 1}{_p var='like'}{else}{_p var='likes'}{/if}
                    </span>
                </div>
            </div>
        </div>

        <div class="page-info">
            <div class="page-name">
                {$user|user}
            </div>

            <div class="category-name">
                {_p var=$user.type_name}
                {if $user.category_name}
                    » {_p var=$user.category_name}
                {/if}
            </div>
        </div>
    </div>
    {/foreach}
</div>
