<?php
if (Phpfox::isAppActive('P_SavedItems')) {
    $specialResources = ['feed', 'forum_thread'];
    $specialItemTypes = [
        'forum_thread' => [
            'item_type' => 'forum',
            'item_id' => 'id',
        ]
    ];

    $tempResourceName = $this->getResourceName();
    if(in_array($tempResourceName, $specialResources) || isset($result['feed_param'])) {
        $isFeedResource = $tempResourceName == 'feed';
        if(in_array($tempResourceName, $specialResources)) {
            $tempResult = [
                'item_type' => $isFeedResource ? $result['item_type'] : $specialItemTypes[$tempResourceName]['item_type'],
                'item_id' => $isFeedResource ? $result['item_id'] : $result[$specialItemTypes[$tempResourceName]['item_id']]
            ];
            if(empty($isFeedResource) && empty($result['feed_param'])) {
                $result = array_merge($result, [
                    'like_type_id' => $tempResult['item_type'],
                    'item_id' => $tempResult['item_id'],
                ]);
                $tempResult['is_detail'] = true;
            }
        } else {
            $tempResult = [
                'item_type' => $result['feed_param']['like_type_id'],
                'item_id' => $result['feed_param']['item_id']
            ];
        }

        $valid = true;

        switch ($tempResult['item_type']) {
            case 'forum':
                if(empty($tempResult['is_detail'])) {
                    $itemId = db()->select('thread_id')
                        ->from(Phpfox::getT('forum_thread'))
                        ->where(['start_id' => $tempResult['item_id']])
                        ->execute('getSlaveField');
                    if(empty($itemId)) {
                        $valid = false;
                    } else {
                        $tempResult['item_id'] = $itemId;
                    }
                }

                break;
        }

        if($valid) {
            $result['saved_id'] = (int)Phpfox::getService('saveditems')->isSaved($tempResult['item_type'], $tempResult['item_id'], true);

            $result['is_saved'] = (int)(!!$result['saved_id']);

            $result['extra']['can_save_item'] = false;

            $exceptionalTypes = Phpfox::getService('saveditems')->getExceptionalTypes();

            if (Phpfox::getUserParam('saveditems.can_save_item') && Phpfox::getUserBy('profile_page_id') == 0 && !in_array($tempResult['item_type'], $exceptionalTypes)) {
                $typeCount = explode('_', $tempResult['item_type']);
                $moduleId = !empty($typeCount) ? $typeCount[0] : '';
                if (Phpfox::isModule($moduleId) && (in_array($tempResult['item_type'],
                            ['user_status', 'link']) || Phpfox::hasCallback($moduleId, 'globalUnionSearch'))) {
                    $result['extra']['can_save_item'] = true;
                }
            }
        }
    }
}
