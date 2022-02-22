<?php

defined('PHPFOX') or exit('NO DICE!');

?>
{if $bIsEdit && $aForms.view_id == '2'}
<div class="error_message">
    {_p var='notice_this_listing_is_marked_as_sold'}
</div>
{/if}

{$sCreateJs}
<form method="post" class="form" action="{url link='current'}" enctype="multipart/form-data" onsubmit="$('#js_marketplace_submit_form').attr('disabled',true); return startProcess({$sGetJsForm}, false);" id="js_marketplace_form">

    {if isset($iItem) && isset($sModule)}
        <div><input type="hidden" name="val[module_id]" value="{$sModule|htmlspecialchars}" /></div>
        <div><input type="hidden" name="val[item_id]" value="{$iItem|htmlspecialchars}" /></div>
    {/if}

    {if $bIsEdit}
        <input type="hidden" name="id" value="{$aForms.listing_id}" />
    {/if}
    <div id="js_custom_privacy_input_holder">
        {if $bIsEdit && Phpfox::isModule('privacy') && empty($sModule)}
            {module name='privacy.build' privacy_item_id=$aForms.listing_id privacy_module_id='marketplace'}
        {/if}
    </div>
    <div><input type="hidden" name="page_section_menu" value="" id="page_section_menu_form" /></div>
    <div><input type="hidden" name="val[attachment]" class="js_attachment" value="{value type='input' id='attachment'}" /></div>
    <div><input type="hidden" name="val[current_tab]" value="" id="current_tab"></div>

    <div id="js_mp_block_detail" class="js_mp_block page_section_menu_holder market-app add" {if !empty($sActiveTab) && $sActiveTab != 'detail'}style="display:none;"{/if}>
        <div class="form-group">
            <label for="title">{required}{_p var='what_are_you_selling'}</label>
            <input class="form-control close_warning" type="text" name="val[title]" value="{value type='input' id='title'}" id="title" size="40" maxlength="100" />
        </div>
        <div class="form-group js_core_init_selectize_form_group">
            <div class="form-inline flex category">
                <div class="form-group category-left">
                    <label for="category">{required}{_p var='category'}</label>
                    {$sCategories}
                </div>
                <div class="form-group price-right">
                    <label for="price">{_p var='price'}</label>
                    {field_price price_name='price' currency_name='currency_id' close_warning=true}
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="mini_description">{_p var='short_description'}</label>
            <textarea id="mini_description" class="form-control close_warning" rows="1" name="val[mini_description]">{value type='textarea' id='mini_description'}</textarea>
        </div>

        <div class="form-group">
            <label for="description">{_p var='description'}</label>
            <div class="table_right close_warning">
                {editor id='description' rows='6'}
            </div>
        </div>

        {if $bCanSellListing}
            <div class="form-group market-app create" id="js-marketplace-is-sell" data-allow-ap="{$bAllowActivityPoint}" data-can-sell="{$bCanSellListing}" data-have-gateway="{$bHaveGateway}">
                <div class="privacy-block-content">
                    <div class="item_is_active_holder">
                        <span class="js_item_active item_is_active">
                            <input type="radio" name="val[is_sell]" value="1" class="checkbox close_warning" style="vertical-align:middle;" {value type='radio' id='is_sell' default='1'}/> {_p var='yes'}
                        </span>
                        <span class="js_item_active item_is_not_active">
                            <input type="radio" name="val[is_sell]" value="0" class="checkbox close_warning" style="vertical-align:middle;" {value type='radio' id='is_sell' default='0' selected='true'}/> {_p var='no'}
                        </span>
                    </div>
                    <div class="inner">
                        <label>{_p var='enable_instant_payment'}</label>
                        <div class="extra_info">
                            {_p var='if_you_enable_this_option_buyers_can_make_a_payment_to_one_of_the_payment_gateways_you_have_on_file_with_us_to_manage_your_payment_gateways_go_a_href_link_here_a' link=$sUserSettingLink}
                        </div>
                    </div>
                </div>
            </div>

            {if $bAllowActivityPoint}
                <div class="form-group market-app create sold" id="js-marketplace-activity-point" data-allow-ap="{$bAllowActivityPoint}">
                    <div class="privacy-block-content">
                        <div class="item_is_active_holder">
                            <span class="js_item_active item_is_active">
                                <input type="radio" name="val[allow_point_payment]" value="1" class="checkbox close_warning" style="vertical-align:middle;" {value type='radio' id='allow_point_payment' default='1'}/> {_p var='yes'}
                            </span>
                            <span class="js_item_active item_is_not_active">
                                <input type="radio" name="val[allow_point_payment]" value="0" class="checkbox close_warning" style="vertical-align:middle;" {value type='radio' id='allow_point_payment' default='0' selected='true'}/> {_p var='no'}
                            </span>
                        </div>
                        <div class="inner">
                            <label>{_p var='enable_activity_point_payment'}</label>
                            <div class="extra_info">
                                {_p var='if_you_enable_this_option_buyers_can_make_a_payment_with_their_activity_points'}
                            </div>
                            {if !$bHaveGateway}
                                <div class="extra_info">
                                    {if $bIsEdit}
                                        {_p var='listing_owner_has_not_setup_any_payment_info_so_activity_point_payment_is_required_if_you_enable_instant_payment'}
                                    {else}
                                        {_p var='you_have_not_setup_any_payment_info_so_activity_point_payment_is_required_if_you_enable_instant_payment'}
                                    {/if}
                                </div>
                            {/if}
                        </div>
                    </div>
                </div>
            {/if}

            <div class="form-group market-app create sold">
                <div class="privacy-block-content">
                    <div class="item_is_active_holder ">
                        <span class="js_item_active item_is_active"><input type="radio" name="val[auto_sell]" value="1" class="checkbox close_warning" style="vertical-align:middle;" {value type='radio' id='auto_sell' default='1' selected='true'}/> {_p var='yes'}</span>
                        <span class="js_item_active item_is_not_active"><input type="radio" name="val[auto_sell]" value="0" class="checkbox close_warning" style="vertical-align:middle;" {value type='radio' id='auto_sell' default='0'}/> {_p var='no'}</span>
                    </div>
                    <div class="inner">
                        <label>{_p var='auto_sold'}</label>
                        <div class="extra_info">
                            {_p var='if_this_is_enabled_and_once_a_successful_purchase_of_this_item_is_made'}
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        {if $bIsEdit && ($aForms.view_id == '0' || $aForms.view_id == '2')}
            <div class="form-group market-app create sold">
                <div class="privacy-block-content">
                    <div class="item_is_active_holder {if !isset($aForms.view_id)}item_selection_not_active{else}{if $aForms.view_id}item_selection_active{else}item_selection_not_active{/if}{/if}">
                        <span class="js_item_active item_is_active"><input type="radio" name="val[view_id]" value="2" class="checkbox close_warning" style="vertical-align:middle;"{value type='checkbox' id='view_id' default='2' selected=true}/> {_p var='yes'}</span>
                        <span class="js_item_active item_is_not_active"><input type="radio" name="val[view_id]" value="0" class="checkbox close_warning" style="vertical-align:middle;"{value type='checkbox' id='view_id' default='0'}/> {_p var='no'}</span>
                    </div>
                    <div class="inner">
                        <label>{_p var='closed_item_sold'}</label>
                        <div class="extra_info">
                            {_p var='enable_this_option_if_this_item_is_sold_and_this_listing_should_be_closed'}
                        </div>
                    </div>
                </div>
            </div>
        {/if}

        <div class="form-group">
            {required}<label for="location">{_p var='location'}</label>
            {location_input close_warning=true}
        </div>

    <div class="special_close_warning">
        {if empty($sModule) && Phpfox::isModule('privacy')}
        <div class="form-group">
            <label>{_p var='listing_privacy'}</label>
            {module name='privacy.form' privacy_info='control_who_can_see_this_listing' privacy_name='privacy' default_privacy='marketplace.display_on_profile'}
        </div>
        {/if}
    </div>

        <div class="form-group footer">
            <button type="submit" class="btn btn-primary" id="js_marketplace_submit_form">{if $bIsEdit}{_p var='update'}{else}{_p var='submit'}{/if}</button>
        </div>
    </div>

    <div id="js_mp_block_customize" class="js_mp_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'customize'}style="display:none;"{/if}>
        {module name='marketplace.photo'}
    </div>

    <div id="js_mp_block_invite" class="js_mp_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'invite'}style="display:none;"{/if}>
        <div class="block">
            {if Phpfox::isModule('friend')}
                <div class="form-group">
                    <label for="js_find_friend">{_p var='invite_friends'}</label>
                    {if isset($aForms.listing_id)}
                        <div id="js_selected_friends" class="hide_it"></div>
                        {module name='friend.search' input='invite' hide=true friend_item_id=$aForms.listing_id friend_module_id='marketplace'}
                    {/if}
                </div>
            {/if}
            {if !isset($bIsRestrictGroup) || (isset($bIsRestrictGroup) && $bIsRestrictGroup == false)}
            <div class="form-group invite-friend-by-email">
                <label for="emails">{_p var='invite_people_via_email'}</label>
                <input name="val[emails]" id="invite_people_via_email" class="form-control close_warning" data-component="tokenfield" data-type="email" >
                <p class="help-block">
                    {_p var='separate_multiple_emails_with_comma_or_enter_or_tab'}
                </p>
            </div>
            {/if}
            <div class="form-group">
                <label for="add_a_personal_message">{_p var='add_a_personal_message'}</label>
                <textarea rows="3" name="val[personal_message]" id="add_a_personal_message" class="form-control close_warning" placeholder="{_p var='write_message'}"></textarea>
            </div>
            <div class="form-group">
                <input type="submit" value="{_p var='send_invitations'}" class="btn btn-primary" name="invite_submit"/>
            </div>
        </div>
    </div>

    {if isset($aForms.listing_id) && $bIsEdit}
        <div id="js_mp_block_manage" class="js_mp_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'manage'}style="display:none;"{/if}>
            {module name='marketplace.list'}
        </div>
    {/if}
</form>
{section_menu_js}