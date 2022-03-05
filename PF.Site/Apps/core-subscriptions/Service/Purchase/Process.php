<?php

namespace Apps\Core_Subscriptions\Service\Purchase;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('subscribe_purchase');
    }

    /**
     * Change purchase id for user when signing up but not paid yet
     * @param null $purchaseId
     * @param null $userId
     * @return bool
     */
    public function changePurchaseForSigningUp($purchaseId = null, $userId = null)
    {
        if (empty($purchaseId)) {
            return false;
        }
        if (empty($userId)) {
            $userId = Phpfox::getUserId();
        }
        $subscribeId = db()->select('subscribe_id')
            ->from(Phpfox::getT('user_field'))
            ->where('user_id = ' . (int)$userId)
            ->execute('getSlaveField');
        if ((int)$subscribeId > 0) {
            db()->update(Phpfox::getT('user_field'), ['subscribe_id' => $purchaseId], 'user_id = ' . (int)$userId);
        }
    }

    public function updatePurchaseForFirstTimeForFreeAndRecurring($iPurchaseId)
    {
        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getPurchase($iPurchaseId, true))) {
            return false;
        }
        $aPackage = Phpfox::getService('subscribe')->getPackage($aPurchase['package_id']);
        if ($aPackage['default_cost'] != '0.00' || (int)$aPackage['is_recurring'] == 0 || (int)$aPackage['recurring_period'] == 0 || (int)$aPurchase['expiry_date'] > 0) {
            return false;
        }

        $sTransactionId = Phpfox::getService('subscribe.helper')->generateTransactionId();

        $aRecentPurchase = [
            'purchase_id' => $iPurchaseId,
            'status' => 'completed',
            'time_stamp' => PHPFOX_TIME,
            'currency_id' => $aPurchase['currency_id'],
            'payment_method' => '',
            'transaction_id' => $sTransactionId,
            'total_paid' => $aPackage['default_cost']
        ];
        Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aRecentPurchase);

        $iExpireTime = 0;

        switch ($aPurchase['recurring_period']) {
            case 1:
                $iExpireTime = strtotime("+1 month", PHPFOX_TIME);
                break;
            case 2:
                $iExpireTime = strtotime("+3 month", PHPFOX_TIME);
                break;
            case 3:
                $iExpireTime = strtotime("+6 month", PHPFOX_TIME);
                break;
            case 4:
                $iExpireTime = strtotime("+1 year", PHPFOX_TIME);
                break;
            default:
                break;
        }


        db()->update(Phpfox::getT('subscribe_purchase'), ['transaction_id' => $sTransactionId, 'expiry_date' => $iExpireTime], 'purchase_id = ' . $iPurchaseId);

        return true;
    }

    public function updateRenewType($iPurchaseId, $iRenewType = 0)
    {
        db()->update($this->_sTable, ['renew_type' => $iRenewType], 'purchase_id = ' . $iPurchaseId);
    }

    public function updateOrder($aVals)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        if (!isset($aVals['ordering'])) {
            return Phpfox_Error::set(_p('not_a_valid_request'));
        }

        foreach ($aVals['ordering'] as $iId => $iOrder) {
            $this->database()->update(Phpfox::getT('subscribe_reason'), ['ordering' => (int)$iOrder], 'reason_id = ' . (int)$iId);
        }
        return null;
    }

    public function getMoreInfoForAdmin($aPurchase)
    {
        $aRow = db()->select('u.*, spack.title')
            ->from(Phpfox::getT('subscribe_purchase'), 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = sp.user_id')
            ->where('sp.purchase_id = ' . $aPurchase['purchase_id'])
            ->execute('getSlaveRow');
        $aPurchase = array_merge($aRow, $aPurchase);
        $aPurchase['title_parsed'] = _p($aPurchase['title']);
        switch ($aPurchase['recurring_period']) {
            case 1:
                {
                    $aPurchase['type'] = '1 ' . _p('Month');
                    break;
                }
            case 2:
                {
                    $aPurchase['type'] = '3 ' . _p('Months');
                    break;
                }
            case 3:
                {
                    $aPurchase['type'] = '6 ' . _p('Months');
                    break;
                }
            case 4:
                {
                    $aPurchase['type'] = '1 ' . _p('Year');
                    break;
                }
            default:
                {
                    break;
                }
        }
        return $aPurchase;
    }

    public function getRecentPayments($iPurchaseId, $iPage = 1, $iSize = 20, $bCount = false)
    {
        if ($bCount) {
            $iCnt = db()->select('COUNT(recent_id)')
                ->from(Phpfox::getT('subscribe_recent_payment'))
                ->where('purchase_id = ' . $iPurchaseId)
                ->execute('getSlaveField');
        }
        $aRows = db()->select('srp.time_stamp, srp.currency_id, srp.payment_method, srp.transaction_id, srp.status, srp.total_paid')
            ->from(Phpfox::getT('subscribe_recent_payment'), 'srp')
            ->where('srp.purchase_id = ' . $iPurchaseId)
            ->order('srp.time_stamp DESC')
            ->limit($iPage, $iSize)
            ->execute('getSlaveRows');
        return ($bCount ? [$aRows, $iCnt] : $aRows);
    }

    public function addRecentPurchase($aVals)
    {
        db()->insert(Phpfox::getT('subscribe_recent_payment'), $aVals);
    }

    public function add($aVals, $iUserId = null)
    {
        if ($iUserId === null) {
            Phpfox::isUser(true);
            $iUserId = Phpfox::getUserId();
        }

        $aForms = [
            'package_id' => [
                'message' => _p('package_is_required'),
                'type' => 'int:required'
            ],
            'currency_id' => [
                'message' => _p('currency_is_required'),
                'type' => ['string:required', 'regex:currency_id']
            ],
            'expiry_date' => [
                'type' => 'int'
            ]
        ];
        if (!isset($aVals['price']) || (isset($aVals['price']) && ($aVals['price'] != '0.00'))) {
            $aForms['price'] = [
                'message' => !isset($aVals['price']) ? _p('price_is_required') : _p('subscribe_price_must_be_numeric_value'),
                'type' => 'price:required'
            ];
        }

        // get renew type
        $iRenewType = $aVals['renew_type'];

        // validation date before insert to db
        $aVals = $this->validator()->process($aForms, $aVals);

        if (!Phpfox_Error::isPassed()) {
            return false;
        }

        $aExtra = [
            'user_id' => ($iUserId === null ? Phpfox::getUserId() : $iUserId),
            'time_stamp' => PHPFOX_TIME,
            'renew_type' => $iRenewType,
            'expiry_date' => $aVals['expiry_date']
        ];

        return $this->database()->insert($this->_sTable, array_merge($aExtra, $aVals));
    }

    public function update($iPurchaseId, $iPackageId, $sStatus, $iUserId, $iUserGroupId)
    {
        $sLink = Phpfox_Url::instance()->makeUrl('subscribe.view', ['id' => $iPurchaseId]);
        switch ($sStatus) {
            case 'completed':
                Phpfox::getService('user.process')->updateUserGroup($iUserId, $iUserGroupId);
                $sSubject = ['subscribe.membership_successfully_updated_site_title', ['site_title' => Phpfox::getParam('core.site_title')]];
                $sMessage = ['subscribe.your_membership_on_site_title_has_successfully_been_updated', [
                    'site_title' => Phpfox::getParam('core.site_title'),
                    'link' => $sLink
                ]
                ];
                $this->database()->update(Phpfox::getT('user_field'), ['subscribe_id' => '0'], 'user_id = ' . (int)$iUserId);

                $aCurrentActiveSubscriptions = db()->select('sp.*')
                    ->from($this->_sTable, 'sp')
                    ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
                    ->where('sp.user_id = ' . $iUserId . ' AND sp.status = "completed" AND purchase_id != ' . $iPurchaseId)
                    ->execute('getSlaveRows');
                $iDefaultReasonId = db()->select('reason_id')
                    ->from(Phpfox::getT('subscribe_reason'))
                    ->where('is_default = 1 AND is_active = 1')
                    ->execute('getSlaveField');
                foreach ($aCurrentActiveSubscriptions as $aCurrentActiveSubscription) {
                    db()->insert(Phpfox::getT('subscribe_cancel_reason'), [
                        'purchase_id' => $aCurrentActiveSubscription['purchase_id'],
                        'reason_id' => $iDefaultReasonId,
                        'time_stamp' => PHPFOX_TIME
                    ]);
                    $aHistory = [
                        'purchase_id' => $aCurrentActiveSubscription['purchase_id'],
                        'status' => 'cancel',
                        'time_stamp' => PHPFOX_TIME,
                        'currency_id' => $aCurrentActiveSubscription['currency_id'],
                        'payment_method' => $aCurrentActiveSubscription['payment_method'],
                        'transaction_id' => $aCurrentActiveSubscription['transaction_id'],
                    ];
                    Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aHistory);
                }
                $this->database()->update($this->_sTable, ['status' => 'cancel', 'time_stamp' => PHPFOX_TIME], 'user_id = ' . $iUserId . ' AND status = "completed" AND purchase_id != ' . $iPurchaseId);

                $aPurchase = Phpfox::getService('subscribe.purchase')->getPurchase($iPurchaseId);
                $aPackage = Phpfox::getService('subscribe')->getPackage($aPurchase['package_id']);
                if ($aPackage['default_cost'] == '0.00' && (int)$aPackage['is_recurring'] == 0) {
                    $sTransactionId = Phpfox::getService('subscribe.helper')->generateTransactionId();
                    $aRecentPurchase = [
                        'purchase_id' => $iPurchaseId,
                        'status' => 'completed',
                        'time_stamp' => PHPFOX_TIME,
                        'currency_id' => $aPurchase['currency_id'],
                        'payment_method' => '',
                        'transaction_id' => $sTransactionId,
                        'total_paid' => $aPackage['default_cost']
                    ];
                    Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aRecentPurchase);

                    db()->update(Phpfox::getT('subscribe_purchase'), ['transaction_id' => $sTransactionId, 'expiry_date' => 0], 'purchase_id = ' . $iPurchaseId);

                }
                break;
            case 'pending':
                $sSubject = ['subscribe.membership_pending_site_title', ['site_title' => Phpfox::getParam('core.site_title')]];
                $sMessage = ['subscribe.your_membership_subscription_on_site_title_is_currently_pending', [
                    'site_title' => Phpfox::getParam('core.site_title'),
                    'link' => $sLink
                ]
                ];
                $this->database()->update(Phpfox::getT('user_field'), ['subscribe_id' => $iPurchaseId], 'user_id = ' . (int)$iUserId);
                break;
            case 'cancel':
                // Store in the log that this user cancelled the subscription.
                $this->database()->insert(Phpfox::getT('api_gateway_log'), [
                    'log_data' => 'cancelled_subscription user_' . (int)($iUserId) . ' purchaseid_' . (int)$iPurchaseId . ' packageid_' . (int)$iPackageId,
                    'time_stamp' => PHPFOX_TIME
                ]);
                break;
        }

        if ($sPlugin = Phpfox_Plugin::get('subscribe.service_purchase_process_update_pre_log')) {
            eval($sPlugin);
        }
        $this->database()->update($this->_sTable, ['status' => $sStatus], 'purchase_id = ' . (int)$iPurchaseId);

        db()->updateCount('subscribe_purchase', 'package_id = ' . $iPackageId . ' AND status = "completed"', 'total_active', 'subscribe_package', 'package_id = ' . $iPackageId);

        $mailObject = Phpfox::getLib('mail');
        if (Phpfox::isUser() && $iUserId == Phpfox::getUserId()) {
            $mailObject->sendToSelf(true);
        }
        $mailObject->to($iUserId)
            ->subject(isset($sSubject) ? $sSubject : '')
            ->message(isset($sMessage) ? $sMessage : '')
            ->notification('subscribe.subscribe_notifications')
            ->send();

    }

    /* This function is called from a cron job.
    *	It searches the database for users who cancelled their subscription before their time was up
    *	and moves them to the correct user group.
    *	It is called once a day and gets the soonest subscription time
    */
    public function downgradeExpiredSubscribers()
    {
        //check expiration for subscription by manual payment method
        $this->checkPurchaseExpirationByManualMethod();
        //send email to notify about subscription expiration base on package
        $this->notifyUserWithManualMethodPayment();
        //Purchase more points if recurring is available
        $this->recurringWithPoint();
        // 1. The shortest term is 1 month
        $iOneMonthAgo = PHPFOX_TIME - (60 * 60 * 24 * 30);

        // 3. Find records in api_gateway_log for people that have cancelled their subscription.
        $aExpiredRecords = $this->database()->select('*')
            ->from(Phpfox::getT('api_gateway_log'))
            ->where('log_data LIKE "cancelled_subscription%" AND time_stamp < ' . $iOneMonthAgo)
            ->execute('getSlaveRows');

        // 4. Find their subscription.
        $aSubscriptionsRows = $this->database()->select('*')
            ->from(Phpfox::getT('subscribe_package'))
            ->execute('getSlaveRows');

        $iCount = 0;
        foreach ($aExpiredRecords as $aExpired) {
            // parse the log
            if (preg_match('/user_(?P<user_id>[0-9]+) purchaseid_(?P<purchase_id>[0-9]+) packageid_(?P<package_id>[0-9]+)/', $aExpired['log_data'], $aRecord)) {
                // find when should this subscription expire
                $iThisExpires = Phpfox::getService('subscribe.purchase')->getExpireTime($aRecord['purchase_id']);

                if ($iThisExpires > PHPFOX_TIME) {
                    continue;
                }
                // find the fail user group
                foreach ($aSubscriptionsRows as $aSubs) {
                    if ($aSubs['package_id'] == $aRecord['package_id']) {
                        // Move user to the on fail user group
                        Phpfox::getService('user.process')->updateUserGroup($aRecord['user_id'], $aSubs['fail_user_group']);

                        // Update this record so we dont process it again
                        $this->database()->update(Phpfox::getT('api_gateway_log'), ['log_data' => 'processed ' . $aExpired['log_data']], 'log_id = ' . $aExpired['log_id']);
                        $this->database()->update(Phpfox::getT('user_field'), ['subscribe_id' => $aRecord['purchase_id']], 'user_id = ' . (int)$aRecord['user_id']);
                        $iCount++;
                    }
                }
            }
        }

        return $iCount;
    }

    public function checkPurchaseExpirationByManualMethod()
    {
        $aSubscriptionPackages = db()->select('*')
            ->from(Phpfox::getT('subscribe_package'))
            ->where('recurring_period > 0')
            ->executeRows();

        if (count($aSubscriptionPackages)) {
            foreach ($aSubscriptionPackages as $aSubscriptionPackage) {

                $iExpiredTime = PHPFOX_TIME - (3 * 24 * 3600);
                //Get user_id is expired
                $aListsExpired = db()->select('user_id, status, purchase_id')
                    ->from(':subscribe_purchase')
                    ->where('package_id=' . (int)$aSubscriptionPackage['package_id'] . ' AND (status="completed" OR status="cancel") AND renew_type = 2')
                    ->group('user_id')
                    ->having('max(expiry_date) < ' . $iExpiredTime)
                    ->executeRows();
                foreach ($aListsExpired as $aListExpired) {
                    //If latest payment is not success
                    if ($aListExpired['status'] == 'cancel') {
                        continue;
                    }

                    Phpfox::getService('user.process')->updateUserGroup($aListExpired['user_id'],
                        $aSubscriptionPackage['fail_user_group']);
                    db()->update(Phpfox::getT('subscribe_purchase'), ['status' => 'expire'],
                        'purchase_id=' . $aListExpired['purchase_id']);
                }
            }
        }
    }

    public function notifyUserWithManualMethodPayment()
    {
        $aSubscriptionPackages = db()->select('*')
            ->from(Phpfox::getT('subscribe_package'))
            ->where('recurring_period > 0 AND is_removed = 0')
            ->executeRows();
        if (count($aSubscriptionPackages)) {
            $sDefaultLanguage = Phpfox::getService('language')->getDefaultLanguage();
            foreach ($aSubscriptionPackages as $aSubscriptionPackage) {
                if ((int)$aSubscriptionPackage['number_day_notify_before_expiration'] == 0) {
                    continue;
                }

                $iNotifyDays = $aSubscriptionPackage['number_day_notify_before_expiration'];
                $iNotifyBeforeDate = PHPFOX_TIME + $iNotifyDays * 24 * 3600; // notify before expired date x days

                //Get user_id is expired
                $aListsExpired = db()->select('sp.user_id, sp.status, sp.purchase_id AS purchase_id, sp.expiry_date, u.full_name, u.language_id')
                    ->from(Phpfox::getT('subscribe_purchase'), 'sp')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = sp.user_id')
                    ->where('sp.package_id =' . (int)$aSubscriptionPackage['package_id'] . ' AND (sp.status="completed") AND sp.renew_type = 2 AND sp.expiry_date > 0 AND sp.expiry_date < ' . $iNotifyBeforeDate)
                    ->group('sp.user_id')
                    ->executeRows();

                foreach ($aListsExpired as $aListExpired) {
                    Phpfox::getService('notification.process')->add('subscribe_notifyexpiration', $aListExpired['purchase_id'], $aListExpired['user_id']);
                    PhpFox::getLib('mail')->to($aListExpired['user_id'])
                        ->subject(['subject_notify_expiration_template', [
                            'package_title' => _p($aSubscriptionPackage['title'], [], !empty($aListExpired['language_id']) ? $aListExpired['language_id'] : $sDefaultLanguage)
                        ]])
                        ->message(['notify_expiration_template', [
                            'username' => $aListExpired['full_name'],
                            'package_title' => _p($aSubscriptionPackage['title'], [], !empty($aListExpired['language_id']) ? $aListExpired['language_id'] : $sDefaultLanguage),
                            'expiry_date' => Phpfox::getLib('date')->convertTime($aListExpired['expiry_date']),
                            'link' => Phpfox_Url::instance()->makeUrl('subscribe.list')
                        ]])
                        ->notification('subscribe.subscribe_notifications')
                        ->send();
                }
            }
        }
    }

    public function recurringWithPoint()
    {
        $aSubscriptionPackages = Phpfox::getLib('database')->select('*')
            ->from(':subscribe_package')
            ->where('recurring_period > 0')
            ->executeRows();

        if (count($aSubscriptionPackages)) {
            foreach ($aSubscriptionPackages as $aSubscriptionPackage) {
                $iExpiredTime = PHPFOX_TIME;
                //Get user_id is expired
                $aListsExpired = Phpfox::getLib('database')->select('user_id, currency_id, status, purchase_id, expiry_date')
                    ->from(':subscribe_purchase')
                    ->where('package_id=' . (int)$aSubscriptionPackage['package_id'] . ' AND status="completed" AND renew_type = 1 AND payment_method = "activitypoints"')
                    ->group('user_id')
                    ->having('max(expiry_date) < ' . $iExpiredTime)
                    ->executeRows();
                foreach ($aListsExpired as $aListExpired) {
                    $aCost = unserialize($aSubscriptionPackage['recurring_cost']);
                    $bStatus = false;
                    if (Phpfox::isAppActive('Core_Activity_Points')) {
                        $bStatus = Phpfox::getService('activitypoint.process')->purchaseWithPoints('subscribe', $aListExpired['purchase_id'],
                            $aCost[$aListExpired['currency_id']], $aListExpired['currency_id'], $aListExpired['user_id']);
                    }
                    //Does not enough point
                    if ($bStatus == false) {
                        PhpFox::getLib('mail')->to($aListExpired['user_id'])
                            ->subject('your_subscription_is_canceled')
                            ->message('subscription_auto_cancel_message')
                            ->notification('subscribe.subscribe_notifications')
                            ->send();
                        Phpfox::getService('user.process')->updateUserGroup($aListExpired['user_id'],
                            $aSubscriptionPackage['fail_user_group']);
                        Phpfox::getLib('database')->update(':subscribe_purchase', ['status' => 'cancel'],
                            'purchase_id=' . (int)$aListExpired['purchase_id']);
                    }
                }
            }
        }
    }

    public function updatePurchase($iPurchaseId, $sFromStatus, $sToStatus)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        $aStatus = [
            'completed',
            'cancel',
            'pending'
        ];

        $aPurchase = $this->database()->select('sp.*, spack.*, u.full_name, u.language_id')
            ->from($this->_sTable, 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = sp.user_id')
            ->where('sp.purchase_id = ' . (int)$iPurchaseId)
            ->execute('getSlaveRow');
        $sDefaultLanguage = Phpfox::getService('language')->getDefaultLanguage();
        if (!isset($aPurchase['purchase_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_purchase_you_are_editing'));
        }
        if ((int)$aPurchase['is_removed'] == 1) {
            return Phpfox_Error::set(_p('Package is no longer available'));
        }

        if (empty($sFromStatus) || empty($sToStatus)) {
            return Phpfox_Error::set(_p('not_a_valid_purchase_status'));
        }
        if (!in_array($sFromStatus, $aStatus) || !in_array($sToStatus, $aStatus)) {
            return Phpfox_Error::set(_p('not_a_valid_purchase_status'));
        }

        if (!empty($aPurchase['extra_params'])) {
            $aPurchase['extra_params'] = unserialize($aPurchase['extra_params']);
        }

        switch ($sFromStatus) {
            case 'completed':
                if ($sToStatus == 'cancel') {
                    Phpfox::getService('user.process')->updateUserGroup($aPurchase['user_id'], $aPurchase['fail_user_group']);
                    $iDefaultReasonId = db()->select('reason_id')
                        ->from(Phpfox::getT('subscribe_reason'))
                        ->where('is_default = 1 AND is_active = 1')
                        ->execute('getSlaveField');
                    db()->insert(Phpfox::getT('subscribe_cancel_reason'), [
                        'purchase_id' => $iPurchaseId,
                        'reason_id' => $iDefaultReasonId,
                        'time_stamp' => PHPFOX_TIME
                    ]);

                    $aHistory = [
                        'purchase_id' => $iPurchaseId,
                        'status' => $sToStatus,
                        'time_stamp' => PHPFOX_TIME,
                        'currency_id' => $aPurchase['currency_id'],
                        'payment_method' => $aPurchase['payment_method'],
                        'transaction_id' => $aPurchase['transaction_id'],
                    ];
                    Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aHistory);

                    Phpfox::getLib('mail')
                        ->to($aPurchase['user_id'])
                        ->subject(['admin_change_status_to_cancel_subject', [
                            'package_title' => _p($aPurchase['title'], [], !empty($aPurchase['language_id']) ? $aPurchase['language_id'] : $sDefaultLanguage)
                        ]])
                        ->message(['admin_change_status_to_cancel_template', [
                            'username' => $aPurchase['full_name'],
                            'package_title' => _p($aPurchase['title'], [], !empty($aPurchase['language_id']) ? $aPurchase['language_id'] : $sDefaultLanguage),
                            'link' => '<a href="' . Phpfox_Url::instance()->makeUrl('subscribe.list') . '" target="_blank">' . _p('subscribe_go_to_subscription_detail') . '</a>'
                        ]])
                        ->notification('subscribe.subscribe_notifications')
                        ->send();

                    $aMessage = [
                        'message' => _p('message_change_status_to_cancel', [
                            'package_title' => _p($aPurchase['title'], [], !empty($aPurchase['language_id']) ? $aPurchase['language_id'] : $sDefaultLanguage),
                            'link' => Phpfox_Url::instance()->makeUrl('subscribe.list')
                        ]),
                        'to' => [$aPurchase['user_id']]
                    ];
                    if (Phpfox::isAppActive('Core_Messages')) {
                        Phpfox::getService('mail.process')->add($aMessage);
                    }

                    if (!empty($aPurchase['payment_method'])
                        && !empty($aPurchase['extra_params']['subscription_id'])) {
                        Phpfox::getService('subscribe.process')->cancel($aPurchase['payment_method'], $aPurchase['extra_params']['subscription_id']);
                    }
                }
                break;
            case 'pending':
                if ($sToStatus == 'completed') {
                    Phpfox::getService('user.process')->updateUserGroup($aPurchase['user_id'], $aPurchase['user_group_id']);
                    $this->database()->update(Phpfox::getT('user_field'), ['subscribe_id' => '0'], 'user_id = ' . (int)$aPurchase['user_id']);
                    $aCurrentActiveSubscriptions = db()->select('sp.*')
                        ->from($this->_sTable, 'sp')
                        ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id AND spack.is_removed = 0')
                        ->where('sp.user_id = ' . $aPurchase['user_id'] . ' AND sp.status = "completed" AND sp.purchase_id != ' . $iPurchaseId)
                        ->execute('getSlaveRows');
                    $iDefaultReasonId = db()->select('reason_id')
                        ->from(Phpfox::getT('subscribe_reason'))
                        ->where('is_default = 1 AND is_active = 1')
                        ->execute('getSlaveField');
                    foreach ($aCurrentActiveSubscriptions as $aCurrentActiveSubscription) {
                        db()->insert(Phpfox::getT('subscribe_cancel_reason'), [
                            'purchase_id' => $aCurrentActiveSubscription['purchase_id'],
                            'reason_id' => $iDefaultReasonId,
                            'time_stamp' => PHPFOX_TIME
                        ]);
                    }
                    $this->database()->update($this->_sTable, ['status' => 'cancel', 'time_stamp' => PHPFOX_TIME], 'user_id = ' . $aPurchase['user_id'] . ' AND status = "completed" AND purchase_id != ' . $iPurchaseId);

                    $iCurrentTime = PHPFOX_TIME;
                    switch ($aPurchase['recurring_period']) {
                        case 0:
                            $iNewExpiriTime = 0;
                            break;
                        case 1:
                            $iNewExpiriTime = strtotime("+1 month", $iCurrentTime);
                            break;
                        case 2:
                            $iNewExpiriTime = strtotime("+3 month", $iCurrentTime);
                            break;
                        case 3:
                            $iNewExpiriTime = strtotime("+6 month", $iCurrentTime);
                            break;
                        case 4:
                            $iNewExpiriTime = strtotime("+1 year", $iCurrentTime);
                            break;
                        default:
                            break;
                    }
                    db()->update($this->_sTable, ['expiry_date' => $iNewExpiriTime], 'purchase_id = ' . $iPurchaseId);
                    $aHistory = [
                        'purchase_id' => $iPurchaseId,
                        'status' => $sToStatus,
                        'time_stamp' => PHPFOX_TIME,
                        'currency_id' => $aPurchase['currency_id'],
                        'payment_method' => $aPurchase['payment_method'],
                        'transaction_id' => $aPurchase['transaction_id'],
                    ];
                    Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aHistory);

                    Phpfox::getLib('mail')
                        ->to($aPurchase['user_id'])
                        ->subject(['admin_change_status_to_active_subject', [
                            'package_title' => _p($aPurchase['title'], [], !empty($aPurchase['language_id']) ? $aPurchase['language_id'] : $sDefaultLanguage)
                        ]])
                        ->message(['admin_change_status_to_active_template', [
                            'username' => $aPurchase['full_name'],
                            'package_title' => _p($aPurchase['title'], [], !empty($aPurchase['language_id']) ? $aPurchase['language_id'] : $sDefaultLanguage),
                            'link' => '<a href="' . Phpfox_Url::instance()->makeUrl('subscribe.list') . '" target="_blank">' . _p('subscribe_go_to_subscription_detail') . '</a>'
                        ]])
                        ->notification('subscribe.subscribe_notifications')
                        ->send();
                    Phpfox::getService('notification.process')->add('subscribe_changestatustoactive', $iPurchaseId, $aPurchase['user_id']);
                }
                break;
            case 'cancel':
                if ($sToStatus == 'completed') {
                    Phpfox::getService('user.process')->updateUserGroup($aPurchase['user_id'], $aPurchase['user_group_id']);
                    $this->database()->update(Phpfox::getT('user_field'), ['subscribe_id' => '0'], 'user_id = ' . (int)$aPurchase['user_id']);
                    $aCurrentActiveSubscriptions = db()->select('sp.*')
                        ->from($this->_sTable, 'sp')
                        ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id AND spack.is_removed = 0')
                        ->where('sp.user_id = ' . $aPurchase['user_id'] . ' AND sp.status = "completed" AND sp.purchase_id != ' . $iPurchaseId)
                        ->execute('getSlaveRows');
                    $iDefaultReasonId = db()->select('reason_id')
                        ->from(Phpfox::getT('subscribe_reason'))
                        ->where('is_default = 1 AND is_active = 1')
                        ->execute('getSlaveField');
                    foreach ($aCurrentActiveSubscriptions as $aCurrentActiveSubscription) {
                        db()->insert(Phpfox::getT('subscribe_cancel_reason'), [
                            'purchase_id' => $aCurrentActiveSubscription['purchase_id'],
                            'reason_id' => $iDefaultReasonId,
                            'time_stamp' => PHPFOX_TIME
                        ]);
                    }
                    $this->database()->update($this->_sTable, ['status' => 'cancel', 'time_stamp' => PHPFOX_TIME], 'user_id = ' . $aPurchase['user_id'] . ' AND status = "completed" AND purchase_id != ' . $iPurchaseId);

                    $iCurrentTime = PHPFOX_TIME;
                    switch ($aPurchase['recurring_period']) {
                        case 0:
                            $iNewExpiriTime = 0;
                            break;
                        case 1:
                            $iNewExpiriTime = strtotime("+1 month", $iCurrentTime);
                            break;
                        case 2:
                            $iNewExpiriTime = strtotime("+3 month", $iCurrentTime);
                            break;
                        case 3:
                            $iNewExpiriTime = strtotime("+6 month", $iCurrentTime);
                            break;
                        case 4:
                            $iNewExpiriTime = strtotime("+1 year", $iCurrentTime);
                            break;
                        default:
                            break;
                    }
                    db()->update($this->_sTable, ['expiry_date' => $iNewExpiriTime], 'purchase_id = ' . $iPurchaseId);
                    $aHistory = [
                        'purchase_id' => $iPurchaseId,
                        'status' => $sToStatus,
                        'time_stamp' => PHPFOX_TIME,
                        'currency_id' => $aPurchase['currency_id'],
                        'payment_method' => $aPurchase['payment_method'],
                        'transaction_id' => $aPurchase['transaction_id'],
                    ];
                    Phpfox::getService('subscribe.purchase.process')->addRecentPurchase($aHistory);
                    db()->delete(Phpfox::getT('subscribe_cancel_reason'), 'purchase_id = ' . $iPurchaseId);

                    Phpfox::getLib('mail')
                        ->to($aPurchase['user_id'])
                        ->subject(['admin_change_status_to_active_subject', [
                            'package_title' => _p($aPurchase['title'], [], !empty($aPurchase['language_id']) ? $aPurchase['language_id'] : $sDefaultLanguage)
                        ]])
                        ->message(['admin_change_status_to_active_template', [
                            'username' => $aPurchase['full_name'],
                            'package_title' => _p($aPurchase['title'], [], !empty($aPurchase['language_id']) ? $aPurchase['language_id'] : $sDefaultLanguage),
                            'link' => '<a href="' . Phpfox_Url::instance()->makeUrl('subscribe.list') . '" target="_blank">' . _p('subscribe_go_to_subscription_detail') . '</a>'
                        ]])
                        ->notification('subscribe.subscribe_notifications')
                        ->send();
                    Phpfox::getService('notification.process')->add('subscribe_changestatustoactive', $iPurchaseId, $aPurchase['user_id']);
                }
                break;
        }
        $this->database()->update($this->_sTable, ['status' => $sToStatus, 'time_stamp' => PHPFOX_TIME], 'purchase_id = ' . (int)$iPurchaseId);

        // update total active
        Phpfox::getService('subscribe.process')->updateTotalActive($aPurchase['package_id']);

        return true;
    }

    public function delete($iId)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);

        $aPurchase = $this->database()->select('sp.*, spack.*')
            ->from($this->_sTable, 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
            ->where('sp.purchase_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aPurchase['purchase_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_purchase_you_are_trying_to_delete'));
        }

        db()->updateCount('subscribe_purchase', 'package_id = ' . $aPurchase['package_id'] . ' AND status = "completed"', 'total_active', 'subscribe_package', 'package_id = ' . $aPurchase['package_id']);
        $this->database()->delete($this->_sTable, 'purchase_id = ' . $aPurchase['purchase_id']);

        return true;
    }

    public function change2Free()
    {
        if (!Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up')) {
            $this->database()->delete(Phpfox::getT('subscribe_purchase'), 'user_id = '.Phpfox::getUserId().' AND status is NULL');
            $this->database()->update(Phpfox::getT('user_field'), ['subscribe_id' => 0], ['user_id' => Phpfox::getUserId()]);
        }
        return true;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     *
     * @return null
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('subscribe.service_purchase_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}