<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
{foreach from=$aUsers name=users item=aUser}
    {if $layoutType == 'content'}
    <div class="t_center p_bottom_10" style="width:23%; float:left;">
    {else}
    <div class="t_center p_bottom_10" style="width:32%; float:left;">
    {/if}
        {if $layoutType == 'content'}
            {img user=$aUser suffix='_120_square' max_width=75 max_height=75}
        {else}
            {img user=$aUser suffix='_120_square' max_width=50 max_height=50}
        {/if}
        <div class="p_top_4">
            {$aUser|user}
        </div>
    </div>
    {if $layoutType == 'content'}
        {if $phpfox.iteration.users == 4}
            <div class="clear"></div>
        {/if}
    {else}
        {if is_int($phpfox.iteration.users/3)}
            <div class="clear"></div>
        {/if}
    {/if}
{/foreach}
<div class="clear"></div>
<div class="t_right">
	<ul class="item_menu">
		<li><a href="{url link='user.browse' sort='joined'}">{_p var='view_more'}</a></li>
	</ul>
</div>