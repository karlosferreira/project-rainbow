<?php

//Generate react color
function preaction_color_title($aItem)
{
    if (empty($aItem['title'])) {
        return '';
    }
    return '<strong style="color: #' . $aItem['color'] . '" class="p-reaction-title">' . _p($aItem['title']) . '</strong>';
}

$module = Phpfox_Module::instance();

$module->addAliasNames('preaction', 'P_Reaction')
    ->addComponentNames('controller', [
        'preaction.admincp.manage-reactions' => Apps\P_Reaction\Controller\Admin\ManageReactionsController::class,
        'preaction.admincp.add-reaction' => Apps\P_Reaction\Controller\Admin\AddReactionController::class
    ])
    ->addComponentNames('block', [
        'preaction.reaction-list-mini' => Apps\P_Reaction\Block\ReactionListMiniBlock::class,
        'preaction.list-react-by-item' => Apps\P_Reaction\Block\ListReactByItemBlock::class,
        'preaction.detail-react' => Apps\P_Reaction\Block\DetailReactBlock::class,
        'preaction.user-row' => Apps\P_Reaction\Block\UserRowBlock::class
    ])
    ->addComponentNames('ajax', [
        'preaction.ajax' => Apps\P_Reaction\Ajax\Ajax::class
    ])
    ->addServiceNames([
        'preaction' => Apps\P_Reaction\Service\Preaction::class,
        'preaction.process' => Apps\P_Reaction\Service\Process::class
    ])
    ->addTemplateDirs([
        'preaction' => PHPFOX_DIR_SITE_APPS . 'p-reaction' . PHPFOX_DS . 'views',
    ]);

group('/admincp', function () {
    route('/preaction', function () {
        auth()->isAdmin(true);
        Phpfox::getLib('module')->dispatch('preaction.admincp.manage-reactions');
        return 'controller';
    });
    route('/preaction/reactions-order', function () {
        auth()->isAdmin(true);
        $ids = request()->get('ids');
        $ids = trim($ids, ',');
        $ids = explode(',', $ids);
        $values = [];
        foreach ($ids as $key => $id) {
            $values[$id] = $key + 1;
        }
        Phpfox::getService('core.process')->updateOrdering([
                'table' => 'preaction_reactions',
                'key' => 'id',
                'values' => $values,
            ]
        );
        Phpfox::getLib('cache')->removeGroup('preaction');
        return true;
    });
});