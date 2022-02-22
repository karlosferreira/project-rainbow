<?php

defined('PHPFOX') or exit('NO DICE!');

?>

<div class="featured-album-block">
    <div class="flag_style_parent">
        <div class="sticky-label-icon sticky-featured-icon">
            <span class="flag-style-arrow"></span>
            <i class="ico ico-diamond"></i>
        </div>
    </div>
    {foreach from=$aFeaturedAlbums item=aAlbum}
    <article data-url="{$aAlbum.link}" data-uid="{$aAlbum.album_id}" id="js_album_id_{$aAlbum.album_id}" class="photo-album-item pl-1 pr-1 mb-2">
        <div class="item-outer">
            <div class="item-media">
                <a href="{$aAlbum.link}"
                   class="{if ($aAlbum.mature == 0 || (($aAlbum.mature == 1 || $aAlbum.mature == 2) && Phpfox::getUserId() && Phpfox::getUserParam('photo.photo_mature_age_limit') <= Phpfox::getUserBy('age'))) || $aAlbum.user_id == Phpfox::getUserId()}{else}photo-mature{/if}"
                   style="background-image: url(
                {if !empty($aAlbum.destination)}
                    {img return_url="true" server_id=$aAlbum.server_id path='photo.url_photo' file=$aAlbum.destination suffix='_500' max_width=500 max_height=500}
                {else}
                {param var='photo.default_album_photo'}
                {/if}
                )">
                    <span class="item-total-photo">
                        <i class="ico ico-photos-alt-o"></i>
                        {if isset($aAlbum.total_photo)}
                            {if $aAlbum.total_photo == '1'}1{else}{$aAlbum.total_photo|number_format}{/if}
                        {/if}
                        {plugin call='photo.template_block_album_entry_extra_info'}
                    </span>
                </a>

            </div>
        </div>

    </article>

    {/foreach}
</div>
