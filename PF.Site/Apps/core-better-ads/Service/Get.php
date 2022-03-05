<?php

namespace Apps\Core_BetterAds\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Template;

/**
 * Class Get
 * @package Apps\Core_BetterAds\Service
 */
class Get extends \Phpfox_Service
{
    private $_aThumbnailSizes = [200, 400, 650, 1200];

    public function __construct()
    {
        $this->_sTable = ':better_ads';
    }

    /**
     * Get default genders
     * @return array
     */
    public function getDefaultGenders()
    {
        $aGenders = array_keys(Phpfox::getService('core')->getGenders(true));
        $aGenders = !empty($aGenders) ? $aGenders : [];
        array_unshift($aGenders, 0, 127);
        sort($aGenders);
        return $aGenders;
    }

    public function getHiddenAdsByUser($iUserId)
    {
        if (empty($iUserId)) {
            return false;
        }

        $sCacheId = $this->cache()->set('better_ads_hidden_user_' . (int)$iUserId);
        if (false === ($aIds = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('ads_id')
                ->from(Phpfox::getT('better_ads_hide'))
                ->where('module_id IS NULL AND user_id = ' . (int)$iUserId . ' AND ads_id IS NOT NULL')
                ->execute('getSlaveRows');
            $aIds = array_column($aRows, 'ads_id');
            $this->cache()->save($sCacheId, $aIds);
        }
        return $aIds;
    }


    /**
     * Get active feed sponsors
     *
     * @param null $iItemId
     *
     * @return array|int
     */
    public function getFeedSponsors($iItemId = null)
    {
        $sCacheId = $this->cache()->set('better_ads_feed_sponsors');
        if (false === ($aItems = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('item_id, sponsor_id')
                ->from(Phpfox::getT('better_ads_sponsor'))
                ->where('module_id = "feed" AND is_custom = 3')
                ->execute('getSlaveRows');
            $aItems = array_combine(array_column($aRows, 'item_id'), array_column($aRows, 'sponsor_id'));
            $this->cache()->save($sCacheId, $aItems);
            $this->cache()->group('sponsored_feed', $sCacheId);
        }
        return (!empty($iItemId) ? $aItems[$iItemId] : $aItems);
    }

    /**
     * Build section Menu
     */
    public function getSectionMenu()
    {
        $aFilterMenu = [
            _p('better_ads_manage_ads')          => 'ad.manage',
            _p('better_ads_manage_invoices')     => 'ad.invoice',
            _p('better_ads_manage_sponsorships') => 'ad.manage-sponsor',
        ];

        Phpfox_Template::instance()->buildSectionMenu('ad', $aFilterMenu);
    }

    /**
     * Get sponsored ads for a specific user.
     *
     * @param array  $aCond SQL condition.
     * @param string $sSort
     *
     * @return array ARRAY of ads.
     */
    public function getSponsorForUser($aCond, $sSort = 'sponsor_id DESC')
    {
        if (empty($sSort)) {
            $sSort = 'sponsor_id DESC';
        }
        $aAds = db()->select('s.*, i.invoice_id')
            ->from(':better_ads_sponsor', 's')
            ->leftJoin(':better_ads_invoice', 'i', 'i.ads_id = s.sponsor_id AND is_sponsor = 1')
            ->where($aCond)
            ->order($sSort)
            ->execute('getSlaveRows');

        foreach ($aAds as $iKey => &$aAd) {
            $aAd['count_view'] = $aAd['total_view'];
            $aAd['count_click'] = $aAd['total_click'];
            $aAd['start'] = Phpfox::getTime(BETTERADS_DATETIME_FORMAT, $aAd['start_date'], true);
            $aAd['type'] = $aAd['module_id'] == 'feed' ? _p('in_feed') : _p('sponsor_block');
            $this->_build($aAds, $iKey, $aAd);
        }

        return $aAds;
    }

    /**
     * Get ads from the main table based on SQL conditions.
     *
     * @param array      $aConds SQL conditions.
     * @param string     $sSort  SQL sorting.
     * @param int|string $iPage  Current page we are on.
     * @param int|string $iLimit Limit of ads to display per page.
     *
     * @return array 1st value is the total ads found, 2nd value is an ARRAY of all the ads we found.
     */
    public function get($aConds, $sSort = 'bads.ads_id DESC', $iPage = '', $iLimit = '')
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_get__start')) ? eval($sPlugin) : false);

        $iCnt = db()->select('COUNT(*)')
            ->from($this->_sTable, 'bads')
            ->leftJoin(':user', 'u', 'bads.user_id = u.user_id')
            ->where($aConds)
            ->execute('getSlaveField');

        $aAds = db()->select('bads.*')
            ->from($this->_sTable, 'bads')
            ->leftJoin(':user', 'u', 'bads.user_id = u.user_id')
            ->where($aConds)
            ->order($sSort)
            ->limit($iPage, $iLimit, $iCnt)
            ->execute('getSlaveRows');

        foreach ($aAds as $iKey => $aAd) {
            $this->_build($aAds, $iKey, $aAd);
        }

        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_get__end')) ? eval($sPlugin) : false);

        return [$iCnt, $aAds];
    }

    /**
     * Get sponsored ads from the main table based on SQL conditions.
     *
     * @param array      $aConds SQL conditions.
     * @param string     $sSort  SQL sorting.
     * @param int|string $iPage  Current page we are on.
     * @param int|string $iLimit Limit of ads to display per page.
     *
     * @return array 1st value is the total ads found, 2nd value is an ARRAY of all the ads we found.
     */
    public function getAdSponsor($aConds, $sSort = 'sponsor_id DESC', $iPage = '', $iLimit = '')
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_getadsponsor__start')) ? eval($sPlugin) : false);

        $iCnt = db()->select('COUNT(*)')
            ->from(':better_ads_sponsor', 's')
            ->leftJoin(':user', 'u', 'u.user_id = s.user_id')
            ->where($aConds)
            ->execute('getSlaveField');

        $aAds = db()->select('s.*, ' . Phpfox::getUserField())
            ->from(':better_ads_sponsor', 's')
            ->leftJoin(':user', 'u', 'u.user_id = s.user_id')
            ->where($aConds)
            ->order($sSort)
            ->limit($iPage, $iLimit, $iCnt)
            ->execute('getSlaveRows');

        foreach ($aAds as $iKey => &$aAd) {
            $aAd['count_view'] = $aAd['total_view'];
            $aAd['total_view'] = $aAd['impressions'];
            $aAd['count_click'] = $aAd['total_click'];
            $this->_build($aAds, $iKey, $aAd);
        }

        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_getadsponsor__end')) ? eval($sPlugin) : false);

        return [$iCnt, $aAds];
    }

