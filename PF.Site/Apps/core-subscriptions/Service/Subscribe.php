<?php

namespace Apps\Core_Subscriptions\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Subscribe extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('subscribe_package');
    }

    /**
     * @return array
     */
    public function getSectionMenu()
    {
        $menu = [];
        if (setting('subscribe.enable_subscription_packages')) {
            $menu[_p('menu_membership_packages')] = '';
        }
        $menu = array_merge($menu, [
            _p('menu_my_subscriptions') => 'subscribe.list',
            _p('plans_comparision') => 'subscribe.compare'
        ]);
        return $menu;
    }

    public function getCurrentUsingPackageForCompare($iUserId)
    {
        $aPackage = db()->select('*')
            ->from($this->_sTable, 'spack')
            ->join(Phpfox::getT('subscribe_purchase'), 'sp', 'sp.package_id = spack.package_id')
            ->where('sp.user_id = ' . $iUserId . ' AND sp.status = "completed" AND spack.is_active = 1 AND spack.is_removed = 0')
            ->order('field(sp.status,"completed") DESC, sp.time_stamp DESC')
            ->limit(1)
            ->execute('getSlaveRow');
        if (!empty($aPackage)) {
            if (!empty($aPackage['cost']) && Phpfox::getLib('parse.format')->isSerialized($aPackage['cost'])) {
                $aCosts = unserialize($aPackage['cost']);

                foreach ($aCosts as $sKey => $iCost) {
                    if (Phpfox::getService('core.currency')->getDefault() == $sKey) {
                        $aPackage['default_cost'] = $iCost;
                    }
                }
            }

            if (empty($aPackage['recurring_cost']) || !Phpfox::getLib('parse.format')->isSerialized($aPackage['recurring_cost']) || $aPackage['recurring_period'] == 0) {
                $aPackage['recurring_fee'] = (int)$aPackage['recurring_cost'];
            } else {
                $aPackage['aRecurring'] = unserialize($aPackage['recurring_cost']);
                foreach ($aPackage['aRecurring'] as $sCurrency => $iAmount) {
                    if ($sCurrency == Phpfox::getService('core.currency')->getDefault()) {
                        $aPackage['recurring_fee'] = $iAmount;
                        break;
                    }
                }
            }

            return [
                'title' => $aPackage['title'],
                'package_id' => $aPackage['package_id'],
                'description' => $aPackage['description'],
                'background_color' => $aPackage['background_color'],
                'default_cost' => $aPackage['default_cost'],
                'show_price' => $aPackage['show_price'],
                'default_recurring_cost' => (int)$aPackage['recurring_period'] > 0 ? $this->getPeriodPhrase($aPackage['recurring_period'],
                    $aPackage['recurring_fee'], $aPackage['default_cost']) : '',
                'membership_title' => Phpfox::getService('user.group')->getGroup($aPackage['user_group_id'])['title'],
                'user_group_id' => $aPackage['user_group_id'],
                'purchased_by_current_user' => true,
                'membership_permission' => true,
                'is_active' => $aPackage['is_active'],
                'recurring_period' => $aPackage['recurring_period'],
            ];
        }
        return [];
    }

    public function getPackageCount()
    {
        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('is_removed = 0')
            ->execute('getSlaveField');
    }

    public function getActiveUserListByPackage($iPackageId)
    {
        return db()->select('sp.purchase_id AS last_purchase_id, sp.user_id, sp.extra_params, sp.payment_method, u.email, spack.title, u.full_name, sp.expiry_date, u.language_id')
            ->from(Phpfox::getT('subscribe_purchase'), 'sp')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = sp.user_id')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
            ->where('sp.package_id = ' . $iPackageId . ' AND sp.status = "completed" AND (u.email IS NOT NULL OR u.email != "")')
            ->group('sp.user_id')
            ->execute('getSlaveRows');

    }

    public function checkNumbersOfSubscription($iPackageId)
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('subscribe_purchase'))
            ->where('package_id = ' . $iPackageId . ' AND status IS NOT NULL')
            ->execute('getSlaveField');
        return (int)$iCnt > 0;
    }

    public function getStatusList()
    {
        return [
            'completed' => _p('sub_active'),
            'pending' => _p('pending'),
            'cancel' => _p('canceled'),
            'expire' => _p('expired'),
            'pendingaction' => _p('pending_action')
        ];
    }

    public function markPackagePopular($iPackageId)
    {
        Phpfox::isAdmin(true);
        if (!empty($iPackageId)) {
            $aRow = db()->select('package_id')
                ->from(Phpfox::getT('subscribe_package'))
                ->where('is_popular = 1')
                ->execute('getSlaveRow');
            if (!empty($aRow)) {
                $iOldId = $aRow['package_id'];
                db()->update(Phpfox::getT('subscribe_package'), ['is_popular' => 0], 'package_id = ' . $iOldId);
            }
            db()->update(Phpfox::getT('subscribe_package'), ['is_popular' => 1], 'package_id = ' . (int)$iPackageId);
            return true;
        }
        return false;

    }


    /**
     * @param string $sPeriod
     * @param string $sRecurring
     * @param string $sInitialFee
     * @param string $sCurrencyId
     *
     * @return null|string
     */
    public function getPeriodPhrase($sPeriod, $sRecurring, $sInitialFee, $sCurrencyId = '')
    {
        // recurring price = 0 then, no recurring!
        if (empty($sRecurring)) {
            return null;
        }

        $aValues = [
            'price' => Phpfox::getService('core.currency')->getCurrency($sRecurring, $sCurrencyId),
            'period' => $sPeriod
        ];

        switch ($sPeriod) {
            case '0': // no recurring
                if ($sInitialFee > 0) {
                    $sPhrase = _p('never_expire_subscription');
                } else {
                    $sPhrase = _p('free');
                }
                break;
            case '1':
                // monthly
                $aValues['period'] = _p('monthly');
                $sPhrase = _p('recurring_price_period', $aValues);

                break;
            case '2':
                // quarterly
                $aValues['period'] = _p('quarterly');
                $sPhrase = _p('recurring_price_period', $aValues);
                break;
            case '3':
                // biannually
                $aValues['period'] = _p('biannually');
                $sPhrase = _p('recurring_price_period', $aValues);
                break;
            case '4':
                // annually
                $aValues['period'] = _p('annually');
                $sPhrase = _p('recurring_price_period', $aValues);
                break;
        }

        return isset($sPhrase) ? $sPhrase : '';
    }

    public function getPackages($bIsForSignUp = false, $bShowAllSubscriptions = false, $bGetAll = false)
    {
        $aPackages = $this->database()->select('sp.*')
            ->from($this->_sTable, 'sp')
            ->where('sp.is_removed = 0' . ($bIsForSignUp ? ' AND sp.is_registration = 1' : '') . ($bGetAll ? '' : ' AND sp.is_active = 1'))
            ->order('sp.ordering ASC')
            ->execute('getSlaveRows');

        $aSubscriptionsIdPurchasedByUser = $bIsForSignUp ? [] : Phpfox::getService('subscribe.purchase')->getSubscriptionsIdPurchasedByUser(Phpfox::getUserId());
        foreach ($aPackages as $iKey => $aPackage) {
            if ($bShowAllSubscriptions == false) {
                $aVisibleGroup = Phpfox::getLib('parse.format')->isSerialized($aPackage['visible_group']) ? unserialize($aPackage['visible_group']) : $aPackage['visible_group'];
                if (is_array($aVisibleGroup)) {
                    if (!in_array($aPackage['package_id'], $aSubscriptionsIdPurchasedByUser)) {
                        $iCurrenUserGroup = Phpfox::getUserBy('user_group_id');
                        if (!in_array($iCurrenUserGroup, $aVisibleGroup)) {
                            unset($aPackages[$iKey]);
                            continue;
                        }
                    }
                } else {
                    unset($aPackages[$iKey]);
                    continue;
                }
            }

            $sDefaultCurrencyId = Phpfox::getService('core.currency')->getDefault();

            if (!empty($aPackage['cost'])
                && Phpfox::getLib('parse.format')->isSerialized($aPackage['cost'])
                && !empty($aCosts = unserialize($aPackage['cost']))) {
                foreach ($aCosts as $sKey => $iCost) {
                    if ($sDefaultCurrencyId == $sKey) {
                        $aPackages[$iKey]['default_cost'] = $iCost;
                        $aPackages[$iKey]['default_currency_id'] = $sKey;
                    } else {
                        if ((int)$iCost === 0) {
                            continue;
                        }
                        $aPackages[$iKey]['price'][$sKey]['cost'] = $iCost;
                        $aPackages[$iKey]['price'][$sKey]['currency_id'] = $sKey;
                    }
                }
            } else {
                $aPackages[$iKey]['default_cost'] = 0;
                $aPackages[$iKey]['default_currency_id'] = $sDefaultCurrencyId;
            }

            $aPackage = $aPackages[$iKey];
            if ($aPackage['recurring_period'] > 0 && Phpfox::getLib('parse.format')->isSerialized($aPackage['recurring_cost'])) {
                $aRecurringCosts = unserialize($aPackage['recurring_cost']);
                foreach ($aRecurringCosts as $sKey => $iCost) {
                    if ($sDefaultCurrencyId == $sKey) {
                        $aPackages[$iKey]['default_recurring_cost'] = $this->getPeriodPhrase($aPackage['recurring_period'], $iCost, $aPackages[$iKey]['default_cost'], $aPackage['default_currency_id']);
                        $aPackages[$iKey]['default_recurring_cost_no_phrase'] = $aRecurringCosts[$sKey];
                        $aPackages[$iKey]['default_recurring_currency_id'] = $sKey;
                    }
                }
            }
        }
        return $aPackages;
    }

    public function getPackagesForCompare($bIsAdminCP = false)
    {
        $aPackages = $this->getPackages(false, true, $bIsAdminCP);
        $aCompare = $this->database()->select('*')->from(Phpfox::getT('subscribe_compare'))->order('ordering ASC')->execute('getSlaveRows');

        $aForCompare = ['packages' => [], 'features' => []];

        // We store here the packages that have at least one feature assigned, others will be removed
        $aUsedPackages = [];

        $aSubscriptionsIdPurchasedByUser = Phpfox::getService('subscribe.purchase')->getSubscriptionsIdPurchasedByUser(Phpfox::getUserId());
        // figure out the cost, recurring cost and symbol based on my currency
        foreach ($aPackages as $iKey => $aPackage) {
            $aPackage['aCosts'] = unserialize($aPackage['cost']);
            // Assign the initial fee
            foreach ($aPackage['aCosts'] as $sCurrency => $iAmount) {
                if ($sCurrency == Phpfox::getService('core.currency')->getDefault()) {
                    $aPackages[$iKey]['initial_fee'] = $iAmount;
                    break;
                }
            }
            // Assign the recurring fee
            if (empty($aPackage['recurring_cost']) || !Phpfox::getLib('parse.format')->isSerialized($aPackage['recurring_cost']) || $aPackage['recurring_period'] == 0) {
                $aPackages[$iKey]['recurring_fee'] = (int)$aPackage['recurring_cost'];
            } else {
                $aPackage['aRecurring'] = unserialize($aPackage['recurring_cost']);
                foreach ($aPackage['aRecurring'] as $sCurrency => $iAmount) {
                    if ($sCurrency == Phpfox::getService('core.currency')->getDefault()) {
                        $aPackages[$iKey]['recurring_fee'] = $iAmount;
                        break;
                    }
                }
            }

            // check and add to compare packages
            $aPackage = $aPackages[$iKey];
            $aVisibleGroup = Phpfox::getLib('parse.format')->isSerialized($aPackage['visible_group']) ? unserialize($aPackage['visible_group']) : [];
            if (!isset($aPackage['default_recurring_cost']) && (!isset($aPackage['recurring_period']) || !isset($aPackage['recurring_fee']) || !isset($aPackage['initial_fee']))) {
                continue;
            }

            if ($aPackage['recurring_period'] == '0') {
                $aPackage['recurring_fee'] = $aPackage['default_cost'];
            }

            $aForCompare['packages'][$aPackage['package_id']] = [
                'title' => $aPackage['title'],
                'package_id' => $aPackage['package_id'],
                'description' => $aPackage['description'],
                'background_color' => $aPackage['background_color'],
                'default_cost' => $aPackage['default_cost'],
                'show_price' => $aPackage['show_price'],
                'default_recurring_cost' => (int)$aPackage['recurring_period'] > 0 ? $this->getPeriodPhrase($aPackage['recurring_period'], $aPackage['recurring_fee'], $aPackage['initial_fee']) : '',
                'membership_title' => Phpfox::getService('user.group')->getGroup($aPackage['user_group_id'])['title'],
                'user_group_id' => $aPackage['user_group_id'],
                'purchased_by_current_user' => in_array($aPackage['package_id'], $aSubscriptionsIdPurchasedByUser),
                'membership_permission' => !empty($aVisibleGroup) ? in_array(Phpfox::getUserBy('user_group_id'), $aVisibleGroup) : false,
                'is_active' => $aPackage['is_active'],
                'recurring_period' => $aPackage['recurring_period'],
            ];
        }

        // Shape the final array
        foreach ($aCompare as $aRow) {
            $aRow['feature_value'] = json_decode($aRow['feature_value'], true);
            $aForCompare['features'][$aRow['feature_title']] = [];
            $aForCompare['features'][$aRow['feature_title']]['compare_id'] = $aRow['compare_id'];
            $aForCompare['features'][$aRow['feature_title']]['ordering'] = $aRow['ordering'];
            foreach ($aPackages as $aPackage) {
                foreach ($aRow['feature_value'] as $iKey => $aFeatureValue) {
                    if ($iKey == $aPackage['package_id']) {
                        $aForCompare['features'][$aRow['feature_title']]['data'][$aPackage['package_id']] = [
                            'option' => $aFeatureValue['option'],
                            'text' => $aFeatureValue['text']
                        ];
                        $aUsedPackages[$aPackage['package_id']] = 1;
                    }
                }
            }
        }

        if ($bIsAdminCP == false) {
            // Remove unused packages
            foreach ($aForCompare['packages'] as $iPackageId => $sTitle) {
                if (!isset($aUsedPackages[$iPackageId])) {
                    unset($aForCompare['packages'][$iPackageId]);
                }
            }
        }

        // Add empty cells
        foreach ($aForCompare['features'] as $iFeatureId => $aFeature) {
            foreach ($aForCompare['packages'] as $iPackageId => $aPackage) {
                if (!isset($aForCompare['features'][$iFeatureId]['data'][$iPackageId])) {
                    $aForCompare['features'][$iFeatureId]['data'][$iPackageId] = [
                        'option' => 2,
                        'text' => ''
                    ];
                }
            }
        }

        return $aForCompare;
    }

    public function getPackage($iPackageId, $bIsAdminEdit = false)
    {
        $aPackage = $this->database()->select('sp.*')
            ->from($this->_sTable, 'sp')
            ->where('sp.package_id = ' . (int)$iPackageId . ' ' . ($bIsAdminEdit ? '' : 'AND sp.is_active = 1'))
            ->order('sp.ordering ASC')
            ->execute('getSlaveRow');

        if (!isset($aPackage['package_id'])) {
            return false;
        }

        $sDefaultCurrencyId = Phpfox::getService('core.currency')->getDefault();
        if (!empty($aPackage['cost'])
            && Phpfox::getLib('parse.format')->isSerialized($aPackage['cost'])
            && !empty($aCosts = unserialize($aPackage['cost']))) {
            foreach ($aCosts as $sKey => $iCost) {
                if ($sDefaultCurrencyId == $sKey) {
                    $aPackage['default_cost'] = $iCost;
                    $aPackage['default_currency_id'] = $sKey;
                } else {
                    $aPackage['price'][] = [$sKey => $iCost];
                }
            }
        } else {
            $aPackage['default_cost'] = 0;
            $aPackage['default_currency_id'] = $sDefaultCurrencyId;
        }

        if ($aPackage['recurring_period'] > 0 && Phpfox::getLib('parse.format')->isSerialized($aPackage['recurring_cost'])) {
            $aRecurringCosts = unserialize($aPackage['recurring_cost']);
            foreach ($aRecurringCosts as $sKey => $iCost) {
                if ($sDefaultCurrencyId == $sKey) {
                    $aPackage['default_recurring_cost'] = $iCost;
                    $aPackage['default_recurring_currency_id'] = $sKey;
                } else {
                    $aPackage['recurring_price'][] = [$sKey => $iCost];
                }
            }
        }

        if ($aPackage['recurring_period'] > 0) {
            $aPackage['is_recurring'] = '1';
        }
        return $aPackage;
    }

    public function getForEdit($iPackageId)
    {
        return $this->getPackage($iPackageId, true);
    }

    public function getForAdmin($aFilters, $bCount = false)
    {
        $sWhere = '';
        if (!empty($aFilters)) {
            if (!empty($aFilters['type'])) {
                $sWhere .= $aFilters['type'] == "onetime" ? ' AND sp.recurring_period = 0' : ' AND sp.recurring_period > 0';
            }
            if (!empty($aFilters['status'])) {
                $sWhere .= $aFilters['status'] == "on" ? ' AND sp.is_active = 1' : ' AND sp.is_active = 0';
            }
        }

        $iCnt = 0;
        if ($bCount) {
            $iCnt = $this->database()->select('COUNT(sp.package_id)')
                ->from($this->_sTable, 'sp')
                ->where('sp.is_removed = 0' . $sWhere)
                ->execute('getSlaveField');
        }

        $aPackages = $this->database()->select('sp.*')
            ->from($this->_sTable, 'sp')
            ->where('sp.is_removed = 0' . $sWhere)
            ->order('sp.ordering ASC')
            ->execute('getSlaveRows');

        $aTime = (!empty($aFilters['from']) && !empty($aFilters['to'])) ? [
            'time_from' => (int)strtotime($aFilters['from'] . ' 00:00:00'),
            'time_to' => (int)strtotime($aFilters['to'] . ' 23:59:59')
        ] : [];

        foreach ($aPackages as $iKey => $aPackage) {
            $aStatistics = Phpfox::getService('subscribe.purchase')->getStatusStatictisForPackage($aPackage['package_id'], $aTime);
            foreach ($aStatistics as $aStatistic) {
                $aPackages[$iKey]['statistic_' . $aStatistic['status']] = $aStatistic;
            }
            if (!empty($aPackage['cost']) && Phpfox::getLib('parse.format')->isSerialized($aPackage['cost'])) {
                $aCosts = unserialize($aPackage['cost']);
                foreach ($aCosts as $sKey => $iCost) {
                    if (Phpfox::getService('core.currency')->getDefault() == $sKey) {
                        $aPackages[$iKey]['default_cost'] = floatval($iCost);
                    }
                }
            }

            switch ($aPackage['recurring_period']) {
                case 0:
                    $aPackages[$iKey]['type'] = _p('one_time');
                    break;
                case 1:
                    $aPackages[$iKey]['type'] = _p('monthly');
                    break;
                case 2:
                    $aPackages[$iKey]['type'] = _p('quarterly');
                    break;
                case 3:
                    $aPackages[$iKey]['type'] = _p('biannualy');
                    break;
                case 4:
                    $aPackages[$iKey]['type'] = _p('annually');
                    break;
                default:
                    $aPackages[$iKey]['type'] = _p('other');
                    break;
            }
        }

        return ($bCount) ? [$aPackages, $iCnt] : $aPackages;
    }


    public function getCompareArray()
    {
        $aRows = $this->database()->select('*')->from(Phpfox::getT('subscribe_compare'))->execute('getSlaveRows');
        $aCompare = [];
        foreach ($aRows as $aRow) {
            $aCompare[] = [
                'feature_title' => $aRow['feature_title'],
                'feature_value' => json_decode($aRow['feature_value'], true)
            ];
        }

        return $aCompare;
    }

    public function getModuleIdByGateway($gatewayId)
    {
        $moduleId = null;
        if (in_array($gatewayId, ['paypal', 'activitypoints'])) {
            $moduleId = 'subscribe';
        }

        ($plugin = Phpfox_Plugin::get('subscribe.service_subscribe_getmoduleidbygateway')) ? eval($plugin) : null;

        return $moduleId;
    }

    public function generatePaymentMethods()
    {
        return [
            [
                'title' => _p('subscribe_auto_renew'),
                'name' => 'auto',
                'value' => 1,
                'checked' => true
            ],
            [
                'title' => _p('subscribe_manual_renew'),
                'name' => 'manual',
                'value' =>  2,
                'checked' => false
            ]
        ];
    }

    public function getTotalPaymentMethods()
    {
        return count($this->generatePaymentMethods());
    }

    public function getVisiblePaymentMethods($iId)
    {
        $sPackageMethods = db()->select('allow_payment_methods')
            ->from(Phpfox::getT('subscribe_package'))
            ->where([
                'package_id' => $iId
            ])
            ->executeField();

        if (empty($sPackageMethods)) {
            return $this->generatePaymentMethods();
        } else {
            $aMethods = [];
            $aPackageMethods = unserialize($sPackageMethods);
            $aAllMethods = $this->generatePaymentMethods();
            foreach ($aPackageMethods as $key => $value) {
                $idx = array_search($key, array_column($aAllMethods, 'name'));
                $aMethods[] = [
                    'name' => $key,
                    'value' => $value,
                    'title' => $aAllMethods[$idx]['title'],
                    'checked' => false
                ];
            }
            $aMethods[0]['checked'] = true;
            return $aMethods;
        }
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
        if ($sPlugin = Phpfox_Plugin::get('subscribe.service_subscribe__call')) {
            eval($sPlugin);

            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        return Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}