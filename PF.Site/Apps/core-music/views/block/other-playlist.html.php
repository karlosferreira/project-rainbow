<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="playlist-widget-widget item-container list-view music">
    <div class="item-container-list">
        {foreach from=$aOtherPlaylists item=aPlaylist}
            <div class="album-item type-playlist">
                <div class="item-outer">
                    <!-- photo -->
                    <div class="item-media albums-bg">
                        <div class="item-media-inner">
                            <a  class="music-bg-thumb thumb-border gradient" href="{permalink module='music.playlist' id=$aPlaylist.playlist_id title=$aPlaylist.name}"
                                style="background-image: url(
                            {if $aPlaylist.image_path}
                                {img return_url="true" server_id=$aPlaylist.server_id path='music.url_image' file=$aPlaylist.image_path suffix='_120_square'}
                            {else}
                                {param var='music.default_playlist_photo'}
                            {/if}
                            )">
                            <span class="albums-songs"><i class="ico ico-music-note"></i>{$aPlaylist.total_track}</span>
                            </a>
                        </div>
                        <div class="albums-bg-outer"><div class="albums-bg-inner"></div></div>
                    </div>
                    <!-- info -->
                    <div class="item-inner">
                        <div class="item-title">
                            <a href="{permalink module='music.playlist' id=$aPlaylist.playlist_id title=$aPlaylist.name}">{$aPlaylist.name|clean}</a>
                        </div>
                    </div>
                </div>
            </div>
        {/foreach}
    </div>
</div>