    /**
     * Gets ads based on the location (not the block id but the identifier -string-)
     *
     * @param string $sPosition
     *
     * @return array|boolean
     */
    public function getForLocation($sPosition)
    {
        if (empty($sPosition)) {
            return false;
        }

        $aAds = db()->select('*')
            ->from($this->_sTable)
            ->where('location = "' . Phpfox::getLib('parse.input')->clean(str_replace('.', '|', $sPosition)) . '"')
            ->executeRows('getSlaveRows');

        if (is_array($aAds) != true || empty($aAds)) {
            return [];
        }

        $aAd = $aAds[rand(0, count($aAds) - 1)];

        return $aAd;
    }

    /**
     * Get an ads redirection URL and update the "click" count for the ad.
     *
     * @param int $iId ID# for the ad.
     *
     * @return string URL of the ad, which can be used to send the user to that page.
     * @throws \Exception
     */
    public function getAdRedirect($iId)
    {
        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_getadredirect__start')) ? eval($sPlugin) : false);

        db()->updateCounter('better_ads', 'count_click', 'ads_id', (int)$iId);

        $sRedirectCacheId = $this->cache()->set('ad_getAdRedirect_' . $iId);
        Phpfox::getLib('cache')->group('betterads', $sRedirectCacheId);

        $isCaching = true;
        if (!$aAd = $this->cache()->get($sRedirectCacheId)) {
            $aAd = db()->select('ads_id, location, url_link, is_cpm, total_click, count_click')
                ->from($this->_sTable)
                ->where('ads_id = ' . (int)$iId)
                ->execute('getSlaveRow');
            $this->cache()->save($sRedirectCacheId, $aAd);
            $isCaching = false;
        }

        if ($isCaching && $aAd['total_click'] > 0) {
            $aAd['count_click'] = db()->select('count_click')
                ->from(':better_ads')
                ->where('ads_id = ' . (int)$iId)
                ->execute('getSlaveField');
        }

        if (!isset($aAd['ads_id'])) {
            return Phpfox_Error::set(_p('better_ads_the_ad_you_are_looking_for_does_not_exist'));
        }

        $this->cache()->remove('ad_' . $aAd['location']);

        if (($aAd['is_cpm'] != 1 && $aAd['total_click'] > 0 && $aAd['count_click'] >= $aAd['total_click'])) {
            db()->update(':better_ads', ['is_active' => '0', 'is_custom' => 5], 'ads_id =' . $aAd['ads_id']);
            $this->cache()->remove('block_' . $aAd['location'] . '_ads');
            $this->cache()->remove('ad_getAdRedirect_' . $iId);
            if ($aAd['count_click'] > $aAd['total_click']) {
                return Phpfox_Error::set(_p('better_ads_the_ad_you_are_looking_for_does_not_exist'));
            }
        }

        Phpfox::getService('ad.report')->updateAdsCount($aAd['ads_id'], true);

        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_getadredirect__end')) ? eval($sPlugin) : false);

        return $aAd['url_link'];
    }

