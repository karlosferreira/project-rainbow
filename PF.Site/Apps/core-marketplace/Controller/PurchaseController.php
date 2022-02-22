<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Marketplace\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');


class PurchaseController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::isUser(true);
        $bInvoice = ($this->request()->get('invoice') ? true : false);
        $iId = $this->request()->get('id');
        $aListing = null;
        if ($iId) {
            $aListing = Phpfox::getService('marketplace')->getForEdit($iId, true);
        }
        if ($bInvoice) {
            if (($aInvoice = Phpfox::getService('marketplace')->getInvoice($this->request()->get('invoice')))) {
                if ($aInvoice['user_id'] != Phpfox::getUserId()) {
                    return Phpfox_Error::display(_p('unable_to_purchase_this_item'));
                }

                $iId = $aInvoice['listing_id'];
                $aListing = Phpfox::getService('marketplace')->getForEdit($iId, true);
                $aUserGateways = Phpfox::getService('api.gateway')->getUserGateways($aInvoice['marketplace_user_id']);
                $aActiveGateways = Phpfox::getService('api.gateway')->getActive();
                $aPurchaseDetails = [
                    'item_number' => 'marketplace|' . $aInvoice['invoice_id'],
                    'currency_code' => $aInvoice['currency_id'],
                    'amount' => $aInvoice['price'],
                    'item_name' => $aInvoice['title'],
                    'return' => $this->url()->makeUrl('marketplace.invoice', ['payment' => 'done']),
                    'recurring' => '',
                    'recurring_cost' => '',
                    'alternative_cost' => '',
                    'alternative_recurring_cost' => ''
                ];

                // allow payment gateways // PayPal, ..
                $allowGateway = true;
                if (isset($aListing['is_sell']) && $aListing['is_sell'] == 0) {
                    $allowGateway = false;
                }
                // allow activity point payment
                if (isset($aListing['allow_point_payment']) && $aListing['allow_point_payment'] == 0) {
                    $aPurchaseDetails['no_purchase_with_points'] = true;
                }

                if (is_array($aUserGateways) && count($aUserGateways)) {
                    foreach ($aUserGateways as $sGateway => $aData) {
                        if ($allowGateway && is_array($aData['gateway'])) {
                            //Paypal email is required
                            if ($sGateway == 'paypal' && (empty($aData['gateway']['paypal_email']) || !filter_var($aData['gateway']['paypal_email'], FILTER_VALIDATE_EMAIL))) {
                                $aPurchaseDetails['fail_' . $sGateway] = true;
                                continue;
                            }
                            foreach ($aData['gateway'] as $sKey => $mValue) {
                                $aPurchaseDetails['setting'][$sKey] = $mValue;
                            }
                        } else {
                            $aPurchaseDetails['fail_' . $sGateway] = true;
                            continue;
                        }

                        (($sPlugin = Phpfox_Plugin::get('marketplace.controller_purchase_validate_user_gateways')) ? eval($sPlugin) : false);

                        // Payment gateways added after user configured their payment gateway settings
                        if (empty($aActiveGateways)) {
                            continue;
                        }
                        $bActive = false;
                        foreach ($aActiveGateways as $aActiveGateway) {
                            if ($sGateway == $aActiveGateway['gateway_id']) {
                                $bActive = true;
                            }
                        }
                        if (!$bActive) {
                            $aPurchaseDetails['fail_' . $sGateway] = true;
                        }
                    }
                } else {
                    //Disable all gateways
                    foreach ($aActiveGateways as $aActiveGateway) {
                        $aPurchaseDetails['fail_' . $aActiveGateway['gateway_id']] = true;
                    }
                }
                $this->setParam('gateway_data', $aPurchaseDetails);
            }
        }

        if (!$aListing) {
            return Phpfox_Error::display(_p('unable_to_find_the_listing_you_are_looking_for'));
        }

        if ($this->request()->get('process')) {
            if (($iInvoice = Phpfox::getService('marketplace.process')->addInvoice($aListing['listing_id'],
                $aListing['currency_id'], $aListing['price']))
            ) {
                $this->url()->send('marketplace.purchase', ['invoice' => $iInvoice]);
            }
        }

        $this->template()->setTitle(_p('review_and_confirm_purchase'))
            ->setBreadCrumb(_p('marketplace'), $this->url()->makeUrl('marketplace'))
            ->setBreadCrumb(_p('review_and_confirm_purchase'), null, false)
            ->assign([
                    'aListing' => $aListing,
                    'bInvoice' => $bInvoice
                ]
            );
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('marketplace.component_controller_purchase_clean')) ? eval($sPlugin) : false);
    }
}