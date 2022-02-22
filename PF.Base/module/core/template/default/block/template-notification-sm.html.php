<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author           phpFox LLC
 * @package          Phpfox
 * @version          $Id: template-notification.html.php 2838 2011-08-16 19:09:21Z phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
{if Phpfox::isUser()}
<nav class="pull-right">
    <ul class="list-inline header-right-menu">
        {if Phpfox::getUserBy('profile_page_id') > 0}
        {else}
        {plugin call='core.template_notification_sm_list_start'}
        <li class="pl-5" id="hd-request">
            <a role="button" title="{_p('friend_requests')}"
               data-toggle="dropdown"
               class="btn-abr"
               data-panel="#request-panel-body-sm"
               data-url="{url link='friend.panel'}">
                <i class="fa fa-user-plus"></i>
                <span id="js_total_new_friend_requests"></span>
            </a>
            <div class="dropdown-panel">
                <div class="dropdown-panel-body" id="request-panel-body-sm"></div>
            </div>
        </li>
        <li class="pl-5" id="hd-notification">
            <a role="button" title="{_p('notifications')}"
               class="btn-abr"
               data-panel="#notification-panel-body-sm"
               data-toggle="dropdown"
               data-url="{url link='notification.panel'}">
                <i class="fa fa-bell"></i>
                <span id="js_total_new_notifications"></span>
            </a>
            <div class="dropdown-panel">
                <div class="dropdown-panel-body" id="notification-panel-body-sm"></div>
            </div>
        </li>
        <li class="pl-5" id="hd-message">
            <a role="button" title="{_p('messages')}"
               class="btn-abr"
               data-toggle="dropdown"
               data-panel="#message-panel-body-sm"
               data-url="{url link='mail.panel'}">
                <i class="fa fa-comment"></i>
                <span id="js_total_new_messages"></span>
            </a>
            <div class="dropdown-panel">
                <div class="dropdown-panel-body" id="message-panel-body-sm"></div>
                <div class="dropdown-panel-footer {if !empty($aMessages)}one-el{/if}">
                    <a role="button" onclick="$.ajaxCall('mail.markAllRead'); event.stopPropagation();" {if !empty($aMessages)}style="display:none;"{/if} data-action="mail_mark_all_read">
                        <span class="ico ico-check-circle-alt"></span>
                        {_p var='mark_all_read'}
                    </a>
                    <a href="{url link='mail'}">{_p var='view_all_messages'}</a>
                 </div>
            </div>
        </li>
        {/if}
        <li class="pl-0" id="hd-cof">
            <a href="#" title="{_p('account')}"
               class="btn-abr"
               data-toggle="dropdown"
               type="button"
               aria-haspopup="true"
               aria-expanded="false">
                <i class="fa fa-cog"></i>
            </a>
            {if Phpfox::getUserBy('profile_page_id') > 0}
            <ul class="dropdown-menu dropdown-menu-right dont-unbind">
                <li class="header_menu_user_link_page">
                    <a href="#" onclick="$.ajaxCall('pages.logBackIn'); return false;">
                        <i class="fa fa-reply" aria-hidden="true"></i>
                        {_p var='log_back_in_as_global_full_name'
                        global_full_name=$aGlobalProfilePageLogin.full_name|clean}
                    </a>
                </li>
                <li>
                    <a href="{url link='pages.add' id=$iGlobalProfilePageId}">
                        <i class="fa fa-cog"></i>
                        {_p var='edit_page'}
                    </a>
                </li>
            </ul>
            {else}
            <ul class="dropdown-menu dropdown-menu-right dont-unbind">
                {if Phpfox::isAppActive('Core_Pages') && Phpfox::getUserParam('pages.can_add_new_pages')}
                <li>
                    <a href="#" onclick="$Core.box('pages.login', 400); return false;">
                        <i class="fa fa-flag"></i>
                        {_p var='login_as_page'}
                    </a>
                </li>
                {/if}
                <li role="presentation">
                    <a href="{url link='user.setting'}" class="no_ajax">
                        <i class="fa fa-cog"></i>
                        {_p var='account_settings'}
                    </a>
                </li>
                <li role="presentation">
                    <a href="{url link='user.profile'}" class="no_ajax">
                        <i class="fa fa-edit"></i>
                        {_p var='edit_profile'}
                    </a>
                </li>
                <li role="presentation">
                    <a href="{url link='friend'}" class="no_ajax">
                        <i class="fa fa-group"></i>
                        {_p var='manage_friends'}
                    </a>
                </li>
                <li role="presentation">
                    <a href="{url link='user.privacy'}" class="no_ajax">
                        <i class="fa fa-shield"></i>
                        {_p var='privacy_settings'}
                    </a>
                </li>
                {plugin call='core.template_block_notification_dropdown_menu'}
                {plugin call='core.template-notification-custom'}
                <li role="presentation">
                    <a href="javascript:void(0)" onclick="tb_show('{_p var='manage_schedule_items'}', $.ajaxBox('core.manageScheduleItems', ''));">
                        <i class="ico ico-clock-o" aria-hidden="true"></i>
                        {_p var='manage_schedule_items'}
                    </a>
                </li>
                {if Phpfox::isAdmin() }
                    <li class="divider"></li>
                    <li role="presentation">
                        <a href="{url link='admincp'}" target="_blank" class="no_ajax">
                            <i class="fa fa-diamond"></i>
                            {_p var='menu_admincp'}
                        </a>
                    </li>
                {/if}
                <li class="divider"></li>
                <li role="presentation">
                    <a href="{url link='user.logout'}" class="no_ajax logout">
                        <i class="fa fa-toggle-off"></i>
                        {_p var='logout'}
                    </a>
                </li>
            </ul>
            {/if}
        </li>
        <li class="pl-5" id="hd-user">
            {img user=$aGlobalUser suffix='_120_square'}
        </li>
    </ul>
</nav>
{else}
<div class="guest_login_small pull-right">
    <a class="btn btn01 btn-success text-uppercase {if Phpfox::canOpenPopup('login')}popup{else}no_ajax{/if}" rel="hide_box_title" role="link" href="{url link='login'}">
        <i class="fa fa-sign-in"></i> {_p var='login_singular'}
    </a>
    {if Phpfox::getParam('user.allow_user_registration') && !Phpfox::getParam('user.invite_only_community')}
    <a class="btn btn02 btn-warning text-uppercase {if Phpfox::canOpenPopup('login')}popup{else}no_ajax{/if}" rel="hide_box_title" role="link" href="{url link='user.register'}">
        {_p var='register'}
    </a>
    {/if}
</div>
{/if}
