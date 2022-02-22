<?php
defined('PHPFOX') or exit('NO DICE!');
?>

{foreach from=$aWidgetBlocks item=aWidgetBlock}
<div class="block">
    <div class="title">{$aWidgetBlock.title|clean}</div>
    <div class="content">
    	<div class="item_view_content">
	        {$aWidgetBlock.text|parse|shorten:'300':'view_more':true|split:30}
	    </div>
    </div>
</div>
{/foreach}