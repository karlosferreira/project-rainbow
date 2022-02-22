<?php

namespace Apps\Core_MobileApi\Service\Admincp;

defined('PHPFOX') or exit('NO DICE!');

use Apps\Core_MobileApi\Service\CoreApi;
use Phpfox;
use Phpfox_Error;
use Phpfox_Service;

class AdConfigService extends Phpfox_Service
{
    const ADMOB_BLOCK_NAME = 'banner_ad';
    const BANNER = 'banner';
    const INTERSTITIAL = 'interstitial';
    const REWARDED = 'rewarded';

    private $convertTime;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mobile_api_ads_configs');
        $this->convertTime = [
            'per_minute' => 60,
            'per_hour'   => 3600,
            'per_day'    => 86400
        ];

    }

    public function getAdsConfigs($aCond, $iPage, $iLimit, &$iCnt)
    {
        $aCond[] = 'AND 1=1';
        $iCnt = db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where($aCond)
            ->execute('getField');
        $aData = [];
        if ($iCnt) {
            $aData = db()->select('*')
                ->from($this->_sTable)
                ->where($aCond)
                ->limit($iPage, $iLimit, $iCnt)
                ->execute('getSlaveRows');
        }
        $aData = $this->convertAdConfig($aData);

        return $aData;
    }

    public function getAdConfigs($iId, $bForEdit = false)
    {
        $aRow = db()->select('*')
            ->from($this->_sTable)
            ->where('id = ' . (int)$iId)
            ->execute('getRow');
        if (empty($aRow)) {
            return [];
        }
        $aScreens = db()->select('*')
            ->from(':mobile_api_ads_config_screen')
            ->where('config_id =' . (int)$iId)
            ->execute('getSlaveRows');
        if (empty($aScreens)) {
            return $aRow;
        }
        $aRow['screens'] = array_map(function ($val) {
            return $val['screen'];
        }, $aScreens);
        return $this->convertAdConfig([$aRow], $bForEdit)[0];
    }

    public function getAllAdType()
    {
        return [
            self::BANNER       => _p('banner'),
            self::INTERSTITIAL => _p('interstitial'),
            self::REWARDED     => _p('rewarded')
        ];
    }

    public function getAllFrequencyCapping()
    {
        return [
            'no_frequency' => _p('no_frequency_capping'),
            'time'         => _p('time'),
            'views'        => _p('views'),
            'rand'         => _p('random')
        ];
    }

    public function getAllLocation()
    {
        return [
            'top'     => _p('top'),
            'content' => _p('main'),
            'bottom'  => _p('bottom'),
            'right'   => _p('right')
        ];
    }

    public function getAllPageOnMobile($bDetail = false)
    {
        $allScreen = (new CoreApi())->getScreenSettings([
            'screen_only' => true
        ]);
        $options = [];
        if (!empty($allScreen)) {
            foreach ($allScreen as $moduleId => $screens) {
                foreach ($screens as $screenKey => $data) {
                    if (empty($data['no_ads'])) {
                        if ($bDetail) {
                            $options[$screenKey] = [
                                'title'     => isset($data['screen_title']) ? $data['screen_title'] : _p($moduleId) . ' - ' . _p('unknown_page'),
                                'module_id' => $moduleId
                            ];
                        } else {
                            $options[$screenKey] = isset($data['screen_title']) ? $data['screen_title'] : _p($moduleId) . ' - ' . _p('unknown_page');
                        }
                    }
                }
            }
        }
        return $options;
    }

    public function addAdConfigs($aVals, $bIsEdit = false)
    {
        if (!$this->verifyAddConfig($aVals)) {
            return false;
        }
        if (!$bIsEdit) {
            $aOldData = $this->getAdConfigByScreen($aVals['screens'], $aVals['type']);
            if (!empty($aOldData)) {
                return Phpfox_Error::set(_p('duplicate_ad_on_the_same_page_config_notice'));
            }
        } else {
            $aRow = $this->getAdConfigs($aVals['id'], true);
            if (empty($aRow)) {
                return Phpfox_Error::set(_p('unable_to_find_the_ad_config_you_are_looking_for'));
            }
            $aOldData = $this->getAdConfigByScreen($aVals['screens'], $aVals['type'], $aVals['id']);
            if (!empty($aOldData)) {
                return Phpfox_Error::set(_p('duplicate_ad_on_the_same_page_config_notice'));
            }
        }
        $location_priority = [];
        if ($aVals['type'] == self::BANNER) {
            foreach ($aVals['location'] as $key => $location) {
                $location_priority[] = [
                    'location' => $location,
                    'priority' => isset($aVals['priority'][$key]) ? $aVals['priority'][$key] : 1
                ];
            }
        }
        $aScreen = $this->getAllPageOnMobile(true);

        $aDisallow = [];
        $aUserGroups = Phpfox::getService('user.group')->get();
        if (isset($aVals['allow_access'])) {
            foreach ($aUserGroups as $aUserGroup) {
                if (!in_array($aUserGroup['user_group_id'], $aVals['allow_access'])) {
                    $aDisallow[] = $aUserGroup['user_group_id'];
                }
            }
        } else {
            foreach ($aUserGroups as $aUserGroup) {
                $aDisallow[] = $aUserGroup['user_group_id'];
            }
        }
        $capping = isset($aVals['frequency_capping']) ? $aVals['frequency_capping'] : '';
        if (!$bIsEdit) {
            $aInsert = [
                'name'                    => $this->preParse()->clean($aVals['name'], 255),
                'user_id'                 => Phpfox::getUserId(),
                'time_stamp'              => PHPFOX_TIME,
                'type'                    => $aVals['type'],
                'view_capping'            => (isset($aVals['view_capping']) && $capping == 'views') ? intval($aVals['view_capping']) : 0,
                'time_capping_impression' => (isset($aVals['time_capping_impression']) && $capping == 'time') ? intval($aVals['time_capping_impression']) : 0,
                'time_capping_frequency'  => (isset($aVals['time_capping_frequency']) && $capping == 'time') ? $aVals['time_capping_frequency'] : '',
                'frequency_capping'       => $capping,
                'location_priority'       => serialize($location_priority),
                'is_stick'                => isset($aVals['is_stick']) ? $aVals['is_stick'] : 0,
                'is_active'               => isset($aVals['is_active']) ? $aVals['is_active'] : 1,
                'disallow_access'         => (count($aDisallow) ? serialize($aDisallow) : null)
            ];
            $iId = db()->insert($this->_sTable, $aInsert);
        } else {
            $aUpdate = [
                'name'                    => $this->preParse()->clean($aVals['name'], 255),
                'type'                    => $aVals['type'],
                'view_capping'            => (isset($aVals['view_capping']) && $capping == 'views') ? intval($aVals['view_capping']) : 0,
                'time_capping_impression' => (isset($aVals['time_capping_impression']) && $capping == 'time') ? intval($aVals['time_capping_impression']) : 0,
                'time_capping_frequency'  => (isset($aVals['time_capping_frequency']) && $capping == 'time') ? $aVals['time_capping_frequency'] : '',
                'frequency_capping'       => $capping,
                'location_priority'       => serialize($location_priority),
                'is_stick'                => isset($aVals['is_stick']) ? $aVals['is_stick'] : 0,
                'is_active'               => isset($aVals['is_active']) ? $aVals['is_active'] : 1,
                'disallow_access'         => (count($aDisallow) ? serialize($aDisallow) : null)
            ];
            $iId = $aVals['id'];
            if (db()->update($this->_sTable, $aUpdate, 'id = ' . (int)$iId)) {
                //Remove old screen
                db()->delete(':mobile_api_ads_config_screen', 'config_id =' . (int)$iId);
            }
        }
        foreach ($aVals['screens'] as $screen) {
            db()->insert(Phpfox::getT('mobile_api_ads_config_screen'), [
                'config_id' => $iId,
                'screen'    => $screen,
                'module_id' => $aScreen[$screen]['module_id'],
            ]);
        }
        return $iId;
    }

    private function verifyAddConfig($aVals)
    {
        if (empty($aVals['name'])) {
            return Phpfox_Error::set(_p('name_is_required'));
        }
        if (empty($aVals['screens'])) {
            return Phpfox_Error::set(_p('please_select_page_to_apply'));
        }
        if (empty($aVals['type'])) {
            return Phpfox_Error::set(_p('type_is_required'));
        }
        if ($aVals['type'] == self::BANNER) {
            if (empty($aVals['location'])) {
                return Phpfox_Error::set(_p('please_select_location_and_priority'));
            }
            if (empty($aVals['frequency_capping'])) {
                $aVals['frequency_capping'] = 'no_frequency';
            }
            if ($aVals['frequency_capping'] == 'time' && (empty($aVals['time_capping_impression']) || empty($aVals['time_capping_frequency']) || (int)$aVals['time_capping_impression'] < 0)) {
                return Phpfox_Error::set(_p('please_add_number_of_impressions_to_show_for_frequency_capping'));
            } else if ($aVals['frequency_capping'] == 'views' && (empty($aVals['view_capping']) || (int)$aVals['view_capping'] < 0)) {
                return Phpfox_Error::set(_p('please_add_number_of_accessed_page_to_show_for_frequency_capping'));
            }
        }
        return true;
    }

    /**
     * @param      $screens array
     * @param      $type
     * @param null $configId
     *
     * @return array|int|string
     */
    public function getAdConfigByScreen($screens, $type, $configId = null)
    {
        $data = db()->select('ac.*, acs.screen')
            ->from($this->_sTable, 'ac')
            ->join(':mobile_api_ads_config_screen', 'acs', 'acs.config_id = ac.id')
            ->where('acs.screen IN (\'' . implode('\',\'', $screens) . '\') AND ac.type = \'' . $type . '\' AND ac.is_active = 1' . ($configId != null ? ' AND ac.id != ' . (int)$configId : ''))
            ->execute('getSlaveRows');
        $this->convertAdConfig($data);
        return $data;
    }

    private function convertAdConfig($data, $bForEdit = false)
    {
        if (is_array($data) && count($data)) {
            foreach ($data as $key => $val) {
                $data[$key]['location_priority'] = unserialize($val['location_priority']);
                if ($bForEdit) {
                    $data[$key]['location'] = [];
                    $data[$key]['priority'] = [];
                    foreach ($data[$key]['location_priority'] as $nKey => $locationData) {
                        $data[$key]['location'][] = $locationData['location'];
                        $data[$key]['priority'][] = $locationData['priority'];
                    }
                }
                $data[$key]['disallow_access'] = empty($val['disallow_access']) ? [] : unserialize($val['disallow_access']);
            }
        }
        return $data;
    }

    public function deleteAdConfig($configId)
    {
        $aRow = $this->getAdConfigs($configId);
        if (empty($aRow['id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_ad_config_you_are_looking_for'));
        }
        db()->delete($this->_sTable, 'id = ' . (int)$configId);
        db()->delete(':mobile_api_ads_config_screen', 'config_id =' . (int)$configId);
        return true;
    }

    public function toggleActiveMenu($configId, $iActive, $aDuplicateIds = null)
    {
        Phpfox::isAdmin(true);
        $iActive = (int)$iActive;
        db()->update($this->_sTable, [
            'is_active' => ($iActive == 1 ? 1 : 0)
        ], 'id = ' . (int)$configId);

        if ($iActive == 1 && !empty($aDuplicateIds)) {
            db()->update($this->_sTable, [
                'is_active' => 0,
            ], 'id IN (' . $aDuplicateIds . ')');
        }
        return true;
    }

    public function getAllConfigsToSetting($aSettings)
    {
        if (empty($aSettings)) {
            return [];
        }

        $aConfigs = db()->select('*')
            ->from($this->_sTable, 'ac')
            ->join(':mobile_api_ads_config_screen', 'acs', 'acs.config_id = ac.id')
            ->where('ac.is_active = 1')
            ->execute('getSlaveRows');
        $aConfigs = $this->convertAdConfig($aConfigs);
        $userGroupId = Phpfox::getUserBy('user_group_id');
        if (is_array($aConfigs) && count($aConfigs)) {
            $androidBannerUID = Phpfox::getParam('mobile.mobile_android_admob_banner_uid');
            $androidInterstitialUID = Phpfox::getParam('mobile.mobile_android_admob_interstitial_uid');
            $androidRewardedUID = Phpfox::getParam('mobile.mobile_android_admob_rewarded_uid');

            $iosBannerUID = Phpfox::getParam('mobile.mobile_ios_admob_banner_uid');
            $iosInterstitialUID = Phpfox::getParam('mobile.mobile_ios_admob_interstitial_uid');
            $iosRewardedUID = Phpfox::getParam('mobile.mobile_ios_admob_rewarded_uid');

            foreach ($aConfigs as $key => $aConfig) {

                //Disallow user group
                if (!$userGroupId || in_array($userGroupId, $aConfig['disallow_access'])) {
                    continue;
                }
                $module = $aConfig['module_id'];
                $screen = $aConfig['screen'];
                if (!isset($aSettings[$module][$screen])) {
                    $aSettings[$module][$screen] = [];
                }
                if ($aConfig['type'] == self::BANNER) {
                    uasort($aConfig['location_priority'], function ($a, $b) {
                        return $a['priority'] > $b['priority'];
                    });
                    foreach ($aConfig['location_priority'] as $localPriority) {
                        $bannerBlock = [
                            'component'     => self::ADMOB_BLOCK_NAME,
                            'androidUnitId' => $androidBannerUID,
                            'iosUnitId'     => $iosBannerUID,
                            'stick'         => (bool)$aConfig['is_stick'],
                            'capping'       => $aConfig['frequency_capping'],
                            'ordering'      => (int)$localPriority['priority'],
                        ];
                        if ($aConfig['frequency_capping'] == 'time') {
                            $bannerBlock['limit'] = (int)$aConfig['time_capping_impression'];
                            $bannerBlock['time'] = $this->convertTime[$aConfig['time_capping_frequency']];
                        } else if ($aConfig['frequency_capping'] == 'views') {
                            $bannerBlock['limit'] = (int)$aConfig['view_capping'];
                        }
                        $currentBlock = isset($aSettings[$module][$screen][$localPriority['location']]) ? $aSettings[$module][$screen][$localPriority['location']] : [];
                        $realPosition = $localPriority['priority'] > 0 ? $localPriority['priority'] - 1 : 0;
                        if ($localPriority['location'] == 'content') {
                            if (!empty($currentBlock['embedComponents']) && count($currentBlock['embedComponents']) > $realPosition) {
                                array_splice($currentBlock['embedComponents'], $realPosition, 0, [$bannerBlock]);
                            } else {
                                $currentBlock['embedComponents'][] = $bannerBlock;
                            }
                        } else {
                            if (!empty($currentBlock) && count($currentBlock) > $realPosition) {
                                $convertArr = false;
                                foreach ($currentBlock as $curr) {
                                    if (is_array($curr)) {
                                        $convertArr = true;
                                        break;
                                    }
                                }
                                $currentBlock = $convertArr ? $currentBlock : [$currentBlock];
                                array_splice($currentBlock, $realPosition, 0, [$bannerBlock]);
                            } else {
                                $currentBlock[] = $bannerBlock;
                            }
                        }
                        $aSettings[$module][$screen][$localPriority['location']] = $currentBlock;
                    }
                } else {
                    $otherAdBlock = [
                        'type'          => $aConfig['type'],
                        'androidUnitId' => $aConfig['type'] == self::INTERSTITIAL ? $androidInterstitialUID : $androidRewardedUID,
                        'iosUnitId'     => $aConfig['type'] == self::INTERSTITIAL ? $iosInterstitialUID : $iosRewardedUID,
                    ];
                    $aSettings[$module][$screen]['fullscreen'][] = $otherAdBlock;
                }
            }
        }
        return $aSettings;
    }
}