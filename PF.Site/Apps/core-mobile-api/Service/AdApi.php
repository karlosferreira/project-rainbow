<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\AdResource;
use Phpfox;
use Phpfox_Plugin;

class AdApi extends AbstractResourceApi implements MobileAppSettingInterface
{

    protected $adService;

    protected $processService;

    protected $adGetService;

    protected $blockMapping;

    protected $sTable;

    public function __naming()
    {
        return [
            'ad/view-sponsor/:id' => [
                'get' => 'viewSponsorItem'
            ]
        ];
    }

    public function __construct()
    {
        parent::__construct();
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->processService = Phpfox::getService('ad.process');
            $this->adService = Phpfox::getService('ad');
            $this->adGetService = Phpfox::getService('ad.get');
        }
        $this->_sTable = Phpfox::getT('better_ads');
        $this->blockMapping = [
            'right' => '1,3,9,10',
            'top' => '6,11',
            'main' => '2,4,7',
            'bottom' => '5,8,12'
        ];
    }

    function findAll($params = [])
    {
        $params = $this->resolver
            ->setDefined(['location', 'screen', 'resource_name'])
            ->setRequired(['location', 'screen', 'resource_name'])
            ->setAllowedValues('location', ['top', 'bottom', 'main', 'right'])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $controller = $this->mapScreenToController($params['resource_name'], $params['screen']);

        $ads = $this->getForLocation($params['location'], $controller);

        if (count($ads)) {
            $this->processRows($ads);
        }
        return $this->success($ads);
    }

    function findOne($params)
    {
        return null;
    }

    function create($params)
    {

    }

    function update($params)
    {
        return null;
    }

    function patchUpdate($params)
    {
        return null;
    }

    function delete($params)
    {
        return null;
    }

    function form($params = [])
    {
        return null;
    }

    function loadResourceById($id, $returnResource = false)
    {
        return null;
    }

    function approve($params)
    {
        return null;
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }

    public function mapScreenToController($resource_name, $screen)
    {
        $service = NameResource::instance()->getApiServiceByResourceName(str_replace('_', '-', $resource_name));
        if (!is_callable([$service, 'screenToController'])) {
            return '';
        }

        $execResourceName = (new ScreenSetting('ad'))->convertResourceName($resource_name);
        $execScreen = str_replace($execResourceName, '', $screen);

        $controllerMap = $service->screenToController();

        return isset($controllerMap[$execScreen]) ? $controllerMap[$execScreen] : '';
    }

    public function getForLocation($location, $controller)
    {
        static $aCacheAd = [];
        if (!isset($this->blockMapping[$location])) {
            return [];
        }
        $iBlockId = $this->blockMapping[$location];

        if (isset($aCacheAd[$location]) && !$this->isUnitTest()) {
            return $aCacheAd[$location];
        }

        (($sPlugin = Phpfox_Plugin::get('mobil.service_ad_getforblock__start')) ? eval($sPlugin) : false);

        if ($this->getSetting()->getUserSetting('ad.better_ads_show_ads') == false) {
            $aCacheAd[$location] = [];

            return [];
        }
        $bMultiAds = false;
        if ($location == 'right') {
            $bMultiAds = true;
        }

        $bUpdateCounter = true;
//        if (\Phpfox_Module::instance()->getFullControllerName() == 'error.404') {
//            $bUpdateCounter = false;
//        }

        $aConds = [
            'a.is_custom' => 3,
            // get active ads
            'a.is_active' => 1,
            // get ads of current block
            ' AND ap.block_id IN (' . $iBlockId . ')',
            // placement must be active
            'ap.is_active' => 1,
            // start date
            ' AND a.start_date <= ' . PHPFOX_TIME,
            // end date
            ' AND (a.end_date = 0 OR a.end_date >= ' . PHPFOX_TIME . ')'
        ];

        $sCacheId = $this->cache()->set('block_' . $location . '_ads');
        if (($aRows = $this->cache()->get($sCacheId, 5)) === false) {
            $aRows = db()->select('a.*, ap.block_id, ap.disallow_controller, ac.child_id, ac.country_id')
                ->from($this->_sTable, 'a')
                ->join(':better_ads_plan', 'ap', 'ap.plan_id=a.location')
                ->leftJoin(':better_ads_country', 'ac', 'ac.ads_id = a.ads_id')
                ->where($aConds)
                ->executeRows();

            $this->cache()->save($sCacheId, $aRows);
            $this->cache()->group('betterads', $sCacheId);
        }

        $aAds = [];
        foreach ($aRows as $iKey => $aRow) {
            if (!$this->_isValidDisplayAd($aRow, $controller)) {
                continue;
            }

            $iAdId = $aRow['ads_id'];

            if (!isset($aAds[$iAdId])) {
                $aAds[$iAdId] = $aRow;
                $aAds[$iAdId]['country_child_id'] = [];
                $aAds[$iAdId]['countries_list'] = [];
                unset($aAds[$iAdId]['country_id']);
            }

            if (!empty($aRow['child_id'])) {
                $aAds[$iAdId]['country_child_id'][] = $aRow['child_id'];
                unset($aAds[$iAdId]['child_id']);
            }

            if (!empty($aRow['country_id'])) {
                $aAds[$iAdId]['countries_list'][$aRow['country_id']] = $aRow['country_id'];
            }

            if (!empty($aRow['html_code'])) {
                $aAds[$iAdId]['html_code'] = str_replace('target="_blank"', 'target="_blank" class="no_ajax_link"',
                    $aRow['html_code']);

                $aAds[$iAdId] = array_merge($aAds[$iAdId], json_decode($aAds[$iAdId]['html_code'], true));
            }
            $aAds[$iAdId]['url_link'] = \Phpfox_Url::instance()->makeUrl('ad', ['id' => $iAdId]);
        }


        if ($sPlugin = Phpfox_Plugin::get('ad.service_ads_getforblock__1')) {
            eval($sPlugin);
        }

        if (empty($aAds)) {
            $aCacheAd[$location] = [];

            return [];
        }

        if ($bMultiAds) {
            $iTotal = min(intval($this->getSetting()->getAppSetting('ad.better_ads_number_ads_per_location')), count($aAds));
            shuffle($aAds);
            $aAds = array_slice($aAds, 0, $iTotal);

            foreach ($aAds as $aRow) {
                if ($bUpdateCounter) {
                    db()->updateCounter('better_ads', 'count_view', 'ads_id', $aRow['ads_id']);
                    Phpfox::getService('ad.report')->updateAdsCount($aRow['ads_id']);
                }
            }
            $aCacheAd[$location] = $aAds;

            return $aAds;
        }

        $aRow = $aAds[array_rand($aAds)];
        if ($bUpdateCounter) {
            db()->updateCounter('better_ads', 'count_view', 'ads_id', $aRow['ads_id']);
            Phpfox::getService('ad.report')->updateAdsCount($aRow['ads_id']);
        }

        (($sPlugin = Phpfox_Plugin::get('ad.service_ad_getforblock__end')) ? eval($sPlugin) : false);

        return [$aRow];
    }

    private function _isValidDisplayAd($aAd, $controller)
    {
        // limit gender
        $iCurrentUserGenderId = intval(Phpfox::getUserBy('gender'));
        if (!in_array($iCurrentUserGenderId, explode(',', $aAd['gender']))) {
            return false;
        }

        // limit age
        $iCurrentUserAge = intval(Phpfox::getUserBy('age'));
        if (((int)$aAd['age_from'] > 0 && (int)$iCurrentUserAge < (int)$aAd['age_from']) || ((int)$aAd['age_to'] > 0 && (int)$aAd['age_to'] < (int)$iCurrentUserAge)) {
            return false;
        }

        $sCurrentLanguage = Phpfox::getLib('locale')->getLangId();
        if (!in_array($sCurrentLanguage, explode(',', $aAd['languages']))) {
            return false;
        }

        $aHideSponsorIds = Phpfox::getService('ad.get')->getHiddenAdsByUser(Phpfox::getUserId());
        if ($aHideSponsorIds && in_array($aAd['ads_id'], $aHideSponsorIds)) {
            return false;
        }

        // check total view/click
        if (($aAd['is_cpm'] == 1 && $aAd['total_view'] > 0 && $aAd['count_view'] >= $aAd['total_view'])
            || $aAd['is_cpm'] != 1 && $aAd['total_click'] > 0 && $aAd['count_click'] >= $aAd['total_click']
        ) {
            db()->update(':better_ads', ['is_active' => '0', 'is_custom' => 5], 'ads_id = ' . (int)$aAd['ads_id']);
            return false;
        }

        // disallow controller
        if (!empty($aAd['disallow_controller'])) {
            $sControllerName = $controller;
            $aParts = explode(',', $aAd['disallow_controller']);
            foreach ($aParts as $sPart) {
                $sPart = trim($sPart);
                // str_replace for marketplace.invoice/index
                // str_replace for music.browse/album
                if ($sControllerName == $sPart || (str_replace('/index', '',
                            $sControllerName) == $sPart) || (str_replace('/', '.', $sControllerName) == $sPart)
                ) {
                    return false;
                }
            }
        }

        $aLocationInfo = $this->adService->getLocationInformation();

        // Check for Country, Postal Code and City
        if (setting('better_ads_advanced_ad_filters')) {
            $sUserPostalCode = isset($aLocationInfo['postal_code']) ? $aLocationInfo['postal_code'] : '';
            $sUserCityLocation = isset($aLocationInfo['city_location']) ? $aLocationInfo['city_location'] : '';
            $sUserCountryIso = Phpfox::getUserBy('country_iso');
            $aAd['postal_code'] = json_decode(trim($aAd['postal_code']));
            $aAd['city_location'] = json_decode(trim($aAd['city_location']), true);
            $aAd['country_iso'] = trim($aAd['country_id']);

            if (!empty($aAd['postal_code'])) {
                $bSkip = true;
                foreach ($aAd['postal_code'] as $sCode) {
                    if (strtolower($sCode) == strtolower($sUserPostalCode) || $sCode == '') {
                        $bSkip = false;
                        break;
                    }
                }
                if ($bSkip) {
                    return false;
                }
            }

            if (!empty($aAd['city_location'])) {
                $bSkip = true;
                foreach ($aAd['city_location'] as $sCity) {
                    if (strtolower($sCity) == strtolower($sUserCityLocation) || $sCity == '') {
                        $bSkip = false;
                        break;
                    }
                }
                if ($bSkip) {
                    return false;
                }
            }

            if (!empty($aAd['country_iso']) && (empty($sUserCountryIso) || strpos($aAd['country_iso'], $sUserCountryIso) === false)) {
                return false;
            }
        }

        return true;
    }

    public function processRow($item)
    {
        $resource = $this->populateResource(AdResource::class, $item);
        return $resource->displayShortFields()->toArray();
    }

    public function viewSponsorItem($params)
    {
        $id = $this->resolver->resolveId($params);

        if ($id) {
            $sponsor = $this->adGetService->getSponsor($id);
            // split the module if there's a subsection
            $module = $sponsor['module_id'];
            if (strpos($sponsor['module_id'], '_') !== false) {
                $aModule = explode('_', $sponsor['module_id']);
                $module = $aModule[0];
            }

            if (Phpfox::isModule($module)) {
                if ($sponsor['user_id'] != Phpfox::getUserId()) {
                    $this->database()->update(':better_ads_sponsor',
                        ['total_click' => $sponsor['total_click'] + 1],
                        'sponsor_id = ' . $sponsor['sponsor_id'] . ' AND user_id != ' . $this->getUser()->getId());
                }
            }
        }

        return $this->success([]);
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        return new MobileApp('ad', [
            'title' => $l->translate('ads'),
            'home_view' => 'tab',
            'main_resource' => new AdResource([]),
            'other_resources' => []
        ]);
    }
}