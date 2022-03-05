<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7_1\Service;

use Apps\Core_MobileApi\Service\Admincp\MenuService;
use Apps\Core_MobileApi\Service\MenuApi as BaseMenuApi;
use Phpfox;
use Phpfox_Plugin;

class MenuApi extends BaseMenuApi
{
    /**
     * @var MenuService
     */
    private $menuService;

    public function __construct()
    {
        parent::__construct();
        $this->menuService = Phpfox::getService('mobile.admincp.menu');
    }

    public function getMainMenu($version = 'mobile')
    {
        $allMenus = $this->menuService->getForBrowse();
        $showFirst = $this->getSetting()->getAppSetting('mobile.mobile_limit_menu_show_first');
        $menuBody = $moreBody = $menuHeader = $menuFooter = $menuHelper = [];
        if (count($allMenus[self::ITEM])) {
            if ($showFirst && count($allMenus[self::ITEM]) > $showFirst) {
                $menuBody = array_splice($allMenus[self::ITEM], 0, $showFirst);
                $moreBody = $allMenus[self::ITEM];
            } else {
                $menuBody = $allMenus[self::ITEM];
                $moreBody = [];
            }
            $allMenus[self::SECTION_ITEM][0]['item_type'] = self::SECTION;
            array_unshift($menuBody, $allMenus[self::SECTION_ITEM][0]);
        }
        if (count($allMenus[self::HEADER])) {
            $menuHeader = $allMenus[self::HEADER];
            $allMenus[self::SECTION_HEADER][0]['item_type'] = self::SECTION;
            array_unshift($menuHeader, $allMenus[self::SECTION_HEADER][0]);
        }
        if (count($allMenus[self::HELPER])) {
            $menuHelper = $allMenus[self::HELPER];
            $allMenus[self::SECTION_HELPER][0]['item_type'] = self::SECTION;
            array_unshift($menuHelper, $allMenus[self::SECTION_HELPER][0]);
        }
        if (count($allMenus[self::FOOTER])) {
            $menuFooter = $allMenus[self::FOOTER];
            $allMenus[self::SECTION_FOOTER][0]['item_type'] = self::SECTION;
            array_unshift($menuFooter, $allMenus[self::SECTION_FOOTER][0]);
        }

        $moreItem = [
            'id'        => 0,
            'icon'      => 'layers-o',
            'iconColor' => '#686868',
            'label'     => $this->getLocalization()->translate('show_more'),
            'type'      => 'more',
            'path'      => '/',
        ];

        // filter $aMenuItems
        $menuHeader = $this->validateItems($menuHeader, $version);
        $menuBody = $this->validateItems($menuBody, $version);
        $hasMore = count($menuBody) && $showFirst;
        $moreBody = $this->validateItems($moreBody, $version);
        $menuHelper = $this->validateItems($menuHelper, $version);
        $menuFooter = $this->validateItems($menuFooter, $version);

        return [
            'menuHeader' => $menuHeader,
            'menuBody'   => $menuBody,
            'moreItem'   => $hasMore ? $moreItem : false,
            'moreBody'   => $moreBody,
            'menuHelper' => $menuHelper,
            'menuFooter' => $menuFooter,
        ];
    }

    public function getAll($params = [])
    {
        $versionName = isset($params['api_version_name']) ? $params['api_version_name'] : 'mobile';
        return $this->success($this->getMainMenu($versionName));
    }

    private function validateItems($menuItems, $version = 'mobile')
    {
        return array_map(function ($menuItem) use ($version) {
            return $this->processMenuItem($menuItem, $version);
        }, array_filter($menuItems, function ($aItem) {
            return true; // check filter app available and order configure.
        }));
    }

    private function processMenuItem($menuItem, $version = 'mobile')
    {
        $menu = [
            'id'        => intval($menuItem['item_id']),
            'label'     => $this->getLocalization()->translate($menuItem['name']),
            'icon'      => $menuItem['icon_name'],
            'iconColor' => $menuItem['icon_color'],
            'type'      => $menuItem['item_type'],
            'path'      => $this->getMenuPath($menuItem),
        ];
        if ($version != 'mobile' && version_compare($version, 'v1.7.3', '>=') && $menuItem['path'] == 'contact') {
            $menu['path'] = '';
            $menu['routeName'] = 'formEdit';
            $menu['routeParams'] = [
                'module_name' => 'core',
                'resource_name' => 'account',
                'formType' => 'contactUs'
            ];
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.version_171_service_menuapi_process_menu_item')) ? eval($sPlugin) : false);

        return $menu;
    }

    private function getMenuPath($menuItem)
    {
        if (empty($menuItem['is_url'])) {
            return $menuItem['path'];
        }
        switch ($menuItem['path']) {
            case 'terms':
                $path = '@core/terms-policies';
                break;
            case 'policy':
                $path = '@core/privacy';
                break;
            default:
                $path = Phpfox::getLib('url')->makeUrl($menuItem['path']);
                break;
        }

        return $path;
    }
}