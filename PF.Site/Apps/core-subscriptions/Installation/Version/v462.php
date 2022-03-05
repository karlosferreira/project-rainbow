<?php

namespace Apps\Core_Subscriptions\Installation\Version;

use Phpfox;

/**
 * Class v462
 * @package Apps\Core_Subscriptions\Installation\Version
 */
class v462
{
    public function process()
    {
        $this->migrateRenewType();
    }

    private function migrateRenewType()
    {
        $packages = db()->select('package_id, recurring_period')
            ->from(Phpfox::getT('subscribe_package'))
            ->where('recurring_period != 0')
            ->execute('getSlaveRows');

        $packageIds = !empty($packages) ? array_column($packages, 'package_id') : [];
        if (empty($packageIds)) {
            return false;
        }

        $countSubscription = db()->select('COUNT(*)')
            ->from(Phpfox::getT('subscribe_purchase'))
            ->where('package_id IN (' . implode(',', $packageIds) . ') AND renew_type = 0')
            ->execute('getSlaveField');
        if ($countSubscription) {
            $purchaseArray = [];
            $purchaseByPoints = db()->select('log_data')
                ->from(Phpfox::getT('api_gateway_log'))
                ->where('gateway_id = "activitypoints"')
                ->order('log_id DESC')
                ->execute('getSlaveRows');
            $patternArray = [
                "/\'purchase_id\' => \'[0-9]+\'/", "/\'package_id\' => \'[0-9]+\'/", "/\'user_id\' => \'[0-9]+\'/"
            ];

            $checkDuplicate = [];
            foreach ($purchaseByPoints as $purchaseByPoint) {
                $parsedArray = [];
                foreach ($patternArray as $pattern) {
                    if (preg_match($pattern, $purchaseByPoint['log_data'], $match)) {
                        $tempArray = explode('=>', $match[0]);
                        if (empty($tempArray) || (!empty($tempArray) && (!is_array($tempArray) || (is_array($tempArray) && count($tempArray) != 2)))) {
                            continue;
                        }
                        $sKey = trim(str_replace(['\'', '"'], '', $tempArray[0]));
                        $sValue = trim(str_replace(['\'', '"'], '', $tempArray[1]));
                        $parsedArray[$sKey] = (int)$sValue;
                    }
                }
                if (!empty($parsedArray) && is_array($parsedArray) && !empty($parsedArray['purchase_id']) && !empty($parsedArray['package_id']) && !empty($parsedArray['user_id']) && in_array($parsedArray['package_id'], $packageIds)) {
                    $checkArray = [$parsedArray['package_id'] . ',' . $parsedArray['user_id']];
                    if (empty(array_intersect($checkDuplicate, $checkArray))) {
                        $purchaseArray[] = $parsedArray;
                        $checkDuplicate[] = $parsedArray['package_id'] . ',' . $parsedArray['user_id'];
                    }

                }
            }
            if (!empty($packages)) {
                if (!empty($purchaseArray)) {
                    foreach ($packages as $package) {
                        switch ($package['recurring_period']) {
                            case 1:
                                $iDays = 30;
                                break;
                            case 2:
                                $iDays = 90;
                                break;
                            case 3:
                                $iDays = 180;
                                break;
                            case 4:
                                $iDays = 365;
                                break;
                            default:
                                $iDays = 0;
                                break;
                        }
                        if ($iDays == 0) {
                            continue;
                        }
                        foreach ($purchaseArray as $purchase_array) {
                            $completedPurchases = db()->select('purchase_id, currency_id, status, time_stamp')
                                ->from(Phpfox::getT('subscribe_purchase'))
                                ->where('user_id = ' . (int)$purchase_array['user_id'] . ' AND package_id = ' . (int)$purchase_array['package_id'] . ' AND renew_type = 0 AND status IN ("completed","cancel")')
                                ->execute('getSlaveRows');
                            foreach ($completedPurchases as $iKey => $completedPurchase) {
                                $aUpdate = [
                                    'expiry_date' => (int)$completedPurchase['time_stamp'] + $iDays * 24 * 3600,
                                    'renew_type' => 1
                                ];
                                if ((int)$completedPurchase['purchase_id'] == (int)$purchase_array['purchase_id']) {

                                    $aUpdate['payment_method'] = 'activitypoints';
                                    $aHistory = [
                                        'purchase_id' => $completedPurchase['purchase_id'],
                                        'status' => 'completed',
                                        'time_stamp' => $completedPurchase['time_stamp'],
                                        'currency_id' => $completedPurchase['currency_id'],
                                        'payment_method' => 'activitypoints',
                                        'transaction_id' => '',
                                    ];
                                    Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aHistory);
                                    if ($iKey < (count($completedPurchases) - 1)) {
                                        db()->insert(Phpfox::getT('subscribe_cancel_reason'), [
                                            'purchase_id' => $completedPurchase['purchase_id'],
                                            'reason_id' => 1,
                                            'time_stamp' => PHPFOX_TIME
                                        ]);
                                        $aHistory = [
                                            'purchase_id' => $completedPurchase['purchase_id'],
                                            'status' => 'cancel',
                                            'time_stamp' => PHPFOX_TIME,
                                            'currency_id' => $completedPurchase['currency_id'],
                                            'payment_method' => 'activitypoints',
                                            'transaction_id' => '',
                                        ];
                                        Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aHistory);
                                        $aUpdate = array_merge($aUpdate, ['status' => 'cancel']);
                                    }
                                } else {
                                    if (((int)$completedPurchase['purchase_id'] < (int)$purchase_array['purchase_id']) && $completedPurchase['status'] == 'completed') {
                                        $aHistory = [
                                            'purchase_id' => $completedPurchase['purchase_id'],
                                            'status' => 'completed',
                                            'time_stamp' => $completedPurchase['time_stamp'],
                                            'currency_id' => $completedPurchase['currency_id'],
                                            'payment_method' => '',
                                            'transaction_id' => '',
                                        ];
                                        Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aHistory);

                                        db()->insert(Phpfox::getT('subscribe_cancel_reason'), [
                                            'purchase_id' => $completedPurchase['purchase_id'],
                                            'reason_id' => 1,
                                            'time_stamp' => PHPFOX_TIME
                                        ]);
                                        $aHistory = [
                                            'purchase_id' => $completedPurchase['purchase_id'],
                                            'status' => 'cancel',
                                            'time_stamp' => PHPFOX_TIME,
                                            'currency_id' => $completedPurchase['currency_id'],
                                            'payment_method' => '',
                                            'transaction_id' => '',
                                        ];
                                        Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aHistory);
                                        $aUpdate = array_merge($aUpdate, ['status' => 'cancel']);
                                    }
                                }
                                db()->update(Phpfox::getT('subscribe_purchase'), $aUpdate, 'purchase_id = ' . (int)$completedPurchase['purchase_id']);
                            }
                        }
                    }
                }
                db()->update(Phpfox::getT('subscribe_purchase'), ['renew_type' => 1], 'package_id IN (' . implode(',', $packageIds) . ') AND renew_type = 0');
            }
        }
    }
}