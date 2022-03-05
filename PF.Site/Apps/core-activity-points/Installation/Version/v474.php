<?php

namespace Apps\Core_Activity_Points\Installation\Version;

use Phpfox;

class v474
{
    public function process()
    {
        $this->addFieldAndMigrateDataForDeleteItem();
    }

    private function addFieldAndMigrateDataForDeleteItem()
    {
        $aUsers = db()->select('user_id, total_sent')
            ->from(Phpfox::getT('activitypoint_statistics'))
            ->execute('getSlaveRows');
        if (!empty($aUsers)) {
            $aUserDeleteItemsPoints = db()->select('COUNT(*) AS total_delete_item_points, user_id')
                ->from(Phpfox::getT('activitypoint_transaction'))
                ->where('user_id IN (' . implode(',', array_column($aUsers, 'user_id')) . ') AND action = "activitypoint_subtract_points_when_delete_item" AND type = "Sent"')
                ->group('user_id')
                ->execute('getSlaveRows');
            if (!empty($aUserDeleteItemsPoints)) {
                $aParsedUsers = array_combine(array_column($aUsers, 'user_id'), array_column($aUsers, 'total_sent'));
                foreach ($aUserDeleteItemsPoints as $aUserDeleteItemsPoint) {
                    db()->update(Phpfox::getT('activitypoint_statistics'), ['total_sent' => (int)$aParsedUsers[$aUserDeleteItemsPoint['user_id']] - (int)$aUserDeleteItemsPoint['total_delete_item_points'], 'total_retrieved' => (int)$aUserDeleteItemsPoint['total_delete_item_points']], 'user_id = ' . (int)$aUserDeleteItemsPoint['user_id']);
                }
                db()->update(Phpfox::getT('activitypoint_transaction'), ['type' => 'Retrieved'], 'action = "activitypoint_subtract_points_when_delete_item" AND type = "Sent"');
            }
        }
    }
}
