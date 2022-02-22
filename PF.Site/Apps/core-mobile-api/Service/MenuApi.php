<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Phpfox;


class MenuApi extends AbstractApi
{
    /**
     * @var Admincp\MenuService|object
     */
    private $menuService;

    const HEADER = 'header';
    const SECTION_HEADER = 'section-header';
    const ITEM = 'item';
    const SECTION_ITEM = 'section-item';
    const FOOTER = 'footer';
    const SECTION_FOOTER = 'section-footer';
    const SECTION = 'section';
    const HELPER = 'helper';
    const SECTION_HELPER = 'section-helper';

    public function __naming()
    {
        return [
            'menu' => [
                'get' => 'getAll',
            ],
        ];
    }

    public function __construct()
    {
        parent::__construct();
        $this->menuService = Phpfox::getService('mobile.admincp.menu');
    }

    public function getMainMenu()
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
        $menuHeader = $this->validateItems($menuHeader);
        $menuBody = $this->validateItems($menuBody);
        $hasMore = count($menuBody) && $showFirst;
        $moreBody = $this->validateItems($moreBody);
        $menuHelper = $this->validateItems($menuHelper);
        $menuFooter = $this->validateItems($menuFooter);

        return [
            'menuHeader' => $menuHeader,
            'menuBody'   => $menuBody,
            'moreItem'   => $hasMore ? $moreItem : false,
            'moreBody'   => $moreBody,
            'menuHelper' => $menuHelper,
            'menuFooter' => $menuFooter,
        ];
    }

    public function getAll()
    {

        return $this->success($this->getMainMenu());

    }

    private function validateItems($menuItems)
    {
        return array_map(function ($menuItem) {
            return $this->processMenuItem($menuItem);
        }, array_filter($menuItems, function ($aItem) {
            return true; // check filter app available and order configure.
        }));
    }

    private function processMenuItem($menuItem)
    {
        return [
            'id'        => intval($menuItem['item_id']),
            'label'     => $this->getLocalization()->translate($menuItem['name']),
            'icon'      => $menuItem['icon_name'],
            'iconColor' => $menuItem['icon_color'],
            'type'      => $menuItem['item_type'],
            'path'      => $this->getMenuPath($menuItem),
        ];
    }

    private function getMenuPath($menuItem)
    {
        if (empty($menuItem['is_url'])) {
            return $menuItem['path'];
        }
        $page = [];
        if ($menuItem['path'] == 'terms') {
            $page = Phpfox::getService('page')->getPage(2);
        }
        if ($menuItem['path'] == 'policy') {
            $page = Phpfox::getService('page')->getPage(1);

        }
        $path = isset($page['title_url']) ? $page['title_url'] : $menuItem['path'];

        return Phpfox::getLib('url')->makeUrl($path);
    }
}