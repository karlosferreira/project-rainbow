<?php
defined('PHPFOX') or exit('NO DICE!');

?>

{if count($aPlaylists)}
    {if ! PHPFOX_IS_AJAX }<div class="item-container playlist-listing">{/if}
        {foreach from=$aPlaylists name=playlist item=aPlaylist}
            {template file='music.block.playlist-rows'}
        {/foreach}
        {moderation}
        {pager}
    {if ! PHPFOX_IS_AJAX }</div>{/if}
{else}
    {if ! PHPFOX_IS_AJAX }
        <div class="extra_info">
            {_p var='no_playlists_found'}
        </div>
    {/if}
{/if}
