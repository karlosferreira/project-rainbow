<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if $bIsEdit}
<!-- START update group -->
<div id="js_groups_add_holder">
	<form class="form" method="post" id="js_form_groups_add" action="{url link='groups.add'}?id={$aForms.page_id}" enctype="multipart/form-data">
		<div><input type="hidden" name="id" value="{$aForms.page_id}" /></div>
		<div><input type="hidden" name="val[category_id]" value="{value type='input' id='category_id'}" id="js_category_groups_add_holder" /></div>
        <div><input type="hidden" name="val[current_tab]" value="" id="current_tab"></div>

		<div id="js_groups_block_detail" class="js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'detail'}style="display:none;"{/if}>
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
                <label for="type_id">{_p var='Category'}</label>
                {if $aForms.is_app}
                    {_p var='App'}
                {else}
					<div class="groups_add_category form-group">
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
					<div class="groups_sub_category form-group">
						{foreach from=$aTypes item=aType}
							{if isset($aType.categories) && is_array($aType.categories) && count($aType.categories)}
								<div class="js_groups_add_sub_category form-inline" id="js_groups_add_sub_category_{$aType.type_id}"{if $aType.type_id != $aForms.type_id} style="display:none;"{/if}>
									<select name="js_category_{$aType.type_id}" class="form-control inline close_warning">
										<option value="">{_p var='select'}</option>
										{foreach from=$aType.categories item=aCategory}
										<option value="{$aCategory.category_id}" {value type='select' id='category_id' default=$aCategory.category_id}>
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
                {/if}
			</div>

			<div class="table form-group">
				<label for="title">{_p var='Name'}</label>
                {if $aForms.is_app}
                <div><input type="hidden" name="val[title]" value="{$aForms.title|clean}" maxlength="64" size="40" /></div>
                <a href="{permalink module='apps' id=$aForms.app_id title=$aForms.title}">{$aForms.title|clean}</a>
                {else}
                <input type="text" name="val[title]" value="{value type='input' id='title'}" maxlength="64" size="40" class="form-control close_warning" id="title"/>
                {/if}
			</div>

			<div class="table_clear">
				<input type="submit" value="{_p var='Update'}" class="btn btn-primary"/>
			</div>
		</div>

		<div id="js_groups_block_url" class="block js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'url'}style="display:none;"{/if}>
			<div class="form-group">
				<label for="js_vanity_url_new">{_p var='Vanity url'}</label>
                <div>
                    <span class="help-block">{param var='core.path'}</span>
                    <input type="text" name="val[vanity_url]" value="{value type='input' id='vanity_url'}" size="20" id="js_vanity_url_new" class="form-control close_warning"/>
                </div>
			</div>

			<div class="table_clear" id="js_groups_vanity_url_button">
				<ul class="table_clear_button">
					<li>
						<div><input type="hidden" name="val[vanity_url_old]" value="{value type='input' id='vanity_url'}" size="20" id="js_vanity_url_old" /></div>
						<input type="button" value="{_p var='Check url'}" class="btn btn-primary" onclick="if ($('#js_vanity_url_new').val() != $('#js_vanity_url_old').val()) {l} $Core.processForm('#js_groups_vanity_url_button'); $($(this).parents('form:first')).ajaxCall('groups.changeUrl'); {r} return false;" />
					</li>
					<li class="table_clear_ajax"></li>
				</ul>
				<div class="clear"></div>
			</div>
		</div>

		<div id="js_groups_block_photo" class="js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'photo'}style="display:none;"{/if}>
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
			<div id="js_groups_block_customize_holder">
                <div class="form-group-follow special_close_warning">
                    {if $bIsEdit && !empty($aForms.image_path)}
                    {module name='core.upload-form' type='groups' current_photo=$aForms.image_path_200 id=$aForms.page_id}
                    {else}
                    {module name='core.upload-form' type='groups'}
                    {/if}
                </div>

                <div id="js_submit_upload_image" class="table_clear">
                    <input type="submit" value="{_p var='update_photo'}" class="btn btn-primary"/>
                </div>
			</div>
		</div>

		<div id="js_groups_block_info" class="js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'info'}style="display:none;"{/if}>
			{plugin call='groups.template_controller_add_1'}
			<div class="table form-group">
                {editor id='text'}
                <div class="pt-1">
                    <input type="submit" value="{_p var='Update'}" class="btn btn-primary"/>
                </div>
            </div>
		</div>

		<div id="js_groups_block_permissions" class="block js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'permissions'}style="display:none;"{/if}>
			<div id="privacy_holder_table">
				{if $bIsEdit }
				<div class="table form-group">
					<div class="table_left">
						{_p('Groups privacy')}
					</div>
					<div class="table_right">
                        <ul class="list-group">
                            <li class="list-group-item">
                                <label><input type="radio" class="close_warning" name="val[reg_method]" id="reg_method" value="0" {if $aForms.reg_method == '0'} checked{/if}>&nbsp;<i class="fa fa-privacy fa-privacy-0"></i>&nbsp;{_p var='Public'}</label>
                                <div class="extra_info">{_p var="Anyone can see the group, its members and their posts."}</div>
                            </li>
                            <li class="list-group-item">
                                <label><input type="radio" class="close_warning" name="val[reg_method]" id="reg_method" value="1" {if $aForms.reg_method == '1'} checked{/if}>&nbsp;<i class="fa fa-unlock-alt" aria-hidden="true"></i>&nbsp;{_p var='Closed'}</label>
                                <div class="extra_info">{_p var="Anyone can find the group and see who's in it. Only members can see posts."}</div>
                            </li>
                            <li class="list-group-item">
                                <label><input type="radio" class="close_warning" name="val[reg_method]" id="reg_method" value="2" {if $aForms.reg_method == '2'} checked{/if}>&nbsp;<i class="fa fa-lock" aria-hidden="true"></i>&nbsp;{_p var='Secret'}</label>
                                <div class="extra_info">{_p var="Only members can find the group and see posts."}</div>
                            </li>
                        </ul>
					</div>
				</div>
				{/if}
                <div class="privacy-block-content">
				{foreach from=$aPermissions item=aPerm}
                    <div class="item-outer">
                        <div class="form-group">
                            <label>{$aPerm.phrase}</label>
                            <div>
                                <select name="val[perms][{$aPerm.id}]" class="form-control close_warning">
                                    <option value="1"{if $aPerm.is_active == '1'} selected="selected"{/if}>{_p var='Members only'}</option>
                                    <option value="2"{if $aPerm.is_active == '2'} selected="selected"{/if}>{_p var='Admins only'}</option>
                                </select>
                            </div>
                        </div>
                    </div>
				{/foreach}
                </div>
				<div class="table_clear">
					<input type="submit" value="{_p var='Update'}" class="btn btn-primary"/>
				</div>
			</div>
		</div>

		<div id="js_groups_block_admins" class="js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'admins'}style="display:none;"{/if}>
			<div class="table form-group">
                {if Phpfox::getUserBy('profile_page_id')}
                    {_p var="Please login back as user to use this feature."}
                {else}
                    {module name='friend.search-small' input_name='admins' current_values=$aForms.admins}
                {/if}
			</div>

			<div class="table_clear">
				<input type="submit" value="{_p var='Update'}" class="btn btn-primary"/>
			</div>
		</div>

		<div id="js_groups_block_invite" class="js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'invite'}style="display:none;"{/if}>
			<div class="block">
                <div class="form-group">
                    <label for="js_find_friend">{_p var='invite_friends'}</label>
                    {if isset($aForms.page_id)}
                    <div id="js_selected_friends" class="hide_it"></div>
                    {module name='friend.search' input='invite' hide=true friend_item_id=$aForms.page_id friend_module_id='groups' in_form=true}
                    {/if}
                </div>
                <div class="form-group invite-friend-by-email">
                    <label for="emails">{_p var='invite_people_via_email'}</label>
                    <input name="val[emails]" id="emails" class="form-control close_warning" data-component="tokenfield" data-type="email">
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

		<div id="js_groups_block_widget" class="block js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'widget'}style="display:none;"{/if}>
			<div class="table form-group">
				<div class="groups_create_new_widget">
                    <a role="button" class="btn btn-primary" onclick="$Core.box('groups.widget', 700, 'page_id={$aForms.page_id}'); return false;">{_p var='Create new widget'}</a>
				</div>
                {if !empty($aBlockWidgets) && !empty($aMenuWidgets)}
                <p class="help-block">{_p var='drag_to_order_widgets'}</p>
                {/if}

                {if !empty($aBlockWidgets)}
                <div class="mt-2">
                    <label>{_p var='groups_block_type'}</label>
                    <table class="table table-striped drag-drop-table" id="js_drag_drop_block_type_block" data-app="core_groups" data-action-type="init" data-action="init_drag" data-table="#js_drag_drop_block_type_block" data-ajax="groups.orderWidget">
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
                        <tr id="js_groups_widget_{$aBlockWidget.widget_id}">
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
                                            <a role="button" onclick="$Core.box('groups.widget', 700, 'widget_id={$aBlockWidget.widget_id}'); return false;">
                                                <span class="ico ico-pencilline-o mr-1"></span>
                                                {_p var='edit'}
                                            </a>
                                        </li>
                                        <li class="item_delete">
                                            <a role="button" onclick="$Core.jsConfirm({l}message: '{_p var="groups_are_you_sure_you_want_to_delete_this_widget_permanently"}'{r}, function(){l} $.ajaxCall('groups.deleteWidget', 'widget_id={$aBlockWidget.widget_id}'); {r}, function(){l}{r}); return false;">
                                                <span class="ico ico-trash-alt-o mr-1"></span>
                                                {_p var='delete'}
                                            </a>
                                        </li>
                                    </ul>
                                </div>
                            </td>
                        </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>

                {/if}
                {if !count($aBlockWidgets)}
                <div class="alert alert-info">{_p var='groups_no_widget_found'}</div>
                {/if}
			</div>
		</div>

        <div id="js_groups_block_menu" class="block js_groups_block page_section_menu_holder" {if empty($sActiveTab) || $sActiveTab != 'menu'}style="display:none;"{/if}>
            <div class="table form-group">
                <div class="pages_create_new_widget">
                    <a role="button" class="btn btn-primary" onclick="$Core.box('groups.widget', 700, 'page_id={$aForms.page_id}&amp;is_menu=1'); return false;">{_p var='create_new_menu'}</a>
                </div>
                {if !empty($aPageMenus)}
                <p class="help-block">{_p var='drag_to_order_menus'}</p>
                <div class="mt-2">
                    <label>{_p var='group_menu_settings'}</label>
                    <table class="table table-striped drag-drop-table core-group-menu-table" id="js_drag_drop_block_page_menu" data-app="core_groups" data-action-type="init" data-action="init_drag" data-table="#js_drag_drop_block_page_menu" data-ajax="groups.orderMenu">
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
                            <td><div class="core-group-td">{if $aPageMenu.widget_id}{_p var=$aPageMenu.phrase}{else}{_p var=$aPageMenu.landing}{/if}</div></td>
                            <td class="text-center">
                                {if $aPageMenu.landing != 'home'}
                                <div class="core-group-menu-toggle">
                                    <label>
                                        <input id="menu-{$aPageMenu.landing}" type="checkbox" data-page-id="{$aForms.page_id}" data-menu-name="{$aPageMenu.landing}"
                                               data-menu-id="{$aPageMenu.menu_id}" data-app="core_groups" data-action="toggleActivePageMenu"
                                               data-action-type="click" {if !isset($aPageMenu.is_active) || !empty($aPageMenu.is_active)} checked="checked" {/if}>
                                        <span class="item-toggle-icon"></span>
                                    </label>
                                </div>
                                {/if}
                            </td>
                            <td class="widget-actions">
                                {if $aPageMenu.widget_id}
                                <div class="dropdown core-group-table-action">
                                    <a data-toggle="dropdown">
                                        <i class="fa fa-action"></i>
                                    </a>
                                    <ul role="menu" class="dropdown-menu dropdown-menu-right">
                                        <li>
                                            <a href="#" onclick="$Core.box('groups.widget', 700, 'widget_id={$aPageMenu.widget_id}&amp;is_menu=1'); return false;"><span class="ico ico-pencilline-o mr-1"></span> {_p var='edit'}</a>
                                        </li>
                                        <li class="item_delete">
                                            <a href="#" onclick="$Core.jsConfirm({l}message: '{_p var="groups_are_you_sure_you_want_to_delete_this_menu_permanently"}'{r}, function(){l} $.ajaxCall('groups.deleteWidget', 'widget_id={$aPageMenu.widget_id}'); {r}, function(){l}{r}); return false;"><span class="ico ico-trash-o mr-1"></span> {_p var='delete'}</a>
                                        </li>
                                    </ul>
                                </div>
                                {/if}
                            </td>
                        </tr>
                        {/foreach}
                        </tbody>
                    </table>
                </div>
                {/if}
            </div>
        </div>
	</form>