    /**
     * Get an ad for editing.
     *
     * @param int $iId Ad ID#.
     *
     * @return mixed FALSE if ad does not exist, ARRAY if ad exists.
     * @throws \Exception
     */
    public function getForEdit($iId)
    {
        static $aAd = null;

        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_getforedit__start')) ? eval($sPlugin) : false);

        if (isset($aAd['ads_id'])) {
            return $aAd;
        }

        $aAd = db()->select('*')
            ->from($this->_sTable)
            ->where('ads_id = ' . (int)$iId)
            ->execute('getSlaveRow');

        if (!isset($aAd['ads_id'])) {
            return Phpfox_Error::set(_p('better_ads_unable_to_find_this_ad'));
        }

        $aTemp = db()->select('cc.child_id')
            ->from(':country_child', 'cc')
            ->join(':better_ads_country', 'a', 'a.child_id = cc.child_id')
            ->where('a.ads_id = ' . $aAd['ads_id'])
            ->execute('getSlaveRows');

        foreach ($aTemp as $aState) {
            $aAd['province'][] = $aState['child_id'];
        }

        if (empty($aAd['country_iso'])) {
            $aCountries = db()->select('country_id')->from(':better_ads_country')->where('ads_id = ' . $aAd['ads_id'])->execute('getSlaveRows');
            if (!empty($aCountries)) {
                $aAd['countries_list'] = [];
                foreach ($aCountries as $aCountry) {
                    $aAd['countries_list'][] = $aCountry['country_id'];
                }
            }
        } else {
            $aAd['countries_list'] = [$aAd['country_iso']];
        }

        $aAd['start_date'] = Phpfox::getLib('date')->convertFromGmt($aAd['start_date']);
        $aAd['start_month'] = date('n', $aAd['start_date']);
        $aAd['start_day'] = date('j', $aAd['start_date']);
        $aAd['start_year'] = date('Y', $aAd['start_date']);
        $aAd['start_hour'] = date('H', $aAd['start_date']);
        $aAd['start_minute'] = date('i', $aAd['start_date']);

        if (!empty($aAd['end_date'])) {
            $aAd['end_month'] = date('n', $aAd['end_date']);
            $aAd['end_day'] = date('j', $aAd['end_date']);
            $aAd['end_year'] = date('Y', $aAd['end_date']);
            $aAd['end_hour'] = date('H', $aAd['end_date']);
            $aAd['end_minute'] = date('i', $aAd['end_date']);
            $aAd['end_option'] = true;
        }

        if (empty($aAd['total_view'])) {
            $aAd['view_unlimited'] = true;
        }

        if (empty($aAd['total_click'])) {
            $aAd['click_unlimited'] = true;
        }

        if (!empty($aAd['user_groups'])) {
            $aAd['user_groups'] = explode(',', $aAd['user_groups']);
        }

        if ((int)$aAd['total_view'] === 0) {
            $aAd['total_view'] = '';
        }

        if ((int)$aAd['total_click'] === 0) {
            $aAd['total_click'] = '';
        }

        if (!empty($aAd['postal_code'])) {
            $aAd['postal_code'] = implode(',', json_decode($aAd['postal_code']));
        }

        if (!empty($aAd['city_location'])) {
            $aAd['city_location'] = implode(',', json_decode($aAd['city_location']));
        }

        if (!empty($aAd['html_code'])) {
            $aMoreInfo = json_decode($aAd['html_code'], true);
            $aAd = array_merge($aAd, $aMoreInfo);
            if (empty($aAd['url_link'])) {
                $aAd['url_link'] = (isset($aMoreInfo['trimmed_url'])) ? $aMoreInfo['trimmed_url'] : '';
            }
        }

        if (!isset($aAd['gender'])) {
            $aGenders = Phpfox::getService('ad.get')->getDefaultGenders();
            $aAd['gender'] = $aGenders;
        } else {
            $aAd['gender'] = explode(',', $aAd['gender']);
        }

        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_getforedit__end')) ? eval($sPlugin) : false);

        return $aAd;
    }

