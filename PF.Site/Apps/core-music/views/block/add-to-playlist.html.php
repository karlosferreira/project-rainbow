<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="js_music_add_to_playlist_{$aSong.song_id} dropdown-menu-playlist" id="">
    <li class="dropdown-header">
        {_p('add_to_playlist')}
    </li>

    <li class="music-quick-list-playlist ">
        <div class="item-alert item-error js_music_add_to_playlist_error_{$aSong.song_id}" hidden></div>
        <div class="item-alert item-success js_music_add_to_playlist_success_{$aSong.song_id}" hidden></div>
        <div class="playlist-container js_music_list_playlist_{$aSong.song_id}">
            {if !empty($isDetailPage)}
                {module name='music.user-playlist' song_id=$aSong.song_id}
            {else}
                <span class="form-spin-it p-1">
                    <i class="fa fa-spin fa-circle-o-notch"></i>
                </span>
            {/if}
        </div>
    </li>
    {if Phpfox::getService('music.playlist')->canCreateNewPlaylist(null, false)}
        <li class="music-quick-add-playlist-title" data-song-id="{$aSong.song_id}" onclick="$Core.music.toggleQuickAddPlaylistForm(this);">
            <a data-id="{$aSong.song_id}">
                {_p('add_to_new_playlist')}
                <i class="pull-right ico ico-angle-down js_down" aria-hidden="true"></i>
                <i class="pull-right ico ico-angle-up js_up" aria-hidden="true" hidden></i>
            </a>
        </li>
        <li class="music-playlist-quick-add-form js_music_quick_add_playlist_form" style="display:none">
            <div class="js_music_playlist_quick_form_{$aSong.song_id}" id="" >
                <span class="music-error" style="display:none">{_p('please_input_the_playlist_title')}</span>
                <input type="text" name="name" class="js_music_quick_add_playlist_name form-control"/>
                <div class="music-quick-add-playlist-button">
                    <button class="btn btn-primary btn-sm js_submit" data-song-id="{$aSong.song_id}" onclick="$Core.music.addPlaylist(this);">
                        <span>{_p('Create')}</span>
                    </button>
                    <button class="btn btn-default btn-sm js_cancel" data-song-id="{$aSong.song_id}" onclick="$Core.music.toggleQuickAddPlaylistForm(this);">
                        <span>{_p('Cancel')}</span>
                    </button>
                </div>
            </div>
        </li>
    {/if}
</div>
