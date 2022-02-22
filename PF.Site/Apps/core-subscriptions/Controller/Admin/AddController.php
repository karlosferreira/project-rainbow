<?php
namespace Apps\Core_Subscriptions\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Locale;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class AddController extends Phpfox_Component
{
    public function process()
    {
        $bIsEdit = false;
        if (($iId = $this->request()->getInt('id'))) {
            if (($aPackage = Phpfox::getService('subscribe')->getForEdit($iId))) {
                $aPackage['visible_group'] = Phpfox::getLib('parse.format')->isSerialized($aPackage['visible_group']) ? unserialize($aPackage['visible_group']) : $aPackage['visible_group'];
                $bIsEdit = true;
                $this->template()->assign([
                    'aForms'=> $aPackage,
                    'sPhraseTitle'=>$aPackage['title'],
                    'sPhraseDescription'=>$aPackage['description'],
                    'bDisableField' => Phpfox::getService('subscribe')->checkNumbersOfSubscription($iId),
                    'bIsEdit' => $bIsEdit,
                    'iEditId' => $iId
                ]);
                $this->setParam('currency_value_val[cost]', unserialize($aPackage['cost']));
                if (!empty($aPackage['recurring_cost'])) {
                    $this->setParam('currency_value_val[recurring_cost]', unserialize($aPackage['recurring_cost']));
                    $aVisibleMethods = Phpfox::getService('subscribe')->getVisiblePaymentMethods($iId);
                    $aMethodsNames = array_column($aVisibleMethods, 'name');
                    $this->template()->assign([
                        'aMethodsNames' => $aMethodsNames
                    ]);
                }
            }
        }
        $aLang = Phpfox::getService('language')->getAll(true);
        $aDefaultLanguage  = array_shift($aLang);
        $aUserGroups = Phpfox::getService('user.group')->get();
        $aPaymentMethods = Phpfox::getService('subscribe')->generatePaymentMethods();
        $this->template()->assign([
            'aUserGroups' => $aUserGroups,
            'sDefaultLanguage' => $aDefaultLanguage['language_id'],
            'aPaymentMethods' => $aPaymentMethods
        ]);

        if (($aVals = $this->request()->getArray('val'))) {
            $iHasPriceCount = 0;
            $iFreeCount = 0;

            foreach($aVals['cost'] as $iCost)
            {
                if($iCost != '0.00')
                {
                    $iHasPriceCount++;
                }
                else
                {
                    $iFreeCount++;
                }
            }

            $bInvalidPrice = false;

            if($iHasPriceCount > 0 && $iFreeCount > 0)
            {
                Phpfox_Error::set(_p('subscribe_check_cost_title'));
                $bInvalidPrice = true;
            }

            if(!empty($aVals['is_recurring']))
            {
                $iPeriodDays = 0;
                switch ($aVals['recurring_period']){
                    case 1:
                        $iPeriodDays = 30;
                        break;
                    case 2:
                        $iPeriodDays = 90;
                        break;
                    case 3:
                        $iPeriodDays= 180;
                        break;
                    case 4:
                        $iPeriodDays = 365;
                        break;
                    default:
                        break;
                }

                if (empty($aVals['allow_payment_methods'])) {
                    Phpfox_Error::set(_p('must_provide_at_least_1_payment_method'));
                }

                if( !empty($aVals['number_day_notify_before_expiration']) && (int)$aVals['number_day_notify_before_expiration'] >= $iPeriodDays)
                {
                    Phpfox_Error::set(_p('Number of days to notice user must be smaller than recurring period'));
                }

                foreach($aVals['recurring_cost'] as $sId => $sValue)
                {
                    if(empty($sValue))
                    {
                        Phpfox_Error::set(_p('recurring_cost_empty_error',[
                            'currency_title' => $sId
                        ]));
                    }
                }

                $bCheckFree = ($iHasPriceCount == 0 && $iFreeCount > 0) || !empty($aVals['is_free']);

                if(($bCheckFree && (int)$aVals['is_recurring'] == 1) && !$bInvalidPrice)
                {
                    Phpfox_Error::set(_p('We have not supported package which is free for the first payment and then recurring payment'));
                }
            }
            if(!Phpfox_Error::isPassed())
            {
                $this->template()->setHeader('cache',[
                    'jscript/admincp/admincp.js' => 'app_core-subscriptions',
                    'colorpicker/js/colpick.js' => 'static_script',
                    'head' => ['colorpicker/css/colpick.css' => 'static_script'],
                ])->assign([
                    'aForms' => $aVals
                ]);
                $this->setParam('currency_value_val[cost]', $aVals['cost']);
                if (!empty($aVals['recurring_cost'])) {
                    $this->setParam('currency_value_val[recurring_cost]', $aVals['recurring_cost']);
                }
                return false;
            }

            if ($bIsEdit) {
                if (isset($aPackage['package_id']) && Phpfox::getService('subscribe.process')->update($aPackage['package_id'], $aVals)) {
                    $this->url()->send('admincp.subscribe.add', array('id' => $aPackage['package_id']),
                        _p('package_successfully_update'));
                }
            } else {
                if (Phpfox::getService('subscribe.process')->add($aVals)) {
                    $this->url()->send('admincp.subscribe', null, _p('package_successfully_added'));
                }
            }
        }


        $this->template()
            ->setTitle(($bIsEdit ? (_p('editing_subscription_package') . ': ' . (isset($aPackage['title']) ? $aPackage['title'] : '')) : _p('create_new_subscription_package')))
            ->setBreadCrumb(_p('apps'),$this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('subscriptions'),$this->url()->makeUrl('admincp.subscribe'))
            ->setBreadCrumb(($bIsEdit ? _p('editing') . ': ' . Phpfox_Locale::instance()->convert($aPackage['title']) : _p('create_new_subscription_package')), null, true)
            ->setActiveMenu('admincp.member.subscribe')
            ->setHeader('cache', [
                'jscript/admincp/admincp.js' => 'app_core-subscriptions',
                'colorpicker/js/colpick.js' => 'static_script',
                'head' => ['colorpicker/css/colpick.css' => 'static_script'],
            ]);
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('subscribe.component_controller_admincp_add_clean')) ? eval($sPlugin) : false);
    }
}
