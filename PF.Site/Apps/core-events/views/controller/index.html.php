<?php
/**
 * [PHPFOX_HEADER]
 *
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{if !count($aEvents)}
{if ! PHPFOX_IS_AJAX }
<div class="extra_info">
	{_p var='no_events_found'}
</div>
{/if}
{else}
{if ! PHPFOX_IS_AJAX }
<div class="event-container">
{/if}
{if $bIsGroupByDate}
    {foreach from=$aEvents key=sDate item=aGroups}
        {foreach from=$aGroups name=events item=aEvent}
            {template file='event.block.item'}
        {/foreach}
    {/foreach}
{else}
    {foreach from=$aEvents name=events item=aEvent}
        {template file='event.block.item'}
    {/foreach}
{/if}
{pager}
<!--		end foreach2-->
{if ! PHPFOX_IS_AJAX }
</div>
{/if}
{if $bShowModerator}
    {moderation}
{/if}

{/if}
