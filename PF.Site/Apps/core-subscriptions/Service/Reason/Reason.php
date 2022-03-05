<?php
namespace Apps\Core_Subscriptions\Service\Reason;

use Phpfox;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

class Reason extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('subscribe_reason');
    }

    public function getReasonForCancelSubscription()
    {
        $aRows = db()->select('*')
            ->from(Phpfox::getT('subscribe_reason'))
            ->where('is_active = 1')
            ->order('ordering ASC')
            ->execute('getSlaveRows');
        foreach($aRows as $iKey => $aRow)
        {
            $aRows[$iKey]['title_parsed'] = _p($aRow['title']);
        }
        return $aRows;
    }

    public function getReasonOptions($iReasonId)
    {
        $aRows = db()->select('*')
            ->from(Phpfox::getT('subscribe_reason'))
            ->where('reason_id != '.$iReasonId.' AND is_default = 0')
            ->order('ordering ASC')
            ->execute('getSlaveRows');
        foreach($aRows as $iKey => $aRow)
        {
            $aRows[$iKey]['title_parsed'] = _p($aRow['title']);
        }
        return $aRows;
    }

    public function getReasonSearchForAdmin($aConditions = [], $sSort = null, $iPage = null, $iPageSize)
    {
        $sWhere = '';
        foreach($aConditions as $value)
        {
            $sWhere.= $value;
        }
        $aReasons = array();
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('subscribe_reason'))
            ->execute('getSlaveField');

        if ($iCnt)
        {
            $aReasons = db()->select('sr.*, COUNT(scr.reason_id) AS total')
                ->from(Phpfox::getT('subscribe_reason'),'sr')
                ->leftJoin(Phpfox::getT('subscribe_cancel_reason'),'scr', 'scr.reason_id = sr.reason_id '.$sWhere)
                ->group('sr.reason_id')
                ->order($sSort)
                ->limit($iPage, $iPageSize, $iCnt)
                ->execute('getSlaveRows');
            foreach($aReasons as $iKey => $aReason)
            {
                $aReasons[$iKey]['title_parsed'] =_p($aReason['title']);
            }
        }

        return array($iCnt, $aReasons);
    }

    public function getReasonById($iReasonId)
    {
        $aRow = db()->select('*')
            ->from(Phpfox::getT('subscribe_reason'))
            ->where('reason_id = ' . $iReasonId)
            ->execute('getSlaveRow');
        if(!empty($aRow['title']))
        {
            $aRow['title_parsed'] = _p($aRow['title']);
        }

        return $aRow;
    }

    public function getReason($iPurchaseId)
    {
        $aRow = db()->select('sr.title, scr.*')
            ->from(Phpfox::getT('subscribe_cancel_reason'), 'scr')
            ->join(Phpfox::getT('subscribe_reason'),'sr', 'sr.reason_id = scr.reason_id')
            ->where('scr.purchase_id = '.$iPurchaseId)
            ->execute('getSlaveRow');
        $aRow['title_parsed'] = _p($aRow['title']);
        return $aRow;
    }

    public function cancelSubscriptionByUser($iPurchaseId, $iUserGroupFailure,$iUserId, $iPackageId, $iReasonId)
    {
        if (db()->update(Phpfox::getT('subscribe_purchase'),['status' => 'cancel', 'time_stamp' => PHPFOX_TIME], ['purchase_id' => $iPurchaseId])) {
            Phpfox::getService('user.process')->updateUserGroup($iUserId, $iUserGroupFailure);
            $aPackage = Phpfox::getService('subscribe')->getPackage($iPackageId);
            $aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($iPurchaseId);
            if((int)$aPackage['is_removed'] == 0) {
                db()->updateCount('subscribe_purchase', 'package_id = '.$iPackageId.' AND status = "completed"','total_active', 'subscribe_package', 'package_id = '.$iPackageId);
            }
            Phpfox::getService('subscribe.purchase.process')->addRecentPurchase([
                'purchase_id' => $iPurchaseId,
                'status' => 'cancel',
                'time_stamp' => PHPFOX_TIME,
                'currency_id' => $aPurchase['currency_id'],
                'payment_method' => $aPurchase['payment_method'],
                'transaction_id' => $aPurchase['transaction_id'],
            ]);
            db()->insert(Phpfox::getT('subscribe_cancel_reason'), [
                'purchase_id' => $iPurchaseId,
                'reason_id' => $iReasonId,
                'time_stamp' => PHPFOX_TIME
            ]);
            $callbackModule = Phpfox::getService('subscribe')->getModuleIdByGateway($aPurchase['payment_method']);
            if (!empty($callbackModule) && Phpfox::isModule($callbackModule)
                && Phpfox::hasCallback($callbackModule, 'cancelSubscription')) {
                Phpfox::callback($callbackModule . '.cancelSubscription', [
                    'purchase_id' => $iPurchaseId,
                    'payment_method' => $aPurchase['payment_method'],
                ]);
            }
        }
    }
}