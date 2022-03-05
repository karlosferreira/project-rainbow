<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php



 if (Phpfox ::isUser()): ?>
<ul class="user-sticky-bar-items">
<?php if (Phpfox ::getUserBy('profile_page_id') > 0): ?>
<?php else: ?>
<?php (($sPlugin = Phpfox_Plugin::get('core.template_notification_list_start')) ? eval($sPlugin) : false); ?>
    <li class="mr-1" id="hd-request">
        <a role="button"
            data-toggle="dropdown"
            class="notification-icon w-4"
            title="<?php echo _p('friend_requests'); ?>"
            data-panel="#request-panel-body"
            data-url="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('friend.panel', [], false, false); ?>">
            <span class="circle-background s-4">
            </span>
            <span id="js_total_new_friend_requests" class="notify-number"></span>
        </a>
        <div class="dropdown-panel">
            <div class="dropdown-panel-header">
                <span>
<?php echo _p('friend_requests'); ?>
                    <span class="count-unread" id="js_total_friend_requests"></span>
                </span>
            </div>
            <div class="dropdown-panel-body" id="request-panel-body"></div>
             <div class="dropdown-panel-footer" id="request-panel-footer">
                 <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('friend.accept', [], false, false); ?>" id="js_view_all_requests"></a>
             </div>
        </div>
    </li>
    <li class="mr-1" id="hd-message">
        <a role="button"
            class="notification-icon w-4"
            data-toggle="dropdown"
            title="<?php echo _p('messages'); ?>"
            data-panel="#message-panel-body"
            data-url="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('mail.panel', [], false, false); ?>">
            <span class="circle-background s-4">
            </span>
            <span id="js_total_new_messages" class="notify-number"></span>
        </a>
        <div class="dropdown-panel">
            <div class="dropdown-panel-header">
                <span>
<?php echo _p('messages'); ?>
                    <span class="count-unread" id="js_total_unread_messages"></span>
                </span>
            </div>
            <div class="dropdown-panel-body" id="message-panel-body"></div>
            <div class="dropdown-panel-footer <?php if (! empty ( $this->_aVars['aMessages'] )): ?>one-el<?php endif; ?>">
                <a role="button" onclick="$.ajaxCall('mail.markAllRead'); event.stopPropagation();" <?php if (! empty ( $this->_aVars['aMessages'] )): ?>style="display:none;"<?php endif; ?> data-action="mail_mark_all_read">
                    <span class="ico ico-check-circle-alt"></span>
<?php echo _p('mark_all_read'); ?>
                </a>
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('mail', [], false, false); ?>"><?php echo _p('view_all_messages'); ?></a>
             </div>
        </div>
    </li>
    <li class="mr-2" id="hd-notification">
        <a role="button"
            class="notification-icon w-4"
            data-panel="#notification-panel-body"
            title="<?php echo _p('notifications'); ?>"
            data-toggle="dropdown"
            data-url="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('notification.panel', [], false, false); ?>">
            <span class="circle-background s-4">
            </span>
            <span id="js_total_new_notifications" class="notify-number"></span>
        </a>
        <div class="dropdown-panel">
            <div class="dropdown-panel-header">
                <span><?php echo _p('notifications'); ?></span>
                <a role="button" onclick="$.ajaxCall('notification.markAllRead');event.stopPropagation();"><?php echo _p('mark_all_read_notification'); ?></a>
            </div>
            <div class="dropdown-panel-body" id="notification-panel-body"></div>
            <div class="dropdown-panel-footer">
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('notification', [], false, false); ?>"><?php echo _p('view_all_notifications'); ?></a>
            </div>
        </div>
    </li>
<?php endif; ?>
    <li class="mr-1 user-icon s-4 avatar circle" id="hd-user">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aGlobalUser'],'suffix' => '_120_square')); ?>
    </li>
    <li class="settings-dropdown" id="hd-cof">
        <a href="#"
            class="notification-icon w-2"
            data-toggle="dropdown"
            type="button"
            aria-haspopup="true"
            aria-expanded="false">
            <span class="circle-background s-2">
                <span class="ico ico-angle-down"></span>
            </span>
        </a>

<?php if (Phpfox ::getUserBy('profile_page_id') > 0): ?>
        <ul class="dropdown-menu dropdown-menu-right dont-unbind">
            <li class="background-cover-block">
                <a href="#" class="background-cover" <?php if (! empty ( $this->_aVars['aCoverPhoto'] ) || ! empty ( $this->_aVars['sPageCoverDefaultUrl'] )): ?>style="background-image:url('<?php if (! empty ( $this->_aVars['aCoverPhoto'] )):  echo Phpfox::getLib('phpfox.image.helper')->display(array('server_id' => $this->_aVars['aCoverPhoto']['server_id'],'path' => "photo.url_photo",'file' => $this->_aVars['aCoverPhoto']['destination'],'suffix' => "_500",'class' => "cover_photo",'return_url' => true));  else:  echo $this->_aVars['sPageCoverDefaultUrl'];  endif; ?>')"<?php endif; ?>></a>
                <div class="profile-info pl-2 pr-7 py-1">
                    <div class="fullname"><?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aGlobalUser']['full_name'])); ?></div>
                </div>
                <div class="edit-page">
                    <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('pages.add', array('id' => $this->_aVars['iGlobalProfilePageId']), false, false); ?>" class="s-4 btn-gradient btn-primary">
                        <i class="ico ico-pencilline-o"></i>
                    </a>
                </div>
            </li>
            <li class="header_menu_user_link_page">
                <a href="#" onclick="$.ajaxCall('pages.logBackIn'); return false;">
                        <i class="ico ico-reply-o"></i>
