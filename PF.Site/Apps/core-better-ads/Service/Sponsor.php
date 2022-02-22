<?php

namespace Apps\Core_BetterAds\Service;

use Core\Lib;
use Phpfox;

/**
 * Class Sponsor
 * @package Apps\Core_BetterAds\Service
 */
class Sponsor extends \Phpfox_Service
{
    /**
     * @param string $sModuleId
     * @param array $aParam ||array('item_id' => $aUrl['item_id'], 'section' => $sSection)
     *
     * @return string|bool
     */
    public function getLink($sModuleId, $aParam)
    {
        $sLink = '';
        if (Phpfox::hasCallback($sModuleId, 'getLink')) {
            $sLink = Phpfox::callback($sModuleId . '.getLink', $aParam);
        }

        return $sLink;
    }

    public function getAllAppsHaveSponsor($bGetAppOnly = false)
    {
        $aApps = db()->select('m.product_id, m.phrase_var_name, u.module_id, p.title, a.apps_id')
            ->from(':user_group_setting', 'u')
            ->leftJoin(':module', 'm', 'u.module_id = m.module_id')
            ->leftJoin(':apps', 'a', 'a.apps_alias = u.module_id')
            ->leftJoin(':product', 'p', 'm.product_id = p.product_id')
            ->where(['name' => ['like' => '%sponsor_price%']])
            ->executeRows();

        $processedApps = [];
        foreach ($aApps as $app) {
            // get app name
            if (empty($app['apps_id'])) {
                if(empty($app['phrase_var_name'])) {
                    continue;
                }
                $sAppName = _p($app['phrase_var_name']);
            } else {
                $oApp = Lib::appInit($app['apps_id']);
                if (empty($oApp->name)) {
                    continue;
                }
                $sAppName = $oApp->name;
            }

            if (!isset($processedApps[$app['product_id']])) {
                // get product name
                $processedApps[$app['product_id']] = [
                    'title' => $app['title'],
                    'apps' => [
                        [
                            'id' => $app['module_id'],
                            'name' => $sAppName,
                            'link' => \Phpfox_Url::instance()->makeUrl('admincp.ad.sponsor-setting',
                                ['module_id' => $app['module_id']])
                        ]
                    ]
                ];
            } else {
                if (!in_array($app['module_id'], array_column($processedApps[$app['product_id']]['apps'], 'id'))) {
                    $processedApps[$app['product_id']]['apps'] = array_merge($processedApps[$app['product_id']]['apps'],
                        [
                            [
                                'id' => $app['module_id'],
                                'name' => $sAppName,
                                'link' => \Phpfox_Url::instance()->makeUrl('admincp.ad.sponsor-setting',
                                    ['module_id' => $app['module_id']])
                            ]
                        ]);
                }
            }
        }

        if ($bGetAppOnly) {
            $aAppsNames = [];
            foreach ($processedApps as $product) {
                $aAppsNames = array_merge($aAppsNames, $product['apps']);
            }

            return $aAppsNames;
        }

        return $processedApps;
    }

    public function getAppName($sModuleId)
    {
        if (empty($sModuleId)) {
            return '';
        }

        $sAppName = db()->select('phrase_var_name')->from(':module')->where(['module_id' => $sModuleId])->executeField();
        if ($sAppName != 'module_apps') {
            return _p($sAppName);
        }
        // get app id
        $sAppId = db()->select('apps_id')->from(':apps')->where(['apps_alias' => $sModuleId])->executeField();
        $app = Lib::appInit($sAppId);

        if (empty($app)) {
            return '';
        }

        return $app->name;
    }

    public function getAllSponsorSettings($iUserGroupId = 2, $sModuleId = '')
    {
        // get all items
        $aConds = ['name' => ['like' => '%sponsor_price%']];
        if (!empty($sModuleId)) {
            $aConds['module_id'] = $sModuleId;
        }
        $aItems = db()->select('name')->from(':user_group_setting')->where($aConds)->executeRows();
        $aSettings = $aModules = [];

        foreach (array_column($aItems, 'name') as $sSetting) {
            $aExtract = explode('_', $sSetting);
            $sModule = reset($aExtract);
            $sSection = str_replace('_sponsor_price', '', $sSetting);
            $aSection = explode('_', $sSection);
            $sItem = count($aSection) == 2 ? $aSection[1] : '';

            if ($sItem == '') {
                $settingNames = [
                    "can_sponsor_$sModule", "can_purchase_sponsor", "{$sModule}_sponsor_price", "auto_publish_sponsored_item", "can_purchase_sponsor_$sModule"
                ];
            } else {
                $settingNames = [
                    "can_sponsor_$sItem", "can_purchase_sponsor_$sItem", "{$sModule}_{$sItem}_sponsor_price", "auto_publish_sponsored_$sItem", "can_purchase_sponsor"
                ];
            }

            $aSettings = array_merge($aSettings, $settingNames);
            $aModules = array_merge($aModules, [$sModule]);
        }

        return $this->_get($iUserGroupId, null, $aSettings, $aModules);
    }

