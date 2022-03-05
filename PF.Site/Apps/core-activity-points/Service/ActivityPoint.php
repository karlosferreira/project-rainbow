<?php

namespace Apps\Core_Activity_Points\Service;

use Phpfox;
use Phpfox_Service;
use Phpfox_Template;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class ActivityPoint
 * @package Apps\Core_Activity_Points\Service
 */
class ActivityPoint extends Phpfox_Service
{
    /**
     * @param null $userId
     * @return int
     */
    public function getTotalPointsOfUser($userId = null)
    {
        empty($userId) && $userId = Phpfox::getUserId();

        $totalPoints = db()->select('activity_points')
                        ->from(':user_activity')
                        ->where([
                            'user_id' => $userId,
                        ])->executeField();

        return (int)$totalPoints;
    }

    /**
     * @param $sModule
     * @param $sVarName
     * @return array|false
     */
    public function getAllPointSettingsForUpdatePoints($sModule, $sVarName)
    {
        if (empty($sVarName)) {
            return false;
        }
        $sCacheId = $this->cache()->set('activitypoint_all_settings_for_update_points');
        if (false === ($aResults = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('*')
                ->from(Phpfox::getT('activitypoint_setting'))
                ->execute('getSlaveRows');
            $aResults = [];
            if (!empty($aRows)) {
                foreach ($aRows as $aRow) {
                    $aResults[$aRow['var_name']] = $aResults[$aRow['module_id'] . '.' . $aRow['var_name']] = $aRow;
                }
            }
            $this->cache()->save($sCacheId, $aResults);
        }
        return isset($aResults[$sVarName]) ? [$aResults[$sVarName], $sVarName] : (isset($aResults[$sModule . '.' . $sVarName]) ? [$aResults[$sModule . '.' . $sVarName], $sModule . '.' . $sVarName] : false);
    }

    public function getTypePhrase($sType)
    {
        if (empty($sType)) {
            return '';
        }

        $sVarName = '';

        switch ($sType) {
            case 'Bought':
                {
                    $sVarName = 'activitypoint_bought';
                    break;
                }
            case 'Spent':
                {
                    $sVarName = 'activitypoint_spent';
                    break;
                }
            case 'Sent':
                {
                    $sVarName = 'activitypoint_sent';
                    break;
                }
            case 'Earned':
                {
                    $sVarName = 'activitypoint_earned';
                    break;
                }
            case 'Received':
                {
                    $sVarName = 'activitypoint_received';
                    break;
                }
            case 'Retrieved':
                {
                    $sVarName = 'activitypoint_retrieved';
                    break;
                }
        }

        return $sVarName;
    }

    /**
     * Get all modules and apps name for transactions
     * @return array
     */
    public function getAllAppAndModuleNameForTransaction()
    {
        return get_from_cache('activitypoint_get_all_app_module_name', function () {
            $aApps = db()->select('m.module_id, apps_name AS name')
                ->from(Phpfox::getT('module'), 'm')
                ->join(Phpfox::getT('apps'), 'a', 'a.apps_alias = m.module_id')
                ->where('m.phrase_var_name = "module_apps" AND (a.apps_name != "" OR a.apps_name IS NOT NULL)')
                ->execute('getSlaveRows');
            if (!empty($aApps)) {
                $aApps = array_combine(array_column($aApps, 'module_id'), array_column($aApps, 'name'));
            }
            $aModules = db()->select('m.module_id, m.phrase_var_name AS name')
                ->from(Phpfox::getT('module'), 'm')
                ->where('m.phrase_var_name != "module_apps" AND (m.is_core = 0 OR m.module_id = "user")')
                ->execute('getSlaveRows');
            if (!empty($aModules)) {
                $aModules = array_combine(array_column($aModules, 'module_id'), array_column($aModules, 'name'));
            }

            $aMerge = array_merge($aModules, $aApps);
            ksort($aMerge);

            return (!empty($aMerge) ? $aMerge : []);
        });
    }

    /**
     * Get members statistics of points in admincp
     * @param $aConds
     * @param $iPage
     * @param $iSize
     * @param $sSort
     * @return array
     */
    public function getMemberPointsForAdmin($aConds, $iPage, $iSize, $sSort)
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('activitypoint_statistics'), 's')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = s.user_id')
            ->join(Phpfox::getT('user_activity'), 'a', 'a.user_id = s.user_id')
            ->where($aConds)
            ->execute('getSlaveField');
        $aRows = [];
        if ($iCnt) {
            $aRows = db()->select('s.*, a.activity_points, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('activitypoint_statistics'), 's')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = s.user_id')
                ->join(Phpfox::getT('user_activity'), 'a', 'a.user_id = s.user_id')
                ->where($aConds)
                ->order($sSort)
                ->limit($iPage, $iSize)
                ->execute('getSlaveRows');
        }
        return [$iCnt, $aRows];

    }

    /**
     * Get active point settings
     * @param $aPointSettings
     * @param $iUserGroupId
     * @return array
     */
    public function filterActivePointSetting($aPointSettings, $iUserGroupId)
    {
        if (empty($aPointSettings)) {
            return [];
        }
        return get_from_cache('active_point_settings_usergroup_' . $iUserGroupId, function () use ($aPointSettings) {
            foreach ($aPointSettings as $sModule => $aSetting) {
                if (empty($aSetting['settings'])) {
                    unset($aPointSettings[$sModule]);
                    continue;
                }
                foreach ($aSetting['settings'] as $sName => $aValue) {
                    if (!$aValue['is_active']) {
                        unset($aPointSettings[$sModule]['settings'][$sName]);
                        continue;
                    }
                    $aPointSettings[$sModule]['settings'][$sName]['text'] = $this->getActionVarName($aValue['text']);
                }
                if (empty($aPointSettings[$sModule]['settings'])) {
                    unset($aPointSettings[$sModule]);
                }
            }
            return $aPointSettings;
        });
    }

    /**
     * Handle period to period time
     * @param $sPeriod
     * @return int
     */
    public function getPeriodTime($sPeriod)
    {
        switch ($sPeriod) {
            case 'per_day':
                {
                    $iPeriodTime = strtotime("-1 day", PHPFOX_TIME);
                    break;
                }
            case 'per_week':
                {
                    $iPeriodTime = strtotime("-7 days", PHPFOX_TIME);
                    break;
                }
            case 'per_month':
                {
                    $iPeriodTime = strtotime("-1 month", PHPFOX_TIME);
                    break;
                }
            case 'per_year':
                {
                    $iPeriodTime = strtotime("-1 year", PHPFOX_TIME);
                    break;
                }
            default:
                {
                    $iPeriodTime = 0;
                    break;
                }
        }

        return $iPeriodTime;
    }

    /**
     * Get points number that user can send for others in admincp
     * @param $iUserId
     * @return int
     */
    public function getPointsUserCanSentInAdmincp($iUserId)
    {
        if (!Phpfox::getUserParam('activitypoint.can_admin_adjust_activity_points')) {
            return false;
        }
        $sPeriod = Phpfox::getUserParam('activitypoint.period_time_admin_adjust_activity_points');
        $iPeriodTime = $this->getPeriodTime($sPeriod);

        if (!$iPeriodTime) {
            return false;
        }
        $iPoints = db()->select('SUM(points)')
            ->from(Phpfox::getT('activitypoint_period_adjust_point'))
            ->where('type = "send" AND user_id = ' . (int)$iUserId . ' AND time_stamp >=' . (int)$iPeriodTime)
            ->execute('getSlaveField');
        $iMaxPoints = Phpfox::getUserParam('activitypoint.maximum_activity_points_admin_can_adjust');

        return ($iPoints < $iMaxPoints ? ((int)$iMaxPoints - (int)$iPoints) : 0);
    }

    /**
     * Get array of apps and modules for active or deactive
     * @return array
     */
    public function getAppsAndModulesStatus()
    {
        $aParsed = [];
        $aRows = db()->select('a.is_active AS is_app_active, m.is_active AS is_module_active, a.apps_id as app_id, s.module_id')
            ->from(Phpfox::getT('activitypoint_setting'), 's')
            ->leftJoin(Phpfox::getT('apps'), 'a', 'a.apps_alias = s.module_id')
            ->leftJoin(Phpfox::getT('module'), 'm', 'm.module_id = s.module_id')
            ->group('s.module_id')
            ->execute('getSlaveRows');
        foreach ($aRows as $aRow) {
            $aParsed[$aRow['module_id']]['is_active'] = (!empty($aRow['app_id']) ? (int)$aRow['is_app_active'] : (int)$aRow['is_module_active']);
        }
        return $aParsed;

    }

    /**
     * Get array with key(module/app id) and value(module/app name)
     * @return array
     */
    public function getSettingApps()
    {
        $aDatas = get_from_cache('activitypoint_setting_apps', function () {
            $aRows = db()->select('s.module_id, a.apps_name, m.phrase_var_name')
                ->from(Phpfox::getT('activitypoint_setting'), 's')
                ->leftJoin(Phpfox::getT('apps'), 'a', 'a.apps_alias = s.module_id')
                ->leftJoin(Phpfox::getT('module'), 'm', 'm.module_id = s.module_id')
                ->order('s.module_id ASC')
                ->group('s.module_id')
                ->execute('getSlaveRows');

            $aParsedArray = [];
            foreach ($aRows as $iKey => $aRow) {
                $aParsedArray[$aRow['module_id']] = !empty($aRow['apps_name']) ? $aRow['apps_name'] : ($aRow['phrase_var_name'] != 'module_apps' ? $aRow['phrase_var_name'] : '');
            }
            ksort($aParsedArray);
            return $aParsedArray;
        });

        return $aDatas;
    }

    /**
     * Get some special modules/apps which do not have setting for point but they needed for transaction filter
     *
     * @return array
     */
    public function getMoreAppsForTransactionFilter()
    {
        $defaultApps = [
            'activitypoint' => 'activitypoint_title'
        ];

        $isSubscriptionAppInstalled = Phpfox::isAppActive('Core_Subscriptions') || !empty(db()->select('product_id')->from(':module')->where(['module_id' => 'subscribe'])->executeField());

        if (!empty($isSubscriptionAppInstalled)) {
            $defaultApps = array_merge($defaultApps, [
               'subscribe' => 'module_subscribe',
            ]);
        }

        return $defaultApps;
    }

    /**
     * Get setting actions of module/app
     * @param bool $isTransaction
     * @return array
     */
    public function getSettingActions($isTransaction = false)
    {
        $aRows = db()->select('s.var_name, s.phrase_var_name as text')
            ->from(Phpfox::getT('activitypoint_setting'), 's')
            ->order('s.var_name ASC')
            ->execute('getSlaveRows');
        foreach ($aRows as $iKey => $aRow) {
            if ($isTransaction) {
                $aRows[$iKey]['text'] = _p($this->getActionVarName($aRow['text']));
            } else {
                $aRows[$iKey]['text'] = _p($aRow['text']);
            }
        }
        usort($aRows, function ($a, $b) {
            if (is_array($a) && is_array($b)) {
                return ($a['text'] < $b['text']) ? -1 : 1; // -1 no need re-order
            } else {
                return -1; // -1 no need re-order
            }
        });
        return $aRows;
    }

    /**
     * Get users for send or reduce points in admincp
     * @param $sUserId
     * @return array
     */
    public function getUsersForPointActions($sUserId)
    {
        $sUserId = Phpfox::getLib('parse.input')->clean(strip_tags(trim($sUserId, ',')));
        $aRows = db()->select(Phpfox::getUserField())
            ->from(Phpfox::getT('user'), 'u')
            ->where('u.user_id IN (' . $sUserId . ')')
            ->execute('getSlaveRows');
        return $aRows;
    }

    /**
     * Get points of user who has minimum points in list for admincp
     * @param $sUserId
     * @return int
     */
    public function getMaximumPointsForReduceAction($sUserId)
    {
        $sUserId = Phpfox::getLib('parse.input')->clean(strip_tags(trim($sUserId, ',')));
        if (!empty($sUserId)) {
            $iPoint = db()->select('MIN(activity_points)')
                ->from(Phpfox::getT('user_activity'))
                ->where('user_id IN (' . $sUserId . ')')
                ->execute('getSlaveField');
            return $iPoint;
        }
        return 0;

    }

    /**
     * Prepare menu for FE
     */
    public function buildMenu()
    {
        $aMenu = [
            _p('activitypoint_point_transaction_title') => '',
            _p('activitypoint_how_to_earn') => 'activitypoint.information'
        ];
        Phpfox_Template::instance()->buildSectionMenu('activitypoint', $aMenu);
        return $aMenu;
    }

    /**
     * Get point statistics for users
     * @param $iUserId
     * @return array
     */
    public function getStatisticsForUser($iUserId = null)
    {
        if (!isset($iUserId)) {
            $iUserId = Phpfox::getUserId();
        }
        $aRow = db()->select('a.activity_points, s.*')
            ->from(Phpfox::getT('activitypoint_statistics'), 's')
            ->join(Phpfox::getT('user_activity'), 'a', 'a.user_id = s.user_id')
            ->where('s.user_id = ' . (int)$iUserId)
            ->execute('getSlaveRow');
        $aStatistics = [
            'current_points' => [
                'points' => $aRow['activity_points'],
                'information' => 'activitypoint_current_points_information',
                'title' => _p('activitypoint_current_points')
            ],
            'earned_points' => [
                'points' => $aRow['total_earned'],
                'information' => 'activitypoint_total_earned_information',
                'title' => _p('activitypoint_earned')
            ],
            'bought_points' => [
                'points' => $aRow['total_bought'],
                'information' => 'activitypoint_total_bought_information',
                'title' => _p('activitypoint_bought')
            ],
            'received_points' => [
                'points' => $aRow['total_received'],
                'information' => 'activitypoint_total_received_information',
                'title' => _p('activitypoint_received')
            ],
            'spent_points' => [
                'points' => $aRow['total_spent'],
                'information' => 'activitypoint_total_spent_information',
                'title' => _p('activitypoint_spent')
            ],
            'sent_points' => [
                'points' => $aRow['total_sent'],
                'information' => 'activitypoint_total_sent_information',
                'title' => _p('activitypoint_sent')
            ],
            'retrieved_points' => [
                'points' => $aRow['total_retrieved'],
                'information' => 'activitypoint_total_retrieved_information',
                'title' => _p('activitypoint_retrieved')
            ],
        ];
        $aConversionRate = Phpfox::getParam('activitypoint.activity_points_conversion_rate');
        $sCurrency = Phpfox::getService('core.currency')->getDefault();
        if (isset($aConversionRate[$sCurrency]) && (float)$aConversionRate[$sCurrency] > 0) {
            $aStatistics['points_to_currency'] = [
                'points' => (float)$aRow['activity_points'] * (float)$aConversionRate[$sCurrency],
                'information' => 'activitypoint_point_in_currency',
                'title' => _p('activitypoint_points_in_currency', ['currency' => $sCurrency]),
                'sub_information' => _p('activitypoint_one_point_equal_to_money', ['money' => Phpfox::getService('core.currency')->getCurrency((float)$aConversionRate[$sCurrency], $sCurrency)])
            ];
        }
        return $aStatistics;

    }

    /**
     * Get point settings for user group
     * @param $iGroupId
     * @return array
     */
    public function getPointSettings($iGroupId)
    {
        return get_from_cache('point_settings_usergroup_' . $iGroupId, function () use ($iGroupId) {
            $aModules = $this->getSettingApps();
            $aStatuses = $this->getAppsAndModulesStatus();
            $aRows = db()->select('*')
                ->from(Phpfox::getT('activitypoint_setting'))
                ->execute('getSlaveRows');
            $aGroup = Phpfox::getService('user.group')->getGroup($iGroupId);

            foreach ($aModules as $sKey => $sModule) {
                if (!$aStatuses[$sKey]['is_active']) {
                    unset($aModules[$sKey]);
                    continue;
                }

                $aSettings = [];
                $iActiveCount = 0;
                foreach ($aRows as $aRow) {
                    if ($sKey == $aRow['module_id']) {
                        $aMaxEarned = unserialize($aRow['max_earned']);
                        $aPeriod = unserialize($aRow['period']);
                        $aActive = unserialize($aRow['is_active']);
                        $iMaxEarned = (int)((empty($aRow['max_earned']) ? 0 : !empty($aMaxEarned[$iGroupId])) ? $aMaxEarned[$iGroupId] : (!empty($aGroup['inherit_id'] ? (!empty($aMaxEarned[$aGroup['inherit_id']]) ? $aMaxEarned[$aGroup['inherit_id']] : 0) : 0)));
                        $iPeriod = (int)((empty($aRow['period']) ? 0 : !empty($aPeriod[$iGroupId])) ? $aPeriod[$iGroupId] : (!empty($aGroup['inherit_id'] ? (!empty($aPeriod[$aGroup['inherit_id']]) ? $aPeriod[$aGroup['inherit_id']] : 0) : 0)));
                        $bIsActive = (empty($aRow['is_active']) ? 1 : (is_numeric($aActive[$iGroupId]) ? (int)$aActive[$iGroupId] : (!empty($aGroup['inherit_id'] ? (is_numeric($aActive[$aGroup['inherit_id']]) ? (int)$aActive[$aGroup['inherit_id']] : 1) : 1))));
                        $iValue = Phpfox::getUserGroupParam($iGroupId, $sKey . '.' . $aRow['var_name']);
                        if (Phpfox::isPhrase($aRow['phrase_var_name'])) {
                            $sSettingText = $aRow['phrase_var_name'];
                        } else {
                            $sSettingText = $this->parsePhraseToNewFormat($aRow['phrase_var_name'], $aRow['module_id']);
                        }
                        $aTemp = [
                            'max_earned' => $iMaxEarned,
                            'period' => $iPeriod,
                            'is_active' => $bIsActive,
                            'value' => empty($iValue) ? 0 : $iValue,
                            'text' => $sSettingText,
                            'module_id' => $aRow['module_id']
                        ];
                        $aSettings[$aRow['var_name']] = $aTemp;
                        if ($bIsActive) {
                            $iActiveCount++;
                        }
                    }
                }
                $aModules[$sKey] = [
                    'name' => $sModule,
                    'settings' => $aSettings,
                    'bActive' => (int)$iActiveCount > 0
                ];
            }
            return $aModules;
        });

    }

    /**
     * Get transactions by conditions for users
     * @param $aConds
     * @param $iPage
     * @param $iSize
     * @param $sSort
     * @return array
     */
    public function getTransactions($aConds, $iPage, $iSize, $sSort)
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('activitypoint_transaction'), 't')
            ->where($aConds)
            ->execute('getSlaveField');
        $aRows = [];
        if ((int)$iCnt > 0) {
            $aRows = db()->select('t.*, s.phrase_var_name')
                ->from(Phpfox::getT('activitypoint_transaction'), 't')
                ->leftJoin(Phpfox::getT('activitypoint_setting'), 's', 's.var_name = t.action')
                ->where($aConds)
                ->order($sSort)
                ->group('t.transaction_id')
                ->limit($iPage, $iSize)
                ->execute('getSlaveRows');
            foreach ($aRows as $iKey => $aRow) {
                $aRows[$iKey]['phrase'] = $this->getActionVarName(!empty($aRow['phrase_var_name']) ? $aRow['phrase_var_name'] : $aRow['action']);
                $aRows[$iKey]['type'] = Phpfox::getService('activitypoint')->getTypePhrase($aRow['type']);
            }
        }
        return [$iCnt, $aRows];
    }

    /**
     * Get transactions searching for admincp
     * @param $aConds
     * @param $iPage
     * @param $iSize
     * @param $sSort
     * @return array
     */
    public function getTransactionsForAdmin($aConds, $iPage, $iSize, $sSort)
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('activitypoint_transaction'), 't')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = t.user_id')
            ->where($aConds)
            ->execute('getSlaveField');
        $aRows = [];
        if ((int)$iCnt > 0) {
            $aRows = db()->select('t.*, s.phrase_var_name, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('activitypoint_transaction'), 't')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = t.user_id')
                ->leftJoin(Phpfox::getT('activitypoint_setting'), 's', 's.var_name = t.action')
                ->where($aConds)
                ->order($sSort)
                ->limit($iPage, $iSize)
                ->execute('getSlaveRows');

            foreach ($aRows as $iKey => $aRow) {
                $aRows[$iKey]['phrase'] = $this->getActionVarName(!empty($aRow['phrase_var_name']) ? $aRow['phrase_var_name'] : $aRow['action']);
                $aRows[$iKey]['type'] = Phpfox::getService('activitypoint')->getTypePhrase($aRow['type']);
            }
        }
        return [$iCnt, $aRows];
    }

    /**
     * @param $phraseVarName
     * @return mixed|string
     */
    public function getActionVarName($phraseVarName)
    {
        $newPhraseVarName = str_replace('user_setting_', '', $phraseVarName);
        return Phpfox::isPhrase($newPhraseVarName) ? $newPhraseVarName : $phraseVarName;
    }

    /**
     * Get all point settings
     * @return array
     */
    public function getAllPointSettings()
    {
        return get_from_cache('activitypoint_get_all_point_settings', function () {
            $aRows = db()->select('s.*, us.setting_id')
                ->from(Phpfox::getT('activitypoint_setting'), 's')
                ->join(Phpfox::getT('module'), 'm', 'm.module_id = s.module_id')
                ->join(Phpfox::getT('user_group_setting'), 'us', 'us.name = s.var_name')
                ->execute('getSlaveRows');
            $aSettings = [];
            foreach ($aRows as $iKey => $aRow) {
                $aSettings[$aRow['var_name']] = $aRow;
            }
            return $aSettings;
        });
    }

    public function getPointSettingValue($sModule, $sName, $iGroupId = 0)
    {
        list($aRow, $sName) = $this->getAllPointSettingsForUpdatePoints($sModule, $sName);
        if (!empty($aRow)) {
            $aMaxEarned = unserialize($aRow['max_earned']);
            $aPeriod = unserialize($aRow['period']);
            $aActive = unserialize($aRow['is_active']);
            if ((int)$iGroupId > 0) {
                $aGroup = Phpfox::getService('user.group')->getGroup($iGroupId);
                $iMaxEarned = (int)((empty($aRow['max_earned']) ? 0 : !empty($aMaxEarned[$iGroupId])) ? $aMaxEarned[$iGroupId] : (!empty($aGroup['inherit_id'] ? (!empty($aMaxEarned[$aGroup['inherit_id']]) ? $aMaxEarned[$aGroup['inherit_id']] : 0) : 0)));
                $iPeriod = (int)((empty($aRow['period']) ? 0 : !empty($aPeriod[$iGroupId])) ? $aPeriod[$iGroupId] : (!empty($aGroup['inherit_id'] ? (!empty($aPeriod[$aGroup['inherit_id']]) ? $aPeriod[$aGroup['inherit_id']] : 0) : 0)));
                $bIsActive = (empty($aRow['is_active']) ? 1 : (is_numeric($aActive[$iGroupId]) ? (int)$aActive[$iGroupId] : (!empty($aGroup['inherit_id'] ? (is_numeric($aActive[$aGroup['inherit_id']]) ? (int)$aActive[$aGroup['inherit_id']] : 1) : 1))));
                $aReturn = [$iMaxEarned, $iPeriod, $bIsActive, $sName];
            } else {
                $aReturn = [$aMaxEarned, $aPeriod, $aActive, $sName];
            }
            return $aReturn;
        }
        return false;
    }

    public function parsePhraseToNewFormat($sPhrase, $sModule)
    {
        if (strpos($sPhrase, 'user_setting_') !== false) {
            return str_replace('user_setting_', 'user_setting_' . $sModule . '_', $sPhrase);
        }
    }
}