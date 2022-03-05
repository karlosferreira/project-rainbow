<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class AddController
 * @package Apps\Core_BetterAds\Controller
 */
class AddController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::isUser(true);
        user('better_can_create_ad_campaigns', null, null, true);
        $aAllCountries = Phpfox::getService('ad')->getCountriesAndChildren();

        $bIsEdit = false;
        $bCompleted = $this->request()->get('req3') == 'completed';

        if (($iId = $this->request()->getInt('id')) && ($aAd = Phpfox::getService('ad.get')->getForEdit($iId))) {
            if ($aAd['user_id'] != Phpfox::getUserId()) {
                return Phpfox_Error::display(_p('better_ads_unable_to_edit_purchase_this_ad'));
            }

            if (!$bCompleted) {
                $bIsEdit = true;
            }

            $aAd['country_iso_custom'] = $aAd['country_iso'];

            $this->template()
                ->assign([
                    'aForms' => $aAd,
                    'aAllCountries' => $aAllCountries,
                    'aEditLanguages' => !empty($aAd['languages']) ? explode(',',$aAd['languages']) : []
                ]);
        } else {
            $this->template()->assign('aForms', [
                'gender' => array_keys(Phpfox::getService('core')->getGenders(true))
            ]);
        }

        if ($bIsEdit) {
            $aValidation = array();
        } else {
            $aValidation = array(
                'url_link' => array(
                    'def' => 'url'
                )
            );
        }

        $aValidation['name'] = _p('better_ads_provide_a_campaign_name');
        $aValidation['gender'] = _p('please_select_gender');

        if (!$bIsEdit) {
            $aValidation['total_view'] = _p('better_ads_define_how_many_impressions_for_this_ad');
        }

        $oValidator = Phpfox_Validator::instance()->set(array('sFormName' => 'js_form', 'aParams' => $aValidation));

        if (($aVals = $this->request()->getArray('val'))) {
            $this->template()->assign([
                'isSubmitted' => true
            ]);
            if ($oValidator->isValid($aVals)) {
                if (isset($aVals['location'])) {
                    $aPlan = Phpfox::getService('ad.get')->getPlan($aVals['location']);
                    $aVals = array_merge($aPlan, $aVals);
                }
                if ($bIsEdit) {
                    if ((Phpfox::getService('ad.process')->updateCustom($iId, $aVals))) {
                        $this->url()->send('ad.report', ['ads_id' => $iId], _p('better_ads_ad_successfully_updated'));
                    }
                } else {
                    if ($iId = Phpfox::getService('ad.process')->addCustom($aVals)) {
                        $this->url()->send('ad.add.completed', array('id' => $iId));
                    }
                }
            }
        }

        $aAge = array();
        $iAgeEnd = (int)date('Y') - Phpfox::getParam('user.date_of_birth_start');
        $iAgeStart = (int)date('Y') - Phpfox::getParam('user.date_of_birth_end');

        for ($i = $iAgeStart; $i <= $iAgeEnd; $i++) {
            $aAge[$i] = $i;
        }

        if (($bCompleted || $bIsEdit) && !empty($aAd)) {
            $aPlan = Phpfox::getService('ad.get')->getPlan($aAd['location']);

            if (!isset($aPlan['plan_id'])) {
                return Phpfox_Error::display(_p('better_ads_not_a_valid_ad_plan'));
            }

            // is it free?
            $aCosts = unserialize($aPlan['cost']);
            $bIsFree = true;
            foreach ($aCosts as $sCurrency => $fCost) {
                if ($fCost > 0) {
                    $bIsFree = false;
                    break;
                }
            }
            $this->template()->assign(array('bIsFree' => $bIsFree));
            if ($bIsFree && !$bIsEdit) {
                $this->url()->send('ad.manage', null, _p('better_ads_ad_successfully_added'));
            }
            $fCost = round((float)$aPlan['default_cost'],2);
            $amount = ($fCost * (int)$aAd['total_click']);
            if($aPlan['is_cpm']) {
                $amount = ($fCost * (int)$aAd['total_view']) / 1000;
            }

            $this->setParam('gateway_data', array(
                    'item_number' => 'ad|' . $aAd['ads_id'],
                    'currency_code' => $aPlan['default_currency_id'],
                    'amount' => $amount,
                    'item_name' => $aPlan['title'],
                    'return' => $this->url()->makeUrl('ad.manage', array('view' => 'pending', 'payment' => 'done')),
                    'recurring' => '',
                    'recurring_cost' => '',
                    'alternative_cost' => '',
                    'alternative_recurring_cost' => ''
                )
            );
        }

        $aAllPlans = Phpfox::getService('ad.get')->getPlansForAdd(true);
        $sCurrency = Phpfox::getService('user')->getCurrency();
        if (empty($sCurrency)) {
            $sCurrency = Phpfox::getService('ad.get')->getDefaultCurrency();
        }

        $aLanguages = Phpfox::getService('language')->getAll();

        $this->template()->setTitle(($bIsEdit ? _p('better_ads_updating_an_ad') : _p('add_new_ad')))
            ->setBreadCrumb(_p('better_ads_advertise'), $this->url()->makeUrl('ad'))
            ->setBreadCrumb(($bIsEdit ? _p('better_ads_updating_an_ad') : _p('add_new_ad')),
                $this->url()->makeUrl('ad.add'), true)
            ->setPhrase([
                'better_ads_select_an_ad_placement',
                'better_ads_there_is_minimum_of_1000_impressions',
                'you_cannot_write_more_then_limit_characters',
                'you_have_limit_character_s_left',
                'better_ads_amount_currency_per_1000_impressions',
                'better_ads_amount_currency_per_click',
                'better_ads_min_age_cannot_be_higher_than_max_age',
                'better_ads_impressions',
                'better_ads_clicks',
                'please_input_all_required_fields',
                'alert'
            ])
            ->assign([
                'aAge' => $aAge,
                'bIsEdit' => $bIsEdit,
                'sCreateJs' => $oValidator->createJS(),
                'sGetJsForm' => $oValidator->getJsForm(),
                'bCompleted' => $bCompleted,
                'iPlacementCount' => count($aAllPlans),
                'aAllCountries' => $aAllCountries,
                'bAdvancedAdFilters' => setting('better_ads_advanced_ad_filters'),
                'aAllPlans' => $aAllPlans,
                'sCurrency' => $sCurrency,
                'aLanguages' => $aLanguages,
                'sPaymentType' => strtolower(_p('ad')),
                'userGroups' => Phpfox::getService('user.group')->getAll(),
            ]);

        Phpfox::getService('ad.get')->getSectionMenu();

        return null;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_add_clean')) ? eval($sPlugin) : false);
    }
}
