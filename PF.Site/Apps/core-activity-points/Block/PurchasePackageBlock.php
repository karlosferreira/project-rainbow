<?php

namespace Apps\Core_Activity_Points\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class PurchasePackageBlock
 * @package Apps\Core_Activity_Points\Block
 */
class PurchasePackageBlock extends Phpfox_Component
{
    public function process()
    {
        $iPackageId = $this->getParam('iPackageId');
        $aPackage = Phpfox::getService('activitypoint.package')->getPackage($iPackageId);
        if (empty($aPackage)) {
            return Phpfox_Error::set(_p('Invalid Package'));
        }
        $aPrice = unserialize($aPackage['price']);
        $sDefaultCurrency = Phpfox::getService('core.currency')->getDefault();
        $iPurchaseId = Phpfox::getService('activitypoint.package.process')->createPurchase($aPackage['package_id'], Phpfox::getUserId(), $sDefaultCurrency, $aPackage['points']);
        $this->setParam('gateway_data', array(
                'item_number' => 'activitypoint|' . $iPurchaseId,
                'currency_code' => $sDefaultCurrency,
                'amount' => $aPrice[$sDefaultCurrency],
                'item_name' => _p($aPackage['title']),
                'return' => $this->url()->makeUrl('activitypoint.complete'),
                'recurring' => 0,
                'recurring_cost' => '',
                'alternative_cost' => $aPackage['price'],
                'alternative_recurring_cost' => '',
                'no_purchase_with_points' => true
            )
        );
    }
}