<?php echo _p('log_back_in_as_global_full_name', array('global_full_name' => Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aGlobalProfilePageLogin']['full_name'])))); ?>
                </a>
            </li>
        </ul>
<?php else: ?>
        <ul class="dropdown-menu dropdown-menu-right dont-unbind">
            <li class="background-cover-block">
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('profile', [], false, false); ?>" class="background-cover" <?php if (! empty ( $this->_aVars['aCoverPhoto'] ) || ! empty ( $this->_aVars['sCoverDefaultUrl'] )): ?>style="background-image:url('<?php if (! empty ( $this->_aVars['aCoverPhoto'] )):  echo Phpfox::getLib('phpfox.image.helper')->display(array('server_id' => $this->_aVars['aCoverPhoto']['server_id'],'path' => "photo.url_photo",'file' => $this->_aVars['aCoverPhoto']['destination'],'suffix' => "_500",'class' => "cover_photo",'return_url' => true));  else:  echo $this->_aVars['sCoverDefaultUrl'];  endif; ?>')"<?php endif; ?>></a>
                <div class="profile-info pl-2 pr-7 py-1">
<?php if (! empty ( $this->_aVars['aCurentUser']['full_name'] )): ?>
                    <div class="fullname"><?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aCurentUser']['full_name'])); ?></div>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aCurentUser']['title'] )): ?>
                    <div class="memebership-level"><?php echo _p($this->_aVars['aCurentUser']['title']); ?></div>
<?php endif; ?>
                </div>
                <div class="edit-profile">
                    <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.profile', [], false, false); ?>" title="<?php echo _p('edit_profile'); ?>" class="no_ajax s-4 btn-primary btn-gradient">
                        <i class="ico ico-pencilline-o"></i>
                    </a>
                </div>
            </li>
<?php if (Phpfox ::isModule('pages') && Phpfox ::getUserParam('pages.can_add_new_pages')): ?>
            <li>
                <a href="#" onclick="$Core.box('pages.login', 400); return false;">
                    <i class="ico ico-unlock-o"></i>
<?php echo _p('login_as_page'); ?>
                </a>
            </li>
<?php endif; ?>
            <li role="presentation">
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.setting', [], false, false); ?>" class="no_ajax">
                    <i class="ico ico-businessman"></i>
<?php echo _p('account_settings'); ?>
                </a>
            </li>

            <li role="presentation">
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('friend', [], false, false); ?>" class="no_ajax">
                     <i class="ico ico-user-couple"></i>
<?php echo _p('manage_friends'); ?>
                </a>
            </li>
            <li role="presentation">
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.privacy', [], false, false); ?>" class="no_ajax">
                    <i class="ico ico-shield"></i>
<?php echo _p('privacy_settings'); ?>
                </a>
            </li>

<?php (($sPlugin = Phpfox_Plugin::get('core.template_block_notification_dropdown_menu')) ? eval($sPlugin) : false); ?>
<?php (($sPlugin = Phpfox_Plugin::get('core.template-notification-custom')) ? eval($sPlugin) : false); ?>

            <li role="presentation">
                <a href="javascript:void(0)" onclick="tb_show('<?php echo _p('manage_schedule_items'); ?>', $.ajaxBox('core.manageScheduleItems', ''));">
                    <i class="ico ico-clock-o" aria-hidden="true"></i>
<?php echo _p('manage_schedule_items'); ?>
                </a>
            </li>

<?php if (Phpfox ::isAdmin()): ?>
                <li role="presentation">
                    <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('admincp', [], false, false); ?>" target="_blank" class="no_ajax">
                        <i class="ico ico-gear-o"></i>
<?php echo _p('menu_admincp'); ?>
                    </a>
                </li>
<?php endif; ?>
            <li class="divider"></li>
            <li role="presentation">
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.logout', [], false, false); ?>" class="no_ajax logout">
<?php echo _p('logout'); ?>
                </a>
            </li>
        </ul>
<?php endif; ?>
    </li>
</ul>
<?php else: ?>
<div class="guest-login-small" data-component="guest-actions">
    <a class="btn btn-sm btn-success btn-gradient <?php if (Phpfox ::canOpenPopup('login')): ?>popup<?php else: ?>no_ajax<?php endif; ?>"
       rel="hide_box_title visitor_form" role="link" href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('login', [], false, false); ?>">
<?php echo _p('sign_in'); ?>
    </a>
</div>
<?php endif; ?>

