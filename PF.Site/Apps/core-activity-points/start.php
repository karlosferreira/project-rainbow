<?php

namespace Apps\Core_Activity_Points;

use Phpfox;

Phpfox::getLib('module')
    ->addAliasNames('activitypoint', 'Core_Activity_Points')
    ->addServiceNames([
        'activitypoint' => Service\ActivityPoint::class,
        'activitypoint.package' => Service\Package\Package::class,
        'activitypoint.package.process' => Service\Package\Process::class,
        'activitypoint.callback' => Service\Callback::class,
        'activitypoint.process' => Service\Process::class
    ])
    ->addTemplateDirs([
        'activitypoint' => PHPFOX_DIR_SITE_APPS . 'core-activity-points' . PHPFOX_DS . 'views'
    ])
    ->addComponentNames('controller', [
        'activitypoint.index' => Controller\IndexController::class,
        'activitypoint.package' => Controller\PackageController::class,
        'activitypoint.information' => Controller\InformationController::class,
        'activitypoint.complete' => Controller\CompleteController::class,
        'activitypoint.admincp.index' => Controller\Admin\IndexController::class,
        'activitypoint.admincp.package' => Controller\Admin\PackageController::class,
        'activitypoint.admincp.add-package' => Controller\Admin\AddPackageController::class,
        'activitypoint.admincp.transaction' => Controller\Admin\TransactionController::class,
        'activitypoint.admincp.point' => Controller\Admin\PointController::class,
    ])
    ->addComponentNames('ajax', [
        'activitypoint.ajax' => Ajax\Ajax::class
    ])
    ->addComponentNames('block', [
        'activitypoint.purchase-package' => Block\PurchasePackageBlock::class,
        'activitypoint.adjust-point' => Block\AdjustPointBlock::class
    ]);

group('/activitypoint', function () {
    // BackEnd routes
    route('/admincp', function () {
        auth()->isAdmin(true);
        Phpfox::getLib('module')->dispatch('activitypoint.admincp.index');
        return 'controller';
    });
    route('/', 'activitypoint.index');
    route('/package', 'activitypoint.package');
    route('/information', 'activitypoint.information');
    route('/complete', 'activitypoint.complete');
});
Phpfox::getLib('setting')->setParam('activitypoint.dir_image', Phpfox::getParam('core.dir_pic') . 'activitypoint' . PHPFOX_DS);
Phpfox::getLib('setting')->setParam('activitypoint.url_image', Phpfox::getParam('core.url_pic') . 'activitypoint' . PHPFOX_DS);
Phpfox::getLib('setting')->setParam('activitypoint.url_asset_images', Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-activity-points/assets/images/');