<?php

namespace Apps\Core_Subscriptions\Service\Purchase;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Request;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Purchase extends Phpfox_Service
{
    private static $_iRedirectId = null;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('subscribe_purchase');
    }

    public function getPackagesWithUser($iUserId)
    {
        $aPackages = $this->database()->select('spack.*, ugs.title AS s_title, ugf.title AS f_title')
            ->from($this->_sTable, 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id AND spack.is_removed = 0')
            ->join(':user_group', 'ugs', 'ugs.user_group_id=spack.user_group_id')
            ->join(':user_group', 'ugf', 'ugf.user_group_id=spack.fail_user_group')
            ->where('sp.user_id = ' . $iUserId)
            ->group('spack.package_id')
            ->order('spack.ordering ASC')
            ->execute('getSlaveRows');

        return $aPackages;
    }

    public function getStatusStatictisForPackage($iPackageId, $aTime = [])
    {
        $sWhere = '';
        if (!empty($aTime)) {
            $sWhere .= ' AND time_stamp BETWEEN ' . $aTime['time_from'] . ' AND ' . $aTime['time_to'];
        }
        $aStatistic = db()->select('status, COUNT(*) as total')
            ->from(Phpfox::getT('subscribe_purchase'))
            ->where('package_id = ' . $iPackageId . ' AND status IN ("cancel", "expire", "completed")' . $sWhere)
            ->group('status')
            ->execute('getSlaveRows');
        return $aStatistic;
    }


    public function getSubscriptionsIdPurchasedByUser($iUserId)
    {
        $aRows = $this->get($iUserId);
        $aFilter = array_filter($aRows, function ($aRow) {
            return ($aRow['status'] == 'completed');
        });
        $aIds = array_column($aFilter, 'package_id');
        return $aIds;
    }


    public function setRedirectId($iRedirectId)
    {
        self::$_iRedirectId = $iRedirectId;
    }

    public function getRedirectId()
    {
        return self::$_iRedirectId;
    }

    public function getSearch($aConditions, $sSort, $iPage, $iPageSize, $bSearchReason = false)
    {
        if (empty($aConditions[0])) {
            $aConditions[] = '(sp.status IS NOT NULL OR sp.status != \'\')';

        }
        $aPurchases = [];

        if ($bSearchReason) {
            $this->database()->join(Phpfox::getT('subscribe_cancel_reason'), 'scr', 'scr.purchase_id = sp.purchase_id')
                ->join(Phpfox::getT('subscribe_reason'), 'sr', 'sr.reason_id = scr.reason_id');
        }
        $iCnt = $this->database()->select('COUNT(*)')
            ->from($this->_sTable, 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = sp.user_id')
            ->join(':user_group', 'ugs', 'ugs.user_group_id=spack.user_group_id')
            ->join(':user_group', 'ugf', 'ugf.user_group_id=spack.fail_user_group')
            ->where($aConditions)
            ->execute('getSlaveField');

        if ($iCnt) {
            if ($bSearchReason) {
                $this->database()->join(Phpfox::getT('subscribe_cancel_reason'), 'scr', 'scr.purchase_id = sp.purchase_id')
                    ->join(Phpfox::getT('subscribe_reason'), 'sr', 'sr.reason_id = scr.reason_id');
            }
            $aPurchases = $this->database()->select('spack.*, sp.*, ugs.title AS s_title, ugf.title AS f_title, ' . Phpfox::getUserField())
                ->from($this->_sTable, 'sp')
                ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = sp.user_id')
                ->join(':user_group', 'ugs', 'ugs.user_group_id=spack.user_group_id')
                ->join(':user_group', 'ugf', 'ugf.user_group_id=spack.fail_user_group')
                ->where($aConditions)
                ->limit($iPage, $iPageSize, $iCnt)
                ->order($sSort)
                ->execute('getSlaveRows');
            $this->_build($aPurchases);
        }

        return [$iCnt, $aPurchases];
    }

    public function getMySubscriptions($iUserId, $aConds = [], $iPage = 1, $iLimit = 10, $bCount = false)
    {
        $sWhere = '';

        if (!empty($aConds['title']) && empty($aConds['status'])) {
            $sWhere = ' AND spack.title = "' . $aConds['title'] . '"';
        } elseif (empty($aConds['title']) && !empty($aConds['status'])) {
            $sWhere = ($aConds['status'] == "pendingaction") ? ' AND (sp.status IS NULL OR sp.status = "") ' : ' AND sp.status = "' . $aConds['status'] . '"';
        } elseif (!empty($aConds['title']) && !empty($aConds['status'])) {
            $sWhere = ' AND spack.title = "' . $aConds['title'] . '"' . ($aConds['status'] == "pendingaction" ? ' AND (sp.status IS NULL OR sp.status = "") ' : ' AND sp.status = "' . $aConds['status'] . '"');
        }
        if ($bCount) {
            $iCnt = $this->database()->select('COUNT(spack.package_id)')
                ->from($this->_sTable, 'sp')
                ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id AND spack.is_removed = 0')
                ->join(':user_group', 'ugs', 'ugs.user_group_id=spack.user_group_id')
                ->join(':user_group', 'ugf', 'ugf.user_group_id=spack.fail_user_group')
                ->where('sp.user_id = ' . $iUserId . $sWhere)
                ->execute('getSlaveField');
        }

        $aPurchases = $this->database()->select('spack.*, sp.*, ugs.title AS s_title, ugf.title AS f_title')
            ->from($this->_sTable, 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id AND spack.is_removed = 0')
            ->join(':user_group', 'ugs', 'ugs.user_group_id=spack.user_group_id')
            ->join(':user_group', 'ugf', 'ugf.user_group_id=spack.fail_user_group')
            ->where('sp.user_id = ' . $iUserId . $sWhere)
            ->limit($iPage, $iLimit)
            ->order('field(sp.status,"completed") DESC, sp.time_stamp DESC')
            ->execute('getSlaveRows');
        $this->_build($aPurchases);

        return ($bCount ? [$aPurchases, $iCnt] : $aPurchases);
    }


    public function get($iUserId, $iLimit = null)
    {
        $aPurchases = $this->database()->select('spack.*, sp.*, ugs.title AS s_title, ugf.title AS f_title')
            ->from($this->_sTable, 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
            ->join(':user_group', 'ugs', 'ugs.user_group_id=spack.user_group_id')
            ->join(':user_group', 'ugf', 'ugf.user_group_id=spack.fail_user_group')
            ->where('sp.user_id = ' . $iUserId)
            ->limit($iLimit)
            ->order('sp.time_stamp DESC')
            ->execute('getSlaveRows');

        $this->_build($aPurchases);

        return $aPurchases;
    }

    public function getPurchase($iId, $bGetMoreInfo = false)
    {
        $aPurchase = $this->database()->select('sp.*, spack.user_group_id, ugs.title AS s_title, ugf.title AS f_title, spack.fail_user_group, spack.recurring_period, spack.recurring_cost, spack.recurring_period')
            ->from($this->_sTable, 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
            ->join(':user_group', 'ugs', 'ugs.user_group_id=spack.user_group_id')
            ->join(':user_group', 'ugf', 'ugf.user_group_id=spack.fail_user_group')
            ->where('sp.purchase_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aPurchase['purchase_id'])) {
            return false;
        }
        if ($bGetMoreInfo) {
            if ($aPurchase['recurring_period'] > 0 && Phpfox::getLib('parse.format')->isSerialized($aPurchase['recurring_cost'])) {
                $aRecurringCosts = unserialize($aPurchase['recurring_cost']);
                foreach ($aRecurringCosts as $sKey => $iCost) {
                    if (Phpfox::getService('core.currency')->getDefault() == $sKey) {
                        $aPurchase['default_recurring_cost'] = $iCost;
                    }
                }
            }

            $iNewExpiriTime = !empty((int)$aPurchase['expiry_date']) ? (int)$aPurchase['expiry_date'] : 0;
            switch ($aPurchase['recurring_period']) {
                case 0:
                    $iNewExpiriTime = 0;
                    break;
                case 1:
                    $iNewExpiriTime = strtotime("+1 month", $iNewExpiriTime);
                    break;
                case 2:
                    $iNewExpiriTime = strtotime("+3 month", $iNewExpiriTime);
                    break;
                case 3:
                    $iNewExpiriTime = strtotime("+6 month", $iNewExpiriTime);
                    break;
                case 4:
                    $iNewExpiriTime = strtotime("+1 year", $iNewExpiriTime);
                    break;
                default:
                    break;
            }
            $aPurchase['new_expiry_date'] = $iNewExpiriTime;
        }

        if (!empty($aPurchase['extra_params'])) {
            $aPurchase['extra_params'] = unserialize($aPurchase['extra_params']);
        }

        return $aPurchase;
    }

    public function getInvoice($iId, $bIsOrder = false, $sCacheUserId = null)
    {
        $aPurchase = $this->database()->select('spack.*, sp.*, ugs.title AS s_title, ugf.title AS f_title, sp.time_stamp AS time_purchased')
            ->from($this->_sTable, 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'spack', 'spack.package_id = sp.package_id')
            ->join(':user_group', 'ugs', 'ugs.user_group_id=spack.user_group_id')
            ->join(':user_group', 'ugf', 'ugf.user_group_id=spack.fail_user_group')
            ->where('sp.purchase_id = ' . (int)$iId . ' AND sp.user_id = ' . (!$sCacheUserId ? Phpfox::getUserId() : $sCacheUserId))
            ->execute('getSlaveRow');

        if (!isset($aPurchase['purchase_id'])) {
            return false;
        }
        $aPurchases[] = $aPurchase;
        $this->_build($aPurchases, $bIsOrder);
        return $aPurchases[0];
    }

    private function &_build(&$aPurchases, $bIsOrder = false)
    {
        foreach ($aPurchases as $iKey => $aPurchase) {
            $aPurchase['time_purchased'] = $aPurchase['time_stamp'];
            $aPurchases[$iKey]['time_purchased'] = $aPurchase['time_stamp'];
            if (!empty($aPurchase['cost']) && Phpfox::getLib('parse.format')->isSerialized($aPurchase['cost'])) {
                $aCosts = unserialize($aPurchase['cost']);
                foreach ($aCosts as $sKey => $iCost) {
                    if (Phpfox::getService('core.currency')->getDefault() == $sKey) {
                        $aPurchases[$iKey]['default_cost'] = $iCost;
                        $aPurchases[$iKey]['default_currency_id'] = $sKey;
                    }
                }
            }

            if ($aPurchase['recurring_period'] > 0 && Phpfox::getLib('parse.format')->isSerialized($aPurchase['recurring_cost'])) {
                $aRecurringCosts = unserialize($aPurchase['recurring_cost']);
                foreach ($aRecurringCosts as $sKey => $iCost) {
                    if (Phpfox::getService('core.currency')->getDefault() == $sKey) {
                        $aPurchases[$iKey]['default_recurring_cost'] = ($bIsOrder ? $iCost : Phpfox::getService('subscribe')->getPeriodPhrase($aPurchase['recurring_period'], $iCost, $aPurchase['default_cost'], $sKey));
                        $aPurchases[$iKey]['default_recurring_currency_id'] = $sKey;
                    }
                }
            }
            switch ($aPurchase['recurring_period']) {
                case 0:
                    $aPurchases[$iKey]['type'] = _p('one_time');
                    break;
                case 1:
                    $aPurchases[$iKey]['type'] = _p('monthly');
                    break;
                case 2:
                    $aPurchases[$iKey]['type'] = _p('quarterly');
                    break;
                case 3:
                    $aPurchases[$iKey]['type'] = _p('biannualy');
                    break;
                case 4:
                    $aPurchases[$iKey]['type'] = _p('annually');
                    break;
                default:
                    $aPurchases[$iKey]['type'] = _p('other');
                    break;
            }
            $aPurchases[$iKey]['user_link'] = Phpfox_Url::instance()->makeUrl('profile');
            $aPurchases[$iKey]['title_parse'] = Phpfox::isPhrase($aPurchase['title']) ? _p($aPurchase['title']) : $aPurchase['title'];
            $aPurchases[$iKey]['s_title'] = Phpfox::isPhrase($aPurchase['s_title']) ? _p($aPurchase['s_title']) : $aPurchase['s_title'];
            $aPurchases[$iKey]['f_title'] = Phpfox::isPhrase($aPurchase['f_title']) ? _p($aPurchase['f_title']) : $aPurchase['f_title'];
            $aPurchases[$iKey]['url_detail'] = Phpfox_Url::instance()->makeUrl('subscribe.view') . '?id=' . $aPurchase['package_id'];
            if (Phpfox::getLib('parse.format')->isSerialized($aPurchase['cost'])) {
                $aCost = unserialize($aPurchase['cost']);
                $iCost = (float)$aCost[$aPurchase['currency_id']];
                $sSymbol = Phpfox::getService('core.currency')->getSymbol($aPurchase['currency_id']);
                $aPurchases[$iKey]['cost_parse'] = !empty($iCost) ? $sSymbol . $iCost : _p('Free');
            }
            if ((int)$aPurchase['recurring_period'] > 0 && (int)$aPurchase['renew_type'] == 2 && (int)$aPurchase['is_removed'] == 0) {
                $iNotifyDays = (int)$aPurchase['number_day_notify_before_expiration'];
                $iNotifyBeforeDate = (int)$aPurchase['expiry_date'] - $iNotifyDays * 24 * 3600; // start notify user
                $canRenewDate = (int)$aPurchase['expiry_date'] + 3 * 24 * 3600; // add 3 day after expired
                if (PHPFOX_TIME >= $iNotifyBeforeDate && PHPFOX_TIME <= $canRenewDate) {
                    $aPurchases[$iKey]['show_renew'] = true;
                }
            }
        }

        return $aPurchases;
    }

    /* This function tells when will a purchased subscription expire */
    public function getExpireTime($iPurchaseId)
    {
        $aPurchase = $this->database()->select('sp.expiry_date, sk.recurring_period')
            ->from(Phpfox::getT('subscribe_purchase'), 'sp')
            ->join(Phpfox::getT('subscribe_package'), 'sk', 'sk.package_id = sp.package_id')
            ->where('sp.purchase_id = ' . (int)$iPurchaseId . ' AND sp.status = "completed"')
            ->order('sp.purchase_id DESC')
            ->execute('getSlaveRow');

        if (empty($aPurchase)) {
            return false;
        }

        if ((int)$aPurchase['recurring_period'] > 0) {
            return $aPurchase['expiry_date'];
        }

        return false;
    }

    public function isCompleteSubscribe()
    {
        $sReq1 = Phpfox_Request::instance()->get('req1');
        $return = true;
        if (!in_array($sReq1, ['subscribe', 'api', 'core'])) {
            $aStatus = $this->database()->select('sp.*')
                ->from(':subscribe_purchase', 'sp')
                ->join(':user_field', 'uf', 'uf.subscribe_id=sp.purchase_id')
                ->where('sp.user_id=' . (int)Phpfox::getUserId())
                ->execute('getSlaveRow');

            if (!isset($aStatus['purchase_id'])) {
                $return = true;//No using subscribe
            } elseif (!isset($aStatus['status'])) {
                $return = $aStatus['purchase_id'];
            } else {
                $return = true;//Status not null
            }
        }

        return $return;
    }

    public function hasAnyPurchases($userId = null)
    {
        empty($userId) && $userId = Phpfox::getUserId();
        $check = db()->select('purchase_id')
                    ->from(':subscribe_purchase')
                    ->where([
                        'user_id' => $userId
                    ])->executeField();
        return !!$check;
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('subscribe.service_purchase_purchase__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}
