<?php
namespace Apps\Core_Messages;

use Phpfox;

Phpfox::getLib('module')
    ->addAliasNames('mail', 'Core_Messages')
    ->addServiceNames([
        'mail' => Service\Mail::class,
        'mail.process' => Service\Process::class,
        'mail.callback' => Service\Callback::class,
        'mail.helper' => Service\Helper::class,
        'mail.customlist' => Service\CustomList\CustomList::class,
        'mail.customlist.process' => Service\CustomList\Process::class,
    ])
    ->addTemplateDirs([
        'mail' => PHPFOX_DIR_SITE_APPS . 'core-messages' . PHPFOX_DS . 'views'
    ])
    ->addComponentNames('controller', [
        'mail.admincp.index' => Controller\Admin\IndexController::class,
        'mail.admincp.messages' => Controller\Admin\ManageMessageController::class,
        'mail.admincp.conversations' => Controller\Admin\ManageConversationsController::class,
        'mail.admincp.export-data-chat-plus' => Controller\Admin\ExportDataController::class,
        'mail.download-export' => Controller\DownloadExportController::class,
        'mail.compose' => Controller\ComposeController::class,
        'mail.index' => Controller\IndexController::class,
        'mail.panel' => Controller\PanelController::class,
        'mail.thread' => Controller\ThreadController::class,
        'mail.send-message' => Controller\SendMessageController::class,
        'mail.conversation-popup' => Controller\ConversationPopupController::class,
        'mail.customlist.index' => Controller\CustomListController::class,
        'mail.customlist.add' => Controller\AddCustomListController::class,
    ])
    ->addComponentNames('ajax', [
        'mail.ajax' => Ajax\Ajax::class
    ])
    ->addComponentNames('block', [
        'mail.group-members' => Block\GroupMember::class
    ]);

group('/mail', function () {

    // BackEnd routes
    route('/admincp', function () {
        auth()->isAdmin(true);
        Phpfox::getLib('module')->dispatch('mail.admincp.index');
        return 'controller';
    });
    route('/','mail.index');
    route('/compose','mail.compose');
    route('/panel','mail.panel');
    route('/thread','mail.thread');
    route('/send-message','mail.send-message');
    route('/conversation-popup','mail.conversation-popup');
    route('/download-export','mail.download-export');
});

group('/mail/customlist', function(){
    route('/','mail.customlist.index');
    route('/add','mail.customlist.add');
});