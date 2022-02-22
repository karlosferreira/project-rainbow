<?php
    defined('PHPFOX') or exit('NO DICE!');
?>
{if $aPlaylist.canEdit}
    <li>
        <a href="{url link='music.playlist' id=$aPlaylist.playlist_id}">
            <i class="ico ico-pencilline-o mr-1"></i>{_p var='edit'}
        </a>
    </li>
    <li>
        <a href="{url link='music.playlist' id=$aPlaylist.playlist_id tab='manage'}">
            <i class="ico ico-music-note-o mr-1"></i>{_p var='manage_songs'}
        </a>
    </li>
{/if}
{if $aPlaylist.canDelete}
    {if $aPlaylist.canEdit}
    <li role="separator" class="divider"></li>
    {/if}
    <li class="item_delete">
        <a href="{url link='music.browse.playlist' id=$aPlaylist.playlist_id}" class="sJsConfirm" data-message="{_p var='are_you_sure_you_want_to_delete_this_playlist'}">
            <i class="ico ico-trash-o mr-1"></i>{_p var='delete'}
        </a>
    </li>
{/if}