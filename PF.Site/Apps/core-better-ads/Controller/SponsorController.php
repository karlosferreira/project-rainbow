<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Database;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class SponsorController
 * @package Apps\Core_BetterAds\Controller
 */
class SponsorController extends Phpfox_Component
{
    public function process()
    {
        // Updating clicks and redirecting. No page is shown because its a redirect
        if (($this->request()->getInt('view')) != 0) {
            $this->_viewSponsorItem();

            return 'controller';
        }

        // Check if a user
        Phpfox::isUser(true);

        // process payment
        if ($this->request()->getInt('pay')) {
            $this->_processPayment();

            return 'controller';
        }

        // edit sponsor
        if ($this->request()->get('edit')) {
            $this->_showEditForm();

            return 'controller';
        }

        $aVals = $this->request()->getArray('val');
        // process edit
        if ($aVals && !empty($aVals['sponsor_id'])) {
            if (Phpfox::getService('ad.process')->updateSponsor($aVals)) {
                Phpfox::addMessage(_p('sponsor_successfully_edited'));
                $this->url()->send('ad.manage-sponsor');
            }

            return 'controller';
        }

        // define default values
        $sSection = $sModule = $this->request()->get('section');
        $sFunction = 'getToSponsorInfo';
        $sStatus = $this->request()->get('status');

        $bIsSponsorInFeed = false;
        if ($this->request()->get('where') == 'feed') {
            $bIsSponsorInFeed = true;
        }

        $aSection = [];
        if (strpos($sSection, '_') !== false) {
            $aSection = explode('_', $sSection);
            $sModule = reset($aSection);
        }
        $sItem = count($aSection) == 2 ? $aSection[1] : '';

        if ($sItem) {
            $sFunction = $sFunction . ucfirst($sItem);
        }

        // Sponsoring posts in the feed
        if ($bIsSponsorInFeed) {
            $this->template()->assign(array('sFormerModule' => $this->request()->get('section')));
            $sFunction = 'getSponsorPostInfo';
            $sModule = $sSection = 'feed';
            $sItem = '';
        }

        if (Phpfox::hasCallback($sModule, $sFunction)) {
            $item = $bIsSponsorInFeed ? [
                'iItemId' => $this->request()->getInt('item'),
                'sModule' => $this->request()->get('section')
            ] : $this->request()->get('req3');

            $aItem = Phpfox::callback($sModule . '.' . $sFunction, $item);
            if (empty($aItem)) {
                return Phpfox_Error::display(_p('module_is_not_a_valid_module', array('module' => $sModule)));
            }

            if (!empty($aItem['error'])) {
                return Phpfox_Error::display($aItem['error']);
            }
            // check that the user viewing is either the owner of the item or an admin
            if ((!$bIsSponsorInFeed && $aItem['user_id'] != Phpfox::getUserId()) || ($bIsSponsorInFeed && ($aItem['user_id'] != Phpfox::getUserId()) && !Phpfox::getUserParam('feed.can_sponsor_feed'))) {
                return Phpfox_Error::display(_p('sponsor_error_owner'));
            }

            if ($sItem) {
                $aPrices = Phpfox::getUserParam($sModule . '.' . $sModule . '_' . $sItem . '_sponsor_price');
                $bWithoutPaying = Phpfox::getUserParam($sModule . '.can_sponsor_' . $sItem);
            } else {
                $aPrices = Phpfox::getUserParam($sModule . '.' . $sModule . '_sponsor_price');
                $bWithoutPaying = Phpfox::getUserParam($sModule . '.can_sponsor_' . $sModule);
            }

            if (is_array($aPrices)) {
                if (!isset($aPrices[Phpfox::getService('core.currency')->getDefault()])) {
                    return Phpfox_Error::display(_p('the_default_currency_has_no_price'));
                }
                $aItem['ad_cost'] = $aPrices[Phpfox::getService('core.currency')->getDefault()];
            } elseif (is_numeric($aPrices) && $aPrices >= 0) {
                $aItem['ad_cost'] = $aPrices;
            } else {
                return Phpfox_Error::display(_p('the_currency_for_your_membership_has_no_price'));
            }

            $aItem['name'] = $aItem['title'];
            $aItem['default_cost'] = isset($aItem['cpm']) ? $aItem['cpm'] : $aItem['ad_cost'];

            if((float)$aItem['default_cost'] <= 0) {
                $bWithoutPaying = true;
            }

            if (($sWhere = $this->request()->get('where')) != '') {
                $aItem['where'] = $sWhere;
            }

            if (($iItemId = $this->request()->get('item')) != '') {
                $aItem['item_id'] = $iItemId;
            }
        } else {
            return Phpfox_Error::display(_p('better_ads_module_is_not_a_valid_module', ['module' => $sModule]));
        }

        // PROCESS SUBMIT
        if ($aVals) {
            if ($bIsSponsorInFeed && Phpfox::isModule('feed')) {
                // get "feed" item_id instead of original "module name" item_id
                $aNewItemId = Phpfox::getService('feed')->getForItem($this->request()->get('section'), $this->request()->getInt('item'));
                if (!empty($aNewItemId)) {
                    // correct "feed" item_id
                    $aVals['item_id'] = $aNewItemId['feed_id'];
                }
                $aVals['module'] = 'feed'; // assign feed as module instead of original

                // Can sponsor without paying? (note that param is not plural)
                if ($bWithoutPaying) {
                    // add the sponsor
                    if (Phpfox::getService('ad.process')->addSponsor($aVals)) {
                        Phpfox::addMessage(_p('better_ads_finished'));
                        $this->url()->send('ad.manage-sponsor');
                    }
                } else {
                    if ($iInvoiceId = Phpfox::getService('ad.process')->addSponsor($aVals)) {
                        $this->url()->send('ad.sponsor', ['pay' => $iInvoiceId]);
                    }
                }
            } else {
                // validate values
                if (!isset($aVals['module'])) {
                    $aVals['module'] = $sModule;
                }
                if (!isset($aVals['item_id']) && isset($iItemId)) {
                    $aVals['item_id'] = $iItemId;
                }
                if ($sItem) {
                    $aVals['section'] = $sItem;
                }

                // if price is 0
                if (empty($aVals['ad_cost'])) {
                    // Payment completed: no payment required
                    // add the sponsor
                    $aVals['is_active'] = true;
                    if (Phpfox::getService('ad.process')->addSponsor($aVals)) {
                        Phpfox::addMessage(_p('better_ads_finished'));
                        $this->url()->send('ad.manage-sponsor');
                    }
                } else {
                    if (!isset($aVals['total_view']) || ($aVals['total_view'] != 0 && $aVals['total_view'] < 1000)) {
                        Phpfox_Error::set(_p('better_ads_impressions_cant_be_less_than_a_thousand'));
                    }

                    if (!isset($aVals['name']) || empty($aVals['name'])) {
                        Phpfox_Error::set(_p('better_ads_provide_a_campaign_name'));
                    }

                    if (Phpfox_Error::isPassed()) {
                        if ($iInvoiceId = Phpfox::getService('ad.process')->addSponsor($aVals)) {
                            $this->url()->send('ad.sponsor', ['pay' => $iInvoiceId]);
                        }
                    }
                }
            }
        }
        else {
            $aItem['gender'] = array_keys(Phpfox::getService('core')->getGenders(true));
        }

        $sTitle = $bIsSponsorInFeed ? _p('sponsor_in_feed') : _p('sponsor_item');
        $this->template()->setTitle($sTitle)
            ->setBreadCrumb(_p('better_ads_advertise'), $this->url()->makeUrl('ad'))
            ->setBreadCrumb($sTitle, '', true)
            ->assign([
                'sStatus' => $sStatus,
                'sModule' => $sModule . ($sItem ? '_' . $sItem : ''),
                'iId' => $this->request()->get('req3', null),
                'aForms' => $aItem,
                'aAge' => $this->_getAgeRange(),
                'currency_code' => Phpfox::getService('ad.get')->getDefaultCurrency(),
                'bWithoutPaying' => $bWithoutPaying,
                'aAllCountries' => Phpfox::getService('ad')->getCountriesAndChildren(),
                'bAdvancedAdFilters' => setting('better_ads_advanced_ad_filters'),
                'bNotShowStateProvince' => true,
                'aLanguages' => Phpfox::getService('language')->getAll(),
                'sPaymentType' => strtolower(_p('better_ads_sponsorship'))
            ]);
        Phpfox::getService('ad.get')->getSectionMenu();

        return 'controller';
    }

