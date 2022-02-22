<?php 
	defined('PHPFOX') or exit('NO DICE!'); 
?>

<div class="albums-widget-widget item-container list-view music clearfix">
	<div class="item-container-list">
    {foreach from=$aNewAlbums item=aAlbum}
        {template file='music.block.mini-album'}
    {/foreach}
	</div>
</div>