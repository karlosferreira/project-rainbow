<?php

namespace Apps\P_SavedItems;

use Phpfox;
use Phpfox_Template;

Phpfox::getLib('module')
    ->addAliasNames('saveditems', 'P_SavedItems')
    ->addServiceNames([
        'saveditems' => Service\SavedItems::class,
        'saveditems.process' => Service\Process::class,
        'saveditems.collection' => Service\Collection\Collection::class,
        'saveditems.collection.browse' => Service\Collection\Browse::class,
        'saveditems.collection.process' => Service\Collection\Process::class,
        'saveditems.callback' => Service\Callback::class,
        'saveditems.friend' => Service\Friend\Friend::class,
        'saveditems.friend.process' => Service\Friend\Process::class,
        'mobile.saveditems_api' => Service\Api\SavedItemsApi::class,
        'mobile.saveditems_collection_api' => Service\Api\SavedItemsCollectionApi::class,
    ])
    ->addTemplateDirs([
        'saveditems' => PHPFOX_DIR_SITE_APPS . 'p-saved-items' . PHPFOX_DS . 'views'
    ])
    ->addComponentNames('controller', [
        'saveditems.index' => Controller\IndexController::class,
        'saveditems.collections' => Controller\CollectionsController::class,
        'saveditems.admincp.index' => Controller\Admin\IndexController::class,
        'saveditems.profile' => Controller\ProfileController::class,
    ])
    ->addComponentNames('ajax', [
        'saveditems.ajax' => Ajax\Ajax::class,
    ])
    ->addComponentNames('block', [
        'saveditems.collection.form' => Block\AddCollectionBlock::class,
        'saveditems.category' => Block\CategoryBlock::class,
        'saveditems.collection.recent-update' => Block\Collection\RecentUpdateBlock::class,
        'saveditems.open-confirmation-popup' => Block\OpenConfirmationPopup::class,
        'saveditems.collection.add-collection-popup' => Block\Collection\AddCollectionPopup::class,
        'saveditems.collection.add-friend-popup' => Block\Collection\AddFriendPopup::class,
        'saveditems.collection.friend-list-popup' => Block\Collection\FriendListPopup::class,
        'saveditems.listing-user' => Block\ListingUser::class
    ]);

group('/saved', function () {
    // FrontEnd routes
    route('/collections/*', 'saveditems.collections');
    route('/*', 'saveditems.index');
});

new \Core\Event([
    'lib_phpfox_template_getheader' => function (Phpfox_Template $Template) {
        $isDetail = $Template->getVar('bIsDetailPage');
        if(defined('PHPFOX_IS_USER_PROFILE') || (defined('PHPFOX_IS_PAGES_VIEW') && defined('PHPFOX_PAGES_ITEM_TYPE'))) {
            $isDetail = false;
        }
        $turnOnConfirmationWithUnsavedAction = Phpfox::getParam('saveditems.open_confirmation_in_item_detail');
        $Template->setPhrase(['saveditems_unsave_item']);
        $Template->setHeader( '<script>
                oTranslations["'. ($isDetail ? 'saveditemscheckdetail_1' : 'saveditemscheckdetail_0') .'"] = "'. ($isDetail ? 'saveditemscheckdetail_1' : 'saveditemscheckdetail_0') .'";
                oTranslations["'. ($turnOnConfirmationWithUnsavedAction ? 'saveditemscheckturnonconfirmationwithunsavedaction_1' : 'saveditemscheckturnonconfirmationwithunsavedaction_0') .'"] = "'. ($turnOnConfirmationWithUnsavedAction ? 'saveditemscheckturnonconfirmationwithunsavedaction_1' : 'saveditemscheckturnonconfirmationwithunsavedaction_0') .'";
                </script>');
    }
]);

Phpfox::getLib('setting')->setParam('saveditems.url_pic', Phpfox::getParam('core.path_actual'). 'PF.Base' . PHPFOX_DS . 'file' . PHPFOX_DS . 'pic' . PHPFOX_DS . 'saveditems' . PHPFOX_DS);
Phpfox::getLib('setting')->setParam('saveditems.images_url', Phpfox::getParam('core.path_actual'). 'PF.Site/Apps/p-saved-items/assets/images/');
Phpfox::getLib('setting')->setParam('saveditems.default_collection_photo', Phpfox::getParam('saveditems.images_url'). 'default-collection-photo.png');