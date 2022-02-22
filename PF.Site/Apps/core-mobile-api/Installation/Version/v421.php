<?php

namespace Apps\Core_MobileApi\Installation\Version;

use Phpfox;

class v421
{
    public function process()
    {
        if (db()->tableExists(Phpfox::getT('mobile_api_notification_queue'))) {
            $allQueues = db()->select('*')->from(Phpfox::getT('mobile_api_notification_queue'))->execute('getSlaveRows');
            if (count($allQueues)) {
                foreach ($allQueues as $queue) {
                    //Move to job
                    \Phpfox_Queue::instance()->addJob('mobile_push_notification', $queue);
                }
            }
            //Remove cron
            db()->delete(':cron', [
                'module_id' => 'mobile',
                'php_code'  => 'Phpfox::getService(\'mobile.device\')->executePushNotification();'
            ]);
            //Drop table
            db()->dropTable(Phpfox::getT('mobile_api_notification_queue'));
        }
    }
}