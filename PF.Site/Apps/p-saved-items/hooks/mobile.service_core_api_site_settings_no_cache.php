<?php
if (Phpfox::isAppActive('P_SavedItems') && !empty($data['screen_setting']['saveditems'])) {
    $savedTypes = Phpfox::getService('saveditems')->getStatisticByType();
    $scrollEnable = false;
    $resourceName = \Apps\P_SavedItems\Api\Resource\SavedItemsResource::populate([])->getResourceName();
    $tabs = [
        [
            'label' => 'all',
            'component' => \Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting::SMART_RESOURCE_LIST,
            'module_name' => 'saveditems',
            'resource_name' => $resourceName,
            'item_template' => 'saveditems.saved-item',
            'search' => true,
            'use_query' => ['type' => 'all'],
            'scrollEnabled' => $scrollEnable,
        ]
    ];

    if (!empty($savedTypes)) {
        foreach ($savedTypes as $savedType) {
            $tabs[] = [
                'label' => $savedType['type_name'],
                'component' => \Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting::SMART_RESOURCE_LIST,
                'module_name' => 'saveditems',
                'resource_name' => $resourceName,
                'item_template' => 'saveditems.saved-item',
                'search' => true,
                'use_query' => ['type' => $savedType['type_id']],
                'scrollEnabled' => $scrollEnable,
            ];
        }
    }

    $tabsForCollection = [];
    foreach ($tabs as $tab) {
        $tabsForCollection[] = array_merge($tab, [
            'use_query' => [
                'type' => $tab['use_query']['type'],
                'collection_id' => ':id'
            ]
        ]);
    }

    $data['screen_setting']['saveditems']['moduleSaveditems']['content']['embedComponents'][] = [
        'component' => \Apps\P_SavedItems\Api\Resource\SavedItemsResource::SMART_TAB_BAR,
        'apiUrl' => 'saveitems/get-tab',
        'initialQuery' => [
            "type"=> "all"
        ],
    ];

    $data['screen_setting']['saveditems']['detailSaveditemsCollection']['content']['embedComponents'][] = [
        'component' => \Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting::SMART_TABS,
        'tabs' => $tabsForCollection,
    ];
}