    /**
     * Gets a record from the ad_sponsor table.
     *
     * @param int      $iId
     * @param int|null $iUser
     *
     * @return array|bool Information about the ad.|FALSE if the ad is not found.
     */
    public function getSponsor($iId, $iUser = null, $iInvoiceId = null)
    {
        if (!empty($iInvoiceId)) {
            db()->join(':better_ads_invoice', 'ai', 'ai.ads_id = s.sponsor_id AND ai.invoice_id = ' . (int)$iInvoiceId);
        } else {
            db()->leftJoin(':better_ads_invoice', 'ai', 'ai.ads_id = s.sponsor_id');
        }

        $aSponsor = db()->select('s.*, ai.invoice_id, ai.ads_id, ai.is_sponsor, ai.currency_id, ai.price, ai.status, ai.time_stamp, ai.time_stamp_paid')
            ->from(':better_ads_sponsor', 's')
            ->where((($iUser !== null) ? ('s.user_id = ' . (int)$iUser . ' AND ') : '') . 'sponsor_id = ' . (int)$iId)
            ->executeRow();

        if (empty($aSponsor)) {
            return false;
        }

        $sModule = $aSponsor['module_id'];
        $sFunction = 'getToSponsorInfo';
        if (strpos($sModule, '_') !== false) {
            $aModule = explode('_', $sModule);
            $sModule = $aModule[0];
            $sFunction = $sFunction . ucfirst($aModule[1]);
        }

        if (Phpfox::hasCallback($sModule, $sFunction)) {
            $aItem = Phpfox::callback($sModule . '.' . $sFunction, $aSponsor['item_id']);
            $aSponsor['paypal_msg'] = $aItem['paypal_msg'];
        }

        return $aSponsor;
    }

    /**
     * Get a payment plan based on a blocks position from the table "ad_plan".
     *
     * @param string $iPlanId Block ID#.
     *
     * @return bool|array FALSE if the plan does not exist.|Information about the plan.
     */
    public function getPlan($iPlanId)
    {
        static $aPlan = [];

        if (!isset($aPlan[$iPlanId])) {
            $aPlan[$iPlanId] = db()->select('*')
                ->from(':better_ads_plan')
                ->where('plan_id= ' . (int)$iPlanId . ' AND is_active = 1')
                ->execute('getSlaveRow');

            if (!isset($aPlan[$iPlanId]['plan_id'])) {
                $aPlan[$iPlanId] = false;
            } else {
                if (!empty($aPlan[$iPlanId]['cost']) && Phpfox::getLib('parse.format')->isSerialized($aPlan[$iPlanId]['cost'])) {
                    $aCosts = unserialize($aPlan[$iPlanId]['cost']);
                    $iLastCurrency = null;
                    foreach ($aCosts as $sKey => $iCost) {
                        if (Phpfox::getService('core.currency')->getDefault() == $sKey) {
                            $aPlan[$iPlanId]['default_cost'] = $iCost;
                            $aPlan[$iPlanId]['default_currency_id'] = $sKey;
                        }
                    }
                }
            }
        }

        return $aPlan[$iPlanId];
    }

    /**
     * Get all the plans from the table "ad_plan".
     *
     * @param bool $bKey TRUE to create a key for each row based on the block ID the plan is assigned to.
     *
     * @return array List of all the plans.
     */
    public function getPlans($bKey = false)
    {
        $sCacheId = $this->cache()->set('ad_getPlans');
        Phpfox::getLib('cache')->group('betterads', $sCacheId);

        if (($aPlans = $this->cache()->get($sCacheId)) === false) {
            $aPlans = db()->select('*')
                ->from(':better_ads_plan')
                ->execute('getSlaveRows');
            $this->cache()->save($sCacheId, $aPlans);
        }

        if ($bKey === true) {
            $aCache = $aPlans;
            $aPlans = [];
            foreach ($aCache as $aPlan) {
                $aPlans[$aPlan['block_id']] = $aPlan;
            }
            unset($aCache);
        }

        return $aPlans;
    }