    private function _getAgeRange()
    {
        $aAge = [];
        $iAgeDiff = date('Y') - Phpfox::getParam('user.date_of_birth_start');
        for ($i = 0; $i <= Phpfox::getParam('user.date_of_birth_end') - Phpfox::getParam('user.date_of_birth_start'); $i++) {
            $aAge[Phpfox::getParam('user.date_of_birth_end') + $i] = $iAgeDiff - $i;
        }
        asort($aAge);

        return $aAge;
    }

    private function _processPayment()
    {
        $iInvoiceId = $this->request()->getInt('pay');
        $adsId = db()->select('ads_id')
            ->from(':better_ads_invoice')
            ->where('invoice_id = ' . $iInvoiceId . ' AND is_sponsor = 1')
            ->executeField();
        $aVals = Phpfox::getService('ad.get')->getSponsor($adsId, Phpfox::getUserId(), $iInvoiceId);
        if(!$aVals) {
            $this->url()->send('ad.manage-sponsor');
        }
        $fTotalCost = $aVals['price'];
        $this->template()->assign(array('iInvoice' => $iInvoiceId));
        $this->setParam('gateway_data', array(
                'item_number' => 'ad|' . $iInvoiceId . '-sponsor',
                'currency_code' => Phpfox::getService('ad.get')->getDefaultCurrency(),
                'amount' => $fTotalCost,
                'item_name' => $aVals['campaign_name'], // this is for paypal
                'return' => $this->url()->makeUrl('ad.invoice'), // dummy page to say it all went fine
                'recurring' => 0,
                'recurring_cost' => '',
                'alternative_cost' => 0,
                'alternative_recurring_cost' => 0,
            )
        );
    }

