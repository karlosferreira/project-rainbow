<article class="music_row item-music item-feed-music-song" id="js_controller_music_track_{$aSong.song_id}" data-songid="{$aSong.song_id}" xmlns="http://www.w3.org/1999/html">
    <div class="item-outer {if !isset($aSong.is_in_feed) && $aSong.hasPermission}item-manage{/if}">
        <div class="item-media">
            <span class="button-play" onclick="$Core.music.playSongRow(this)"><i class="ico ico-play-o"></i></span>
        </div>
        <div class="item-inner">
            <div class="item-title">
                <a class="" href="{if $bIsSponsorFeed && $iSponsorId}{url link='ad.sponsor' view=$iSponsorId}{else}{permalink title=$aSong.title id=$aSong.song_id module='music'}{/if}">{$aSong.title|clean}</a>
            </div>
        </div>
        <div class="feed-music-action ">
            <div class="item-statistic dot-separate">
                {if $aSong.total_play != 1}
                    {_p var='music_total_plays' total=$aSong.total_play|short_number}
                {else}
                    {_p var='music_total_play' total=$aSong.total_play|short_number}
                {/if}
            </div>
            {if Phpfox::getUserParam('music.can_access_music') }
            <div class="feed-music-action-inner">
                <a class="feed-music-action-icon" href="{permalink module='music' id=$aSong.song_id title=$aSong.title}"><i class="ico ico-external-link"></i></a>
                {if Phpfox::isUser() && (int)Phpfox::getUserBy('profile_page_id') === 0}
                    <div class="dropdown js_music_dropdown_add_to_playlist item-option-playlist">
                            <span role="button" class="row_edit_bar_action feed-music-action-icon" data-toggle="dropdown" data-song-id="{$aSong.song_id}" onclick="$Core.music.loadUserPlaylist(this);">
                                <i class="ico ico-list-plus"></i>
                            </span>
                        <ul class="dropdown-menu dropdown-menu-right js-music-prevent-browser-scroll">
                            {template file='music.block.add-to-playlist'}
                        </ul>
                    </div>
                {/if}
                {if Phpfox::getUserParam('music.can_download_songs')}
                    <a href="{url link='music.download' id=$aSong.song_id}" class="feed-music-action-icon no_ajax_link download" title="{_p('download')}">
                        <span>
                            <i class="ico ico-download-alt" aria-hidden="true"></i>
                        </span>
                    </a>
                {/if}
            </div>
            <div class="feed-music-action-more js_feed_music_action_more">
                <span class="item-icon"><i class="ico  ico-dottedmore-vertical-o"></i></span>
            </div>
            {/if}
        </div>
    </div>
    
    <div class="item-player music_player">
        <div class="audio-player dont-unbind-children js_player_holder  {if !Phpfox::getUserParam('music.can_download_songs')}disable-download{/if}">
            <div class="js_music_controls">
                <a href="javascript:void(0)" class="js_music_repeat ml-1" title="{_p('repeat')}">
                    <i class="ico ico-play-repeat-o"></i>
                </a>
                
            </div>
            <audio class="js_song_player" src="{$aSong.song_path}" type="audio/mp3" controls="controls"></audio>
        </div>
    </div>
 
</article>