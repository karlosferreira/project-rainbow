<?php

namespace Apps\Core_BetterAds\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Parse_Input;
use Phpfox_Plugin;
use Phpfox_Request;
use Phpfox_Url;
use Phpfox_Validator;

/**
 * Class Process
 * @package Apps\Core_BetterAds\Service
 */
class Process extends \Phpfox_Service
{
    /**
     * Class constructor.
     */
    public function __construct()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_process_construct__start')) ? eval($sPlugin) : false);

        $this->_sTable = ':better_ads';
    }

    /**
     * @param $Ids
     */
    public function updateAdStatusForCpm($Ids)
    {
        if (!empty($Ids)) {
            $Ids = trim($Ids, ',');
            $viewedAds = db()->select('a.count_view, a.total_view, a.ads_id, ap.block_id AS location')
                ->from($this->_sTable, 'a')
                ->join(':better_ads_plan', 'ap', 'ap.plan_id = a.location')
                ->where('ads_id IN (' . $Ids . ')')
                ->execute('getSlaveRows');
            $updatedIds = '';
            $locations = [];
            foreach ($viewedAds as $viewedAd) {
                if ($viewedAd['count_view'] >= $viewedAd['total_view']) {
                    $updatedIds .= $viewedAd['ads_id'] . ',';
                    $locations[$viewedAd['location']] = 1;
                }
            }

            if (!empty($updatedIds)) {
                $updatedIds = trim($updatedIds, ',');
                db()->update($this->_sTable, ['is_active' => 0, 'is_custom' => 5], 'ads_id IN (' . $updatedIds . ')');
                foreach ($locations as $locationId => $location) {
                    $this->cache()->remove('block_' . $locationId . '_ads');
                }
            }
        }
    }

    /**
     * Creates a record of the purchase into phpfox_ad_sponsor and returns the ID
     * to be used as an invoice with the payment gateway.
     *
     * @example if admin is adding, aVals looks like: array('module' => 'music', 'section' => 'album', 'item_id' => $this->get('album_id'))
     * @param array $aVals
     * @param bool $bValidate
     *
     * @return int
     * @throws \Exception
     */
    public function addSponsor($aVals, $bValidate = true)
    {
        if (!PHPFOX_IS_AJAX && $bValidate) {
            // check required fields
            $aForms = [
                'name' => [
                    'message' => _p('better_ads_provide_a_campaign_name'),
                    'type' => ['string:required']
                ],
                'total_view' => [
                    'message' => _p('better_ads_impressions_cant_be_less_than_a_thousand'),
                    'type' => 'int:required'
                ],
                'gender' => [
                    'message' => _p('please_select_gender'),
                    'type' => 'array:required'
                ],
            ];

            Phpfox_Validator::instance()->process($aForms, $aVals);
            if (!Phpfox_Error::isPassed()) {
                return false;
            }
        }

        $sModule = $aVals['module'];
        $sSection = !empty($aVals['section']) ? $aVals['section'] : '';
        $aPrices = Phpfox::getUserParam($sModule . '.' . $sModule . (!empty($sSection) ? "_$sSection" : '') . '_sponsor_price');

        if (empty($sSection)) {
            $iAutoPublish = Phpfox::getUserParam($sModule . '.auto_publish_sponsored_item');
            $bWithoutPaying = $bCanSponsorAll = Phpfox::getUserParam($sModule . '.can_sponsor_' . $sModule);
        } else {
            $iAutoPublish = Phpfox::getUserParam($sModule . '.auto_publish_sponsored_' . $sSection);
            $bWithoutPaying = $bCanSponsorAll = Phpfox::getUserParam($sModule . '.can_sponsor_' . $sSection);
        }

        $iCpm = 0;
        if (!$bWithoutPaying) {
            if (is_array($aPrices)) {
                if (!isset($aPrices[Phpfox::getService('core.currency')->getDefault()])) {
                    return Phpfox_Error::display(_p('the_default_currency_has_no_price'));
                }
                $iCpm = $aPrices[Phpfox::getService('core.currency')->getDefault()];
            }
        } else {
            $iAutoPublish = true;
        }

        if ($iCpm <= 0) {
            $bWithoutPaying = true;
        }

        if (empty($aVals['has_total_view'])) {
            $iTotalView = 0;
        } else {
            $iTotalView = $aVals['total_view'];
        }

        if (!empty($aVals['start_hour']) && !empty($aVals['start_minute']) && !empty($aVals['start_day']) && !empty($aVals['start_month']) && !empty($aVals['start_year'])) {
            $iStartDate = Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->mktime($aVals['start_hour'], $aVals['start_minute'], 0, $aVals['start_month'], $aVals['start_day'], $aVals['start_year']));
        } else {
            $iStartDate = PHPFOX_TIME;
        }

        if (!empty($aVals['end_option']) && !empty($aVals['end_hour']) && !empty($aVals['end_minute']) && !empty($aVals['end_day']) && !empty($aVals['end_month']) && !empty($aVals['end_year'])) {
            $iEndDate = Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->mktime($aVals['end_hour'], $aVals['end_minute'], 0, $aVals['end_month'], $aVals['end_day'], $aVals['end_year']));
        } else {
            $iEndDate = 0;
        }

        // process country
        $sCountries = null;
        if (!empty($aVals['country_iso_custom'])) {
            $aVals['country_iso_custom'] = array_filter($aVals['country_iso_custom']);
            $sCountries = implode(',', $aVals['country_iso_custom']);
        }

        $aLanguages = Phpfox::getService('language')->getAll();
        $aGenders = Phpfox::getService('ad.get')->getDefaultGenders();

        // if its an admin sponsoring something we don't need all the checks:
        $aInsertSponsor = [
            'module_id' => $sModule . (!empty($sSection) ? '_' . $sSection : ''),
            'item_id' => $aVals['item_id'],
            'user_id' => Phpfox::getUserId(),
            'country_iso' => $sCountries,
            'gender' => empty($aVals['gender']) ? implode(',', $aGenders) : implode(',', $aVals['gender']),
            'postal_code' => empty($aVals['postal_code']) ? null : $aVals['postal_code'],
            'city_location' => empty($aVals['city_location']) ? null : $aVals['city_location'],
            'age_from' => empty($aVals['age_from']) ? 0 : (int)$aVals['age_from'],
            'age_to' => empty($aVals['age_from']) ? 0 : (int)$aVals['age_to'],
            'campaign_name' => $this->preParse()->clean($aVals['name'], 255),
            'impressions' => $iTotalView,
            'cpm' => $bWithoutPaying ? 0 : $iCpm,
            'start_date' => $iStartDate,
            'end_date' => $iEndDate,
            'auto_publish' => $iAutoPublish,
            'is_custom' => (!$bWithoutPaying) ? 1 : ($iAutoPublish ? 3 : 2),
            'is_active' => (!$bWithoutPaying) ? (isset($aVals['is_active']) ? $aVals['is_active'] : 0) : 1,
            'languages' => !empty($aVals['languages']) ? implode(',', $aVals['languages']) : implode(',', array_column($aLanguages, 'language_id'))
        ];
        $iInsert = db()->insert(':better_ads_sponsor', $aInsertSponsor);

        if ($bCanSponsorAll || ($bWithoutPaying && $iAutoPublish)) {
            if ($sModule == 'feed') {
                $this->cache()->removeGroup('sponsored_feed');
            }
        }

        if ($bWithoutPaying && $sModule != 'feed') {
            if ($bCanSponsorAll || $iAutoPublish) {
                // mark item as sponsored
                Phpfox::callback($sModule . '.enableSponsor' . ($sSection ? ucfirst($sSection) : ''),
                    ['item_id' => $aVals['item_id']]);
            }
            else {
                $this->cache()->remove($aInsertSponsor['module_id'].'_pending_sponsor');
            }
            return $iInsert;
        }
        /**
         * @param `phpfox_ad_invoice`.`status`:
         *        1 => Submitted but not paid or approved.
         *        2 => Paid but not approved,
         *        3 => Approved and should be displayed
         */
        $aInsertInvoice = [
            'ads_id' => $iInsert,
            'price' => round((($iCpm * $aVals['total_view']) / 1000) * 100) / 100, // round to 2 decimal numbers
            'currency_id' => Phpfox::getService('ad.get')->getDefaultCurrency(),
            'status' => null,
            'user_id' => Phpfox::getUserId(),
            'is_sponsor' => 1,
            'time_stamp' => Phpfox::getTime()
        ];
        $iInsertInvoice = db()->insert(':better_ads_invoice', $aInsertInvoice);
        $this->cache()->removeGroup('sponsored_feed');
        return $iInsertInvoice;
    }

    public function updateSponsor($aVals)
    {
        // check required fields
        $aForms = [
            'name' => [
                'message' => _p('better_ads_provide_a_campaign_name'),
                'type' => ['string:required']
            ],
            'gender' => [
                'message' => _p('please_select_gender'),
                'type' => 'array:required'
            ],
        ];

        Phpfox_Validator::instance()->process($aForms, $aVals);
        if (!Phpfox_Error::isPassed()) {
            return false;
        }

        // process country
        $sCountries = null;
        if (!empty($aVals['country_iso_custom'])) {
            $sCountries = implode(',', $aVals['country_iso_custom']);
        }

        $aLanguages = Phpfox::getService('language')->getAll();
        $aGenders = Phpfox::getService('ad.get')->getDefaultGenders();

        db()->update(':better_ads_sponsor', [
            'campaign_name' => $aVals['name'],
            'gender' => !empty($aVals['gender']) ? implode(',', $aVals['gender']) : implode(',', $aGenders),
            'country_iso' => $sCountries,
            'postal_code' => $aVals['postal_code'],
            'city_location' => $aVals['city_location'],
            'age_from' => $aVals['age_from'],
            'age_to' => $aVals['age_to'],
            'languages' => !empty($aVals['languages']) ? implode(',', $aVals['languages']) : implode(',', array_column($aLanguages, 'language_id'))
        ], [
            'sponsor_id' => $aVals['sponsor_id']
        ]);

        return true;
    }

    /**
     * Update an ad and set it to be active or inactive. Table "ad".
     *
     * @param array $aVals ARRAY of $_POST values.
     *
     * @return bool Always returns TRUE.
     */
    public function updateActivity($aVals)
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_updateactivity__start')) ? eval($sPlugin) : false);
        foreach ($aVals as $iId => $aVal) {
            db()->update($this->_sTable, ['is_active' => (isset($aVal['is_active']) ? '1' : '0')],
                'ads_id = ' . (int)$iId);
        }

        $this->removeAdsCache();
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_updateactivity__end')) ? eval($sPlugin) : false);

        return true;
    }

    /**
     * Update an ad. Table "ad".
     *
     * @param int $iId Ad ID#.
     * @param array $aVals ARRAY of $_POST values.
     *
     * @return bool Always returns TRUE.
     * @throws \Exception
     */
    public function update($iId, $aVals)
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_update__start')) ? eval($sPlugin) : false);
        $aAd = Phpfox::getService('ad.get')->getForEdit($iId);
        $aAdHtml = json_decode($aAd['html_code'], true);
        $iStartTime = Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->mktime($aVals['start_hour'], $aVals['start_minute'], 0, $aVals['start_month'], $aVals['start_day'], $aVals['start_year']));

        if ($aAd['type_id'] == 1) {
            $image_tooltip_text = $aVals['image_tooltip_text'];
        } else {
            $image_tooltip_text = '';
        }
        if (!isset($aVals['country_iso_custom'])) {
            $aVals['country_iso_custom'] = !empty($aVals['country_iso']) ? $aVals['country_iso'] : [];
        }

        if (is_array($aVals['country_iso_custom']) && !empty($aVals['country_iso_custom'])) {
            foreach ($aVals['country_iso_custom'] as $iKey => $sCountry) {
                if (empty($sCountry)) {
                    unset($aVals['country_iso_custom'][$iKey]);
                }
            }

            if (count($aVals['country_iso_custom']) == 1) {
                $aVals['country_iso'] = $aVals['country_iso_custom'][0];
            } else {
                $aVals['country_iso'] = null;
            }
        }

        if ($aVals['type_id'] == 2) {
            $aVals['html_code'] = json_encode([
                'body' => Phpfox_Parse_Input::instance()->clean($aVals['body']),
                'title' => Phpfox_Parse_Input::instance()->clean($aVals['title']),
                'trimmed_url' => (isset($aVals['url_link'])) ? Phpfox_Parse_Input::instance()->clean($aVals['url_link']) : $aAdHtml['trimmed_url'],
                'image_path' => $aAdHtml['image_path'],
                'server_id' => $aAdHtml['server_id'],
            ]);
        }

        $aLanguages = Phpfox::getService('language')->getAll();
        $aGenders = Phpfox::getService('ad.get')->getDefaultGenders();

        $aSql = [
            'name' => Phpfox_Parse_Input::instance()->clean($aVals['name'], 150),
            'url_link' => $aVals['url_link'],
            'start_date' => $iStartTime,
            'end_date' => (!empty($aVals['end_option']) ? Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->mktime($aVals['end_hour'],
                $aVals['end_minute'], 0, $aVals['end_month'], $aVals['end_day'], $aVals['end_year'])) : 0),
            'total_view' => (isset($aVals['total_view']) ? (int)$aVals['total_view'] : 0),
            'total_click' => (($aVals['type_id'] == 1 && isset($aVals['total_click'])) ? (int)$aVals['total_click'] : 0),
            'is_active' => (int)$aVals['is_active'],
            'module_access' => (empty($aVals['module_access']) ? null : $aVals['module_access']),
            'location' => $aVals['location'],
            'country_iso' => '',
            'gender' => empty($aVals['gender']) ? implode(',', $aGenders) : implode(',', $aVals['gender']),
            'age_from' => (empty($aVals['age_from']) ? 0 : (int)$aVals['age_from']),
            'age_to' => (empty($aVals['age_from']) ? 0 : (int)$aVals['age_to']),
            'gmt_offset' => Phpfox::getLib('date')->getGmtOffset($iStartTime),
            'image_tooltip_text' => $image_tooltip_text,
            'languages' => !empty($aVals['languages']) ? implode(',', $aVals['languages']) : implode(',', array_column($aLanguages, 'language_id'))
        ];

        if (setting('better_ads_advanced_ad_filters')) {
            $oParse = Phpfox::getLib('parse.input');
            $aVals['postal_code'] = str_replace('"', '', $aVals['postal_code']);
            if (empty($aVals['postal_code'])) {
                $aSql['postal_code'] = null;
            } else {
                $aSql['postal_code'] = explode(',', $oParse->clean($aVals['postal_code']));
                $aSql['postal_code'] = json_encode($aSql['postal_code']);
            }

            if (empty($aVals['city_location'])) {
                $aSql['city_location'] = null;
            } else {
                $aSql['city_location'] = explode(',', $oParse->clean($aVals['city_location']));
                $aSql['city_location'] = json_encode($aSql['city_location']);
            }
        }

        if (empty($aSql['url_link'])) {
            unset($aSql['url_link']);
        }

        $this->_adCountries($aVals, $iId);

        if (isset($aVals['approve'])) {
            $aSql['is_custom'] = '3';
            $aSql['is_active'] = '1';
        }

        if (isset($aVals['deny'])) {
            $aSql['is_custom'] = '4';
            $aSql['is_active'] = '0';
        }

        if (!empty($aVals['temp_file'])) {
            // delete old image
            $this->deleteImage($aAd);
            // update new image
            $aFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
            $sImagePath = $aFile['path'];
        } elseif (!empty($aVals['remove_photo'])) {
            // remove image only
            $this->deleteImage($aAd);
            $sImagePath = '';
        }

        if ($aAd['type_id'] == 2) {
            $aSql['html_code'] = json_encode([
                'body' => Phpfox_Parse_Input::instance()->clean($aVals['body']),
                'title' => Phpfox_Parse_Input::instance()->clean($aVals['title']),
                'trimmed_url' => Phpfox_Parse_Input::instance()->clean($aVals['url_link']),
                'image_path' => isset($sImagePath) ? $sImagePath : $aAdHtml['image_path'],
                'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
            ]);
        } else {
            if (isset($sImagePath)) {
                $aSql['image_path'] = $sImagePath;
            }
            $aSql['server_id'] = Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID');
        }

        db()->update($this->_sTable, $aSql, 'ads_id =' . (int)$iId);
        // clear cache
        $this->removeAdsCache();

        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_update__end')) ? eval($sPlugin) : false);

        return true;
    }

    public function deleteImage($aAd)
    {
        if (!$aAd['image_path']) {
            return true;
        }

        $aParams = Phpfox::getService('ad.get')->getUploadPhotoParams();
        $aParams['type'] = 'ad';
        $aParams['path'] = $aAd['image_path'];
        $aParams['user_id'] = $aAd['user_id'];
        $aParams['update_space'] = false;
        $aParams['server_id'] = $aAd['server_id'];

        return Phpfox::getService('user.file')->remove($aParams);
    }

    /**
     * Delete an ad from the table "ad".
     *
     * @param int $iId Ad ID#.
     * @param bool $bCheckPermission
     *
     * @return bool TRUE if ad was deleted, FALSE if it was not.
     * @throws \Exception
     */
    public function delete($iId, $bCheckPermission = false)
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_delete__start')) ? eval($sPlugin) : false);

        $aAd = db()->select('*')
            ->from($this->_sTable)
            ->where('ads_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!Phpfox::isAdmin() && $bCheckPermission && $aAd['user_id'] != Phpfox::getUserId()) {
            return Phpfox_Error::set(_p('you_dont_have_permission_to_delete_this_ad'));
        }

        if (!isset($aAd['ads_id'])) {
            return Phpfox_Error::set(_p('better_ads_unable_to_find_the_ad_you_want_to_delete'));
        }

        // delete image
        $this->deleteImage($aAd);

        // remove from database better_ads
        $aDatabases = [
            $this->_sTable,
            ':better_ads_view',
            ':better_ads_hide',
            ':better_ads_log',
            ':better_ads_country'
        ];

        foreach ($aDatabases as $database) {
            db()->delete($database, 'ads_id = ' . $aAd['ads_id']);
        }

        // clear cache
        $this->removeAdsCache();

        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_delete__end')) ? eval($sPlugin) : false);

        return true;
    }

    /**
     * @return bool
     * We can't use group Ads in Phpfox::getService('ad')->getForBlock() with group "betterads", so we must delete cache on each block manual
     */
    public function removeAdsCache()
    {
        $oCache = $this->cache();
        $oCache->removeGroup('betterads');
        for ($i = 1; $i <= 12; $i++) {
            $sCacheId = $oCache->set('block_' . $i . '_ads');
            $oCache->remove($sCacheId);
        }
        return true;
    }
    /**
     * Deletes an invoice alone
     *
     * @param int $iId
     *
     * @return bool
     */
    public function deleteInvoice($iId)
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_deleteinvoice__start')) ? eval($sPlugin) : false);

        //Delete cache
        return db()->delete(':better_ads_invoice', 'invoice_id = ' . (int)$iId);
    }

    /**
     * Delete a sponsored ad from the table "better_ads_sponsor".
     *
     * @param int $iId Sponsor ad ID#.
     * @param bool $bByFeedId
     *
     * @return bool Always returns TRUE.
     */
    public function deleteSponsor($iId, $bByFeedId = false)
    {
        if ($bByFeedId) {
            db()->delete(':better_ads_sponsor', ['module_id' => 'feed', 'item_id' => $iId]);
            $this->cache()->removeGroup('sponsored_feed');
        } else {
            //Callback to module to un-sponsor that item
            $aSponsorItem = db()->select('module_id, item_id, is_custom')
                ->from(':better_ads_sponsor')
                ->where('sponsor_id = ' . (int)$iId)
                ->executeRow();
            if (Phpfox::hasCallback($aSponsorItem['module_id'], 'deleteSponsorItem')) {
                Phpfox::callback($aSponsorItem['module_id'] . '.deleteSponsorItem', ['item_id' => $aSponsorItem['item_id']]);
            }
            if (!empty($aSponsorItem['module_id']) && (int)$aSponsorItem['is_custom'] == 3) {
                if ($aSponsorItem['module_id'] == 'feed') {
                    $this->cache()->removeGroup('sponsored_feed');
                }
            }
            if ((int)$aSponsorItem['is_custom'] == 2) {
                $this->cache()->remove($aSponsorItem['module_id'] . '_pending_sponsor');
            }
        }
        db()->delete(':better_ads_sponsor', 'sponsor_id = ' . (int)$iId);
        return true;
    }

    /**
     * Add a new ad to the table "ad".
     *
     * @param array $aVals ARRAY of $_POST form values.
     *
     * @return bool|int FALSE if ad was not added.|Ad ID# if ad was successfully created.
     * @throws \Exception
     */
    public function add($aVals)
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_add__start')) ? eval($sPlugin) : false);

        if (empty($aVals['temp_file'])) {
            return Phpfox_Error::set(_p('please_upload_ad_image'));
        }

        $iStartTime = Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->mktime($aVals['start_hour'],
            $aVals['start_minute'], 0, $aVals['start_month'], $aVals['start_day'], $aVals['start_year']));
        $iEndTime = (!empty($aVals['end_option']) ? Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->mktime($aVals['end_hour'],
            $aVals['end_minute'], 0, $aVals['end_month'], $aVals['end_day'], $aVals['end_year'])) : 0);

        if ($iEndTime > 0 && $iEndTime < $iStartTime) {
            return Phpfox_Error::set(_p('better_ads_end_time_cannot_be_earlier_than_start_time'));
        }

        if (!isset($aVals['country_iso_custom'])) {
            $aVals['country_iso_custom'] = !empty($aVals['country_iso']) ? $aVals['country_iso'] : [];
        }

        if (is_array($aVals['country_iso_custom']) && !empty($aVals['country_iso_custom'])) {
            foreach ($aVals['country_iso_custom'] as $iKey => $sCountry) {
                if (empty($sCountry)) {
                    unset($aVals['country_iso_custom'][$iKey]);
                }
            }

            if (count($aVals['country_iso_custom']) == 1) {
                $aVals['country_iso'] = $aVals['country_iso_custom'][0];
            } else {
                $aVals['country_iso'] = null;
            }
        }
        if ($aVals['type_id'] == 2) {
            $aVals['html_code'] = json_encode([
                'body' => Phpfox_Parse_Input::instance()->clean($aVals['body']),
                'title' => Phpfox_Parse_Input::instance()->clean($aVals['title']),
                'trimmed_url' => Phpfox_Parse_Input::instance()->clean($aVals['url_link']),
                'image_path' => '',
                'server_id' => '',
            ]);
        }

        $aLanguages = Phpfox::getService('language')->getAll();
        $aGenders = Phpfox::getService('ad.get')->getDefaultGenders();

        $aSql = [
            'type_id' => (int)$aVals['type_id'],
            'name' => Phpfox_Parse_Input::instance()->clean($aVals['name'], 150),
            'url_link' => $aVals['url_link'],
            'start_date' => $iStartTime,
            'end_date' => $iEndTime,
            'total_view' => !empty($aVals['use_total_view']) ? intval($aVals['total_view']) : 0,
            'total_click' => !empty($aVals['use_total_click']) ? intval($aVals['total_click']) : 0,
            'is_active' => (int)$aVals['is_active'],
            'module_access' => (empty($aVals['module_access']) ? null : $aVals['module_access']),
            'location' => $aVals['location'],
            'image_tooltip_text' => $aVals['image_tooltip_text'],
            'country_iso' => (empty($aVals['country_iso']) ? null : $aVals['country_iso']),
            'gender' => empty($aVals['gender']) ? implode(',', $aGenders) : implode(',',
                $aVals['gender']),
            'age_from' => (empty($aVals['age_from']) ? 0 : (int)$aVals['age_from']),
            'age_to' => (empty($aVals['age_from']) ? 0 : (int)$aVals['age_to']),
            'html_code' => (empty($aVals['html_code']) ? null : $aVals['html_code']),
            'gmt_offset' => Phpfox::getLib('date')->getGmtOffset($iStartTime),
            'user_id' => Phpfox::getUserId(),
            'is_custom' => 3,
            'languages' => !empty($aVals['languages']) ? implode(',', $aVals['languages']) : implode(',', array_column($aLanguages, 'language_id')),
            'auto_publish' => 1,
            'is_cpm' => !empty($aVals['use_total_view']) ? 1 : (!empty($aVals['use_total_click']) ? 0 : null)
        ];

        if (setting('better_ads_advanced_ad_filters')) {
            $oParse = Phpfox::getLib('parse.input');
            if (empty($aVals['postal_code'])) {
                $aSql['postal_code'] = null;
            } else {
                $aSql['postal_code'] = explode(',', $oParse->clean($aVals['postal_code']));
                $aSql['postal_code'] = json_encode($aSql['postal_code']);
            }

            if (empty($aVals['city_location'])) {
                $aSql['city_location'] = null;
            } else {
                $aSql['city_location'] = explode(',', $oParse->clean($aVals['city_location']));
                $aSql['city_location'] = json_encode($aSql['city_location']);
            }
        }

        $iId = db()->insert($this->_sTable, $aSql);
        $this->_adCountries($aVals, $iId);

        //Upload image
        if (isset($aVals['temp_file'])) {
            $aTempFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
            $sFileName = $aTempFile['path'];

            if ($aVals['type_id'] == 1) {
                db()->update($this->_sTable, [
                    'image_path' => $sFileName,
                    'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
                ], 'ads_id = ' . (int)$iId);
            } else {
                db()->update($this->_sTable, [
                    'html_code' => json_encode([
                        'body' => Phpfox_Parse_Input::instance()->clean($aVals['body']),
                        'title' => Phpfox_Parse_Input::instance()->clean($aVals['title']),
                        'trimmed_url' => Phpfox_Parse_Input::instance()->clean($aVals['url_link']),
                        'image_path' => $sFileName,
                        'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                    ]),
                    'image_path' => $sFileName,
                    'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
                ], 'ads_id = ' . (int)$iId);
            }
        }

        $this->removeAdsCache();
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_add__end')) ? eval($sPlugin) : false);

        return $iId;
    }

    /**
     * This function increases the view count of an item and checks if its time to stop showing
     * This function is called from a block.
     *
     * @param int $iSponsorId
     * @param string $sModule
     * @param string $sFunction
     *
     * @return bool|int|null
     */
    public function addSponsorViewsCount($iSponsorId, $sModule, $sFunction = 'sponsor')
    {
        $aItem = db()->select('sponsor_id, impressions, total_view, module_id, item_id, cpm')
            ->from(':better_ads_sponsor')
            ->where('sponsor_id = ' . (int)$iSponsorId)
            ->execute('getSlaveRow');

        if (empty($aItem)) {
            return false;
        }

        if ((int)$aItem['impressions'] !== 0 && $aItem['impressions'] <= $aItem['total_view']) {
            // stop showing this sponsor by updating its table
            if (!defined('PHPFOX_API_CALLBACK')) // this overrides security checks
            {
                define('PHPFOX_API_CALLBACK', true);
            }
            // update in ad_sponsor to stop showing it
            db()->update(':better_ads_sponsor', ['is_active' => '4'], 'sponsor_id = ' . (int)$iSponsorId);

            return Phpfox::getService($sModule . '.process')->$sFunction($aItem['item_id'], 0);
        }

        db()->update(':better_ads_sponsor', ['total_view' => $aItem['total_view'] + 1],
            'sponsor_id = ' . (int)$iSponsorId);

        return null;
    }

    /*
        Handles inserting and updating countries and states
    */
    private function _adCountries($aVals, $iId)
    {
        if (!setting('better_ads_advanced_ad_filters') || empty($aVals['country_iso_custom'])) {
            return;
        }

        // remove old countries of current ad
        db()->delete(':better_ads_country', 'ads_id = ' . intval($iId));

        // insert countries
        foreach ($aVals['country_iso_custom'] as $sCountry) {
            $aInsert = [
                'ads_id' => $iId,
                'country_id' => $sCountry
            ];

            if (!empty($aVals['child_country']) && in_array($sCountry, array_keys($aVals['child_country']))) {
                // insert child countries
                foreach ($aVals['child_country'][$sCountry] as $sChildCountryId) {
                    db()->insert(':better_ads_country', array_merge($aInsert, [
                        'child_id' => $sChildCountryId
                    ]));
                }
            } else {
                db()->insert(':better_ads_country', $aInsert);
            }
        }
    }

    /**
     * Add a custom ad, which are created by the end-users.
     *
     * @param array $aVals ARRAY of $_POST form values.
     *
     * @return bool|int FALSE if ad was not created.|Ad ID# if ad was successfully created.
     * @throws \Exception
     */
    public function addCustom($aVals)
    {
        Phpfox::isUser(true);

        if ((int)$aVals['total_view'] < 1000 && $aVals['is_cpm']) {
            return Phpfox_Error::set(_p('better_ads_there_is_minimum_of_1000_impressions'));
        }

        if (empty($aVals['temp_file'])) {
            return Phpfox_Error::set(_p('please_upload_ad_image'));
        }
        $fPrice = (round((float)$aVals['default_cost'],2) * (int)$aVals['total_view']);

        if ($aVals['is_cpm']) {
            $fPrice = $fPrice / 1000;
        }

        if (isset($aVals['country_iso_custom']) && is_array($aVals['country_iso_custom']) && !empty($aVals['country_iso_custom'])) {
            foreach ($aVals['country_iso_custom'] as $iKey => $sCountry) {
                if (empty($sCountry)) {
                    unset($aVals['country_iso_custom'][$iKey]);
                }
            }
            if (count($aVals['country_iso_custom']) == 1) {
                $aVals['country_iso'] = reset($aVals['country_iso_custom']);
            } else {
                $aVals['country_iso'] = null;
            }
        }

        $iStartTime = Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->mktime($aVals['start_hour'],
            $aVals['start_minute'], 0, $aVals['start_month'], $aVals['start_day'], $aVals['start_year']));

        /*
        * The field is_custom tells the state of the ad as follows:
            1: Pending Payment
            2: Pending Approval
            3: Approved?
            4: Denied
        */

        $aLanguages = Phpfox::getService('language')->getAll();
        $aGenders = Phpfox::getService('ad.get')->getDefaultGenders();;
        $mustBeApprove = Phpfox::getUserParam('better_ad_campaigns_must_be_approved_first');

        $aInsert = [
            'is_custom' => ($fPrice == 0 ? ($mustBeApprove ? '2' : '3') : '1'),
            // if its free set it as approved
            'user_id' => Phpfox::getUserId(),
            'type_id' => $aVals['type_id'],
            'name' => Phpfox_Parse_Input::instance()->clean($aVals['name']),
            'url_link' => $aVals['url_link'],
            'start_date' => $iStartTime,
            'end_date' => 0,
            'total_view' => ((int)$aVals['is_cpm'] == 0) ? '0' : (isset($aVals['total_view']) ? (int)$aVals['total_view'] : 0),
            'total_click' => ((int)$aVals['is_cpm'] == 1) ? '0' : (isset($aVals['total_view']) ? (int)$aVals['total_view'] : 0),
            'is_active' => ($fPrice == 0 ? '1' : '0'),
            'location' => $aVals['location'],
            'country_iso' => (empty($aVals['country_iso']) ? null : $aVals['country_iso']),
            'gender' => empty($aVals['gender']) ? implode(',', $aGenders) : implode(',',
                $aVals['gender']),
            'age_from' => (empty($aVals['age_from']) ? 0 : (int)$aVals['age_from']),
            'age_to' => (empty($aVals['age_from']) ? 0 : (int)$aVals['age_to']),
            'gmt_offset' => ($iStartTime > 0 ? Phpfox::getLib('date')->getGmtOffset($iStartTime) : null),
            'image_path' => $aVals['image_path'],
            'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
            'is_cpm' => (int)$aVals['is_cpm'],
            'image_tooltip_text' => $aVals['image_tooltip_text'],
            'languages' => !empty($aVals['languages']) ? implode(',', $aVals['languages']) : implode(',', array_column($aLanguages, 'language_id')),
            'auto_publish' => $mustBeApprove ? 0 : 1,
            'user_groups' => !empty($aVals['user_groups']) ? implode(',', $aVals['user_groups']) : null,
        ];

        if (setting('better_ads_advanced_ad_filters')) {
            $oParse = Phpfox::getLib('parse.input');
            if (empty($aVals['postal_code'])) {
                $aInsert['postal_code'] = null;
            } else {
                $aInsert['postal_code'] = explode(',', $oParse->clean($aVals['postal_code']));
                $aInsert['postal_code'] = json_encode($aInsert['postal_code']);
            }

            if (empty($aVals['city_location'])) {
                $aInsert['city_location'] = null;
            } else {
                $aInsert['city_location'] = explode(',', $oParse->clean($aVals['city_location']));
                $aInsert['city_location'] = json_encode($aInsert['city_location']);
            }
        }

        // Upload image
        if (isset($aVals['temp_file'])) {
            $aTempFile = Phpfox::getService('core.temp-file')->get($aVals['temp_file']);
            Phpfox::getService('core.temp-file')->delete($aVals['temp_file']);
            $sFileName = $aTempFile['path'];

            if ($aVals['type_id'] == 1) {
                $aInsert = array_merge($aInsert, [
                    'image_path' => $sFileName,
                    'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
                ]);
            } else {
                $aInsert = array_merge($aInsert, [
                    'html_code' => json_encode([
                        'body' => Phpfox_Parse_Input::instance()->clean($aVals['body']),
                        'title' => Phpfox_Parse_Input::instance()->clean($aVals['title']),
                        'trimmed_url' => Phpfox_Parse_Input::instance()->clean($aVals['url_link']),
                        'image_path' => $sFileName,
                        'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'),
                    ]),
                    'image_path' => $sFileName,
                    'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
                ]);
            }
        }

        $iId = db()->insert(':better_ads', $aInsert);
        // Insert countries
        $this->_adCountries($aVals, $iId);
        $aPlan = Phpfox::getService('ad.get')->getPlan($aVals['location']);

        db()->insert(':better_ads_invoice', [
            'ads_id' => $iId,
            'user_id' => Phpfox::getUserId(),
            'currency_id' => $aPlan['default_currency_id'],
            'price' => $fPrice,
            'time_stamp' => PHPFOX_TIME,
            'status' => $fPrice == 0 ? 'completed' : null,
            'time_stamp_paid' => $fPrice == 0 ? PHPFOX_TIME : '0',
            'is_sponsor' => 0
        ]);

        $this->removeAdsCache();

        return $iId;
    }

    /**
     * Update a custom ad created by end-users. Currently we only update the ads name.
     *
     * 2nd argument $_POST form values support:
     * - name (STRING)
     *
     * @param int $iId Custom ad ID#.
     * @param array $aVals ARRAY of $_POST form values.
     *
     * @return bool
     * @throws \Exception
     */
    public function updateCustom($iId, $aVals)
    {
        Phpfox::isUser(true);
        if (!Phpfox::isAdmin()) {
            // check that this user is the owner of the ad
            $iUserId = db()->select('user_id')
                ->from(':better_ads')
                ->where('ads_id = ' . (int)$aVals['id'])
                ->execute('getSlaveField');
            if ($iUserId != Phpfox::getUserId()) {
                return Phpfox_Error::set(_p('better_ads_you_are_not_allowed_to_edit_this_ad'));
            }
        }

        $this->_adCountries($aVals, $iId);
        $aLanguages = Phpfox::getService('language')->getAll();
        $aGenders = Phpfox::getService('ad.get')->getDefaultGenders();

        $aSql = [
            'name' => Phpfox_Parse_Input::instance()->clean($aVals['name']),
            'country_iso' => '',
            'gender' => empty($aVals['gender']) ? implode(',', $aGenders) : implode(',',
                $aVals['gender']),
            'age_from' => (int)$aVals['age_from'],
            'age_to' => (int)$aVals['age_to'],
            'languages' => !empty($aVals['languages']) ? implode(',', $aVals['languages']) : implode(',', array_column($aLanguages, 'language_id')),
            'user_groups' => !empty($aVals['user_groups']) ? implode(',', $aVals['user_groups']) : null,
        ];

        if (setting('better_ads_advanced_ad_filters')) {
            $oParse = Phpfox::getLib('parse.input');
            if (empty($aVals['postal_code'])) {
                $aSql['postal_code'] = null;
            } else {
                $aSql['postal_code'] = explode(',', $oParse->clean($aVals['postal_code']));
                $aSql['postal_code'] = json_encode($aSql['postal_code']);
            }

            if (empty($aVals['city_location'])) {
                $aSql['city_location'] = null;
            } else {
                $aSql['city_location'] = explode(',', $oParse->clean($aVals['city_location']));
                $aSql['city_location'] = json_encode($aSql['city_location']);
            }
        }

        db()->update(':better_ads', $aSql, 'ads_id = ' . (int)$iId);

        return true;
    }

    /**
     * Approve an ad created by an end-user.
     *
     * @param int $iId Ad ID#.
     *
     * @return bool Always returns TRUE.
     * @throws \Exception
     */
    public function approve($iId)
    {
        if (!user('better_can_approval_ad_campaigns')) {
            return false;
        }

        db()->update(':better_ads', [
            'is_custom' => '3'
        ], 'ads_id = ' . (int)$iId);

        // send email to owner
        $this->sendApproveEmail($iId);
        // send notification to owner
        $iOwnerUserId = db()->select('user_id')->from(':better_ads')->where(['ads_id' => $iId])->executeField();
        Phpfox::getService('notification.process')->add('ad_approve_item', $iId, $iOwnerUserId, $iOwnerUserId);

        $this->removeAdsCache();

        return true;
    }

    /**
     * Approve a sponsored ad campaign created by an end-user.
     *
     * @param int $iId Sponsored ad ID#.
     *
     * @return bool FALSE if ad cannot be found, TRUE if ad was successfully approved.
     */
    public function approveSponsor($iId)
    {
        // stop showing this sponsor by updating its table
        if (!defined('PHPFOX_API_CALLBACK')) { // this overrides security checks
            define('PHPFOX_API_CALLBACK', true);
        }

        db()->update(':better_ads_sponsor', [
            'is_custom' => '3',
            'is_active' => '1'
        ], 'sponsor_id = ' . (int)$iId);

        $aAd = Phpfox::getService('ad.get')->getSponsor($iId);
        if ($aAd) {
            $sModule = isset($aAd['module_id']) ? $aAd['module_id'] : '';
            $sSection = '';
            if (strpos($sModule, '_') !== false) {
                $aModule = explode('_', $sModule);
                $sModule = $aModule[0];
                $sSection = $aModule[1];
            }

            if (!empty($sModule) && $sModule != 'feed' && Phpfox::hasCallback($sModule, 'enableSponsor')) {
                Phpfox::callback($sModule . '.enableSponsor', [
                    'item_id' => $aAd['item_id'],
                    'section' => $sSection
                ]);
            }
            if (isset($aAd['module_id'])) {
                $this->cache()->remove($aAd['module_id'] . '_pending_sponsor');
                if ($aAd['module_id'] == 'feed') {
                    $this->cache()->removeGroup('sponsored_feed');
                }
            }
        }

        $this->sendApproveEmail($iId, true);
        //Notify to owner of this sponsor
        Phpfox::getService('notification.process')->add('ad_approve_sponsor', $iId, $aAd['user_id'], $aAd['user_id']);
        return true;
    }

    /**
     * Deny an ad from being displayed publicly on the site.
     *
     * @param int $iId Ad ID#.
     *
     * @return bool Always returns TRUE.
     * @throws \Exception
     */
    public function deny($iId)
    {
        if (!user('better_can_approval_ad_campaigns')) {
            return false;
        }

        db()->update(':better_ads', [
            'is_custom' => '4',
            'is_active' => '1'
        ], 'ads_id = ' . (int)$iId);

        // send email to owner
        $this->sendDenyEmail($iId);
        // send notification to owner
        $iOwnerUserId = db()->select('user_id')->from(':better_ads')->where(['ads_id' => $iId])->executeField();
        Phpfox::getService('notification.process')->add('ad_deny_item', $iId, $iOwnerUserId, $iOwnerUserId);

        $this->removeAdsCache();

        return true;
    }

    /**
     * Deny a sponsored ad.
     *
     * @param int $iId Sponsored ad ID#.
     *
     * @return bool Always returns TRUE.
     */
    public function denySponsor($iId)
    {
        $aItem = $this->database()->select('user_id, module_id')
            ->from(':better_ads_sponsor')
            ->where('sponsor_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (empty($aItem)) {
            return false;
        }

        db()->update(':better_ads_sponsor', [
            'is_custom' => '4',
            'is_active' => '1'
        ], 'sponsor_id = ' . (int)$iId
        );

        $this->sendDenyEmail($iId, true);
        // send notification
        Phpfox::getService('notification.process')->add('ad_deny_sponsor', $iId, $aItem['user_id'], $aItem['user_id']);
        $this->cache()->remove($aItem['module_id'] . '_pending_sponsor');

        return true;
    }

    /**
     * Send an email to the user if their ad has been approved.
     *
     * @param int $iId Ad ID#.
     * @param bool $bSponsor TRUE if this is a sponsored ad.
     *
     * @return bool FALSE if ad was not found, TRUE if email was sent.
     */
    public function sendApproveEmail($iId, $bSponsor = false)
    {
        $aAd = db()->select(($bSponsor ? 'sponsor_id' : 'ads_id') . ', user_id')
            ->from(':better_ads' . ($bSponsor ? '_sponsor' : ''))
            ->where(($bSponsor ? 'sponsor_id = ' : 'ads_id = ') . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aAd)) {
            return false;
        }

        if ($bSponsor === true) {
            $sSubject = ['better_ads_sponsor_ad_approved'];
            $sMessage = ['better_ads_your_sponsor_ad_on_site_name_has_been_approved', [
                'site_name' => Phpfox::getParam('core.site_title'),
                'link' => Phpfox_Url::instance()->makeUrl('ad.sponsor', ['view' => $aAd['sponsor_id']])
            ]];
        } else {
            $sSubject = 'better_ads_ad_approved';
            $sMessage = ['better_ads_your_ad_on_site_name_has_been_approved', [
                'site_name' => Phpfox::getParam('core.site_title'),
                'link' => Phpfox_Url::instance()->makeUrl('ad.report', ['ads_id' => $aAd['ads_id']])
                ]
            ];
        }

        Phpfox::getLib('mail')->to($aAd['user_id'])
            ->subject($sSubject)
            ->message($sMessage)
            ->notification('ad.ad_notifications')
            ->send();

        return true;
    }

    /**
     * Send a denied email when an ad was not approved by an admin.
     *
     * @param int $iId Ad ID#.
     * @param bool $bSponsor TRUE if this is a sponsored ad.
     *
     * @return false|null FALSE if ad was not found.|NULL if ad was found and email was sent.
     */
    public function sendDenyEmail($iId, $bSponsor = false)
    {
        $aAd = db()->select(($bSponsor ? 'sponsor_id' : 'ads_id') . ', user_id')
            ->from(':better_ads' . ($bSponsor ? '_sponsor' : ''))
            ->where(($bSponsor ? 'sponsor_id = ' : 'ads_id = ') . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aAd)) {
            return false;
        }

        if ($bSponsor === true) {
            $sSubject = 'better_ads_sponsor_ad_denied';
            $sMessage = ['better_ads_your_sponsor_ad_on_site_name_has_been_denied', [
                'site_name' => Phpfox::getParam('core.site_title'),
                'link' => Phpfox_Url::instance()->makeUrl('ad.sponsor', ['view' => $aAd['sponsor_id']])
            ]];
        } else {
            $sSubject = 'ad_denied';
            $sMessage = ['better_ads_your_ad_on_site_name_has_been_denied', [
                'site_name' => Phpfox::getParam('core.site_title'),
                'link' => Phpfox_Url::instance()->makeUrl('ad.report', ['ads_id' => $aAd['ads_id']])
                ]
            ];
        }

        Phpfox::getLib('mail')->to($aAd['user_id'])
            ->subject($sSubject)
            ->message($sMessage)
            ->notification('ad.ad_notifications')
            ->send();

        return null;
    }

    /**
     * Update the activity of an ad via AJAX from the table "ad".
     *
     * @param int $iId Ad ID#.
     * @param int $iType Activity ID. 1 = active, 0 = inactive.
     * @param int|null $iUserId (Optional) Pass a user ID if we need to check on the user ID of the ad.
     *
     * @return string|null
     * @throws \Exception
     */
    public function updateActivityAjax($iId, $iType, $iUserId = null)
    {
        Phpfox::isUser(true);

        if ($iUserId === null) {
            Phpfox::getUserParam('admincp.has_admin_access', true);
            db()->update($this->_sTable, ['is_active' => (int)($iType == '1' ? 1 : 0)],
                'ads_id = ' . (int)$iId);
        } else {
            $aAd = db()->select('is_cpm, is_active, total_view, count_view, total_click, count_click, user_id')
                ->from(':better_ads')
                ->where('ads_id = ' . (int)$iId)
                ->execute('getSlaveRow');
            if ($aAd['user_id'] != $iUserId) {
                return Phpfox_Error::set(_p('better_ads_you_are_not_the_owner_of_this_ad'));
            }
            if ($iType == 1 && $aAd['is_cpm'] == 1 && $aAd['count_view'] >= $aAd['total_view']) {
                if ($aAd['total_view'] != 0) {
                    return Phpfox_Error::set(_p('better_ads_this_ad_has_used_all_its_views'));
                }
            }
            if ($iType == 1 && $aAd['is_cpm'] != 1 && $aAd['count_click'] >= $aAd['total_click']) {
                if ($aAd['total_click'] != 0) {
                    return Phpfox_Error::set(_p('better_ads_this_ad_has_used_all_its_clicks'));
                }
            }
            if ($aAd['is_active'] != $iType) {
                db()->update($this->_sTable, ['is_active' => (int)($iType == '1' ? 1 : 0)],
                    'ads_id = ' . (int)$iId . ' AND user_id = ' . (int)$iUserId);
            }
        }

        $this->cache()->remove();

        return null;
    }

    /**
     * This function is called from an ajax function in the AdminCP to dis/enabling a sponsored campaign.
     *
     * @param int $iId
     * @param int $iType
     *
     * @return bool TRUE on success, FALSE on failure.
     * @throws \Exception
     */
    public function updateSponsorActivity($iId, $iType)
    {
        Phpfox::isUser(true);
        // get the item to check for ownership
        $aAd = db()->select('user_id, item_id, module_id')
            ->from(':better_ads_sponsor')
            ->where('sponsor_id = ' . (int)$iId)
            ->execute('getSlaveRow');
        $iUser = $aAd['user_id'];
        $bIsOwner = $iUser == Phpfox::getUserId();

        if ($bIsOwner || Phpfox::isAdmin()) {
            if ($iType == '1') {
                if (!defined('PHPFOX_API_CALLBACK')) {
                    define('PHPFOX_API_CALLBACK', true);
                }
            }

            db()->update(':better_ads_sponsor', [
                'is_active' => $iType == '1' ? 1 : 0
            ], 'sponsor_id = ' . $iId);

            $this->removeAdsCache();

            return true;
        } else {
            return Phpfox_Error::set(_p('better_ads_you_cant_do_that_dot_dot_dot'));
        }
    }

    /**
     * Updates the ad placement activity.
     *
     * @param int $iId Ad placement ID#.
     * @param int $iType Activity ID, 1 = active, 0 = inactive.
     */
    public function updateAdPlacementActivity($iId, $iType)
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('admincp.has_admin_access', true);
        db()->update(':better_ads_plan', ['is_active' => (int)($iType == '1' ? 1 : 0)],
            'plan_id = ' . (int)$iId);
        $this->cache()->remove();
    }

    /**
     * Adds an ad placement plan into the table "ad_plan".
     *
     * Valid $_POST data for the 1st argument:
     * - title (STRING)
     * - block_id (INT)
     * - cost (STRING)
     * - is_active (INT)
     *
     * @param array $aVals ARRAY of $_POST values.
     * @param int $iId (Optional) If we are editing this ad we pass the ad ID here.
     *
     * @return bool|int FALSE if errors were found when creating the ad placement.|Ad ID# when ad placement has successfully been created.
     */
    public function addPlacement($aVals, $iId = null)
    {
        $aForms = [
            'title' => [
                'message' => _p('better_ads_provide_a_title'),
                'type' => ['string:required']
            ],
            'block_id' => [
                'message' => _p('better_ads_select_a_placement'),
                'type' => ['int:required']
            ],
            'cost' => [
                'message' => _p('better_ads_provide_a_cost'),
                'type' => 'currency'
            ],
            'is_active' => [
                'message' => _p('better_ads_select_if_this_ad_placement_is_active_or_not'),
                'type' => 'int:required'
            ],
            'is_cpm' => [
                'message' => _p('better_ads_you_need_to_define_if_this_placement_is_for_cpm_or_ppc'),
                'type' => 'int:required'
            ],
            'disallow_controller' => [
                'type' => 'array'
            ],
            'user_group' => [
                'type' => 'array'
            ],
        ];

        $aVals = Phpfox_Validator::instance()->process($aForms, $aVals);

        if (!Phpfox_Error::isPassed()) {
            return false;
        }

        $aVals['cost'] = serialize($aVals['cost']);
        $aVals['title'] = \Phpfox_Parse_Input::instance()->convert($aVals['title']);
        if (is_array($aVals['disallow_controller'])) {
            $aVals['disallow_controller'] = implode(',', $aVals['disallow_controller']);
        }
        if (is_array($aVals['user_group'])) {
            $aVals['user_group'] = implode(',', $aVals['user_group']);
        }

        if ($iId === null) {
            $iId = db()->insert(':better_ads_plan', $aVals);
        } else {
            db()->update(':better_ads_plan', $aVals, 'plan_id = ' . (int)$iId);
        }

        $this->removeAdsCache();

        return $iId;
    }

    /**
     * Update ad placement.
     *
     * @see self::addPlacement()
     *
     * @param int $iId
     * @param array $aVals
     *
     * @return mixed
     */
    public function updatePlacement($iId, $aVals)
    {
        return $this->addPlacement($aVals, $iId);
    }

    /**
     * Deletes an ad placement.
     *
     * @param $aDelete
     * @return bool Always returns TRUE.
     * @throws \Exception
     */
    public function deletePlacement($aDelete)
    {
        if (!empty($aDelete['child_action']) && $aDelete['child_action'] == 'move') {
            // update new placement
            db()->update(':better_ads', [
                'location' => $aDelete['new_placement_id']
            ], [
                'location' => $aDelete['placement_id']
            ]);
        } else {
            $aAds = db()->select('*')->from(':better_ads')->where([
                'location' => $aDelete['placement_id']
            ])->executeRows();
            // delete all ads
            foreach ($aAds as $aAd) {
                $this->delete($aAd['ads_id']);
            }
        }

        // delete placement
        db()->delete(':better_ads_plan', 'plan_id = ' . intval($aDelete['placement_id']));
        // clear cache
        $this->removeAdsCache();

        return true;
    }

    /**
     * Deletes sponsor entries added by an administrator from the ad.better_ads_sponsor table.
     * If more than one campaign was created by an admin for the same item, they will be deleted
     * This function is called from the ajax functions addSponsor, triggered when an admin sponsors
     * an item.
     *
     * @param string $sModule
     * @param int $iItem
     */
    public function deleteAdminSponsor($sModule, $iItem)
    {
        $sModule = Phpfox::getLib('parse.input')->clean($sModule);
        db()->delete(':better_ads_sponsor', 'module_id = "' . $sModule . '" AND item_id = ' . (int)$iItem);
        $this->cache()->removeGroup('sponsored_feed');
    }

    public function hideAds($iAdsId)
    {
        $iUserId = (int)Phpfox::getUserId();
        //check was hidden
        $iCnt = Phpfox::getLib('database')->select('COUNT(*)')
            ->from(':better_ads_hide')
            ->where('ads_id=' . (int)$iAdsId . '  AND user_id=' . (int)$iUserId)
            ->executeField();
        if ($iCnt) {
            return true;
        } else {
            $iId = Phpfox::getLib('database')->insert(':better_ads_hide', [
                'ads_id' => $iAdsId,
                'user_id' => $iUserId,
                'module_id' => null
            ]);
            $this->cache()->remove('better_ads_hidden_user_' . (int)$iUserId);
            return $iId;
        }
    }

    public function addActionMenus()
    {
        \Phpfox_Template::instance()->setActionMenu([
            _p('add_new_ad') => [
                'url' => Phpfox_Url::instance()->makeUrl('admincp.ad.add')
            ],
            _p('add_new_placement') => [
                'url' => Phpfox_Url::instance()->makeUrl('admincp.ad.addplacement')
            ],
        ]);
    }

    public function cancelInvoice($iInvoiceId)
    {
        db()->update(':better_ads_invoice', [
            'status' => 'cancel'
        ], [
            'invoice_id' => $iInvoiceId
        ]);
    }

    /**
     * Close all sponsorships of an item
     *
     * @param string $sModule
     * @param int $iItem
     */
    public function closeSponsorItem($sModule, $iItem)
    {
        $sModule = Phpfox::getLib('parse.input')->clean($sModule);
        $this->database()->update(Phpfox::getT('better_ads_sponsor'), [
            'is_custom' => 5,
            'is_active' => 0
        ], 'module_id = "' . $sModule . '" AND item_id = ' . (int)$iItem);
    }

    public function convertToTimestamp($iDay, $iMonth, $iYear, $sType = 'start')
    {
        $sDate = "{day}-{month}-{year}";
        if ($sType == 'start') {
            $sDate .= ' 00:00:00';
        } elseif ($sType == 'end') {
            $sDate .= ' 23:59:59';
        }

        return strtotime(strtr($sDate, [
            '{day}' => $iDay,
            '{month}' => $iMonth,
            '{year}' => $iYear,
        ]));
    }

    /**
     * @param $sMethod
     * @param $aArguments
     *
     * @return mixed|null
     */
    public function __call($sMethod, $aArguments)
    {
        if ($sPlugin = Phpfox_Plugin::get('ad.service_process__call')) {
            return eval($sPlugin);
        }
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);

        return null;
    }
}
