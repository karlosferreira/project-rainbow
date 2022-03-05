<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{if count($aItems)}
    {foreach from=$aItems name=playlist item=aItem}
    <div class="checkbox core-music-custom-checkbox">
        <label>
            <input class="music_playlist_checklist_{$iSongId}_{$aItem.playlist_id}"
                   onclick="$Core.music.toggleAddSongToPlaylist(this);"
                   type="checkbox" {if $aItem.id}checked{/if} data-song-id={$iSongId} data-playlist-id={$aItem.playlist_id} data-is-added={$aItem.id}/>
                   <i class="ico ico-square-o"></i>
            {$aItem.name}
        </label>
    </div>
    {/foreach}
{else}
<span class="playlist-none">{_p('no_playlists_found')}</span>
{/if}
