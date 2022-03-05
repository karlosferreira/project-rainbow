<?php

namespace Apps\Core_BetterAds;

use Phpfox_Module;

Phpfox_Module::instance()->addServiceNames([
    'ad' => Service\Ad::class,
    'ad.get' => Service\Get::class,
    'ad.process' => Service\Process::class,
    'ad.report' => Service\Report::class,
    'ad.sponsor' => Service\Sponsor::class,
    'ad.browse' => Service\Browse::class,
    'ad.callback' => Service\Callback::class,
    'ad.migrate' => Service\Migrate::class,
])->addComponentNames('controller', [
    'ad.admincp.add' => Controller\Admin\AddController::class,
    'ad.admincp.index' => Controller\Admin\IndexController::class,
    'ad.admincp.addplacement' => Controller\Admin\AddPlacementController::class,
    'ad.admincp.invoice' => Controller\Admin\InvoiceController::class,
    'ad.admincp.placements' => Controller\Admin\PlacementController::class,
    'ad.admincp.sponsor' => Controller\Admin\SponsorController::class,
    'ad.admincp.sponsor-setting' => Controller\Admin\SponsorSettingController::class,
    'ad.admincp.migrate-ads' => Controller\Admin\MigrateAdsController::class,
    'ad.admincp.migrate-sponsorships' => Controller\Admin\MigrateSponsorshipsController::class,
    'ad.add' => Controller\AddController::class,
    'ad.image' => Controller\ImageController::class,
    'ad.index' => Controller\IndexController::class,
    'ad.invoice' => Controller\InvoiceController::class,
    'ad.manage' => Controller\ManageController::class,
    'ad.manage-sponsor' => Controller\ManageSponsorController::class,
    'ad.sample' => Controller\SampleController::class,
    'ad.preview' => Controller\PreviewController::class,
    'ad.sponsor' => Controller\SponsorController::class,
    'ad.report' => Controller\ReportController::class,
])->addComponentNames('block', [
    'ad.display' => Block\Display::class,
    'ad.inner' => Block\Inner::class,
    'ad.sample' => Block\Sample::class,
    'ad.sponsored' => Block\Sponsored::class,
    'ad.delete-placement' => Block\DeletePlacement::class,
    'ad.daily-reports' => Block\DailyReports::class,
    'ad.migrate-ad' => Block\MigrateAd::class,
])->addComponentNames('ajax', [
    'ad.ajax' => '\Apps\Core_BetterAds\Ajax\Ajax',
])->addTemplateDirs([
    'ad' => (new Install())->path . PHPFOX_DS . 'views',
])->addAliasNames('ad', 'Core_BetterAds');

group('/ad', function () {
    route('/', 'ad.index');
    route('/manage', 'ad.manage');
    route('/iframe', 'ad.iframe');
    route('/add/*', 'ad.add');
    route('/sample/*', 'ad.sample');
    route('/preview/*', 'ad.preview');
    route('/invoice', 'ad.invoice');
    route('/sponsor/*', 'ad.sponsor');
    route('/report/*', 'ad.report');
    route('/manage-sponsor/*', 'ad.manage-sponsor');
});

group('/ad/admincp', function () {
    route('/', 'ad.admincp.index');
    route('/add', 'ad.admincp.add');
    route('/addplacement/*', 'ad.admincp.addplacement');
    route('/placement', 'ad.admincp.placement');
    route('/invoice', 'ad.admincp.invoice');
    route('/sponsor', 'ad.admincp.sponsor');
    route('/sponsor-setting', 'ad.admincp.sponsor-setting');
    route('/migrate-ads', 'ad.admincp.migrate-ads');
    route('/migrate-sponsorships', 'ad.admincp.migrate-sponsorships');
});

defined('BETTERADS_DATETIME_FORMAT') or define('BETTERADS_DATETIME_FORMAT', 'g:i F j, Y');