</div>
<!-- END of edit group -->
{else}
<!-- START add group -->
    {if !Phpfox::getUserBy('profile_page_id')}
    <div id="js_groups_add_holder" class="item-container group-add">
        <div class="main_break"></div>
        {foreach from=$aTypes item=aType}
        <div class="group-item" data-app="core_groups" data-action="add_new_group" data-type-id="{$aType.type_id}" data-action-type="click">
            <div class="item-outer">
            <div class="group-photo"
                 {if !empty($aType.image_path)}
                    style="background-image: url('{img server_id=$aType.image_server_id path='core.path_actual' file=$aType.image_path suffix='_200' return_url=true}')"
                 {else}
                    style="background-image: url('{img path='core.path_actual' file='PF.Site/Apps/core-groups/assets/img/default-category/default_category.png' return_url=true}')"
                 {/if}
            >
                <div class="group-add-inner-link">
                    <div class="groups-add-info">
                        <span class="item-title">
                        {if Phpfox::isPhrase($this->_aVars['aType']['name'])}
                            {_p var=$aType.name}
                        {else}
                            {$aType.name|convert}
                        {/if}
                        </span>
                        <div class="item-number-group">
                            {if $aType.pages_count != 1}
                                {$aType.pages_count} {_p var='groups'}
                            {else}
                                {$aType.pages_count} {_p var='_group'}
                            {/if}
                        </div>
                    </div>
                    <a class="item-group-add" data-app="core_groups" data-action="add_new_group" data-type-id="{$aType.type_id}" data-action-type="click"><span class="ico ico-plus"></span></a>
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