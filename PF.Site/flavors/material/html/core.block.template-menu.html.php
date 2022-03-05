<?php 

defined('PHPFOX') or exit('NO DICE!'); 

?>

{if $bOnlyMobileLogin}
	<ul class="nav navbar-nav visible-xs visible-sm site-menu site_menu">
		<li>
			<div class="login-menu-btns-xs clearfix">
				<div class="{if Phpfox::getParam('user.allow_user_registration') && !Phpfox::getParam('user.invite_only_community')}div01{/if}">
					<a class="btn btn01 btn-success text-uppercase {if Phpfox::canOpenPopup('login')}popup{else}no_ajax{/if}" rel="hide_box_title visitor_form" role="link" href="{url link='login'}">
						<i class="fa fa-sign-in"></i> {_p var='login_singular'}
					</a>
				</div>
				{if Phpfox::getParam('user.allow_user_registration') && !Phpfox::getParam('user.invite_only_community')}
				<div class="div02">
					<a class="btn btn02 btn-warning text-uppercase {if Phpfox::canOpenPopup('login')}popup{else}no_ajax{/if}" rel="hide_box_title visitor_form" role="link" href="{url link='user.register'}">
						{_p var='register'}
					</a>
				</div>
				{/if}
			</div>
		</li>
	</ul>
{else}
	{plugin call='core.template_block_template_menu_1'}
	<div class="visible-xs visible-sm">
		<span class="btn-close">
			<span class="ico ico-close"></span>
		</span>
		{ logo }
		<ul class="site-menu-small site_menu">
			{if Phpfox::getUserBy('profile_page_id') <= 0 && isset($aMainMenus)}
			{plugin call='theme_template_core_menu_list'}
			{if ($iMenuCnt = 0)}{/if}
			{foreach from=$aMainMenus key=iKey item=aMainMenu name=menu}
                {if !isset($aMainMenu.is_force_hidden)}
                    {iterate int=$iMenuCnt}
                {/if}
                <li rel="menu{$aMainMenu.menu_id}" {if (isset($iTotalHide) && isset($iMenuCnt) && $iMenuCnt > $iTotalHide)} style="display:none;" {/if} {if (($aMainMenu.url == 'apps' && count($aInstalledApps)) || (isset($aMainMenu.children) && count($aMainMenu.children))) || (isset($aMainMenu.is_force_hidden))}class="{if (isset($aMainMenu.children) && count($aMainMenu.children))}menu-has-sub{/if} {if isset($aMainMenu.is_force_hidden) && isset($iTotalHide)}is_force_hidden{else}explore{/if}{if ($aMainMenu.url == 'apps' && count($aInstalledApps))} explore_apps{/if}"{/if}>
                    <a {if !isset($aMainMenu.no_link) || $aMainMenu.no_link != true}href="{url link=$aMainMenu.url}" {else} href="#" onclick="return false;" {/if} class="menu-link-item {if isset($aMainMenu.is_selected) && $aMainMenu.is_selected} menu_is_selected {/if}{if isset($aMainMenu.external) && $aMainMenu.external == true}no_ajax_link {/if}ajax_link">
                        {if isset($aMainMenu.mobile_icon) && $aMainMenu.mobile_icon}
                            <i class="{$aMainMenu.mobile_icon}"></i>
                        {else}
                            <i class="ico ico-box-o"></i>
                        {/if}
                        <span>
                            {_p var=$aMainMenu.var_name}{if isset($aMainMenu.suffix)}{$aMainMenu.suffix}{/if}
                        </span>
                    </a>
                    {if !empty($aMainMenu.children)}
                        <a data-toggle="dropdown" role="button" class="menu-link-sub-more {if isset($aMainMenu.is_selected) && $aMainMenu.is_selected} menu_is_selected {/if}">
                            <span class="ico ico-caret-down"></span>
                        </a>
                        <ul class="site_sub_menu">
                            {foreach from=$aMainMenu.children key=cKey item=aChildMenu name=cmenu}
                                <li rel="menu{$aChildMenu.menu_id}">
                                    <a {if !isset($aChildMenu.no_link) || $aChildMenu.no_link != true}href="{url link=$aChildMenu.url}" {else} href="#" onclick="return false;" {/if} class="{if isset($aChildMenu.is_selected) && $aChildMenu.is_selected} menu_is_selected {/if}{if isset($aChildMenu.external) && $aChildMenu.external == true}no_ajax_link {/if}ajax_link">
                                        {if isset($aChildMenu.mobile_icon) && $aChildMenu.mobile_icon}
                                            <i class="{$aChildMenu.mobile_icon}"></i>
                                        {else}
                                            <i class="ico ico-box-o"></i>
                                        {/if}
                                        <span>
                                            {_p var=$aChildMenu.var_name}{if isset($aChildMenu.suffix)}{$aChildMenu.suffix}{/if}
                                        </span>
                                    </a>
                                </li>
                            {/foreach}
                        </ul>
                    {/if}
                </li>
			{/foreach}
			{/if}
		</ul>
	</div>

    {if !$iGlobalProfilePageId}
	<div class="visible-md visible-lg">
		<ul class="site-menu site_menu" data-component="menu">
            <div class="overlay"></div>
            {if Phpfox::getUserBy('profile_page_id') <= 0 && isset($aMainMenus)}
                {plugin call='theme_template_core_menu_list'}
                {assign var='iMenuPos' value=0}
                {foreach from=$aMainMenus key=iKey item=aMainMenu name=menu}
                    <li rel="menu{$aMainMenu.menu_id}" class="{if (isset($aMainMenu.children) && count($aMainMenu.children))}menu-has-sub{/if} {if $iMenuPos == 0 && $aMainMenu.url == ''}menu-home {/if}{if (($aMainMenu.url == 'apps' && count($aInstalledApps)) || (isset($aMainMenu.children) && count($aMainMenu.children))) || (isset($aMainMenu.is_force_hidden))}{if isset($aMainMenu.is_force_hidden) && isset($iTotalHide)}is_force_hidden{else}explore{/if}{if ($aMainMenu.url == 'apps' && count($aInstalledApps))} explore_apps{/if}{/if}">
                        <a {if !isset($aMainMenu.no_link) || $aMainMenu.no_link != true}href="{url link=$aMainMenu.url}" {else} href="#" onclick="return false;" {/if} class="menu-link-item {if isset($aMainMenu.is_selected) && $aMainMenu.is_selected} menu_is_selected {/if}{if isset($aMainMenu.external) && $aMainMenu.external == true}no_ajax_link {/if}ajax_link">
                            {if $iMenuPos == 0 && $aMainMenu.url == ''}
                                <i class="ico ico-home menu-home"></i>
                            {else}
                                <span>
                                    {_p var=$aMainMenu.var_name}{if isset($aMainMenu.suffix)}{$aMainMenu.suffix}{/if}
                                </span>
                            {/if}
                        </a>
                        
                        {if !empty($aMainMenu.children)}
                        <a data-toggle="dropdown" role="button" class="menu-link-sub-more">
                            <span class="ico ico-caret-down"></span>
                        </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-sub js_dropdown_menu_sub">
                                <ul class="site_sub_menu">
                                    {foreach from=$aMainMenu.children key=cKey item=aChildMenu name=cmenu}
                                    <li rel="menu{$aChildMenu.menu_id}">
                                        <a {if !isset($aChildMenu.no_link) || $aChildMenu.no_link != true}href="{url link=$aChildMenu.url}" {else} href="#" onclick="return false;" {/if} class="{if isset($aChildMenu.is_selected) && $aChildMenu.is_selected} menu_is_selected {/if}{if isset($aChildMenu.external) && $aChildMenu.external == true}no_ajax_link {/if}ajax_link">
                                            <!-- current hide icon on this case theme -->
                                            <!-- {if isset($aChildMenu.mobile_icon) && $aChildMenu.mobile_icon}
                                                <i class="{$aChildMenu.mobile_icon}"></i>
                                            {else}
                                                <i class="ico ico-box-o"></i>
                                            {/if} -->
                                            <span>
                                                {_p var=$aChildMenu.var_name}{if isset($aChildMenu.suffix)}{$aChildMenu.suffix}{/if}
                                            </span>
                                        </a>
                                    </li>
                                    {/foreach}
                                </ul>
                            </div>
                        {/if}
                    </li>
                {assign var='iMenuPos' value=$iKey}
                {/foreach}

                <li class="dropdown dropdown-overflow hide explorer menu-overflow">
                    <a data-toggle="dropdown" role="button">
                        <span class="ico ico-dottedmore-o"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                    </ul>
                </li>
            {/if}
		</ul>
	</div>
    {/if}
{/if}