    /**
     * Get all plans when create new ads
     *
     * @param bool $bFilterUserGroup
     *
     * @return array
     */
    public function getPlansForAdd($bFilterUserGroup = false)
    {
        if (!Phpfox::isAdminPanel()) {
            db()->where(['is_active' => 1]);
        }

        $aPlans = db()->select('*')
            ->from(':better_ads_plan')
            ->executeRows();

        $aReturns = [];
        $sCurrency = Phpfox::getService('user')->getCurrency();

        if (empty($sCurrency)) {
            $sCurrency = Phpfox::getService('ad.get')->getDefaultCurrency();
        }

        foreach ($aPlans as $aPlan) {
            $aAllowUserGroup = array_filter(explode(',', $aPlan['user_group']));

            if ($bFilterUserGroup && !empty($aAllowUserGroup) &&
                !in_array(Phpfox::getUserBy('user_group_id'), $aAllowUserGroup)
            ) {
                continue;
            }

            $aCost = unserialize($aPlan['cost']);
            $iCost = (isset($aCost[$sCurrency])) ? $aCost[$sCurrency] : 0;
            $aReturns[] = [
                'plan_id'      => $aPlan['plan_id'],
                'block_id'     => $aPlan['block_id'],
                'title'        => $aPlan['title'],
                'block_title'  => _p('better_ads_block_id', ['id' => $aPlan['block_id']]),
                'price_text'   => $iCost ? _p('better_ads_cost_money_per_action_type', [
                    'cost'  => $iCost,
                    'money' => $sCurrency,
                    'type'  => $aPlan['is_cpm'] ? '1000 ' . _p('better_ads_views') : _p('better_ads_click')
                ]) : _p('better_ads_free'),
                'cost'         => $aPlan['cost'],
                'is_cpm'       => $aPlan['is_cpm'],
                'default_cost' => $iCost,
                'currency'     => $sCurrency
            ];
        }

        return $aReturns;
    }

    /**
     * Get an invoice based on the ad ID# from the table "ad_invoice".
     *
     * @param int $iAdId Ad ID#.
     *
     * @return array|bool Information about the invoice.|FALSE if we are unable to find the invoice.
     */
    public function getInvoice($iAdId)
    {
        $aInvoice = db()->select('*')
            ->from(':better_ads_invoice')
            ->where('ads_id = ' . (int)$iAdId . ' AND is_sponsor = 0')
            ->execute('getSlaveRow');

        return (isset($aInvoice['invoice_id']) ? $aInvoice : false);
    }

    /**
     * Get invoices based on SQL conditions from the table "ad_invoice".
     *
     * @param string|array $aConds SQL conditions.
     * @param string       $sSort  SQL sorting.
     * @param int|string   $iPage  Page we are on.
     * @param int|string   $iLimit Total invoices to display per page.
     *
     * @return array 1st value is the total invoices, 2nd value is the ARRAY of invoices.
     */
    public function getInvoices($aConds, $sSort = 'time_stamp DESC', $iPage = '', $iLimit = '')
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(':better_ads_invoice', 'ai')
            ->join(':user', 'u', 'u.user_id = ai.user_id')
            ->where($aConds)
            ->execute('getSlaveField');

        $aInvoices = db()->select('u.*, ai.*')
            ->from(':better_ads_invoice', 'ai')
            ->join(':user', 'u', 'u.user_id = ai.user_id')
            ->where($aConds)
            ->order($sSort)
            ->limit($iPage, $iLimit, $iCnt)
            ->execute('getSlaveRows');

        foreach ($aInvoices as $iKey => $aInvoice) {
            switch ($aInvoice['status']) {
                case 'completed':
                    $aInvoices[$iKey]['status_phrase'] = _p('better_ads_paid');
                    break;
                case 'cancel':
                    $aInvoices[$iKey]['status_phrase'] = _p('better_ads_cancelled');
                    break;
                default:
                    $aInvoices[$iKey]['status_phrase'] = _p('unpaid');
                    break;
            }
        }

