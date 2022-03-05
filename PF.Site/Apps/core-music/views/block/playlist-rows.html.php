<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<article class="albums-item list-view music type-playlist" id="js_album_{$aPlaylist.playlist_id}">
	<div class="item-outer">
        <div class="item-media albums-bg view-all">
            <div class="item-media-inner">
                <a  href="{permalink title=$aPlaylist.name id=$aPlaylist.playlist_id module='music.playlist'}"
                    class="music-bg-thumb thumb-border"
                    style="background-image:url(
                        {if $aPlaylist.image_path}
                            {img return_url="true" server_id=$aPlaylist.server_id title=$aPlaylist.name path='music.url_image' file=$aPlaylist.image_path suffix='_500_square'}
                        {else}
                            {param var='music.default_playlist_photo'}
                        {/if}
                    )">
                    <span class="albums-songs"><i class="ico ico-music-note"></i>{$aPlaylist.total_track}</span>
                    <span class="music-overlay"><i class="ico ico-play-o"></i></span>
                </a>
                {if $aPlaylist.canDelete}
                <div class="moderation_row">
                       <label class="item-checkbox">
                           <input type="checkbox" class="js_global_item_moderate" name="item_moderate[]" value="{$aPlaylist.playlist_id}" id="check{$aPlaylist.playlist_id}" />
                           <i class="ico ico-square-o"></i>
                       </label>
                </div>
                {/if}
            </div>
            <div class="albums-bg-outer"><div class="albums-bg-inner"></div></div>
        </div>

        <div class="item-inner">
            <div class="item-title">
                <a href="{permalink title=$aPlaylist.name id=$aPlaylist.playlist_id module='music.playlist'}">{$aPlaylist.name|clean}</a>
            </div>

            <div class="item-statistic dot-separate">
                <span>
                    {if $aPlaylist.total_track != 1}
                        {_p var='music_total_tracks' total=$aPlaylist.total_track|short_number}
                    {else}
                        {_p var='music_total_track' total=$aPlaylist.total_track|short_number}
                    {/if}
                </span>
                <span class="music-dots">.</span>
                <span>
                    {if $aPlaylist.total_view != 1}
                        {_p var='music_total_views' total=$aPlaylist.total_view|short_number}
                    {else}
                        {_p var='music_total_view' total=$aPlaylist.total_view|short_number}
                    {/if}
                </span>
            </div>
            {if !isset($aPlaylist.is_in_feed)}
                <div class="item-author">
                    {img user=$aPlaylist suffix='_120_square'}
                    <div class="item-author-infor">
                        <span>{_p var='by'} {$aPlaylist|user}</span>
                        <span>{$aPlaylist.time_stamp|convert_time}</span>
                    </div>
                </div>
                {if $aPlaylist.canHasPermission}
                    <div class="item-option">
                        <div class="dropdown">
                            <span role="button" class="row_edit_bar_action" data-toggle="dropdown">
                                <i class="ico ico-gear-o"></i>
                            </span>
                            <ul class="dropdown-menu dropdown-menu-right">
                                {template file='music.block.menu-playlist'}
                            </ul>
                        </div>
                    </div>
                {/if}
            {/if}
        </div>
    </div>
</article>