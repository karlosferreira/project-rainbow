<?php 
/**
 * [PHPFOX_HEADER]
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
<div class="dont-unbind-children">
    <form class="form" id="event_rsvp" method="post" action="{url link='current'}" data-event-id="{$aEvent.event_id}" data-current-rsvp="{$aEvent.rsvp_id}">
        {if isset($aCallback) && $aCallback !== false}
        <div><input type="hidden" name="module" value="{$aCallback.module}" /></div>
        <div><input type="hidden" name="item" value="{$aCallback.item}" /></div>
        {/if}
        <input type="hidden" class="js_event_invited" value="{$bInvited}">
        {if $aEvent.view_id == 0}
        <div class="item-event-option-wrapper js_btn_rsvp_actions {if $aEvent.rsvp_id != 0}hide{/if}">
            <div class="item-event-option attending"  data-phrase="{_p var='attending'}" data-icon="ico-check-circle">
                <label class="item-event-radio">
                    <input type="radio" name="rsvp" value="1" class="v_middle" data-rsvp="1" {if $aEvent.rsvp_id == 1}checked="checked"{/if}/>
                    <span class="btn btn-sm btn-default btn-icon js_rsvp_title"><i class="ico ico-check-circle-o"></i>{_p var='attending'}</span>
                </label>
            </div>

            <div class="item-event-option maybe_attending" data-phrase="{_p var='maybe_attending'}" data-icon="ico-star">
                <label class="item-event-radio">
                    <input type="radio" name="rsvp" value="2" class="v_middle" data-rsvp="2" {if $aEvent.rsvp_id == 2}checked="checked"{/if}/>
                    <span class="btn btn-sm btn-default btn-icon js_rsvp_title"><i class="ico ico-star-o"></i>{_p var='maybe_attending'}</span>
                </label>
            </div>
            
            {if $bInvited || $aEvent.user_id == Phpfox::getUserId() }
            <div class="item-event-option not_attending" data-phrase="{_p var='not_attending'}" data-icon="ico-ban">
                <label class="item-event-radio">
                    <input type="radio" name="rsvp" value="3" class="v_middle" data-rsvp="3" {if $aEvent.rsvp_id == 3}checked="checked" {/if}/>
                    <span class="btn btn-sm btn-default btn-icon js_rsvp_title"><i class="ico ico-ban"></i>{_p var='not_attending'}</span>
                </label>
            </div>
            {/if}
        </div>

        <div class="item-event-option-dropdown-wrapper js_dropdown_rsvp_actions {if $aEvent.rsvp_id == 0}hide{/if}">
            <div class="dropdown">
                <div data-toggle="dropdown" class="btn btn-default btn-sm">
                    <div id="js_dropdown_rsvp_text">
                        {if $aEvent.rsvp_id == 1}
                        <i class="ico ico-check-circle mr-1"></i>{_p var='attending'}
                        {elseif $aEvent.rsvp_id == 2}
                        <i class="ico ico-star mr-1"></i>{_p var='maybe_attending'}
                        {elseif $aEvent.rsvp_id == 3}
                        <i class="ico ico-ban mr-1"></i>{_p var='not_attending'}
                        {/if}
                    </div>
                    <i class="ico ico-caret-down ml-1"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-right">
                    <li class="item-event-option {if $aEvent.rsvp_id == 1}active{/if}" data-rsvp-dropdown="1" data-phrase="{_p var='attending'}" data-icon="ico-check-circle">
                        <a href="javascript:void(0);"><i class="ico ico-check-circle-o"></i>{_p var='attending'}</a>
                    </li>
                    <li class="item-event-option {if $aEvent.rsvp_id == 2}active{/if}" data-rsvp-dropdown="2" data-phrase="{_p var='maybe_attending'}" data-icon="ico-star">
                        <a href="javascript:void(0);"><i class="ico ico-star-o"></i>{_p var='maybe_attending'}</a>
                    </li>
                    {if $bInvited || $aEvent.user_id == Phpfox::getUserId()}
                    <li class="item-event-option {if $aEvent.rsvp_id == 3}active{/if}" data-rsvp-dropdown="3" data-phrase="{_p var='not_attending'}" data-icon="ico-ban">
                        <a href="javascript:void(0);"><i class="ico ico-ban"></i>{_p var='not_attending'}</a>
                    </li>
                    {else}
                    <li role="separator" class="divider"></li>
                    <li class="js_rsvp_cancel">
                        <a href="javascript:void(0);">{_p var='cancel'}</a>
                    </li>
                    {/if}
                </ul>
            </div>
        </div>
        {/if}
    </form>
    <div class="item-statistic">
        <span class="item-count-view">{$aEvent.total_view|short_number}</span>{if $aEvent.total_view == 1}{_p var='view__l'}{else}{_p var='views_lowercase'}{/if}
    </div>
</div>

{literal}
<script>
	$Behavior.event_rsvp = function() {
	    setTimeout(function(){
            $('.js_btn_rsvp_actions .item-event-option .btn').off('click');
            $('.js_dropdown_rsvp_actions ul li.item-event-option a').off('click');
        },100);
		$('#event_rsvp .item-event-option').click(function() {
			var t = $(this), f = $(this).parents('form:first');
			var bIsInvited = $('.js_event_invited').val();
            $('#event_rsvp ul li.item-event-option').removeClass('active');
			if(t.data('rsvp-dropdown'))
            {
                var iRsvp = t.data('rsvp-dropdown');
                $('#event_rsvp input[data-rsvp="' + iRsvp + '"]').prop('checked', true);
                t.addClass('active');
            }
            else
            {
                var iRsvp = t.find('input').val();
                t.find('input').prop('checked', true);
                $('.js_btn_rsvp_actions').addClass('hide');
                $('.js_dropdown_rsvp_actions').removeClass('hide');
                $('[data-rsvp-dropdown="' + iRsvp + '"]').addClass('active');
            }

            var sText = '<i class="mr-1 ico '+ t.data('icon') +'"></i>' + t.data('phrase');
            $('#js_dropdown_rsvp_text').html(sText);

            //Apply the same change for rsvp action in responsive with mobile
			if($('#js_item_choice_share_responsive').length)
            {
                var iCurrentRsvp = !empty($('#event_rsvp').data('current-rsvp')) ? parseInt($('#event_rsvp').data('current-rsvp')) : 0;
                var oChoiceResponsive = $('#js_item_choice_share_responsive');
                if(iCurrentRsvp == 0)
                {
                    oChoiceResponsive.find('.js_event_rsvp_action_btn').addClass('hide');
                    oChoiceResponsive.find('.js_event_rsvp_action_dropdown').removeClass('hide');
                    oChoiceResponsive.find('.js_event_rsvp_action_dropdown span:first').html(sText);
                    oChoiceResponsive.find('[data-toggle="event_rsvp"]').removeClass('is_active_image');
                    oChoiceResponsive.find('[data-toggle="event_rsvp"][rel="' + iRsvp + '"]').addClass('is_active_image');
                }
                else
                {
                    if(bIsInvited || (!bIsInvited && parseInt(iRsvp) != 0))
                    {
                        oChoiceResponsive.find('[data-toggle="event_rsvp"]').removeClass('is_active_image');
                        oChoiceResponsive.find('.js_event_rsvp_action_dropdown span:first').html(sText);
                        oChoiceResponsive.find('[data-toggle="event_rsvp"][rel="' + iRsvp + '"]').addClass('is_active_image');
                        oChoiceResponsive.find('.js_event_rsvp_action_btn').addClass('hide');
                        oChoiceResponsive.find('.js_event_rsvp_action_dropdown').removeClass('hide');
                    }
                }

            }
            $('#event_rsvp').data('current-rsvp', iRsvp);
            f.ajaxCall('event.addRsvp', '&id=' + f.data('event-id') + '&event_detail=1&is_invited=' + bIsInvited);
		});
        $('#event_rsvp .js_rsvp_cancel').click(function (e) {
            //Apply the same change for mobile
            if($('#js_item_choice_share_responsive').length)
            {
                var oChoiceResponsive = $('#js_item_choice_share_responsive');
                var sDefaultText = '<i class="ico ico-star-o mr-1"></i>' + oTranslations['maybe_attending'];
                oChoiceResponsive.find('.js_event_rsvp_action_dropdown span:first').html(sDefaultText);
                oChoiceResponsive.find('.js_event_rsvp_action_btn').removeClass('hide');
                oChoiceResponsive.find('.js_event_rsvp_action_dropdown').addClass('hide');
                oChoiceResponsive.find('#js_event_rsvp_action_' + $(this).parents('form:first').data('event-id') ).data('current-rsvp', 0);
            }
            $.ajaxCall('event.addRsvp', 'event_detail=1&rsvp=0&id=' + $(this).parents('form:first').data('event-id') + "&is_invited=" + $('.js_event_invited').val());
            $('#event_rsvp').data('current-rsvp', 0);
        });
	};
</script>
{/literal}