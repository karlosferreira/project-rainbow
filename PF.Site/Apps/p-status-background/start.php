<?php
namespace Apps\P_StatusBg;

use Phpfox;
use Phpfox_Module;

$oModule = Phpfox_Module::instance();
$oModule->addAliasNames('pstatusbg', 'P_StatusBg')
    ->addComponentNames('controller', [
        'pstatusbg.admincp.manage-collections' => Controller\Admin\ManageCollectionsController::class,
        'pstatusbg.admincp.add-collection' => Controller\Admin\AddCollectionController::class,
        'pstatusbg.admincp.frame-upload' => Controller\Admin\FrameUploadController::class
    ])
    ->addComponentNames('block', [
        'pstatusbg.collections-list' => Block\CollectionsListBlock::class
    ])
    ->addComponentNames('ajax', [
        'pstatusbg.ajax' => Ajax\Ajax::class
    ])
    ->addServiceNames([
        'pstatusbg' => Service\Pstatusbg::class,
        'pstatusbg.process' => Service\Process::class,
        'pstatusbg.callback' => Service\Callback::class
    ])
    ->addTemplateDirs([
        'pstatusbg' => PHPFOX_DIR_SITE_APPS . 'p-status-background' . PHPFOX_DS . 'views',
    ]);
group('/admincp', function () {
    route('/pstatusbg', function () {
        auth()->isAdmin(true);
        Phpfox::getLib('module')->dispatch('pstatusbg.admincp.manage-collections');
        return 'controller';
    });
});
Phpfox::getLib('setting')->setParam('pstatusbg.thumbnail_sizes', array(48, 300, 1024));