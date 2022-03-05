<?php

namespace Apps\Core_BetterAds\Service;

use Phpfox;

/**
 * Class Migrate
 * @package Apps\Core_BetterAds\Service
 */
class Migrate extends \Phpfox_Service
{
    public function getAds($aConds, $sSort, $iPage = 1, $iLimit = 10)
    {
        if(!db()->tableExists(Phpfox::getT('ad'))) {
            return [0, []];
        }
        $iCnt = db()->select('count(*)')
            ->from(':ad', 'a')
            ->leftJoin(':user', 'u', 'u.user_id = a.user_id')
            ->where($aConds)
            ->executeField();

        $aAds = db()->select('a.*')
            ->from(':ad', 'a')
            ->leftJoin(':user', 'u', 'u.user_id = a.user_id')
            ->where($aConds)
            ->order($sSort)
            ->limit($iPage, $iLimit)
            ->executeRows();

        // preprocess
        foreach ($aAds as &$aAd) {
            $aAd['start'] = Phpfox::getTime(BETTERADS_DATETIME_FORMAT, $aAd['start_date']);
            $aAd['end'] = Phpfox::getTime(BETTERADS_DATETIME_FORMAT, $aAd['end_date']);
            $aAd['user'] = Phpfox::getService('user')->getUser($aAd['user_id']);
            $aAd['status'] = Phpfox::getService('ad.get')->getStatusPhrase($aAd['is_custom'], $aAd['start_date'], $aAd['end_date']);
        }

        return [$iCnt, $aAds];
    }

    public function deleteAd($iAdId)
    {
        if(!db()->tableExists(Phpfox::getT('ad'))) {
            return;
        }
        db()->delete(':ad', ['ad_id' => $iAdId]);
    }

    public function importAd($iAdId, $iPlacementId)
    {
        if(!db()->tableExists(Phpfox::getT('ad'))) {
            return;
        }
        $aAd = db()->select('*')->from(':ad')->where(['ad_id' => $iAdId])->executeRow();
        // 1. import ad
        unset($aAd['ad_id'], $aAd['user_group'], $aAd['disallow_controller']);
        $aAd['location'] = $iPlacementId;
        $aHtml = json_decode($aAd['html_code'], true);
        $aAd['html_code'] = json_encode([
            'body' => $aHtml['body'],
            'title' => $aHtml['title'],
            'trimmed_url' => $aAd['url_link'],
            'image_path' => $aAd['image_path'],
            'server_id' => $aAd['server_id'],
        ]);
        $iNewAdId = db()->insert(':better_ads', $aAd);

        // 2. import countries
        $aCountries = db()->select('country_id, child_id')->from(':ad_country')->where(['ad_id' => $iAdId])->executeRows();
        foreach ($aCountries as $aCountry) {
            db()->insert(':better_ads_country', [
                'ads_id' => $iNewAdId,
                'country_id' => $aCountry['country_id'],
                'child_id' => $aCountry['child_id'],
            ]);
        }

        // 3. import invoice
        $aInvoices = db()->select('is_sponsor, user_id, currency_id, price, status, time_stamp, time_stamp_paid')
            ->from(':ad_invoice')
            ->where([
                'ad_id' => $iAdId,
                'is_sponsor' => 0
            ])->executeRows();
        foreach ($aInvoices as $aInvoice) {
            db()->insert(':better_ads_invoice', array_merge($aInvoice, [
                'ads_id' => $iNewAdId,
            ]));
        }

        // 4. delete imported ad
        $this->deleteAd($iAdId);
    }

    public function getSponsorships($aConds, $sSort, $iPage = 1, $iLimit = 10)
    {
        if(!db()->tableExists(Phpfox::getT('ad_sponsor'))) {
            return [0, []];
        }
        $iCnt = db()->select('count(*)')
            ->from(':ad_sponsor', 's')
            ->leftJoin(':user', 'u', 'u.user_id = s.user_id')
            ->where($aConds)
            ->executeField();

        $aSponsorships = db()->select('s.*')
            ->from(':ad_sponsor', 's')
            ->leftJoin(':user', 'u', 'u.user_id = s.user_id')
            ->where($aConds)
            ->order($sSort)
            ->limit($iPage, $iLimit)
            ->executeRows();

        // preprocess
        foreach ($aSponsorships as &$aSponsorship) {
            $aSponsorship['start'] = Phpfox::getTime(BETTERADS_DATETIME_FORMAT, $aSponsorship['start_date']);
            $aSponsorship['user'] = Phpfox::getService('user')->getUser($aSponsorship['user_id']);
            $aSponsorship['status'] = Phpfox::getService('ad.get')->getStatusPhrase($aSponsorship['is_custom'], $aSponsorship['start_date'], $aSponsorship['end_date']);
        }

        return [$iCnt, $aSponsorships];
    }

    public function importSponsorship($iSponsorId)
    {
        if(!db()->tableExists(Phpfox::getT('ad_sponsor'))) {
            return;
        }
        $aSponsorship = db()->select('*')->from(':ad_sponsor')->where(['sponsor_id' => $iSponsorId])->executeRow();
        unset($aSponsorship['sponsor_id']);
        // 1. insert sponsorship
        $iNewSponsorId = db()->insert(':better_ads_sponsor', $aSponsorship);
        // 2. insert invoice
        $aInvoices = db()->select('*')->from(':ad_invoice')->where([
            'ad_id' => $iSponsorId,
            'is_sponsor' => 1
        ])->executeRows();
        foreach ($aInvoices as $aInvoice) {
            unset($aInvoice['ad_id']);
            db()->insert(':better_ads_invoice', array_merge($aInvoice, [
                'ads_id' => $iNewSponsorId,
            ]));
        }
        // 3. remove imported sponsorship
        $this->deleteSponsorship($iSponsorId);
    }

    public function deleteSponsorship($iSponsorId)
    {
        if(!db()->tableExists(Phpfox::getT('ad_sponsor'))) {
            return;
        }
        db()->delete(':ad_sponsor', [
            'sponsor_id' => $iSponsorId
        ]);
    }
}
