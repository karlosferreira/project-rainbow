<?php 
/**
 * [PHPFOX_HEADER]
 *
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
{$sCreateJs}
<form class="form item-event-form-add" method="post" action="{url link='event.add.manage' id=$aForms.event_id}" enctype="multipart/form-data" onsubmit="return startProcess({$sGetJsForm}, false);" id="js_event_form" >
    {if !empty($sModule)}
        <div><input type="hidden" name="module" value="{$sModule|htmlspecialchars}" /></div>
    {/if}
    {if !empty($iItem)}
        <div><input type="hidden" name="item" value="{$iItem|htmlspecialchars}" /></div>
    {/if}
    {if $bIsEdit}
        {if $isRepeat }
            {template file='event.block.applyforrepeatevent'}
        {/if}
    {/if}
    {if $bIsEdit}
        <div><input type="hidden" name="id" value="{$aForms.event_id}" /></div>
    {/if}
    <div><input type="hidden" name="val[current_tab]" value="" id="current_tab"></div>

    <div id="js_custom_privacy_input_holder">
        {if $bIsEdit && empty($sModule) && Phpfox::isModule('privacy')}
            {module name='privacy.build' privacy_item_id=$aForms.event_id privacy_module_id='event'}
        {/if}
    </div>

	<div id="js_event_block_detail" class="js_event_block page_section_menu_holder" {if !empty($sActiveTab) && $sActiveTab != 'detail'}style="display:none;"{/if}>
        <div><input type="hidden" name="val[attachment]" class="js_attachment" value="{value type='input' id='attachment'}" /></div>
		<div class="form-group">
			{required}<label for="title">{_p var='event_name'}</label>
				<input type="text" name="val[title]" value="{value type='input' id='title'}" id="title" size="40" maxlength="100" class="form-control close_warning" />
		</div>

		<div class="form-group js_core_init_selectize_form_group" style="width: 200px;">
            <label for="category">{_p var='category'}</label>
            <div class="form-group">
                <select class="form-control close_warning" name="val[category][]" id="js_event_parent_category">
                    <option value="">{_p var='select'}:</option>
                    {foreach from=$aCategories item=aCategory}
                    <option value="{$aCategory.category_id}" {value type='select' id='parent_category_id' default=$aCategory.category_id}>{$aCategory.name}</option>
                    {/foreach}
                </select>
            </div>

            <div class="form-group">
                {foreach from=$aCategories item=aCategory}
                    {if !empty($aCategory.sub)}
                    <div class="js_event_sub_category" id="js_event_sub_category_{$aCategory.category_id}" {if $aForms.parent_category_id == $aCategory.category_id }{else}style="display: none;"{/if}>
                        <select name="val[category][]" class="form-control close_warning">
                            <option value="">{_p var='select_a_sub_category'}:</option>
                            {foreach from=$aCategory.sub item=sub_category}
                            <option value="{$sub_category.category_id}" {value type='select' id='sub_category_id' default=$sub_category.category_id}>{$sub_category.name}</option>
                            {/foreach}
                        </select>
                    </div>
                    {/if}
                {/foreach}
            </div>
		</div>

        <div class="form-group">
            <label>{_p var='event_type'}</label>
            <div class="pl-1">
                <div>
                    <input class="js_online_event_checkbox close_warning" type="checkbox" name="val[is_online]" value="1" {value type='checkbox' id='is_online' default='1'}>
                    {_p var='online_event'}
                </div>
            </div>
        </div>

        <div class="js_online_event_container {if empty($bIsEdit) || empty($aForms.is_online)}hide{/if}">
            <div class="form-group">
                <label>{_p var='online_link'}</label>
                <input class="form-control close_warning" type="url" name="val[online_link]" value="{value type='input' id='online_link'}" maxlength="255" autocomplete="off" placeholder="{_p var='online_link_placeholder'}">
                <div class="extra_info">
                    <p class="help-block">{_p var='add_a_link_so_people_know_where_to_go_when_your_event_starts'}</p>
                </div>
            </div>
        </div>

		<div class="form-group">
            <label for="description">{_p var='description'}</label>
            {editor id='description' rows='6'}
		</div>			
			
		<div class="form-group">
            <label>{_p var='start_time'}</label>
            <div style="position: relative;" class="js_event_select">
                {select_date prefix='start_' id='_start' start_year='current_year' end_year='+1' field_separator=' / ' field_order='MDY' default_all=true add_time=true start_hour='+1' time_separator='event.time_separator'}
            </div>
		</div>	
		
		<div class="form-group" id="js_event_add_end_time">
				<label>{_p var='end_time'}</label>
				<div style="position: relative;" class="js_event_select">
				{select_date prefix='end_' id='_end' start_year='current_year' end_year='+1' field_separator=' / ' field_order='MDY' default_all=true add_time=true start_hour='+4' time_separator='event.time_separator'}
				</div>
		</div>

        {if !$bIsEdit && Phpfox::getParam('event.event_allow_create_recurring_event')}
            <div class="form-group js_core_init_selectize_form_group core-event-formgroup-repeat" >
                <label for="description">{_p var='event.repeat'}:</label>
                <div class="form-group">
                    <select name="val[isrepeat]" id="event_repeat_select" class="form-control w-auto close_warning">
                        <option value="-1" {value type='select' id='isrepeat' default='-1'}>
                            {_p var='event.no_repeat'}
                        </option>
                        <option value="0" {value type='select' id='isrepeat' default='0'}>
                            {_p var='event.daily'}
                        </option>
                        <option value="1" {value type='select' id='isrepeat' default='1'}>
                            {_p var='event.weekly'}
                        </option>
                        <option value="2" {value type='select' id='isrepeat' default='2'}>
                            {_p var='event.monthly'}
                        </option>
                    </select>
                </div>
            </div>

            <div id="event_end_repeat" class="" {if !isset($aForms) || $aForms.isrepeat == -1}style="display: none;"{/if}>
                <label>{_p var='event.end_repeat'}</label>
                <div class="core-event-form-row-inline">
                    <div class="form-group">
                        <label>
                            <input type="radio" name="val[repeat_section_end_repeat]"
                                   {if isset($aForms.repeat_section_end_repeat) == false || (isset($aForms.repeat_section_end_repeat) && $aForms.repeat_section_end_repeat == 'after_number_event')}checked="checked"{/if}
                            value="after_number_event">
                            {_p var='event.after'}
                        </label>
                        <input type="number" class="form-control d-block w-full"
                               name="val[repeat_section_after_number_event]"
                               value="{if isset($aForms.repeat_section_after_number_event)}{$aForms.repeat_section_after_number_event}{/if}"
                               id="event_after_number_event" size="5" maxlength="5" min="1" max="{$iMaxRepeatEvent}"/>
                        <p class="help-block">
                            {_p var='event.event_s'} ({_p var='event.allow_maximum'} {$iMaxRepeatEvent} {_p var='event.event_s'})
                        </p>
                    </div>
                    <div class="form-group">
                        <label>
                            <input type="radio" name="val[repeat_section_end_repeat]"
                                   {if isset($aForms.repeat_section_end_repeat) && $aForms.repeat_section_end_repeat == 'repeat_until'}checked="checked"{/if}
                            value="repeat_until">
                            {_p var='event.at_uppercase'}
                        </label>
                        {select_date prefix='repeat_section_repeatuntil_' id='_repeatuntil' start_year='current_year' end_year='+3' field_separator=' / ' field_order='MDY' default_all=true time_separator='event.time_separator'}
                    </div>
                </div>
            </div>
        {/if}

		<div class="form-group">
            <span class="js_online_event_container {if !empty($aForms.is_online)}hide{/if}">{required}</span><label for="location">{_p var='location_venue'}</label>
            {location_input}
		</div>

        <div class="special_close_warning">
            {if !empty($aForms.current_image) && !empty($aForms.event_id)}
                {module name='core.upload-form' type='event' current_photo=$aForms.current_image id=$aForms.event_id}
            {else}
                {module name='core.upload-form' type='event' }
            {/if}
        </div>

        {if empty($sModule) && Phpfox::isModule('privacy')}
            <div class="form-group-flow special_close_warning">
                    <label>{_p var='event_privacy'}</label>
                    {module name='privacy.form' privacy_name='privacy' privacy_info='event.control_who_can_see_this_event' default_privacy='event.display_on_profile'}
            </div>
        {/if}
        <div class="">
            <input type="submit" value="{if $bIsEdit}{_p var='update'}{else}{_p var='submit'}{/if}" class="button btn-primary js_event_submit_form"/>
        </div>
    </div>

    <div id="js_event_block_invite" class="js_event_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'invite'}style="display:none;"{/if}>
        <div class="block">
            <div class="form-group">
                <label for="js_find_friend">{_p var='invite_friends'}</label>
                {if isset($aForms.event_id)}
                <div id="js_selected_friends" class="hide_it"></div>
                {module name='friend.search' input='invite' hide=true friend_item_id=$aForms.event_id friend_module_id='event' }
                {/if}
            </div>
            {if !isset($bIsRestrictGroup) || (isset($bIsRestrictGroup) && $bIsRestrictGroup == false)}
            <div class="form-group invite-friend-by-email">
                <label for="emails">{_p var='invite_people_via_email'}</label>
                <input name="val[emails]" id="emails" class="form-control close_warning" data-component="tokenfield" data-type="email" >
                <p class="help-block">{_p var='separate_multiple_emails_with_comma_or_enter_or_tab'}</p>
            </div>
            {/if}
            <div class="form-group">
                <label for="personal_message">{_p var='add_a_personal_message'}</label>
                <textarea rows="1" name="val[personal_message]" id="personal_message" class="form-control textarea-auto-scale close_warning" placeholder="{_p var='write_message'}"></textarea>
            </div>
            <div class="form-group">
                <input type="submit" value="{_p var='send_invitations'}" class="btn btn-primary" name="invite_submit"/>
            </div>

        </div>
    </div>

    {if $bIsEdit}
	<div id="js_event_block_manage" class="js_event_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'manage'}style="display:none;"{/if}>
		{module name='event.list'}
	</div>
	{/if}
	
	{if $bIsEdit && Phpfox::getUserParam('event.can_mass_mail_own_members')}
        <div id="js_event_block_email" class="js_event_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'email'}style="display:none;"{/if}>
            <p class="help-block">
                {_p var='send_out_an_email_to_all_the_guests_that_are_joining_this_event'}
                {if isset($aForms.mass_email) && $aForms.mass_email}
                    <br />
                    {_p var='last_mass_email'}: {$aForms.mass_email|date:'core.global_update_time'}
                {/if}
            </p>

            <div class="mass-email-guests-block">
                <div id="js_send_email"{if !$bCanSendEmails} style="display:none;"{/if}>
                    <div class="form-group">
                        <label for="js_mass_email_subject">{_p var='subject'}</label>
                        <input type="text" name="val[mass_email_subject]" value="" size="30" id="js_mass_email_subject" class="form-control close_warning"/>
                    </div>
                    <div class="form-group">
                        <label for="js_mass_email_text">{_p var='text'}</label>
                        <textarea class="form-control close_warning" rows="8" name="val[mass_email_text]" id="js_mass_email_text"></textarea>
                    </div>
                </div>
            </div>
            <ul>
                <li><input type="button" value="{_p var='send'}" class="btn btn-primary" onclick="$('#js_event_mass_mail_li').show(); $.ajaxCall('event.massEmail', 'type=message&amp;id={$aForms.event_id}&amp;subject=' + $('#js_mass_email_subject').val() + '&amp;text=' + $('#js_mass_email_text').val()); return false;" /></li>
                <li id="js_event_mass_mail_li" style="display:none;">{img theme='ajax/add.gif' class='v_middle'} <span id="js_event_mass_mail_send">Sending mass email...</span></li>
            </ul>
            <div id="js_send_email_fail"{if $bCanSendEmails} style="display:none;"{/if}>
                <p class="help-block">
                    {_p var='you_are_unable_to_send_out_any_mass_emails_at_the_moment'}
                    <br />
                    {_p var='please_wait_till'}: <span id="js_time_left">{$iCanSendEmailsTime|date:'core.global_update_time'}</span>
                </p>
            </div>
        </div>
	{/if}
	
</form>
{section_menu_js}

<script type="text/javascript">
{literal}
	$Behavior.resetDatepicker = function(){
		$('.js_event_select .js_date_picker').datepicker('option', 'maxDate', '+1y');
		let onlineCheckbox = $('.js_online_event_checkbox');
		if (onlineCheckbox.length) {
		    onlineCheckbox.off('change').on('change', function() {
		        let otherOnlineContainers = $(this).closest('form').find('.js_online_event_container');
		        if (otherOnlineContainers.length) {
                    otherOnlineContainers.toggleClass('hide');
                }
            });
        }
	};
{/literal}
</script>
