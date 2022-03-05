<?php

namespace Apps\Core_MobileApi\Service\Admincp;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Service;

class MenuService extends Phpfox_Service
{
    private $_sLanguageId;
    private $_aLanguages;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('mobile_api_menu_item');
        $this->_sLanguageId = Phpfox::getLanguageId();
        $this->_aLanguages = Phpfox::getService('language')->getAll();
    }

    public function getForAdmin($type = null)
    {
        $sCacheId = $this->cache()->set('mobile_menus_manage_' . $this->_sLanguageId . '_' . $type);
        $this->cache()->group('mobile', $sCacheId);
        if (!$aMenus = $this->cache()->get($sCacheId)) {
            $aMenus = db()->select('mc.*')
                ->from($this->_sTable, 'mc')
                ->order('mc.ordering ASC')
                ->where($type ? 'mc.item_type = \'' . $type . '\'' : '1=1')
                ->execute('getSlaveRows');
            $this->cache()->save($sCacheId, $aMenus);
        }
        return $aMenus;
    }

    public function getSectionHeader($sType)
    {
        return db()->select('*')
            ->from($this->_sTable)
            ->where('item_type = \'section-' . $sType . '\'')
            ->execute('getSlaveRow');
    }

    public function getForEdit($iMenuId)
    {
        $aMenu = db()->select('*')
            ->from($this->_sTable)
            ->where('item_id=' . (int)$iMenuId)
            ->execute('getSlaveRow');
        return $aMenu;
    }

    public function getTotalMenu()
    {
        return db()->select('COUNT(*)')->from($this->_sTable)->execute('getField');
    }

    /**
     * @param $iMenuId
     * @param $iActive
     *
     * @return bool
     */
    public function toggleActiveMenu($iMenuId, $iActive)
    {
        Phpfox::isAdmin(true);
        $aMenu = $this->getMenu($iMenuId);
        if (!$aMenu) {
            return false;
        }
        $iActive = (int)$iActive;
        db()->update($this->_sTable, [
            'is_active' => ($iActive == 1 ? 1 : 0)
        ], 'item_id= ' . (int)$iMenuId);

        $this->clearCache($aMenu['item_type']);
    }

    public function getMenu($iMenuId)
    {
        return db()->select('*')->from($this->_sTable)->where(['item_id' => (int)$iMenuId])->execute('getRow');
    }

    public function update($aVals)
    {
        //Verify data
        if (!isset($aVals['edit_id'])) {
            return false;
        }
        $aMenu = $this->getMenu($aVals['edit_id']);
        if (!$aMenu) {
            return false;
        }
        $finalPhrase = '';
        $bNotEmptyAll = false;
        if (isset($aVals['name']) && Phpfox::isPhrase($aVals['name'])) {
            $finalPhrase = $aVals['name'];
            //Update phrase
            foreach ($this->_aLanguages as $aLanguage) {
                if (isset($aVals['name_' . $aLanguage['language_id']])) {
                    $name = $aVals['name_' . $aLanguage['language_id']];
                    Phpfox::getService('language.phrase.process')->updateVarName($aLanguage['language_id'],
                        $aVals['name'], $name);
                }
            }
        } else {
            //Verify name
            $aFirstLang = current($this->_aLanguages);
            $aText = [];
            foreach ($this->_aLanguages as $aLanguage) {
                if (isset($aVals['name_' . $aLanguage['language_id']]) && !empty($aVals['name_' . $aLanguage['language_id']])) {
                    $bNotEmptyAll = true;
                    $aText[$aLanguage['language_id']] = $aVals['name_' . $aLanguage['language_id']];
                }
            }
            $name = $aVals['name_' . $aFirstLang['language_id']];
            $phrase_var_name = 'core_mobile_api_' . md5('Mobile Api' . $name . PHPFOX_TIME);

            $aValsPhrase = [
                'var_name' => $phrase_var_name,
                'text'     => $aText
            ];
            if ($bNotEmptyAll) {
                $finalPhrase = \Phpfox::getService('language.phrase.process')->add($aValsPhrase);
            }
        }
        $disallow = [];
        $userGroups = Phpfox::getService('user.group')->get();
        if (!isset($aVals['allow_all'])) {
            if (isset($aVals['allow_access'])) {
                foreach ($userGroups as $userGroup) {
                    if (!in_array($userGroup['user_group_id'], $aVals['allow_access'])) {
                        $disallow[] = $userGroup['user_group_id'];
                    }
                }
            } else {
                foreach ($userGroups as $userGroup) {
                    $disallow[] = $userGroup['user_group_id'];
                }
            }
        }
        db()->update($this->_sTable, [
            'name'       => $finalPhrase,
            'icon_color' => isset($aVals['icon_color']) ? $aVals['icon_color'] : '#2681d5',
            'disallow_access' => (isset($aVals['disallow_access'])) ? null : (count($disallow) ? serialize($disallow) : null),
            ], 'item_id = ' . $aVals['edit_id']
        );
        $this->clearCache($aMenu['item_type']);
        return true;
    }

    public function getForBrowse()
    {
        $aMenus = db()->select('mc.*')
            ->from($this->_sTable, 'mc')
            ->join(':module', 'm', 'm.module_id = mc.module_id')
            ->where('mc.is_active = 1 AND m.is_active = 1')
            ->order('mc.ordering ASC')
            ->execute('getSlaveRows');
        $aResult = [];
        foreach ($aMenus as $key => $aMenu) {
            if (!empty($aMenu['module_id']) && Phpfox::hasCallback($aMenu['module_id'], 'getDashboardActivity') && empty(Phpfox::callback($aMenu['module_id'] . '.getDashboardActivity'))) {
                continue;
            }
            if (!empty($aMenu['disallow_access'])) {
                $aUserGroups = unserialize($aMenu['disallow_access']);
                if (in_array(Phpfox::getUserBy('user_group_id'), $aUserGroups)) {
                    continue;
                }
            }
            $aResult[$aMenu['item_type']][] = $aMenu;
        }
        return $aResult;
    }

    public function clearCache($type = null)
    {
        if (empty($this->_aLanguages)) {
            return false;
        }
        foreach ($this->_aLanguages as $language) {
            $this->cache()->remove('mobile_menus_browse_' . $language['language_id']);
            if ($type) {
                $this->cache()->remove('mobile_menus_manage_' . $language['language_id'] . '_' . $type);
            } else {
                $this->cache()->remove('mobile_menus_manage_' . $language['language_id'] . '_header');
                $this->cache()->remove('mobile_menus_manage_' . $language['language_id'] . '_item');
                $this->cache()->remove('mobile_menus_manage_' . $language['language_id'] . '_helper');
                $this->cache()->remove('mobile_menus_manage_' . $language['language_id'] . '_footer');
                $this->cache()->remove('mobile_menus_manage_' . $language['language_id'] . '_');
            }
        }
        $this->cache()->removeGroup('mobile');
        return true;
    }
}