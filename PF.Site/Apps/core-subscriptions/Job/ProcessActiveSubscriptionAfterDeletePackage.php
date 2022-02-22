<?php

namespace Apps\Core_Subscriptions\Job;

use Core\Queue\JobAbstract;
use Phpfox;

/**
 * Class Convert
 *
 * @package Apps\PHPfox_Videos\Job
 */
class ProcessActiveSubscriptionAfterDeletePackage extends JobAbstract
{
    /**
     * @throws \Exception
     */
    public function perform()
    {
        $params = $this->getParams();

        if (empty($params['purchase_id'])) {
            return $this->delete();
        }

        Phpfox::getLib('mail')
            ->to($params['user_id'])
            ->subject(['subject_delete_package_template',[
                'package_title' => _p($params['title'], [], $params['language_id'])
            ]])
            ->message(['delete_package_template',[
                'username' => $params['full_name'],
                'package_title' => _p($params['title'], [], $params['language_id']),
                'expiry_date' => Phpfox::getLib('date')->convertTime($params['expiry_date']),
                'link' => \Phpfox_Url::instance()->makeUrl('subscribe')
            ]])
            ->notification('subscribe.subscribe_notifications')
            ->send();

        Phpfox::getService('notification.process')->add('subscribe_deletepackage', $params['purchase_id'], $params['user_id'], $params['sender_id']);

        if (!empty($params['subscription_id']) && !empty($params['gateway'])) {
            Phpfox::getService('subscribe.process')->cancel($params['gateway'], $params['subscription_id']);
        }

        $this->delete();
    }
}
