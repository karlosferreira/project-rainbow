<?php 
/**
 * [PHPFOX_HEADER]
 *
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>

<div class="event-detail-view">
	<div class="item-banner image_load" data-src="{if $aEventInfo.image_path}{img server_id=$aEventInfo.server_id title=$aEventInfo.title path='event.url_image' file=$aEventInfo.image_path suffix='' return_url=true}{else}{param var='event.event_default_photo'}{/if}"></div>
	<div class="core-events-detail-header-container">
		<div class="core-events-view-title header-page-title item-title {if isset($aTitleLabel.total_label) && $aTitleLabel.total_label > 0}header-has-label-{$aTitleLabel.total_label}{/if}">
	        <a href="{$aEventInfo.link}" class="ajax_link">{$aEventInfo.title|clean}</a>
	        <div class="event-icon">
	            {if (isset($sView) && $sView == 'my' || isset($bIsDetail)) && $aEventInfo.view_id == 1}
	            <div class="sticky-label-icon sticky-pending-icon">
	                <span class="flag-style-arrow"></span>
	                <i class="ico ico-clock-o"></i>
	            </div>
	            {/if}
	            {if $aEventInfo.is_sponsor}
	            <div class="sticky-label-icon sticky-sponsored-icon">
	                <span class="flag-style-arrow"></span>
	                <i class="ico ico-sponsor"></i>
	            </div>
	            {/if}
	            {if $aEventInfo.is_featured}
	            <div class="sticky-label-icon sticky-featured-icon">
	                <span class="flag-style-arrow"></span>
	                <i class="ico ico-diamond"></i>
	            </div>
	            {/if}
	        </div>
	    </div>
		<div class="event-detail-top">
			<div class="item-outer">
				<div class="item-date-month" >
		            <div class="item-date">{$aEventInfo.start_day}</div>
		            <div class="item-month">{$aEventInfo.start_month}</div>
		            <div class="item-hour">{$aEventInfo.start_hour_minute}</div>
		        </div>
		        <div class="item-inner">
					<div class="event-info">
				        <div class="event-info-image">{img user=$aEventInfo suffix='_120_square'}</div>
				        <div class="event-info-main">
				            <span class="event-author">{$aEventInfo|user} {_p var='on'} {$aEventInfo.time_stamp|convert_time:'core.global_update_time'}</span>
				            <!-- @back-end please fill info -->
				            <span class="item-privacy-view">{$aEventInfo.privacy_title}<span class="item-dots"></span>{$aEventInfo.total_view|number_format} {if $aEvent.total_view == 1}{_p var='view__l'}{else}{_p var='views_lowercase'}{/if}</span>
				        </div>
				    </div>
				    <div class="item-choice-default" id="js_item_choice_default">
					    {module name='event.rsvp'}
					</div>
					<div class="item-choice-share-responsive" id="js_item_choice_share_responsive">
						<div class="item-wrapper-outer">
							<div class="item-choice-responsive ">
							    {template file='event.block.rsvp-action'}
							</div>
                            {if (int)$aEventInfo.privacy == 0}
							<!-- @back-end please fill action share -->
							<div class="item-share-responsive ">
								<a href="javascript:void(0);" onclick="tb_show('{_p var='share' phpfox_squote=true}', $.ajaxBox('share.popup', 'height=300&amp;width=550&amp;type={$aShare.sBookmarkType}&amp;url={$aShare.sBookmarkUrl}&amp;title={$aShare.sBookmarkTitle}{if isset($aShare.sFeedShareId) && $aShare.sFeedShareId > 0}&amp;feed_id={$aShare.sFeedShareId}{/if}&amp;sharemodule={$aShare.sShareModuleId}')); return false;">
								    <span class="ico ico-share-o"></span> {_p var='share'}
								</a>
							</div>
                            {/if}
						</div>
					</div>
				</div>
				{if $aEventInfo.hasPermission}
				<div class="item_bar event-button-option">
			        <div class="item_bar_action_holder">
			            <a href="#" class="item_bar_action" data-toggle="dropdown" role="button"><span>{_p('Actions')}</span><i class="ico ico-gear-o"></i></a>
			            <ul class="dropdown-menu dropdown-menu-right">
			                {template file='event.block.menu'}
			            </ul>
			        </div>
			    </div>
				{/if}
			</div>
		</div>
	</div>
	<div class="item-map-info">
		<div class="item-time">
			<span class="ico ico-calendar-o"></span>
			<div class="item-info">
				{$aEventInfo.event_date}
			</div>
		</div>
        {if $aEventInfo.location != ''}
            <div class="item-location">
                <span class="ico ico-checkin-o"></span>
                <div class="item-info">
                    <div class="item-info-1">{$aEventInfo.location|clean|split:60}</div>
                </div>
            </div>
        {/if}
		{if isset($aEventInfo.map_location)}
		<div class="item-map-container">
			<div class="item-map-action">
				<div class="item-map-collapse js_core_event_toggle_map">
					<a class="js_core_event_show_map">{_p var='event_show_map'}</a>
					<a class="js_core_event_hide_map hide">{_p var='event_hide_map'}</a>
				</div>
				<div class="item-map-viewmore">
					<a href="https://maps.google.com/?q={$aEventInfo.map_location}" target="_blank">{_p var='view_on_google_maps'}</a>
				</div>
			</div>
			<div class="item-location-map js_core_event_map_collapse hide">
				<div class="item-map-img">
					{if isset($aEventInfo.map_location) && !empty($sExtraParam)}
                        {if !empty($aEventInfo.location_lat) && !empty($aEventInfo.location_lng)}
                            {module name='core.gmap-entry-view' lat=$aEventInfo.location_lat lng=$aEventInfo.location_lng map_height='300px'}
                        {else}
                            <a href="https://maps.google.com/?q={$aEventInfo.map_location}" target="_blank" title="{_p var='view_this_on_google_maps'}">
                                <div class="item-map" style="background-image: url(//maps.googleapis.com/maps/api/staticmap?center={$aEventInfo.map_location}&amp;zoom=16&amp;size=1200x600&amp;maptype=roadmap{$sExtraParam});">
                                    <div style="margin-left:-8px; margin-top:-8px; position:absolute; background:#fff; border:8px blue solid; width:12px; height:12px; left:50%; top:50%; z-index:1; overflow:hidden; text-indent:-1000px; border-radius:12px;">Marker</div>
                                </div>
                            </a>
                        {/if}
					{/if}
				</div>
			</div>
		</div>
		{/if}
        {if !empty($aEventInfo.online_link)}
            <div class="item-location mt-1">
                <span class="ico ico-link"></span>
                <div class="item-info">
                    <div class="item-info-1">
                        <a href="{$aEventInfo.online_link}" target="_blank">{$aEventInfo.online_link}</a>
                    </div>
                </div>
            </div>
        {/if}
	</div>
	
	<div class="event-detail-main">
		{if $aEventInfo.view_id == 1}
            {template file='core.block.pending-item-action'}
		{/if}

        {if $aEventInfo.view_id == 0}
		<!-- @back-end please fill info  -->
		<div class="core-event-detail-member" data-event-id="{$aEventInfo.event_id}" id="js_core_event_detail_member">
			<div class="item-member">
				<a href="javascript:void(0);" data-tab="attending"  class="js_core_event_detail_guest_list"><span class="item-number js_attending_number_info">{$iAttendingCnt}</span><span class="item-text">{_p var='attending'}</span></a>
			</div>
			<div class="item-member">
				<a href="javascript:void(0);" data-tab="maybe" class="js_core_event_detail_guest_list"><span class="item-number js_maybe_attending_number_info">{$iMaybeCnt}</span><span class="item-text">{_p var='maybe_attending'}</span></a>
			</div>
			<div class="item-member">
				<a href="javascript:void(0);" data-tab="awaiting" class="js_core_event_detail_guest_list"><span class="item-number js_not_attending_number_info">{$iAwaitingCnt}</span><span class="item-text">{_p var='not_attending'}</span></a>
			</div>
		</div>
        {/if}
		<div class="core-events-view-content-collapse-container">  
			<div class="core-events-view-content-collapse js_core_events_view_content_collapse">
				<!-- dont break-line description issue layout -->
				<div class="event-item-content item_view_content">{$aEventInfo.description|parse|split:55}
				</div>
		        
				{if is_array($aEventInfo.categories) && count($aEventInfo.categories)}
		        <div class="event-category">
		        	<span class="item-category-title">{_p var='category'}</span>
		        	{$aEventInfo.categories|category_display}
		        </div>
		        {/if}
		    </div>
		    <div class="core-events-view-action-collapse js-core-event-action-collapse">
	            <a class="item-viewmore-btn js-item-btn-toggle-collapse">{_p var="view_more"} <i class="ico ico-caret-down"></i></a>
	            <a class="item-viewless-btn js-item-btn-toggle-collapse">{_p var="view_less"} <i class="ico ico-caret-up"></i></a>
	        </div>
		</div>
		{if $aEventInfo.total_attachment}
		<div class="mb-2">
                {module name='attachment.list' sType=event iItemId=$aEventInfo.event_id}
        </div>
        {/if}
        {addthis url=$aEventInfo.bookmark title=$aEventInfo.title description=$sShareDescription}
        <div class="event-detail-feedcomment item-detail-feedcomment">
            {module name='feed.comment'}
        </div>
        {unset var=$sFeedType}
	</div>
</div>
<div class="marvic_separator clearfix"></div>