    private function _get($iGroupId, $iModuleId = null, $aUserGroupInclude = [], $aModulesInclude = [])
    {
        $excludes = Phpfox::getExcludeSettingsConditions();

        switch ($iGroupId) {
            case ADMIN_USER_ID:
                $sVar = 'default_admin';
                break;
            case GUEST_USER_ID:
                $sVar = 'default_guest';
                break;
            case STAFF_USER_ID:
                $sVar = 'default_staff';
                break;
            case NORMAL_USER_ID:
                $sVar = 'default_user';
                break;
            default:
                break;
        }

        if (!isset($sVar)) {
            $sVar = 'default_value';

            $this->database()->select('ugc.default_value, inherit_id, ')
                ->leftJoin(Phpfox::getT('user_group_custom'), 'ugc',
                    'ugc.user_group_id = ' . (int)$iGroupId
                    . ' AND ugc.module_id = user_group_setting.module_id AND ugc.name = user_group_setting.name')
                ->join(Phpfox::getT('user_group'), 'ug',
                    'ug.user_group_id = ' . (int)$iGroupId);
        }

        $aConds = ['user_group_setting.is_hidden' => 0];
        if (!empty($iModuleId)) {
            $aConds['user_group_setting.module_id'] = $iModuleId;
        }
        if (!empty($aUserGroupInclude)) {
            $aConds['user_group_setting.name'] = ['in' => '"' . implode('","', $aUserGroupInclude) . '"'];
        }
        if (!empty($aModulesInclude)) {
            $aConds['user_group_setting.module_id'] = [
                'in' => '"' . implode('","', $aModulesInclude) . '"'
            ];
        }
        $aRows = $this->database()
            ->select('user_group_setting.*, user_setting.value_actual, m.module_id AS module_name')
            ->from(':user_group_setting', 'user_group_setting')
            ->leftJoin(Phpfox::getT('module'), 'm',
                'm.module_id = user_group_setting.module_id')
            ->leftJoin(Phpfox::getT('product'), 'product',
                'product.product_id = user_group_setting.product_id AND product.is_active = 1')
            ->leftJoin(Phpfox::getT('user_setting'), 'user_setting',
                "user_setting.user_group_id = '" . $iGroupId
                . "' AND user_setting.setting_id = user_group_setting.setting_id")
            ->order('m.module_id ASC, user_group_setting.ordering ASC')
            ->where($aConds)
            ->executeRows();

        $aSettings = [];
        foreach ($aRows as $aRow) {
            $moduleId = $aRow['module_id'];
            if (isset($excludes[$moduleId]) and $excludes[$moduleId] == $aRow['product_id']) {
                continue;
            }

            $aParts = explode('</title><info>',
                Lib::phrase()->isPhrase('user_setting_' . $aRow['name'])
                    ? _p('user_setting_' . $aRow['name']) : $aRow['name']);

            $aRow['setting_name'] = strip_tags($aParts[0]);
            if (isset($aParts[1])) {
                $aRow['setting_info'] = strip_tags($aParts[1]);
            } else {
                $aRow['setting_info'] = '';
            }

            $aRow['setting_name'] = str_replace("\n", "<br />", $aRow['setting_name']);
            $aRow['user_group_id'] = $sVar;
            $aRow['values'] = unserialize($aRow['option_values']);
            $sModuleName = $aRow['module_name'];
            unset($aRow['module_name']);
            $this->_setType($aRow, $sVar);

            if (preg_match('/_sponsor_price/i', $aRow['name'])) {
                $aVals = Phpfox::getLib('parse.format')->isSerialized($aRow['value_actual']) ? unserialize($aRow['value_actual']) : _p('better_ads_no_price_set');
                if (is_array($aVals) && is_numeric(reset($aVals)))
                {
                    $aRow['value_actual'] = $aVals;
                }
                $aRow['isCurrency'] = 'Y';
            }

            $aSettings[$aRow['product_id']][$sModuleName][] = $aRow;
        }

        return $aSettings;
    }

    private function &_setType(&$aRow, $sVar)
    {
        if (empty($aRow['value_actual']) && $aRow['value_actual'] != '0') {
            if (is_null($aRow[$sVar]) && $aRow['inherit_id'] > 0) {
                switch ($aRow['inherit_id']) {
                    case ADMIN_USER_ID:
                        $sVar = 'default_admin';
                        break;
                    case GUEST_USER_ID:
                        $sVar = 'default_guest';
                        break;
                    case STAFF_USER_ID:
                        $sVar = 'default_staff';
                        break;
                    case NORMAL_USER_ID:
                        $sVar = 'default_user';
                        break;
                    default:

                        break;
                }

                $aRow['value_actual'] = $aRow[$sVar];
            } else {
                $aRow['value_actual'] = $aRow[$sVar];
            }
        }

        switch ($aRow['type_id']) {
            case 'boolean':
                if (strtolower($aRow['value_actual']) == 'true'
                    || strtolower($aRow['value_actual']) == 'false'
                ) {
                    $aRow['value_actual'] = (strtolower($aRow['value_actual'])
                    == 'true' ? '1' : '0');
                }
                settype($aRow['value_actual'], 'boolean');
                break;
            case 'integer':
                settype($aRow['value_actual'], 'integer');
                break;
            case 'array':
            case 'multi_text':
            case 'currency':
                $aRow['value_actual'] = Phpfox::getLib('setting')
                    ->getActualValue($aRow['type_id'], $aRow['value_actual'], $aRow);
                break;
        }

        return $aRow;
    }
}
