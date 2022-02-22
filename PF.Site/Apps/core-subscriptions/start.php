<?php
namespace Apps\Core_Subscriptions;

use Phpfox;

Phpfox::getLib('module')
    ->addAliasNames('subscribe', 'Core_Subscriptions')
    ->addServiceNames([
        'subscribe' => Service\Subscribe::class,
        'subscribe.callback' => Service\Callback::class,
        'subscribe.process' => Service\Process::class,
        'subscribe.purchase' => Service\Purchase\Purchase::class,
        'subscribe.purchase.process' => Service\Purchase\Process::class,
        'subscribe.helper' => Service\Helper::class,
        'subscribe.reason' => Service\Reason\Reason::class,
        'subscribe.reason.process' => Service\Reason\Process::class,
        'subscribe.compare' => Service\Compare\Compare::class,
        'subscribe.compare.process' => Service\Compare\Process::class,
    ])
    ->addTemplateDirs([
        'subscribe' => PHPFOX_DIR_SITE_APPS . 'core-subscriptions' . PHPFOX_DS . 'views'
    ])
    ->addComponentNames('controller', [
        'subscribe.admincp.add' => Controller\Admin\AddController::class,
        'subscribe.admincp.index' => Controller\Admin\IndexController::class,
        'subscribe.admincp.compare' => Controller\Admin\CompareController::class,
        'subscribe.admincp.list' => Controller\Admin\ListController::class,
        'subscribe.admincp.view' => Controller\Admin\ViewController::class,
        'subscribe.admincp.reason' => Controller\Admin\ReasonController::class,
        'subscribe.admincp.add-reason' => Controller\Admin\AddReasonController::class,
        'subscribe.admincp.delete-reason' => Controller\Admin\DeleteReasonController::class,
        'subscribe.admincp.add-compare' => Controller\Admin\AddCompareController::class,
        'subscribe.complete' => Controller\CompleteController::class,
        'subscribe.index' => Controller\IndexController::class,
        'subscribe.list' => Controller\ListController::class,
        'subscribe.register' => Controller\RegisterController::class,
        'subscribe.view' => Controller\ViewController::class,
        'subscribe.compare' => Controller\CompareController::class,
        'subscribe.renew-method' => Controller\RenewMethodController::class,
    ])
    ->addComponentNames('ajax', [
        'subscribe.ajax' => Ajax\Ajax::class
    ])
    ->addComponentNames('block', [
        'subscribe.list' => Block\ListBlock::class,
        'subscribe.message' => Block\MessageBlock::class,
        'subscribe.upgrade' => Block\UpgradeBlock::class,
        'subscribe.renew-payment' => Block\RenewPaymentBlock::class,
        'subscribe.cancel-subscription' => Block\CancelSubscriptionBlock::class,
        'subscribe.cancel-reason' => Block\CancelReasonBlock::class,
        'subscribe.add-reason' => Block\AddReasonBlock::class,
    ]);

group('/subscribe', function () {
    // FrontEnd routes
    route('/', 'subscribe.index');
    route('/complete', 'subscribe.complete');
    route('/list', 'subscribe.list');
    route('/register/*', 'subscribe.register');
    route('/view/*', 'subscribe.view');
    route('/compare', 'subscribe.compare');
    route('/renew-method', 'subscribe.renew-method');
});
group('/subscribe/admincp', function(){
    // BackEnd routes
    route('/', 'subscribe.admincp.index');
    route('/order', function () {
        $table = request()->get('table');
        $field = request()->get('field');
        auth()->isAdmin(true);
        $ids = request()->get('ids');
        $ids = trim($ids, ',');
        $ids = explode(',', $ids);
        $values = [];
        foreach ($ids as $key => $id) {
            $values[$id] = $key + 1;
        }
        Phpfox::getService('core.process')->updateOrdering([
                'table' => $table,
                'key' => $field,
                'values' => $values,
            ]
        );
        return true;
    });
});

Phpfox::getLib('setting')->setParam('subscribe.default_photo_package', 'assets/images/membership_thumbnail.jpg');
Phpfox::getLib('setting')->setParam('subscribe.photo_url', setting('core.url_pic').'subscribe'. PHPFOX_DS);
Phpfox::getLib('setting')->setParam('subscribe.app_url', Phpfox::getParam('core.path_actual').'PF.Site/Apps/core-subscriptions/');