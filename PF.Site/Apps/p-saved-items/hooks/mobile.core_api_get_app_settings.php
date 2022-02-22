<?php
$l = $this->getLocalization();
$specialResources = Phpfox::getService('saveditems')->getSpecialResourceNamesForAddingSaveAction();
$defaultActionMenus = $this->getDefaultActionMenu();
foreach ($settings as $moduleId => $setting) {
    if ($moduleId != 'saveditems') {
        $tempSources = $setting['setting']['resources'];
        foreach ($tempSources as $resourceName => $resourceSetting) {
            if (isset($resourceSetting['action_menu']) || in_array($resourceName, $specialResources)) {
                if(empty($resourceSetting['action_menu'])) {
                    $resourceSetting['action_menu'] = $defaultActionMenus['options'];
                }

                if (count($resourceSetting['action_menu']) == 1) {
                    $resourceSetting['action_menu'] = array_merge([
                        [
                            'label' => $l->translate('save'),
                            'value' => $moduleId != 'feed' ? (Phpfox::getParam('saveditems.open_popup_in_item_detail') ? 'saveditems/save_popup_detail_item' : 'saveditems/save_detail_item') : 'saveditems/save',
                            'acl' => 'can_save_item',
                            'show' => '!is_saved&&!is_pending'
                        ],
                        [
                            'label' => $l->translate('saveditems_unsave'),
                            'value' => $moduleId != 'feed' ? (Phpfox::getParam('saveditems.open_confirmation_in_item_detail') ? 'saveditems/unsave_confirmation_detail_item' : 'saveditems/unsave_detail_item') : 'saveditems/unsave_in_feed',
                            'acl' => 'can_save_item',
                            'show' => 'is_saved&&!is_pending'
                        ],
                    ], $resourceSetting['action_menu']);
                } else {
                    $lastAction = array_pop($resourceSetting['action_menu']);
                    $resourceSetting['action_menu'] = array_merge($resourceSetting['action_menu'], [
                        [
                            'label' => $l->translate('save'),
                            'value' => $moduleId != 'feed' ? (Phpfox::getParam('saveditems.open_popup_in_item_detail') ? 'saveditems/save_popup_detail_item' : 'saveditems/save_detail_item') : 'saveditems/save',
                            'acl' => 'can_save_item',
                            'show' => '!is_saved&&!is_pending'
                        ],
                        [
                            'label' => $l->translate('saveditems_unsave'),
                            'value' => $moduleId != 'feed' ? (Phpfox::getParam('saveditems.open_confirmation_in_item_detail') ? 'saveditems/unsave_confirmation_detail_item' : 'saveditems/unsave_detail_item') : 'saveditems/unsave_in_feed',
                            'acl' => 'can_save_item',
                            'show' => 'is_saved&&!is_pending'
                        ],
                        $lastAction,
                    ]);
                }
                $tempSources[$resourceName] = $resourceSetting;
            }
        }
        $settings[$moduleId]['setting']['resources'] = $tempSources;
    }
}