        return [$iCnt, $aInvoices];
    }

    /**
     * Get all the pending ads from the table "ad".
     *
     * @return int
     */
    public function getPendingCount()
    {
        return db()->select('COUNT(*)')
            ->from(':better_ads')
            ->where('is_custom = 2')
            ->execute('getSlaveField');
    }

    /**
     * Get all the pending sponsorships
     *
     * @return int
     */
    public function getPendingSponsorCount()
    {
        return db()->select('COUNT(*)')
            ->from(':better_ads_sponsor')
            ->where('is_custom = 2')
            ->executeField();
    }

    /**
     * Get all the plans from the table "ad_plan".
     *
     * @param bool $bIsCount
     * @param bool $bGetActive
     *
     * @return array
     */
    public function getPlacements($bIsCount = false, $bGetActive = false)
    {
        if ($bGetActive) {
            db()->where(['ap.is_active' => 1]);
        }

        if ($bIsCount) {
            return db()->select('count(*)')->from(':better_ads_plan', 'ap')->executeField();
        }

        $aPlacements = db()->select('ap.*, COUNT(DISTINCT a.ads_id) AS total_campaigns')
            ->from(':better_ads_plan', 'ap')
            ->leftJoin(':better_ads', 'a', 'a.location = ap.plan_id')
            ->group('ap.plan_id')
            ->executeRows();

        foreach ($aPlacements as &$aPlacement) {
            $aPlacement['type'] = $aPlacement['is_cpm'] ? _p('better_ads_cpm_cost_per_mille') : _p('better_ads_ppc_pay_per_click');
        }

        return $aPlacements;
    }

    /**
     * Get a specific ad placement plan.
     *
     * @param int $iId Plan ID#.
     *
     * @return array|bool Information about the plan.|FALSE if we cannot find the plan.
     */
    public function getPlacement($iId)
    {
        $sCacheId = $this->cache()->set('ad_getPlacement_' . (int)$iId);
        Phpfox::getLib('cache')->group('betterads', $sCacheId);

        if (!$aRow = $this->cache()->get($sCacheId)) {
            $aRow = db()->select('*')
                ->from(':better_ads_plan')
                ->where('plan_id = ' . (int)$iId)
                ->execute('getSlaveRow');
            $aRow['disallow_controller'] = explode(',', $aRow['disallow_controller']);
            $aRow['user_group'] = array_filter(explode(',', $aRow['user_group']));

            $this->cache()->save($sCacheId, $aRow);
        }

        return (isset($aRow['plan_id']) ? $aRow : false);
    }

    /**
     * Builds an ad campaign and removes any ads that do not match the
     * current environment (eg. gender, status, date etc...).
     *
     * @param array $aAds ARRAY of all the ads.
     * @param int   $iKey Key of the ARRAY.
     * @param array $aAd  ARRAY of the current ad we are building.
     *
     * @return array ARRAY of all the ads.
     */
    private function &_build(&$aAds, $iKey, $aAd)
    {
        $aAds[$iKey]['status'] = $this->getStatusPhrase($aAd['is_custom'], $aAd['start_date'], $aAd['end_date']);
        $aAds[$iKey]['user'] = Phpfox::getService('user')->get($aAd['user_id']);
        $aAds[$iKey]['start'] = Phpfox::getTime(BETTERADS_DATETIME_FORMAT, $aAd['start_date']);
        $aAds[$iKey]['type'] = (isset($aAd['module_id']) && $aAd['module_id'] == 'feed') ? _p('in_feed') : _p('sponsor_block');

        return $aAds;
    }

    /**
     * Get Default currency of site
     *
     * @return string
     */
    public function getDefaultCurrency()
    {
        $sCurrency = db()->select('currency_id')
            ->from(':currency')
            ->where('is_default = 1')
            ->executeField();

        return $sCurrency;
    }

    public function getThumbnailSizes()
    {
        ($sPlugin = Phpfox_Plugin::get('ad.service_ad_getimagesizes')) && eval($sPlugin);

        return $this->_aThumbnailSizes;
    }

    /**
     * Get upload photo params
     * @return array
     */
    public function getUploadPhotoParams()
    {
        return [
            'max_size'        => null,
            'type_list'       => ['jpg', 'jpeg', 'gif', 'png'],
            'upload_dir'      => Phpfox::getParam('ad.dir_image'),
            'upload_path'     => Phpfox::getParam('ad.url_image'),
            'thumbnail_sizes' => $this->getThumbnailSizes(),
            'label'           => ''
        ];
    }

    public function getStatusPhrase($iCustomId, $iStartDate = 0, $iEndDate = 0)
    {
        $aStatuses = [
            1 => _p('unpaid'),
            2 => _p('pending'),
            4 => _p('denied'),
            5 => _p('completed')
        ];

        if (!empty($aStatuses[$iCustomId])) {
            return $aStatuses[$iCustomId];
        }

        if ($iCustomId == 3 && $iStartDate) {
            return _p($this->getTypeOfApproved($iStartDate, $iEndDate));
        }

        return _p('approved');
    }

    public function getTypeOfApproved($iStartDate, $iEndDate)
    {
        if ($iStartDate > PHPFOX_TIME) {
            return 'upcoming';
        } else if ($iEndDate == 0 || $iEndDate > PHPFOX_TIME) {
            return 'running';
        } else {
            return 'ended';
        }
    }

    public function getPendingSponsorshipsCount()
    {
        $iPendingCount = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('better_ads_sponsor'))
            ->where('is_custom = 2')
            ->execute('getSlaveField');

        return $iPendingCount;
    }

    public function getApprovedCond($sType = 'running', $sAlias = '', $bNoEndDate = false)
    {
        $sPrefix = $sAlias ? "$sAlias." : '';
        switch ($sType) {
            case 'upcoming':
                $sCond = strtr("{prefix}start_date > {current}", [
                    '{prefix}'  => $sPrefix,
                    '{current}' => PHPFOX_TIME
                ]);
                break;
            case 'running':
                $sCond = strtr("{prefix}start_date <= {current}" . ($bNoEndDate ? '' : " AND ({prefix}end_date = 0 OR {prefix}end_date > {current})"),
                    [
                        '{prefix}'  => $sPrefix,
                        '{current}' => PHPFOX_TIME
                    ]);
                break;
            case 'ended':
                if ($bNoEndDate) {
                    $sCond = '';
                } else {
                    $sCond = strtr("{prefix}end_date <= {current} AND {prefix}end_date > 0", [
                        '{prefix}'  => $sPrefix,
                        '{current}' => PHPFOX_TIME
                    ]);
                }
                break;
            default:
                $sCond = '';
                break;
        }

        return $sCond;
    }

    public function getRecommendImageSizes()
    {
        return [
            1  => $this->getSideBlockRecommendImageSizes(),
            2  => $this->getMainBlockRecommendImageSizes(),
            3  => $this->getSideBlockRecommendImageSizes(),
            4  => $this->getMainBlockRecommendImageSizes(),
            5  => $this->getWideBlockRecommendImageSizes(),
            6  => $this->getFullWidthBlockRecommendImageSizes(),
            7  => $this->getMainBlockRecommendImageSizes(),
            8  => $this->getWideBlockRecommendImageSizes(),
            9  => $this->getSideBlockRecommendImageSizes(),
            10 => $this->getSideBlockRecommendImageSizes(),
            11 => $this->getWideBlockRecommendImageSizes(),
            12 => $this->getFullWidthBlockRecommendImageSizes(),
        ];
    }

    public function getSideBlockRecommendImageSizes()
    {
        return [
            // image type
            1 => '400 x 304 (' . html_entity_decode(_p('horizontal_image')) . ') ' . html_entity_decode(_p('or')) . ' 400 x 608 (' . html_entity_decode(_p('vertical_image')) . ')',
            // html type
            2 => '400 x 304'
        ];
    }

    public function getMainBlockRecommendImageSizes()
    {
        return [
            // image type
            1 => '608 x 224',
            // html type
            2 => '392 x 168'
        ];
    }

    public function getWideBlockRecommendImageSizes()
    {
        return [
            // image type
            1 => '1152 x 128',
            // html type
            2 => '918 x 128'
        ];
    }

    public function getFullWidthBlockRecommendImageSizes()
    {
        return [
            // image type
            1 => '(>1200) x 128',
            // html type
            2 => '(>1000) x 128'
        ];
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     *
     * @return null
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('ad.service_ad__call')) {
            return eval($sPlugin);
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);

        return null;
    }
}
