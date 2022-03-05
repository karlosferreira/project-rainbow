<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: add.html.php 4554 2012-07-23 08:44:50Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="core-subscription-admincp-action-package">
    <form class="admincp-add-subscription-package-form" method="post" action="{url link='current'}" enctype="multipart/form-data" onsubmit="$Core.onSubmitForm(this, true);">
        {if $bIsEdit}
        <div><input type="hidden" name="id" value="{$iEditId}" /></div>
        {/if}
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="panel-title">{_p var='subscription_details'}</div>
            </div>
            <div class="panel-body">
                {field_language phrase='sPhraseTitle' label='title' field='title' format='val[title][' size=40 maxlength=100 required=true}
                <div class="max-character">
                    <span class="warning">{_p var='subscribe_max_numbers_character' number='100'}. </span>
                </div>
                {field_language phrase='sPhraseDescription' label='description' field='description' format='val[description][' rows="10" maxlength=200 required=true type="textarea"}
                <div class="max-character">
                    <span class="warning">{_p var='subscribe_max_numbers_character' number='200'}. </span>
                </div>
                <div class="form-group">
                    <label for="image">{_p var='image'}</label>
                    {if $bIsEdit && !empty($aForms.image_path)}
                    <div id="js_subscribe_image_holder">
                        {img server_id=$aForms.server_id title=$aForms.title path='subscribe.url_image' file=$aForms.image_path suffix='_120' max_width='120' max_height='120'}
                        <p class="help-block">
                            <a href="#" onclick="$Core.jsConfirm({l}message: '{_p var='are_you_sure'}'{r}, function(){l} $('#js_subscribe_image_holder').remove(); $('#js_subscribe_upload_image').show(); $.ajaxCall('subscribe.deleteImage', 'package_id={$aForms.package_id}'); {r}, function(){l}{r}); return false;">{_p var='change_this_image'}</a>
                        </p>
                    </div>
                    {/if}
                    <div id="js_subscribe_upload_image"{if $bIsEdit && !empty($aForms.image_path)} style="display:none;"{/if}>
                    <input type="file" id="image" name="image" accept="image/*" size="20" />
                    <p class="help-block">
                        {_p var='you_can_upload_a_jpg_gif_or_png_file'}
                    </p>
                </div>
            </div>
            <div class="js_background_color">
                <div class="form-group">
                    <div class="form-inline">
                        <label for="background_color">
                            {_p var='background_color_for_the_comparison_page'}
                            <input type="hidden" name="val[background_color]" value="#{value id='background_color' type='input' default='ebf1f5'}" data-rel="colorChooser" class="_colorpicker" />
                            <div class="_colorpicker_holder"></div>
                        </label>
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label for="user_group_id">{required}{_p var='user_group_on_success'}</label>
                <select class="form-control" name="val[user_group_id]" id="user_group_id" required>
                    <option {if $bIsEdit && $bDisableField}disabled="true" {/if} value="">{_p var='select'}:</option>
                    {foreach from=$aUserGroups item=aUserGroup}
                    <option {if $bIsEdit && $bDisableField}disabled="true" {/if}value="{$aUserGroup.user_group_id}"{value type='select' id='user_group_id' default=$aUserGroup.user_group_id}>{$aUserGroup.title|convert|clean}</option>
                    {/foreach}
                </select>
                <p class="help-block">
                    {_p var='once_a_user_successfully_purchased_the_package_they_will_be_moved_to_this_user_group'}
                </p>
            </div>
            <div class="form-group">
                <label for="fail_user_group">{required}{_p var='user_group_on_failure'}</label>
                <select name="val[fail_user_group]" id="fail_user_group" required class="form-control">
                    <option {if $bIsEdit && $bDisableField}disabled="true" {/if} value="">{_p var='select'}:</option>
                    {foreach from=$aUserGroups item=aUserGroup}
                    <option {if $bIsEdit && $bDisableField}disabled="true" {/if} value="{$aUserGroup.user_group_id}"{value type='select' id='fail_user_group' default=$aUserGroup.user_group_id}>{$aUserGroup.title|convert|clean}</option>
                    {/foreach}
                </select>
                <p class="help-block">
                    {_p var='once_a_user_cancels_or_fails_to_pay_their_subscription_they_will_be_moved_to_this_user_group'}
                </p>
            </div>
            <div class="form-group form-group-follow">
                <label for="is_registration">{_p var='add_to_registration'}</label>
                <div class="item_is_active_holder">
                    <span class="js_item_active item_is_active"><input type="radio" name="val[is_registration]" value="1" id="is_registration" {value type='radio' id='is_registration' default='1'}/></span>
                    <span class="js_item_active item_is_not_active"><input type="radio" name="val[is_registration]" value="0" id="is_registration" {value type='radio' id='is_registration' default='0' selected='true'}/></span>
                </div>
                <p class="help-block">
                    {_p var='subscribe_allow_users_to_purchase_the_package_when_registering_new_account'}
                </p>
            </div>
            <div class="form-group form-group-follow">
                <label for="is_active">{_p var='is_active'}</label>
                <div class="item_is_active_holder">
                    <span class="js_item_active item_is_active"><input type="radio" id="is_active" name="val[is_active]" value="1" {value type='radio' id='is_active' default='1' selected='true'}/></span>
                    <span class="js_item_active item_is_not_active"><input type="radio" id="is_active" name="val[is_active]" value="0" {value type='radio' id='is_active' default='0'}/></span>
                </div>
            </div>
            <div class="form-group form-group-follow">
                <label>{_p var='subscribe_visible_for_user_groups'}</label>
                <div class="visible-content">
                    {foreach from=$aUserGroups item=aGroup}
                    <div class="visible-item">
                        <input id="group-{$aGroup.user_group_id}" type="checkbox" name="val[visible_group][]" value="{$aGroup.user_group_id}" class="visible-checkbox visible-group" {if empty($bIsEdit) && empty($aForms)}checked="checked" {else}{if (!empty($aForms) && in_array($aGroup.user_group_id, $aForms.visible_group))}checked="checked" {/if}{/if} {if (int)$aForms.user_group_id == (int)$aGroup.user_group_id}disabled{/if}>
                        <span>{$aGroup.title}</span>
                    </div>
                    {/foreach}
                </div>
            </div>
            <hr />
            <h4>{_p var='subscription_costs'}</h4>
            <div class="form-group form-group-follow">
                <label for="show_price">{_p var='show_price'}</label>
                <div class="item_is_active_holder">
                    <span class="js_item_active item_is_active"><input type="radio" id="show_price" name="val[show_price]" value="1" {value type='radio' id='show_price' default='1' selected='true'}/></span>
                    <span class="js_item_active item_is_not_active"><input type="radio" id="show_price" name="val[show_price]" value="0" {value type='radio' id='show_price' default='0'}/></span>
                </div>
            </div>
            <div class="form-group form-group-follow free-package">
                <input type="checkbox" name="val[is_free]" value="1" class="visible-checkbox" id="is-free" {value type='checkbox' id='is_free' checked='checked' default='1'}>
                <span class="checkbox-title">{_p var='subscribe_free_package'}</span>
            </div>
            <div class="form-group currency">
                <label for="">{_p var='price'}</label>
                {module name='core.currency' currency_field_name='val[cost]'}
                <div class="currency-notice">{_p var='subscribe_amount_you_want_to_charge_people_who_join_this_package'}</div>
            </div>
            <div class="form-group form-group-follow recurring-toggle-button">
                <label for="is_recurring">{_p var='recurring'}</label>
                <div class="item_is_active_holder">
                    <span class="js_item_active item_is_active is_recurring"><input {if $bIsEdit && $bDisableField}disabled="true" {/if} type="radio" id="is_recurring" name="val[is_recurring]" value="1" {value type='radio' id='is_recurring' default='1'}/></span>
                    <span class="js_item_active item_is_not_active is_not_recurring"><input {if $bIsEdit && $bDisableField}disabled="true" {/if} type="radio" id="is_recurring" name="val[is_recurring]" value="0" {value type='radio' id='is_recurring' default='0' selected='true'}/></span>
                </div>
                <p class="help-block">
                    {_p var='subscribe_choose_yes_if_you_want_this_subscription_to_be_recurring'}
                </p>
            </div>
        </div>
        <div class="panel-body js_recurring_body" style="{if empty($bIsEdit) && empty($aForms)}display:none;{else}{if !empty($aForms.is_recurring)}display:block;{else}display:none;{/if}{/if}">
            <div class="js_subscribe_is_recurring">
                <div class="form-group form-group-follow">
                    <label for="">{_p var='subscribe_payment_method'}</label>
                    <div class="visible-content">
                        {foreach from=$aPaymentMethods item=aMethod}
                        <div class="visible-item">
                            <input {if $bIsEdit && $bDisableField}disabled="true"{/if} type="checkbox" name="val[allow_payment_methods][{$aMethod.name}]" value="{$aMethod.value}" class="visible-checkbox" {if (empty($bIsEdit) && empty($aForms)) || (isset($aMethodsNames) && in_array($aMethod.name, $aMethodsNames))}checked{/if}>
                            <span>{$aMethod.title}</span>
                        </div>
                        {/foreach}
                    </div>
                </div>
                <div class="form-group recurring-cost">
                    <label for="">{_p var='recurring_price'}</label>
                    {module name='core.currency' currency_field_name='val[recurring_cost]'}
                    <div class="currency-notice">{_p var='subscribe_amount_you_want_to_charge_people_who_want_to_renew_this_package'}</div>
                </div>
                <div class="from-group">
                    <label for="number_day_notify_before_expiration">{_p var='Number of days to notify user'}</label>
                    <input {if $bIsEdit && $bDisableField}disabled="true" {/if} type="text" class="form-control" name="val[number_day_notify_before_expiration]" value="{value type='input' id='number_day_notify_before_expiration'}">
                    <div class="notice">{_p var='subscribe_number_of_days_before_the_expiration_day'}</div>
                </div>
                <div class="form-group">
                    <label for="recurring_period">{_p var='recurring_period'}</label>
                    <select name="val[recurring_period]" id="recurring_period" class="form-control">
                        <option {if $bIsEdit && $bDisableField}disabled="true" {/if} value="1"{value type='select' id='recurring_period' default='1'}>{_p var='monthly'}</option>
                        <option {if $bIsEdit && $bDisableField}disabled="true" {/if} value="2"{value type='select' id='recurring_period' default='2'}>{_p var='quarterly'}</option>
                        <option {if $bIsEdit && $bDisableField}disabled="true" {/if} value="3"{value type='select' id='recurring_period' default='3'}>{_p var='biannualy'}</option>
                        <option {if $bIsEdit && $bDisableField}disabled="true" {/if} value="4"{value type='select' id='recurring_period' default='4'}>{_p var='annually'}</option>
                    </select>
                    <p class="help-block">
                        {_p var='subscribe_the_recurring_period_for_this_package'}
                    </p>
                </div>
            </div>
        </div>
        <div class="panel-footer">
            <input type="submit" value="{_p var='submit'}" class="btn btn-primary" />
        </div>
    </form>
</div>
<script type="text/javascript">
    var bDisableField = {if !empty($bDisableField)}{$bDisableField}{else}0{/if};
</script>