<?php

namespace Apps\Core_BetterAds\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Template;
use Phpfox_Url;

/**
 * Class Get
 * @package Apps\Core_BetterAds\Service
 */
class Callback extends Phpfox_Service
{
    public function __construct()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_callback_construct__start')) ? eval($sPlugin) : false);
    }

    /**
     * Define email notifications
     * @return array
     */
    public function getNotificationSettings()
    {
        return [
            'ad.ad_notifications' => [
                'phrase'  => _p('ad_advertisement_notifications'),
                'default' => 1
            ],
        ];
    }

    /**
     * Handles API callback for payment gateways.
     *
     * @param array $aParams ARRAY of params passed from the payment gateway after a payment has been made.
     * @return bool|null FALSE if payment is not valid|Nothing returned if everything went well.
     * @throws \Exception
     */
    public function paymentApiCallback($aParams)
    {
        define('PHPFOX_API_CALLBACK', true); // used to override security checks in the processes

        if (preg_match('/sponsor/i', $aParams['item_number'])) {
            $isAdSponsor = true;
            // we get the sponsored ad
            $iId = preg_replace("/[^0-9]/", '', $aParams['item_number']);
            $aInvoice = $this->database()->select('*')
                ->from(':better_ads_invoice')
                ->where('invoice_id = ' . $iId . ' AND is_sponsor = 1')
                ->executeRow();

            $aAd = Phpfox::getService('ad.get')->getSponsor($aInvoice['ads_id']);
            $iItemId = $aAd['item_id'];
        } else {
            $isAdSponsor = false;
            $iId = preg_replace("/[^0-9]/", '', $aParams['item_number']);
            $aAd = Phpfox::getService('ad.get')->getForEdit($iId);
            $aInvoice = Phpfox::getService('ad.get')->getInvoice($aAd['ads_id']);
            $iItemId = $aAd['ads_id'];
        }

        if (empty($aAd) || $aAd === false) {
            Phpfox::log('Purchase is not valid');
            return false;
        }

        if (empty($aInvoice) || $aInvoice === false) {
            Phpfox::log('Not a valid invoice');
            return false;
        }

        Phpfox::log('Purchase is valid: ' . var_export($aInvoice, true));

        if ($aParams['status'] == 'completed') {
            if (abs($aParams['total_paid'] - $aInvoice['price']) <= 1) {
                Phpfox::log('Paid correct price');
            } else {
                Phpfox::log('Paid incorrect price');

                return false;
            }
        } else {
            Phpfox::log('Payment is not marked as "completed".');

            return false;
        }

        Phpfox::log('Handling purchase');

        $this->database()->update(":better_ads_invoice", [
            'status' => $aParams['status'],
            'time_stamp_paid' => PHPFOX_TIME
        ], 'invoice_id = ' . $aInvoice['invoice_id']);

        $sModule = isset($aAd['module_id']) ? $aAd['module_id'] : '';
        $sSection = '';
        if (strpos($sModule, '_') !== false) {
            $aModule = explode('_', $sModule);
            $sModule = $aModule[0];
            $sSection = $aModule[1];
        }

        if ($isAdSponsor) { // its a sponsor ad
            $this->database()->update(":better_ads_sponsor", [
                'is_custom' => $aAd['auto_publish'] ? 3 : 2, // 3 means it should be published, 2 means its pending approval
                'is_active' => $aAd['auto_publish'] ? 1 : 0
            ], 'sponsor_id = ' . $aAd['sponsor_id']);

            if (!empty($sModule) && $aAd['auto_publish'] && $sModule != 'feed' && Phpfox::hasCallback($sModule, 'enableSponsor')) {
                Phpfox::callback($sModule . '.enableSponsor', [
                    'item_id' => $iItemId,
                    'section' => $sSection
                ]);
            }

            if (!$aAd['auto_publish'] && isset($aAd['module_id'])) {
                $this->cache()->remove($aAd['module_id'] . '_pending_sponsor');
            }

            if ($aAd['auto_publish']) {
                if ($aAd['module_id'] == 'feed') {
                    $this->cache()->removeGroup('sponsored_feed');
                }
            }
        } else { // is Ad
            $this->database()->update(":better_ads", [
                'is_custom' => $aAd['auto_publish'] ? 3 : 2,
                'is_active' => $aAd['auto_publish'] ? 1 : 0
            ], 'ads_id = ' . $aAd['ads_id']);
        }

        Phpfox::getService('ad.process')->removeAdsCache();
        Phpfox::log('Handling complete');

        if ($sPlugin = Phpfox_Plugin::get('ad.service_callback_purchase_ad_completed')) {
            eval($sPlugin);
        }

        return null;
    }

    public function getUploadParams()
    {
        return Phpfox::getService('ad.get')->getUploadPhotoParams();
    }

    public function pendingApproval()
    {
        return [
            'phrase' => _p('sponsorships'),
            'value' => Phpfox::getService('ad.get')->getPendingSponsorshipsCount(),
            'link' => Phpfox_Url::instance()->makeUrl('admincp.ad.sponsor', ['search[status]' => '2'])
        ];
    }

    public function getAdmincpAlertItems()
    {
        $pending = [];

        $iTotalPending = Phpfox::getService('ad.get')->getPendingSponsorshipsCount();
        if ($iTotalPending > 0) {
            $pending[] = [
                'message' => _p('you_have_total_pending_sponsorships', ['total' => $iTotalPending]),
                'value' => $iTotalPending,
                'link' => Phpfox_Url::instance()->makeUrl('admincp.ad.sponsor', ['search[status]' => '2'])
            ];
        }

        $totalPendingAds = Phpfox::getService('ad.get')->getPendingCount();
        if ($totalPendingAds) {
            $pending[] = [
                'message' => _p(($totalPendingAds == 1) ? 'you_have_total_pending_ad' : 'you_have_total_pending_ads', ['total' => $totalPendingAds]),
                'value' => $totalPendingAds,
                'link' => Phpfox_Url::instance()->makeUrl('admincp.ad', ['search[status]' => '2'])
            ];
        }

        return $pending;
    }

    /**
     * Notification on approve sponsor
     * @param $aNotification
     *
     * @return array
     */
    public function getNotificationApprove_Sponsor($aNotification)
    {
        return [
            'link' => Phpfox_Url::instance()->makeUrl('ad.sponsor', ['view' => $aNotification['item_id']]),
            'message' => _p('your_sponsored_item_has_been_approved'),
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * Notification on approve sponsor
     * @param $aNotification
     *
     * @return array
     */
    public function getNotificationApprove_Item($aNotification)
    {
        return [
            'link' => Phpfox_Url::instance()->makeUrl('ad.report', ['ads_id' => $aNotification['item_id']]),
            'message' => _p('your_ad_has_been_approved'),
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * Notification on approve sponsor
     * @param $aNotification
     *
     * @return array
     */
    public function getNotificationDeny_Item($aNotification)
    {
        return [
            'link' => Phpfox_Url::instance()->makeUrl('ad.report', ['ads_id' => $aNotification['item_id']]),
            'message' => _p('your_ad_has_been_dennied'),
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
    }

    /**
     * Notification on deny sponsor
     * @param $aNotification
     *
     * @return array|bool
     */
    public function getNotificationDeny_Sponsor($aNotification)
    {
        return [
            'link' => Phpfox_Url::instance()->makeUrl('ad.sponsor', ['view' => $aNotification['item_id']]),
            'message' => _p('your_sponsored_item_has_been_denied'),
            'icon' => Phpfox_Template::instance()->getStyle('image', 'activity.png', 'blog')
        ];
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
        if ($sPlugin = Phpfox_Plugin::get('ad.service_callback__call')) {
            return eval($sPlugin);
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);

        return null;
    }
}
