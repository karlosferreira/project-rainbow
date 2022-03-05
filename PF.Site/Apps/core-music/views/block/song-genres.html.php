<?php
/**
 * Created by PhpStorm.
 * User: minhhai
 * Date: 5/15/17
 * Time: 15:49
 */
?>

{if !isset($bNoTitle)}{_p('genres')}:{/if}
{foreach from=$aSong.genres item=aGenre key=iKey}
    {if (isset($sMusicView) && $sMusicView == 'my') || (defined('PHPFOX_IS_PAGES_VIEW') && $aSong.user_id == Phpfox::getUserId())}
    <a href="{permalink module='music.genre' id=$aGenre.genre_id title=$aGenre.name view=my}">{$aGenre.name}</a>{if ($iKey+1) < $iTotal},&nbsp;{/if}
    {else}
    <a href="{permalink module='music.genre' id=$aGenre.genre_id }">{$aGenre.name}</a>{if ($iKey+1) < $iTotal},&nbsp;{/if}
    {/if}
{/foreach}

