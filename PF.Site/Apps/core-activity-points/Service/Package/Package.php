<?php

namespace Apps\Core_Activity_Points\Service\Package;

use Phpfox;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Package
 * @package Apps\Core_Activity_Points\Service\Package
 */
class Package extends Phpfox_Service
{
    /**
     * Package constructor.
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('activitypoint_package');
    }

    /**
     * Get Infomation of Package
     * @param $iPackageId
     * @return array
     */
    public function getPackage($iPackageId)
    {
        return get_from_cache('activitypoint_point_packages_' . $iPackageId, function () use ($iPackageId) {
            return db()->select('*')
                ->from($this->_sTable)
                ->where('package_id = ' . (int)$iPackageId)
                ->execute('getSlaveRow');
        });
    }

    /**
     * Get Packge with Order
     * @param int $iPage
     * @param int $iSize
     * @param string $sOrder
     * @return array
     */
    public function getForAdmin($iPage = 1, $iSize = 10, $sOrder = '')
    {
        $aRows = [];
        $iCnt = db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->execute('getSlaveField');

        if ($iCnt) {
            $aRows = db()->select('*')
                ->from($this->_sTable)
                ->order($sOrder)
                ->limit($iPage, $iSize)
                ->execute('getSlaveRows');
            $sDefaultCurrency = Phpfox::getService('core.currency')->getDefault();
            foreach ($aRows as $iKey => $aRow) {
                if (!empty($aRow['price']) && Phpfox::getLib('parse.format')->isSerialized($aRow['price'])) {
                    $aPrice = unserialize($aRow['price']);
                    if (is_array($aPrice) && isset($aPrice[$sDefaultCurrency])) {
                        $aRows[$iKey]['default_price'] = $aPrice[$sDefaultCurrency];
                    }
                }
            }
        }
        return [$iCnt, $aRows];
    }

    /**
     * Check if system has any packages
     * @return int
     */
    public function checkEmpty()
    {
        $iCnt = db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->execute('getSlaveField');
        return (int)$iCnt;
    }

    /**
     * Get Packages for user
     * @return array
     */
    public function getPackages()
    {
        $sDefaultCurrency = Phpfox::getService('core.currency')->getDefault();
        $cacheId = 'activitypoint_packages_' . $sDefaultCurrency;
        $aPackages = get_from_cache($cacheId, function () use ($sDefaultCurrency, $cacheId) {
            $aRows = db()->select('*')
                ->from($this->_sTable)
                ->order('points ASC')
                ->where('is_active = 1')
                ->execute('getSlaveRows');
            foreach ($aRows as $sKey => $aRow) {
                if (!empty($aRow['price']) && Phpfox::getLib('parse.format')->isSerialized($aRow['price'])) {
                    $aPrice = unserialize($aRow['price']);
                    if (isset($aPrice[$sDefaultCurrency])) {
                        $aRows[$sKey]['default_price'] = $aPrice[$sDefaultCurrency];
                        $aRows[$sKey]['default_currency_id'] = $sDefaultCurrency;
                    } else {
                        unset($aRows[$sKey]);
                    }
                }
            }

            $this->cache()->group('activitypoint_packages', $cacheId);

            return $aRows;
        });
        return $aPackages;
    }

    /**
     * Get purchase infomation
     * @param $iPurchaseId
     * @return array
     */
    public function getPurchase($iPurchaseId)
    {
        $aRow = db()->select('*')
            ->from(Phpfox::getT('activitypoint_package_purchase'))
            ->where('purchase_id = ' . (int)$iPurchaseId)
            ->execute('getSlaveRow');
        return $aRow;
    }
}