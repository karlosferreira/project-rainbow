<?php

namespace Apps\Core_Subscriptions\Service;

use Mockery\Exception;
use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Callback extends Phpfox_Service
{
    public function cancelSubscription($params)
    {
        if (empty($params['purchase_id'])
            || empty($params['payment_method'])
            || empty($purchase = Phpfox::getService('subscribe.purchase')->getPurchase($params['purchase_id']))
            || empty($purchase['extra_params']['subscription_id'])) {
            return false;
        }

        Phpfox::getService('subscribe.process')->cancel($params['payment_method'], $purchase['extra_params']['subscription_id']);
    }

    /**
     * Define email notifications
     * @return array
     */
    public function getNotificationSettings()
    {
        return [
            'subscribe.subscribe_notifications' => [
                'phrase'  => _p('subscription_notifications'),
                'default' => 1
            ],
        ];
    }

    public function getNotificationChangestatustoactive($aNotification)
    {
        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($aNotification['item_id']))) {
            return false;
        }
        $sLink = \Phpfox_Url::instance()->makeUrl('subscribe.list');
        $sMessage = _p('admin_change_status_to_active_notification', [
            'package_title' => _p($aPurchase['title'])
        ]);
        return [
            'link' => $sLink,
            'message' => $sMessage,
            'icon' => ''
        ];
    }

    public function getNotificationDeletepackage($aNotification)
    {
        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($aNotification['item_id']))) {
            return false;
        }
        $sLink = \Phpfox_Url::instance()->makeUrl('subscribe.list');
        $sMessage = _p('user_notification_delete_package_template', [
            'package_title' => _p($aPurchase['title'])
        ]);
        return [
            'link' => $sLink,
            'message' => $sMessage,
            'icon' => ''
        ];
    }

    public function getNotificationNotifyexpiration($aNotification)
    {
        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($aNotification['item_id']))) {
            return false;
        }
        $sLink = \Phpfox_Url::instance()->makeUrl('subscribe.list');
        $sMessage = _p('expiration_user_notification_template', [
            'package_title' => _p($aPurchase['title'])
        ]);
        return [
            'link' => $sLink,
            'message' => $sMessage,
            'icon' => ''
        ];
    }

    public function paymentApiCallback($aParams)
    {
        Phpfox::log('Module callback recieved: ' . var_export($aParams, true));
        Phpfox::log('Attempting to retrieve purchase from the database');
        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getPurchase($aParams['item_number'], true))) {
            Phpfox::log('Purchase is not valid');
            return false;
        }

        Phpfox::log('Purchase is valid: ' . var_export($aPurchase, true));
        if ($aParams['status'] == 'completed') {
            if ($aParams['total_paid'] == $aPurchase['price'] || $aParams['total_paid'] == number_format($aPurchase['default_recurring_cost'], 2)) {
                Phpfox::log('Paid correct price');
            } else {
                Phpfox::log('Paid incorrect price');
                return false;
            }
        } else if ($aParams['status'] == 'cancel') {
            Phpfox::log('Cancel subscription.');
        } else {
            Phpfox::log('Payment is not marked as "completed".');
            return false;
        }

        Phpfox::log('Handling purchase');

        Phpfox::getService('subscribe.purchase.process')->update($aPurchase['purchase_id'], $aPurchase['package_id'], $aParams['status'], $aPurchase['user_id'], $aPurchase['user_group_id'], false);
        db()->update(Phpfox::getT('subscribe_purchase'), ['payment_method' => $aParams['gateway'], 'transaction_id' => (!empty($aParams['ref']) ? $aParams['ref'] : Phpfox::getService('subscribe.helper')->generateTransactionId())], 'purchase_id = ' . $aPurchase['purchase_id']);

        if (!empty($aPurchase['expiry_date']) && $aPurchase['status'] == "completed" && (int)$aPurchase['recurring_period'] > 0) {
            db()->update(Phpfox::getT('subscribe_purchase'), ['expiry_date' => $aPurchase['new_expiry_date']], 'purchase_id = ' . $aPurchase['purchase_id']);
        }

        if (empty($aPurchase['expiry_date']) && $aParams['status'] == "completed" && (int)$aPurchase['recurring_period'] > 0) {
            $iExpiriTime = 0;
            switch ($aPurchase['recurring_period']) {
                case 0:
                    $iExpiriTime = 0;
                    break;
                case 1:
                    $iExpiriTime = strtotime("+1 month", PHPFOX_TIME);
                    break;
                case 2:
                    $iExpiriTime = strtotime("+3 month", PHPFOX_TIME);
                    break;
                case 3:
                    $iExpiriTime = strtotime("+6 month", PHPFOX_TIME);
                    break;
                case 4:
                    $iExpiriTime = strtotime("+1 year", PHPFOX_TIME);
                    break;
                default:
                    break;
            }

            db()->update(Phpfox::getT('subscribe_purchase'), ['expiry_date' => $iExpiriTime, 'time_stamp' => PHPFOX_TIME], 'purchase_id = ' . $aPurchase['purchase_id']);
        }

        if (empty($aPurchase['expiry_date']) && $aParams['status'] == "completed" && (int)$aPurchase['recurring_period'] == 0) {
            db()->update(Phpfox::getT('subscribe_purchase'), ['expiry_date' => 0, 'time_stamp' => PHPFOX_TIME], 'purchase_id = ' . $aPurchase['purchase_id']);
        }
        $sTransactionId = !empty($aParams['ref']) ? $aParams['ref'] : Phpfox::getService('subscribe.helper')->generateTransactionId();
        $aRecentPurchase = [
            'purchase_id' => $aPurchase['purchase_id'],
            'status' => $aParams['status'],
            'time_stamp' => PHPFOX_TIME,
            'currency_id' => $aPurchase['currency_id'],
            'payment_method' => $aParams['gateway'],
            'transaction_id' => $sTransactionId,
            'total_paid' => $aParams['total_paid']
        ];
        Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aRecentPurchase);

        $updateParams = [
            'transaction_id' => $sTransactionId,
        ];
        if (!empty($aParams['extra_params'])) {
            $updateParams['extra_params'] = serialize($aParams['extra_params']);
        }

        db()->update(Phpfox::getT('subscribe_purchase'), $updateParams, 'purchase_id = ' . $aPurchase['purchase_id']);

        Phpfox::log('Handling complete');
        return null;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     * @return null
     */

    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('subscribe.service_callback__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}