<?php
namespace Apps\Core_Activity_Points\Service;

use Phpfox;
use Phpfox_Plugin;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Callback
 * @package Apps\Core_Activity_Points\Service
 */
class Callback extends Phpfox_Service
{
    /**
     * Handling payment gateway
     * @param $aParams
     * @return bool|null
     */
    public function paymentApiCallback($aParams)
    {
        Phpfox::log('App ActivityPoint callback recieved: ' . var_export($aParams, true));

        if(empty($aPurchase = Phpfox::getService('activitypoint.package')->getPurchase($aParams['item_number'])) || empty($aPurchase['purchase_id'])) {
            Phpfox::log('Purchase not found');
            return false;
        } elseif ($aPurchase['status'] == 'completed') {
            Phpfox::log('Purchase has been already completed');
            return false;
        }

        Phpfox::log('Purchase is valid');

        if($aParams['status'] != "completed") {
            Phpfox::log('Status is invalid');
            return false;
        }

        Phpfox::getService('activitypoint.package.process')->processPurchase($aParams, $aPurchase);

        (($sPlugin = Phpfox_Plugin::get('activitypoint.service_callback_purchase_points_completed')) ? eval($sPlugin) : false);

        Phpfox::log('Purchase activitypoint package is completed.');

        return null;
    }
}