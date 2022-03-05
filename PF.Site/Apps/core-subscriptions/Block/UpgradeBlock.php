<?php

namespace Apps\Core_Subscriptions\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class UpgradeBlock extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        $bIsThickBox = $this->getParam('bIsThickBox');
        $iRenewType = $this->getParam('iRenewType');
        $iPurchaseId = (int)$this->getParam('iPurchaseId');
        $this->template()->assign(['bIsThickBox' => $bIsThickBox]);
        $bPurchased = false;
        if ($this->request()->getInt('purchase_id') || !empty($iPurchaseId)) {
            $iId = !empty($this->request()->getInt('purchase_id')) ? $this->request()->getInt('purchase_id') : $iPurchaseId;
            if (!($aPackage = Phpfox::getService('subscribe.purchase')->getInvoice($iId, true))) {
                return Phpfox_Error::set(_p('unable_to_find_the_purchase_you_are_looking_for'));
            }
            $iPurchaseId = $aPackage['purchase_id'];
            $bPurchased = $aPackage['status'] == "completed";
            if (((int)Phpfox::getUserBy('user_group_id') == 1) && !$bPurchased) {
                //Admin can't use this feature
                return Phpfox_Error::set(_p('admin_cant_use_this_feature'));
            }
        } else {
            if ((int)Phpfox::getUserBy('user_group_id') == 1) {
                //Admin can't use this feature
                return Phpfox_Error::set(_p('admin_cant_use_this_feature'));
            }

            if (!($aPackage = Phpfox::getService('subscribe')->getPackage($this->request()->getInt('id')))) {
                return Phpfox_Error::set(_p('unable_to_find_the_package_you_are_looking_for'));
            }

            if (Phpfox::getUserBy('user_group_id') == $aPackage['user_group_id']) {
                return Phpfox_Error::set(_p('attempting_to_upgrade_to_the_same_user_group_you_are_already_in'));
            }

            $aPackage['default_currency_id'] = isset($aPackage['default_currency_id']) ? $aPackage['default_currency_id'] : $aPackage['price'][0]['alternative_currency_id'];
            $aPackage['default_cost'] = isset($aPackage['default_cost']) ? $aPackage['default_cost'] : $aPackage['price'][0]['alternative_cost'];

            $iPurchaseId = Phpfox::getService('subscribe.purchase.process')->add([
                    'package_id' => $aPackage['package_id'],
                    'currency_id' => $aPackage['default_currency_id'],
                    'price' => $aPackage['default_cost'],
                    'renew_type' => !empty($iRenewType) ? (int)$iRenewType : 0,
                ]
            );
            /* Make sure we mark it as free only if the default cost is free and its not a recurring charge */
            if ($aPackage['default_cost'] == '0.00') {
                Phpfox::getService('subscribe.purchase.process')->update($iPurchaseId, $aPackage['package_id'], 'completed', Phpfox::getUserId(), $aPackage['user_group_id'], false);
                if ((int)$aPackage['is_recurring'] == 0) {
                    $this->template()->assign('bIsFree', true);
                } else {
                    Phpfox::getService('subscribe.purchase.process')->updatePurchaseForFirstTimeForFreeAndRecurring($iPurchaseId);
                    $sDateTitle = '';
                    switch ((int)$aPackage['recurring_period']) {
                        case 1:
                            {
                                $sDateTitle = _p('subscribe_monthly');
                                break;
                            }
                        case 2:
                            {
                                $sDateTitle = _p('subscribe_quarterly');
                                break;
                            }
                        case 3:
                            {
                                $sDateTitle = _p('subscribe_biannualy');
                                break;
                            }
                        case 4:
                            {
                                $sDateTitle = _p('subscribe_yearly');
                                break;
                            }
                        default:
                            break;
                    }

                    $this->template()->assign([
                        'bIsFirstFree' => true,
                        'sDateTitle' => $sDateTitle
                    ]);
                }
                $this->template()->assign('iPurchaseId', $iPurchaseId);

                return null;
            }
            Phpfox::getService('subscribe.purchase.process')->changePurchaseForSigningUp($iPurchaseId);
        }
        /* Load the gateway only if its not free */
        if (($aPackage['default_cost'] != '0.00' || $aPackage['recurring_period'] != 0) && $iPurchaseId) {
            $this->setParam('gateway_data', [
                    'item_number' => 'subscribe|' . $iPurchaseId,
                    'currency_code' => $aPackage['default_currency_id'],
                    'amount' => ((int)$aPackage['recurring_period'] == 0 ? $aPackage['default_cost'] : ($bPurchased ? $aPackage['default_recurring_cost'] : $aPackage['default_cost'])),
                    'item_name' => _p($aPackage['title']),
                    'return' => $this->url()->makeUrl('subscribe.complete'),
                    'recurring' => !empty($aPackage['recurring_period']) ? ((int)$iRenewType == 2 ? 0 : $aPackage['recurring_period']) : 0,
                    'recurring_cost' => (isset($aPackage['default_recurring_cost']) ? $aPackage['default_recurring_cost'] : ''),
                    'alternative_cost' => (isset($aPackage['price'][0]) ? serialize($aPackage['price']) : ''),
                    'alternative_recurring_cost' => (isset($aPackage['recurring_price'][0]) ? serialize($aPackage['recurring_price']) : '')
                ]
            );
        }
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_block_upgrade_clean')) ? eval($sPlugin) : false);
    }
}