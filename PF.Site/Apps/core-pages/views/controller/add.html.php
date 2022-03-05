<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if $bIsEdit}
<div id="js_pages_add_holder">
	<form class="form" id="js_form_pages_add" method="post" action="{url link='pages.add' id=$aForms.page_id}" enctype="multipart/form-data">
		<div><input type="hidden" name="id" value="{$aForms.page_id}" /></div>
		<div><input type="hidden" name="val[category_id]" value="{value type='input' id='category_id'}" id="js_category_pages_add_holder" /></div>
        <div><input type="hidden" name="val[current_tab]" value="" id="current_tab"></div>

        <!-- Detail Start -->
		<div id="js_pages_block_detail" class="js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'detail'}style="display:none;"{/if}>
            {if isset($aDetailErrors)}
            <div class="alert alert-danger">
                <strong>{_p var='error'}</strong>
                <ul>
                {foreach from=$aDetailErrors item=sError}
                    <li>{$sError}</li>
                {/foreach}
                </ul>
            </div>
            {/if}
			<div class="form-group js_core_init_selectize_form_group">
				<label for="type_id">{_p var='category'}</label>
                <div class="pages_add_category form-group">
                    <select name="val[type_id]" class="form-control inline close_warning" id="type_id">
                    {foreach from=$aTypes item=aType}
                        <option value="{$aType.type_id}"{value type='select' id='type_id' default=$aType.type_id}>
                            {if Phpfox::isPhrase($this->_aVars['aType']['name'])}
                            {_p var=$aType.name}
                            {else}
                            {$aType.name|convert}
                            {/if}
                        </option>
                    {/foreach}
                    </select>
                </div>
                <div class="pages_sub_category form-group">
                    {foreach from=$aTypes item=aType}
                        {if isset($aType.categories) && is_array($aType.categories) && count($aType.categories)}
                            <div class="js_pages_add_sub_category form-inline" id="js_pages_add_sub_category_{$aType.type_id}"{if $aType.type_id != $aForms.type_id} style="display:none;"{/if}>
                                <select name="js_category_{$aType.type_id}" class="form-control inline close_warning">
                                    <option value="">{_p var='select'}</option>
                                    {foreach from=$aType.categories item=aCategory}
                                    <option value="{$aCategory.category_id}"{value type='select' id='category_id' default=$aCategory.category_id}>
                                        {if Phpfox::isPhrase($this->_aVars['aCategory']['name'])}
                                        {_p var=$aCategory.name}
                                        {else}
                                        {$aCategory.name|convert}
                                        {/if}
                                    </option>
                                    {/foreach}
                                </select>
                            </div>
                        {/if}
                    {/foreach}
                </div>
			</div>
			
			<div class="form-group">
				<label for="title">{_p var='name'}</label>
                {if $aForms.is_app}
                <div><input type="hidden" name="val[title]" value="{$aForms.title|clean}" maxlength="200" size="40"/></div>
                <a href="{permalink module='apps' id=$aForms.app_id title=$aForms.title}">{$aForms.title|clean}</a>
                {else}
                <input name="val[title]" value="{value type='input' id='title'}" maxlength="64" size="40" class="form-control close_warning" id="title"/>
                {/if}
			</div>
			
			<div class="form-group js_core_init_selectize_form_group">
                <label for="landing_page">{_p var='landing_page'}</label>
                <select name="val[landing_page]" class="form-control close_warning" id="landing_page">
                    {foreach from=$aForms.landing_pages item=aLanding}
                    {if isset($aLanding.landing)}
                    <option value="{$aLanding.landing}"{if isset($aLanding.is_selected) && $aLanding.is_selected} selected{/if}>{$aLanding.phrase}</option>
                    {/if}
                    {/foreach}
                </select>
			</div>

            <input type="submit" value="{_p var='update'}" class="btn btn-primary"/>
		</div>
        <!-- Detail End -->

        <!-- Photo START -->
		<div id="js_pages_block_url" class="block js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'url'}style="display:none;"{/if}>
			<div class="form-group">
                <label for="js_vanity_url_new">{_p var='vanity_url'}</label>
                <div class="help-block">{param var='core.path'}</div>
                <input name="val[vanity_url]" value="{value type='input' id='vanity_url'}" size="20" id="js_vanity_url_new" class="form-control close_warning"/>
			</div>
			
			<div id="js_pages_vanity_url_button">
                <div><input type="hidden" name="val[vanity_url_old]" value="{value type='input' id='vanity_url'}" size="20" id="js_vanity_url_old" /></div>
                <input type="button" value="{_p var='check_url'}" class="btn btn-primary" data-app="core_pages" data-action="check_url" data-action-type="click" />
			</div>
		</div>
		
		<div id="js_pages_block_photo" class="js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'photo'}style="display:none;"{/if}>
            {if isset($aPhotoErrors)}
            <div class="alert alert-danger">
                <strong>{_p var='error'}</strong>
                <ul>
                    {foreach from=$aPhotoErrors item=sError}
                    <li>{$sError}</li>
                    {/foreach}
                </ul>
            </div>
            {/if}
			<div id="js_pages_block_customize_holder">
				<div class="form-group-follow special_close_warning">
                    {if $bIsEdit && !empty($aForms.image_path)}
                        {module name='core.upload-form' type='pages' current_photo=$aForms.image_path_200 id=$aForms.page_id}
                    {else}
                        {module name='core.upload-form' type='pages'}
                    {/if}
				</div>

				<div id="js_submit_upload_image" class="table_clear">
					<input type="submit" value="{_p var='update_photo'}" class="btn btn-primary"/>
				</div>
			</div>
		</div>
        <!-- Photo END -->

		<div id="js_pages_block_info" class="js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'info'}style="display:none;"{/if}>
			{plugin call='pages.template_controller_add_1'}
			<div class="form-group">
                {editor id='text'}
			</div>
			<div class="form-group">
				<input type="submit" value="{_p var='update'}" class="btn btn-primary"/>
			</div>
		</div>
		
		<div id="js_pages_block_permissions" class="block js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'permissions'}style="display:none;"{/if}>
			<div id="privacy_holder_table">

                <div class="privacy-block-content">
				{foreach from=$aPermissions item=aPerm}
                    <div class="item-outer">
                        <div class="form-group">
                            <label>{$aPerm.phrase}</label>
                            <div>
                                <select name="val[perms][{$aPerm.id}]" class="form-control close_warning" id="perms_{$aPerm.id}">
                                    <option value="0"{if $aPerm.is_active == '0'} selected="selected"{/if}>{_p var='anyone'}</option>
                                    <option value="1"{if $aPerm.is_active == '1'} selected="selected"{/if}>{_p var='members_only'}</option>
                                    <option value="2"{if $aPerm.is_active == '2'} selected="selected"{/if}>{_p var='admins_only'}</option>
                                </select>
                            </div>
                        </div>
                    </div>
				{/foreach}
                </div>
				<div class="form-group">
					<input type="submit" value="{_p var='update'}" class="btn btn-primary"/>
				</div>
			</div>
		</div>
		
		<div id="js_pages_block_admins" class="js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'admins'}style="display:none;"{/if}>
			<div class="form-group">
                {if Phpfox::getUserBy('profile_page_id')}
                    {_p var="Please login back as user to use this feature."}
                {else}
                    {module name='friend.search-small' input_name='admins' current_values=$aForms.admins}
                {/if}
			</div>

			<div class="form-group">
				<input type="submit" value="{_p var='update'}" class="btn btn-primary"/>
			</div>
		</div>

		<div id="js_pages_block_invite" class="js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'invite'}style="display:none;"{/if}>
			<div class="block">
                <div class="form-group">
                    <label for="js_find_friend">{_p var='invite_friends'}</label>
                    {if isset($aForms.page_id)}
                    <div id="js_selected_friends" class="hide_it"></div>
                    {module name='friend.search' input='invite' hide=true friend_item_id=$aForms.page_id friend_module_id='pages' in_form=true}
                    {/if}
                </div>
                <div class="form-group invite-friend-by-email">
                    <label for="emails">{_p var='invite_people_via_email'}</label>
                    <input name="val[emails]" id="emails" class="form-control close_warning" data-component="tokenfield" data-type="email" >
                    <p class="help-block">{_p var='separate_multiple_emails_with_comma_or_enter_or_tab'}</p>
                </div>
                <div class="form-group">
                    <label for="personal_message">{_p var='add_a_personal_message'}</label>
                    <textarea rows="1" name="val[personal_message]" id="personal_message" class="form-control textarea-auto-scale close_warning" placeholder="{_p var='write_message'}"></textarea>
                </div>
                <div class="form-group">
                    <input type="submit" value="{_p var='send_invitations'}" class="btn btn-primary"/>
                </div>
			</div>
		</div>

		<div id="js_pages_block_widget" class="block js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'widget'}style="display:none;"{/if}>
			<div class="form-group table">
				<div class="pages_create_new_widget">
					<a role="button" class="btn btn-primary" onclick="$Core.box('pages.widget', 700, 'page_id={$aForms.page_id}&amp;is_menu=0'); return false;">{_p var='create_new_widget'}</a>
				</div>

                {if !empty($aBlockWidgets)}
                <p class="help-block">{_p var='drag_to_order_widgets'}</p>
                {/if}

                {if !empty($aBlockWidgets)}
                <label>{_p var='block_type'}</label>
                <table class="table table-striped drag-drop-table" id="js_drag_drop_block_type_block" data-app="core_pages" data-action-type="init" data-action="init_drag" data-table="#js_drag_drop_block_type_block" data-ajax="pages.orderWidget">
                    <thead>
                        <tr>
                            <th style="width: 20px"></th>
                            <th>{_p var='title'}</th>
                            <th style="width: 20px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <input type="hidden" class="drag_handle_input" name="page_id" value="{$aForms.page_id}">
                        {foreach from=$aBlockWidgets item=aBlockWidget}
                        <tr>
                            <td class="drag_handle" style="width: 30px; height: 30px;">
                                <input type="hidden" name="ordering[{$aBlockWidget.widget_id}]">
                            </td>
                            <td>{$aBlockWidget.title|clean}</td>
                            <td class="widget-actions">
                                <div class="dropdown">
                                    <a data-toggle="dropdown">
                                        <i class="fa fa-action"></i>
                                    </a>
                                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                                        <li>
                                            <a href="#" onclick="$Core.box('pages.widget', 700, 'widget_id={$aBlockWidget.widget_id}&amp;is_menu=0'); return false;"><span class="ico ico-pencilline-o mr-1"></span> {_p var='edit'}</a>
                                        </li>
                                        <li class="item_delete">
                                            <a href="#" onclick="$Core.jsConfirm({l}message:'{_p('pages_are_you_sure_you_want_to_delete_this_widget')}'{r}, function(){l} $.ajaxCall('pages.deleteWidget', 'widget_id={$aBlockWidget.widget_id}'); {r}, function(){l}{r}); return false;"><span class="ico ico-trash-o mr-1"></span> {_p var='delete'}</a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                {/if}

                {if !count($aBlockWidgets)}
                    <div class="alert alert-info">{_p var='no_widget_found'}</div>
                {/if}
            </div>
		</div>

        <div id="js_pages_block_menu" class="block js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'menu'}style="display:none;"{/if}>
            <div class="form-group table">
                <div class="pages_create_new_widget">
                    <a role="button" class="btn btn-primary" onclick="$Core.box('pages.widget', 700, 'page_id={$aForms.page_id}&amp;is_menu=1'); return false;">{_p var='create_new_menu'}</a>
                </div>
                {if !empty($aPageMenus)}
                <p class="help-block">{_p var='drag_to_order_menus'}</p>
                <label>{_p var='page_menu_settings'}</label>
                <table class="table table-striped drag-drop-table core-page-menu-table" id="js_drag_drop_block_page_menu" data-app="core_pages" data-action-type="init" data-action="init_drag" data-table="#js_drag_drop_block_page_menu" data-ajax="pages.orderMenu">
                    <thead>
                        <tr>
                            <th style="width: 20px"></th>
                            <th>{_p var='menu_title'}</th>
                            <th style="width: 20px;">{_p var='active'}</th>
                            <th style="width: 20px;">{_p var='action'}</th>
                        </tr>
                    </thead>
                    <tbody>
                        <input type="hidden" class="drag_handle_input" name="page_id" value="{$aForms.page_id}">
                        {foreach from=$aPageMenus item=aPageMenu}
                        <tr>
                            <input type="hidden" class="drag_handle_input" name="page_menu[{$aPageMenu.landing}][menu_id]" value="{$aPageMenu.menu_id}">
                            <td class="drag_handle" style="width: 30px; height: 30px;">
                                <input type="hidden" name="page_menu[{$aPageMenu.landing}][ordering]">
                            </td>
                            <td><div class="core-page-td">{if $aPageMenu.widget_id}{_p var=$aPageMenu.phrase}{else}{_p var=$aPageMenu.landing}{/if}</div></td>
                            <td class="text-center">
                                {if $aPageMenu.landing != 'home'}
                                <div class="core-page-menu-toggle">
                                    <label>
                                        <input id="menu-{$aPageMenu.landing}" type="checkbox" data-page-id="{$aForms.page_id}" data-menu-name="{$aPageMenu.landing}"
                                               data-menu-id="{$aPageMenu.menu_id}" data-app="core_pages" data-action="toggleActivePageMenu"
                                               data-action-type="click" {if !isset($aPageMenu.is_active) || !empty($aPageMenu.is_active)} checked="checked" {/if}>
                                        <span class="item-toggle-icon"></span>
                                    </label>
                                </div>
                                {/if}
                            </td>
                            <td class="widget-actions">
                                {if $aPageMenu.widget_id}
                                <div class="dropdown core-page-table-action">
                                    <a data-toggle="dropdown">
                                        <i class="fa fa-action"></i>
                                    </a>
                                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                                        <li>
                                            <a href="#" onclick="$Core.box('pages.widget', 700, 'widget_id={$aPageMenu.widget_id}&amp;is_menu=1'); return false;"><span class="ico ico-pencilline-o mr-1"></span> {_p var='edit'}</a>
                                        </li>
                                        <li class="item_delete">
                                            <a href="#" onclick="$Core.jsConfirm({l}message:'{_p('pages_are_you_sure_you_want_to_delete_this_menu')}'{r}, function(){l} $.ajaxCall('pages.deleteWidget', 'widget_id={$aPageMenu.widget_id}'); {r}, function(){l}{r}); return false;"><span class="ico ico-trash-o mr-1"></span> {_p var='delete'}</a>
                                        </li>
                                    </ul>
                                </div>
                                {/if}
                            </td>
                        </tr>
                        {/foreach}
                    </tbody>
                </table>
                {/if}
            </div>
        </div>
		
		{if Phpfox::getParam('core.google_api_key')}
			<div id="js_pages_block_location" class="block js_pages_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'location'}style="display:none;"{/if}>
                <div class="form-group" id="js_location_enter">
                    <p>{_p var='place_your_page_in_the_map'}</p>
                    <p>{_p var='you_can_also_write_your_address'}</p>
                    {location_input}
                </div>
                <div class="table_clear">
                    <input type="submit" value="{_p var='update'}" class="btn btn-primary"/>
                </div>
			</div>
		{/if}
	</form>