    /**
     */
    private function _viewSponsorItem()
    {
        $iView = $this->request()->getInt('view');
        $aSponsor = Phpfox::getService('ad.get')->getSponsor($iView);
        // split the module if there's a subsection
        $sModule = $aSponsor['module_id'];
        $sSection = '';
        if (strpos($aSponsor['module_id'], '_') !== false) {
            $aModule = explode('_', $aSponsor['module_id']);
            $sModule = $aModule[0];
            $sSection = $aModule[1];
        }

        if (Phpfox::isModule($sModule)) {
            $sLink = Phpfox::getService('ad.sponsor')->getLink($sModule,
                ['item_id' => $aSponsor['item_id'], 'section' => $sSection]);
            // Update the counter of views (do we need the track table here?)
            // do not log clicks if its an Admin or the creator of the sponsor ad
            if ($aSponsor['user_id'] != Phpfox::getUserId()) {
                Phpfox_Database::instance()->update(':better_ads_sponsor',
                    ['total_click' => $aSponsor['total_click'] + 1],
                    'sponsor_id = ' . $aSponsor['sponsor_id'] . ' AND user_id != ' . Phpfox::getUserId());
            }
            $this->url()->send($sLink);
        } else {
            Phpfox_Error::display(_p('better_ads_module_is_not_a_valid_module', ['module' => $sModule]));
        }
    }

    private function _showEditForm()
    {
        $iSponsorId = $this->request()->get('edit');
        // get sponsor
        $aSponsor = Phpfox::getService('ad.get')->getSponsor($iSponsorId, Phpfox::getUserId());

        if ($aSponsor === false) {
            return Phpfox_Error::display(_p('cannot_edit_this_sponsor'));
        }

        $aSponsor['gender'] = explode(',', $aSponsor['gender']);
        $aSponsor['countries_list'] = explode(',', $aSponsor['country_iso']);


        $this->template()->assign([
            'bIsEdit' => true,
            'aForms' => $aSponsor,
            'aAllCountries' => Phpfox::getService('ad')->getCountriesAndChildren(),
            'bAdvancedAdFilters' => setting('better_ads_advanced_ad_filters'),
            'bNotShowStateProvince' => true,
            'aAge' => $this->_getAgeRange(),
            'aLanguages' => Phpfox::getService('language')->getAll(),
            'sPaymentType' => strtolower(_p('better_ads_sponsorship')),
            'aEditLanguages' => !empty($aSponsor['languages']) ? explode(',',$aSponsor['languages']) : []
        ])->setTitle(_p('edit_sponsor'))->setBreadCrumb(_p('edit_sponsor'), '', true);
        Phpfox::getService('ad.get')->getSectionMenu();

        return true;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_sponsor_clean')) ? eval($sPlugin) : false);
    }
}
