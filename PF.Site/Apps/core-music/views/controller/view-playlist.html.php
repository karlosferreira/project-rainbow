<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<link href="{param var='core.path_actual'}PF.Site/Apps/core-music/assets/jscript/mediaelementplayer/mediaelementplayer.css" rel="stylesheet" type="text/css">

<div class="album-detail item_view">
    <div class="item_info">
        {img user=$aPlaylist suffix='_120_square'}
        <div class="item_info_author">
            <div>{_p var='By'} {$aPlaylist|user:'':'':50}</div>
            <span>{$aPlaylist.time_stamp|convert_time}</span>
        </div>
    </div>
    {if $aPlaylist.canHasPermission}
    <div class="item_bar">
        <div class="item_bar_action_holder">
            <span role="button" data-toggle="dropdown" class="item_bar_action"><i class="ico ico-gear-o"></i></span>
            <ul class="dropdown-menu dropdown-menu-right">
                {template file='music.block.menu-playlist'}
            </ul>
        </div>
    </div>
    {/if}

    <div class="item-content list-view music type-playlist">
        <div class="item-media view-all albums-bg" style="background-image: url('{param var='core.path_actual'}PF.Site/Apps/core-music/assets/image/song_detail_bg.png');">
            <a class="media-thumb" href="javascript:void(0)">
                {if !empty($aPlaylist.image_path)}
                <span class="music-bg-thumb thumb-border" style="background-image: url('{img server_id=$aPlaylist.server_id path='music.url_image' file=$aPlaylist.image_path suffix='_200_square' max_width='200' max_height='200' return_url=true}')"></span>
                {else}
                <img src="{param var='music.default_playlist_photo'}">
                {/if}
                <div class="albums-bg-outer">
                    <div class="albums-bg-inner"></div>
                </div>
            </a>
            {if $aPlaylist.total_track}
                <div class="item-total text-uppercase text-center">
                    <strong>{$aPlaylist.total_track}</strong> {if $aPlaylist.total_track == 1}{_p var='song'}{else}{_p var='songs'}{/if}
                </div>
            {else}
                <div class="playlist-none-info">
                    <p class="extra_info">{_p var='you_not_yet_added_any_song_to_this_playlist'}</p>
                    <a href="{url link='music'}" class="btn btn-default">{_p var='find_your_favorite_songs'}</a>
                </div>
            {/if}
            <div class="item-playing mt-3 text-center" style="display: none;">
                {_p var='playing_uppercase'}: <strong id="js_playing_song_title"></strong>
            </div>
        </div>
    </div>

    <div class="item-player music_player">
        <div class="music_player-inner">
            {module name='music.track' playlist_id=$aPlaylist.playlist_id}
        </div>

        <div class="item-sub-info mt-3">
            <div class="item-description item_view_content">
                {$aPlaylist.description|highlight:'search'|parse|shorten:200:'feed.view_more':true|split:55}
            </div>
        </div>
        {if $aPlaylist.total_attachment}
            {module name='attachment.list' sType=music_playlist iItemId=$aPlaylist.playlist_id}
        {/if}
    </div>
    <div class="js_moderation_on pt-2 mt-4" {if $aPlaylist.view_id != 0}style="display:none;" class="js_moderation_on"{/if}>
        <div class="item-addthis mb-2">{addthis url=$aPlaylist.bookmark title=$aPlaylist.name description=$sShareDescription}</div>
        <div class="item-detail-feedcomment">
            {module name='feed.comment'}
        </div>
    </div>
</div>
