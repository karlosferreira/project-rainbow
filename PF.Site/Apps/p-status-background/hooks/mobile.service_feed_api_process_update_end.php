<?php

if (Phpfox::isAppActive('P_StatusBg') && $result) {
    $bgId = $this->request()->get('status_background_id');
    $ownerUserId = $this->getUser()->getId();
    $statusId = 0;
    $referenceFeed = [];
    if (!empty($values['parent_item_type']) && !empty($values['parent_item_id']) && !empty($callback)) {
        $feed = $this->database()->select('item_id, type_id, user_id')->from(":${callback['table_prefix']}feed")->where(['feed_id' => $id])->execute('getRow');
        $bgItemId = isset($feed['item_id']) ? $feed['item_id'] : 0;
        $bgItemType = isset($feed['type_id']) ? $feed['type_id'] : (isset($callback['feed_id']) ? $callback['feed_id'] : '');
        $ownerUserId = isset($feed['user_id']) ? $feed['user_id'] : $ownerUserId;
    } elseif (!empty($values['parent_user_id'])) {
        $feed = $this->database()->select('item_id, type_id, user_id, time_stamp')->from(':feed')->where(['feed_id' => $id])->execute('getRow');
        $bgItemId = isset($feed['item_id']) ? $feed['item_id'] : 0;
        $bgItemType = isset($feed['type_id']) ? $feed['type_id'] : 'feed_comment';
        $ownerUserId = isset($feed['user_id']) ? $feed['user_id'] : $ownerUserId;
        if (!empty($feed)) {
            $statusId = db()->select('item_id')->from(':feed')->where([
                'user_id' => $ownerUserId,
                'type_id' => 'user_status',
                'time_stamp' => $feed['time_stamp']
            ])->executeField();
        }
    } else {
        $feed = $this->database()->select('item_id, type_id, user_id, time_stamp')->from(':feed')->where(['feed_id' => $id])->execute('getRow');
        $bgItemId = isset($feed['item_id']) ? $feed['item_id'] : 0;
        $bgItemType = isset($feed['type_id']) ? $feed['type_id'] : 'user_status';
        $ownerUserId = isset($feed['user_id']) ? $feed['user_id'] : $ownerUserId;
        //Get feed on tag friend
        if (!empty($feed)) {
            $referenceFeed = db()->select('item_id')->from(':feed')->where([
                'user_id' => $ownerUserId,
                'type_id' => 'feed_comment',
                'time_stamp' => $feed['time_stamp']
            ])->executeRows();
        }
    }
    //Update background
    if ($bgItemId && $bgItemType) {
        $oldBackground = \Phpfox::getService('pstatusbg')->getFeedStatusBackground($bgItemId, $bgItemType, $ownerUserId, true);
        if (!empty($oldBackground)) {
            if ($bgId) {
                $this->database()->update(':pstatusbg_status_background', [
                    'background_id' => $bgId,
                    'time_stamp' => PHPFOX_TIME
                ], 'id =' . (int)$oldBackground['id']);
            } else {
                $this->database()->delete(':pstatusbg_status_background', ['id' => $oldBackground['id']]);
            }
        } else {
            \Phpfox::getService('pstatusbg.process')->addBackgroundForStatus($bgItemType, $bgItemId, $bgId, $ownerUserId, isset($values['parent_item_type']) ? $values['parent_item_type'] : 'user');
        }
    }

    if ($statusId) {
        $oldBackgroundStatus = \Phpfox::getService('pstatusbg')->getFeedStatusBackground($statusId, 'user_status', $ownerUserId, true);
        if (!empty($oldBackgroundStatus)) {
            if ($bgId) {
                $this->database()->update(':pstatusbg_status_background', [
                    'background_id' => $bgId,
                    'time_stamp' => PHPFOX_TIME
                ], 'id =' . (int)$oldBackgroundStatus['id']);
            } else {
                $this->database()->delete(':pstatusbg_status_background', ['id' => $oldBackgroundStatus['id']]);
            }
        } else {
            \Phpfox::getService('pstatusbg.process')->addBackgroundForStatus('user_status', $statusId, $bgId, $ownerUserId, isset($values['parent_item_type']) ? $values['parent_item_type'] : 'user');
        }
    }
    if (!empty($referenceFeed)) {
        foreach ($referenceFeed as $reference) {
            $oldRefer = \Phpfox::getService('pstatusbg')->getFeedStatusBackground($reference['item_id'], 'feed_comment', $ownerUserId, true);
            if (!empty($oldRefer)) {
                if ($bgId) {
                    $this->database()->update(':pstatusbg_status_background', [
                        'background_id' => $bgId,
                        'time_stamp' => PHPFOX_TIME
                    ], 'id =' . (int)$oldRefer['id']);
                } else {
                    $this->database()->delete(':pstatusbg_status_background', ['id' => $oldRefer['id']]);
                }
            } else {
                \Phpfox::getService('pstatusbg.process')->addBackgroundForStatus('feed_comment', $reference['item_id'], $bgId, $ownerUserId, isset($values['parent_item_type']) ? $values['parent_item_type'] : 'user');
            }
        }
    }
}
