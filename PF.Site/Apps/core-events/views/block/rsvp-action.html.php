<?php
/**
 * [PHPFOX_HEADER]
 *
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="item-choice event-rsvp-action-wrapper" id="js_event_rsvp_action_{$aEvent.event_id}" data-current-rsvp="{$aEvent.rsvp_id}" data-invited="{$aEvent.is_invited}">
    <div class="js_event_rsvp_action_dropdown {if !isset($aEvent.rsvp_id) || (isset($aEvent.rsvp_id) && !$aEvent.is_invited && $aEvent.rsvp_id == 0)}hide{/if} dropdown ">
        <a data-toggle="dropdown" class="btn  btn-default btn-icon btn-sm">
            <span class="txt-label">
                    {if $aEvent.rsvp_id == 1}
                        <i class="ico ico-check-circle mr-1" title="{_p var='attending'}"></i><span class="item-text">{_p var='attending'}</span>
                    {elseif $aEvent.rsvp_id == 2 || (!isset($aEvent.rsvp_id) || (isset($aEvent.rsvp_id) && !$aEvent.is_invited && $aEvent.rsvp_id == 0))}
                        <i class="ico ico-star mr-1" title="{_p var='maybe_attending'}"></i><span class="item-text">{_p var='maybe_attending'}</span>
                    {elseif $aEvent.rsvp_id == 3}
                        <i class="ico ico-ban mr-1" title="{_p var='not_attending'}"></i><span class="item-text">{_p var='not_attending'}</span>
                    {elseif $aEvent.rsvp_id == 0 && $aEvent.is_invited}
                        {_p var='confirm'}
                    {/if}
            </span>
            <i class="ico ico-caret-down"></i>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
            <li role="button">
                <a data-event-id="{$aEvent.event_id}" data-toggle="event_rsvp" rel="1"  {if isset($aEvent.rsvp_id) && $aEvent.rsvp_id == 1}class="is_active_image"{/if}>
                    <i class="ico ico-check-circle-o mr-1" title="{_p var='attending'}"></i><span class="item-text">{_p var='attending'}</span>
                </a>
            </li>
            <li role="button">
                <a data-event-id="{$aEvent.event_id}" data-toggle="event_rsvp" rel="2" {if isset($aEvent.rsvp_id) && $aEvent.rsvp_id == 2}class="is_active_image"{/if}>
                    <i class="ico ico-star-o mr-1" title="{_p var='maybe_attending'}"></i><span class="item-text">{_p var='maybe_attending'}</span>
                </a>
            </li>
            {if !$aEvent.is_invited}
                <li role="separator" class="divider"></li>
            {/if}
            <li role="button">
                <a data-event-id="{$aEvent.event_id}" data-toggle="event_rsvp" rel="{if $aEvent.is_invited}3{else}0{/if}" {if isset($aEvent.rsvp_id) && $aEvent.rsvp_id == 3 && $aEvent.is_invited}class="is_active_image"{/if}>
                    <i class="ico ico-ban mr-1" title="{_p var='not_attending'}"></i><span class="item-text">{_p var='not_attending'}</span>
                </a>
            </li>

        </ul>
    </div>

    <div class="js_event_rsvp_action_btn {if (isset($aEvent.rsvp_id) && $aEvent.rsvp_id != 0) || ($aEvent.is_invited && $aEvent.rsvp_id == 0)}hide{/if}">
        <a class="btn btn-default btn-sm" data-event-id="{$aEvent.event_id}" data-toggle="event_rsvp" rel="2" data-public="true">
            <i class="ico ico-star-o mr-1"></i><span class="item-text">{_p var='maybe_attending'}</span>
        </a>
    </div>
</div>