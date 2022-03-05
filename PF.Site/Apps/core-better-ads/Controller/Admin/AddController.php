<?php

namespace Apps\Core_BetterAds\Controller\Admin;

use Admincp_Component_Controller_App_Index;
use Phpfox;
use Phpfox_Error;
use Phpfox_Image_Helper;
use Phpfox_Plugin;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class AddController
 * @package Apps\Core_BetterAds\Controller\Admin
 */
class AddController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_add_process__start')) ? eval($sPlugin) : false);
        $bIsEdit = false;
        $aVals = $this->request()->getArray('val');
        $aAllCountries = Phpfox::getService('ad')->getCountriesAndChildren();

        $this->template()->assign(array('aAllCountries' => $aAllCountries));

        if (($iId = $this->request()->getInt('ads_id'))) {
            if (($aAd = Phpfox::getService('ad.get')->getForEdit($iId))) {
                $bIsEdit = true;
                $this->template()->assign([
                    'aForms' => $aAd,
                    'sCurrentPhoto' => empty($aAd['image_path']) ? '' : Phpfox_Image_Helper::instance()->display([
                        'server_id' => $aAd['server_id'],
                        'path' => 'ad.url_image',
                        'file' => $aAd['image_path'],
                        'suffix' => '',
                        'time_stamp' => true,
                        'return_url' => true
                    ]),
                    'aEditLanguages' => !empty($aAd['languages']) ? explode(',',$aAd['languages']) : []
                ]);

                if (isset($aAd['countries_list']) && !empty($aAd['countries_list'])) {
                    $sCountries = implode('_', $aAd['countries_list']);
                    $aProvinces = array();
                    if (isset($aAd['province']) && !empty($aAd['province'])) {
                        foreach ($aAd['province'] as $sProvince) {
                            $aProvinces[$sProvince] = true;
                        }
                    }

                    $this->template()->setHeader(array(
                        '<script type="text/javascript"> $Behavior.toggleSelected = function(){ $Core.Ads.toggleSelectedCountries("' . $sCountries . '");$Core.Ads.toggleSelectedProvinces(' . json_encode($aProvinces) . ');};  </script>'
                    ));
                } else {
                    $this->template()->setHeader(array(
                        '<script type="text/javascript"> $Behavior.toggleSelectedCountries = function(){ $("#country_iso option:eq(0)").attr("selected", "selected"); }; </script>'
                    ));
                }
            }
        }
        else {
            $this->template()->assign('aForms', [
                'gender' => array_keys(Phpfox::getService('core')->getGenders(true))
            ]);
        }

        $aValidation = array(
            'type_id' => array(
                'title' => _p('better_ads_select_a_banner_type'),
                'def' => 'int'
            ),
            'name' => _p('better_ads_provide_a_name_for_this_campaign'),
            'url_link' => [
                'def' => 'url'
            ],
        );

        $oValidator = Phpfox_Validator::instance()->set(array('sFormName' => 'js_form', 'aParams' => $aValidation));

        if (is_array($aVals) && count($aVals) > 0) {
            if ($aVals['type_id'] == 2) {
                if (empty($aVals['title'])) {
                    Phpfox_Error::set(_p('better_ads_provide_item_for_your_ad', ['item' => _p('better_ads_title')]));
                }
                if (empty($aVals['body'])) {
                    Phpfox_Error::set(_p('better_ads_provide_item_for_your_ad',
                        ['item' => _p('better_ads_body_text')]));
                }
                if (empty($aVals['url_link'])) {
                    Phpfox_Error::set(_p('better_ads_provide_item_for_your_ad',
                        ['item' => _p('better_ads_destination_url')]));
                }
            }

            if ($oValidator->isValid($aVals)) {
                if (!empty($aAd['ads_id'])) {
                    // edit case
                    if (Phpfox::getService('ad.process')->update($aAd['ads_id'], $aVals)) {
                        $this->url()->send('admincp.ad', array('ads_id' => $aAd['ads_id']),
                            _p('better_ads_ad_successfully_updated'));
                    }
                } else {
                    // add new case
                    if (Phpfox::getService('ad.process')->add($aVals)) {
                        $this->url()->send('admincp.ad', null, _p('better_ads_ad_successfully_added'));
                    }
                }
            }

            if (empty($aVals['gender'])) {
                $aVals['gender'] = array_keys(Phpfox::getService('core')->getGenders(true));
            }

            $this->template()->assign('aForms', $aVals);
        }

        $aAge = array();
        $iAgeStart = (int)date('Y') - Phpfox::getParam('user.date_of_birth_end');
        $iAgeEnd = (int)date('Y') - Phpfox::getParam('user.date_of_birth_start');
        for ($i = $iAgeStart; $i <= $iAgeEnd; $i++) {
            $aAge[$i] = $i;
        }
        $aAllPlans = Phpfox::getService('ad.get')->getPlansForAdd();
        $this->template()->setTitle($bIsEdit?_p('edit_ad'):_p('add_new_ad'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('ad'), $this->url()->makeUrl('admincp.ad'))
            ->setBreadCrumb($bIsEdit?_p('edit_ad'):_p('add_new_ad'))
            ->setPhrase(array(
                    'alert',
                    'better_ads_min_age_cannot_be_higher_than_max_age',
                    'better_ads_the_currency_for_your_membership_has_no_price',
                    'better_ads_impressions_cant_be_less_than_a_thousand',
                    'better_ads_select_an_ad_placement',
                    'max_age_cannot_be_lower_than_the_min_age',
                    'please_input_all_required_fields'
                )
            )
            ->assign(array(
                    'aUserGroups' => Phpfox::getService('user.group')->get(),
                    'aAge' => $aAge,
                    'bIsEdit' => $bIsEdit,
                    'sCreateJs' => $oValidator->createJS(),
                    'sGetJsForm' => $oValidator->getJsForm(),
                    'aComponents' => Phpfox::getService('admincp.component')->get(),
                    'aAllPlans' => $aAllPlans,
                    'aLanguages' => Phpfox::getService('language')->getAll(),
                    'sPaymentType' => strtolower(_p('ad'))
                )
            );

        // add action menus
        Phpfox::getService('ad.process')->addActionMenus();

        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_add_process__end')) ? eval($sPlugin) : false);
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_admincp_add_clean')) ? eval($sPlugin) : false);
    }
}
