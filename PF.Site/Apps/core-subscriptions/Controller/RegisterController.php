<?php

namespace Apps\Core_Subscriptions\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Error;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class RegisterController extends Phpfox_Component
{
    public function process()
    {
        $sCacheUserId = null;
        $aRenewCode = [0, 1, 2];
        $iRenewMethod = $this->request()->get('renew_method');

        if ($this->request()->getInt('login') && Phpfox::getLib('session')->get('cache_user_id')) {
            $sCacheUserId = Phpfox::getLib('session')->get('cache_user_id');
        }

        if (!($aPurchase = Phpfox::getService('subscribe.purchase')->getInvoice($this->request()->getInt('id'), true, $sCacheUserId))) {
            return Phpfox_Error::display(_p('unable_to_find_this_invoice'));
        }

        $requestObject = Phpfox::getLib('request');
        $req2 = $requestObject->get('req2');
        if($req2 == 'register' && ($iPackageId = $this->request()->getInt('id'))) {
            $aRow = db()->select('*')->from(Phpfox::getT('subscribe_purchase'))
                ->where([
                    'purchase_id' => $iPackageId,
                    'user_id' => Phpfox::getUserId()
                ])
                ->executeRow();
            if(!empty($aRow)) {
                $this->template()->assign([
                    'bIsRegister' => true,
                    'bSignUpRequired' => Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up')
                ]);
            }
        }

        if ($aPurchase['recurring_period'] > 0 && empty($iRenewMethod)) {
            Phpfox_Url::instance()->send('subscribe.renew-method', ['id' => $this->request()->getInt('id'), 'login' => $this->request()->get('login')], _p('subscribe_please_choose_renew_method'));
            return false;
        }

        $iRenewMethod = empty($iRenewMethod) ? 0 : $iRenewMethod;

        if (!in_array($iRenewMethod, $aRenewCode) || ((int)$aPurchase['recurring_period'] == 0 && $iRenewMethod != 0) || ((int)$aPurchase['recurring_period'] > 0 && !in_array($iRenewMethod, [1, 2]))) {
            return Phpfox_Error::display(_p('Renew method code is invalid'));
        }


        if (empty($aPurchase['status'])) {
            Phpfox::getService('subscribe.purchase.process')->updateRenewType($aPurchase['purchase_id'], $iRenewMethod);
            $this->setParam('gateway_data', [
                    'item_number' => 'subscribe|' . $aPurchase['purchase_id'],
                    'currency_code' => $aPurchase['default_currency_id'],
                    'amount' => $aPurchase['default_cost'],
                    'item_name' => _p($aPurchase['title']),
                    'return' => $this->url()->makeUrl('subscribe.complete'),
                    'recurring' => !empty($aPurchase['recurring_period']) ? ((int)$iRenewMethod == 2 ? 0 : $aPurchase['recurring_period']) : 0,
                    'recurring_cost' => (isset($aPurchase['default_recurring_cost']) ? $aPurchase['default_recurring_cost'] : ''),
                    'alternative_cost' => $aPurchase['cost'],
                    'alternative_recurring_cost' => $aPurchase['recurring_cost'],
                ]
            );
        }

        if (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_register__1'))) {
            eval($sPlugin);
            if (isset($mReturnPlugin)) {
                return $mReturnPlugin;
            }
        }
        $this->template()->setTitle(_p('membership_packages'))
            ->setBreadCrumb(_p('membership_packages'), $this->url()->makeUrl('subscribe'))
            ->setBreadCrumb(_p('subscriptions'), $this->url()->makeUrl('subscribe.list'))
            ->setBreadCrumb(_p('select_payment_gateway'), null, false)
            ->assign([
                    'aPurchase' => $aPurchase,
                    'sPathSubscribe' =>  Phpfox_Url::instance()->makeUrl('subscribe')
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
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_register_clean')) ? eval($sPlugin) : false);
    }
}