</div>
<!-- Edit page END -->
{else}
{if Phpfox::getUserBy('profile_page_id')}
{_p var='logged_in_as_a_page' full_name=$aGlobalProfilePageLogin.full_name}
{else}
<div id="js_pages_add_holder" class="item-container page-add">
	<div class="main_break"></div>
	{foreach from=$aTypes item=aType}
	<div class="page-item" data-app="core_pages" data-action="add_new_page" data-type-id="{$aType.type_id}" data-action-type="click">
        <div class="item-outer">
            <div class="page-photo"
                 {if !empty($aType.image_path)}
                 style="background-image: url('{img server_id=$aType.image_server_id path='core.path_actual' file=$aType.image_path suffix='_200' return_url=true}')"
                 {else}
                 style="background-image: url('{img path='core.path_actual' file='PF.Site/Apps/core-pages/assets/img/default-category/default_category.png' return_url=true}')"
                 {/if}
            >
                <div class="page-add-inner-link">
                    <div class="pages-add-info">
                        <span class="item-title">
                        {if Phpfox::isPhrase($this->_aVars['aType']['name'])}
                            {_p var=$aType.name}
                        {else}
                            {$aType.name|convert}
                        {/if}
                        </span>
                        <div class="item-number-page">
                            {if $aType.pages_count != 1}
                                {$aType.pages_count} {_p var='pages'}
                            {else}
                                {$aType.pages_count} {_p var='page'}
                            {/if}
                        </div>
                    </div>
                    <a class="item-page-add" data-app="core_pages" data-action="add_new_page" data-type-id="{$aType.type_id}" data-action-type="click"><span class="ico ico-plus"></span></a>
                </div>
            </div>
        </div>
	</div>
	{/foreach}
	<div class="clear"></div>
</div>
{/if}
{/if}

{if !empty($aForms) && isset($aForms.user_id)}
    {literal}
    <script type="text/javascript">
        $Behavior.onLoadAdmins = function(){
            if (typeof $Core.searchFriendsInput !== 'undefined') {
                $Core.searchFriendsInput.addLiveUser({/literal}{$aForms.user_id}{literal})
            }
        }
    </script>
    {/literal}
{/if}