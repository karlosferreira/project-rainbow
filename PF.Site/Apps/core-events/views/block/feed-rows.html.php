<article class="event-app core-feed-item" data-url="" data-uid="{$aEvent.event_id}" id="js_event_item_holder_{$aEvent.event_id}">
    <div class="item-media-banner">
        <!-- image -->
        <a class="item-media" href="{$aEvent.url}">
            <span class="item-media-src" style="background-image: url({if $aEvent.image_path}{img server_id=$aEvent.server_id title=$aEvent.title path='event.url_image' file=$aEvent.image_path suffix='' return_url=true}{else}{param var='event.event_default_photo'}{/if})"  alt="{$aEvent.title}"></span>
        </a>
    </div>
    <div class="item-outer">
        <div class="item-calendar">
            <div class="item-date">{$aEvent.start_day}</div>
            <div class="item-month">{$aEvent.start_month}</div>
        </div>
        <div class="item-inner">
            <div class="item-calendar-full">
                {$aEvent.event_date}
            </div>
            <div class="item-title">
                {if isset($sView) && $sView == 'my'}
                    {if (isset($aEvent.view_id) && $aEvent.view_id == 1)}
                        <span class="pending-label">{_p('pending_label')}</span>
                    {/if}
                {/if}
                <a href="{$aEvent.url}" class="core-feed-title line-1" itemprop="url">{$aEvent.title|clean}</a>
            </div>
            <div class="item-wrapper-info">
                <div class="item-side-left">
                        <div class="item-location core-feed-description line-1">
                            {if !empty($aEvent.location)}
                                {$aEvent.location|clean}
                            {elseif !empty($aEvent.is_online) && !empty($aEvent.online_link)}
                                <a href="{$aEvent.online_link}" target="_blank">{$aEvent.online_link}</a>
                            {/if}
                        </div>
                    <div class="item-info core-feed-description">
                        <span class="item-time">
                            {$aEvent.event_date}
                        </span>
                        <span class="item-total-guest">
                            <a class="js_feed_attending_number" href="javascript:void(0);" onclick="tb_show('{_p var='event_guests_title' phpfox_squote=true}', $.ajaxBox('event.showGuestList', 'height=300&amp;width=500&amp;tab=attending&amp;event_id={$aEvent.event_id}')); return false;">{$aEvent.total_attending} {if (int)$aEvent.total_attending == 1}{_p var='event_feed_guest'}{else}{_p var='event_feed_guests'}{/if}</a>
                        </span>
                    </div>
                </div>
                <div class="item-side-right">
                    <div class="item-action">
                        {template file='event.block.rsvp-action'}
                    </div>
                </div>
            </div>
        </div>
    </div>
</article>