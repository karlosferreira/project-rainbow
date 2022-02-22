<?php

namespace Apps\Core_MobileApi\Adapter\MobileApp;

use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;

class ScreenSetting
{

    const MODULE_HOME = 'module';
    const MODULE_LISTING = 'listing';
    const MODULE_DETAIL = 'detail';
    const LOCATION_RIGHT = 'right';
    const LOCATION_TOP = 'top';
    const LOCATION_BOTTOM = 'bottom';
    const LOCATION_MAIN = 'content';
    const LOCATION_HEADER = 'header';

    /** Tabs view */
    const SMART_TABS = 'smart_tabs';
    /** Sort/Filter button */
    const SORT_FILTER_FAB = 'sort_filter_fab';
    /** Header view with: back button | header title | action button */
    const SIMPLE_HEADER = 'simple_header';
    /** Listing items view with pagination */
    const SMART_RESOURCE_LIST = 'smart_resource_list';
    /** Section on listing, work will <sections> property */
    const SMART_RESOURCE_SECTION = 'smart_resource_section';
    /** Feed stream view in detail item*/
    const STREAM_PROFILE_FEEDS = 'stream_profile_feeds';
    /** Block view for item */
    const SIMPLE_LISTING_BLOCK = 'simple_list_block';
    /** List empty component */
    const LIST_EMPTY = 'list_empty';
    /** Mass action component */
    const MASS_ACTION = 'mass_action';

    /**
     * @var string Phpfox's App Alias
     */
    protected $appAlias;

    /**
     * @var array
     */
    protected $resourceNames = [];

    /**
     * @var Screen[]
     */
    protected $screens = [];

    /**
     * @var array
     */
    protected $resources = [];

    /**
     * @var array
     */
    protected $settings = [];

    /**
     * @var array
     */
    private $screenMap = [];

    /**
     * @var string
     */
    private $homePageLayout = null;

    /**
     * @var array
     */
    private $parameters;

    /**
     * @var array
     */
    private $blocks = [];

    private $local;

    /**
     * MobileApp constructor.
     *
     * @param string $alias
     * @param array  $parameters
     */
    public function __construct($alias, array $parameters = [])
    {
        $this->appAlias = $alias;
        $this->parameters = $parameters;
        $this->local = \Phpfox::getService(LocalizationInterface::class);
    }

    protected function addParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->screens[$name] = $value;
        }
    }

    /**
     * @param string $name
     * @param null   $default_value
     *
     * @return mixed|null
     */
    public function getParam($name, $default_value = null)
    {
        return array_key_exists($name, $this->parameters) ? $this->parameters[$name] : $default_value;
    }

    /**
     * @return string
     */
    public function getHomePageLayout()
    {
        return $this->homePageLayout;
    }

    /**
     * @param string $homePageLayout
     *
     * @return $this
     */
    public function setHomePageLayout($homePageLayout)
    {
        $this->homePageLayout = $homePageLayout;

        return $this;
    }

    /**
     * @return string
     */
    public function getAppAlias()
    {
        return $this->appAlias;
    }

    /**
     * @return Screen[]
     */
    public function getScreens()
    {
        return $this->screens;
    }

    /**
     * @param Screen $screen add screen to and app
     */
    public function addScreen(Screen $screen)
    {
        $this->screens[$screen->getName()] = $screen;
    }

    public function addResources($name, $settings)
    {
        $this->resources[$name] = $settings;
    }

    /**
     * Add screen setting
     *
     * @param string $resource_name Resource name
     * @param string $screen        Screen type module_home | module_listing | module_detail
     * @param mixed  $params        If empty, use default screen
     *
     * @return $this
     */
    public function addSetting($resource_name, $screen, $params = [])
    {
        $resource_name = str_replace('-', '_', $resource_name);
        $this->settings[$resource_name][$screen] = $params;
        return $this;
    }

    /**
     * Add block in screen
     *
     * @param $resource_name
     * @param $screen
     * @param $location
     * @param $params
     *
     * @return $this
     */
    public function addBlock($resource_name, $screen, $location, $params)
    {
        $this->blocks[$resource_name][$screen][$location] = $params;
        return $this;
    }

    /**
     * @return array
     */
    public function toSettings()
    {
        $setting = [];
        foreach ($this->settings as $resource_name => $item) {
            $name = $this->convertResourceName($resource_name);
            foreach ($item as $screen => $params) {
                $blocks = isset($this->blocks[$resource_name][$screen]) ? $this->blocks[$resource_name][$screen] : [];
                if (in_array($screen, [self::MODULE_HOME, self::MODULE_DETAIL, self::MODULE_LISTING])) {
                    $screenName = $screen . $name;
                } else {
                    $screenName = $screen;
                }
                $tempSetting = !empty($params) ? $params : $this->getDefaultScreen($screen, $resource_name);
                if (is_array($blocks) && count($blocks)) {
                    foreach ($blocks as $location => $block) {
                        if (count($block) && !isset($block['component'])) {
                            if ($location == self::LOCATION_MAIN) {
                                $tempSetting[$location]['embedComponents'] = isset($tempSetting[$location]['embedComponents']) ? array_merge($tempSetting[$location]['embedComponents'], $block) : $block;
                            } else {
                                $tempSetting[$location] = isset($tempSetting[$location]) ? array_merge($tempSetting[$location], $block) : $block;
                            }
                        } else {
                            if ($location == self::LOCATION_MAIN) {
                                $tempSetting[$location]['embedComponents'][] = $block;
                            } else {
                                $tempSetting[$location][] = $block;
                            }
                        }
                    }
                }
                $setting[$screenName] = $tempSetting;
            }
        }
        return $setting;
    }

    public function convertResourceName($name)
    {
        $explode = explode('_', $name);
        $explode = array_map(function ($val) {
            return ucfirst($val);
        }, $explode);
        return implode('', $explode);
    }

    /**
     * @param $screen
     * @param $resource_name
     *
     * @return array
     */
    private function getDefaultScreen($screen, $resource_name)
    {
        $params = [];
        $screenTitle = $this->local->translate(isset($this->parameters['name']) ? $this->parameters['name'] : $this->appAlias);
        $screenTitle .= ' > ' . $this->local->translate($resource_name);
        switch ($screen) {
            case self::MODULE_HOME:
                $params = $this->getDefaultModuleHome();
                $screenTitle .= ' - ' . $this->local->translate('mobile_home_page');
                break;
            case self::MODULE_LISTING:
                $params = $this->getDefaultModuleListing();
                $screenTitle .= ' - ' . $this->local->translate('mobile_search_page');
                break;
            case self::MODULE_DETAIL:
                $params = $this->getDefaultModuleDetail();
                $screenTitle .= ' - ' . $this->local->translate('mobile_detail_page');
                break;
        }
        $params['screen_title'] = $screenTitle;
        return $params;
    }

    public function getDefaultModuleHome($noAds = false, $screenTitle = '')
    {
        return [
            'header'       => [
                'component' => 'module_header'
            ],
            'content'      => [
                'component' => self::SMART_RESOURCE_LIST,
            ],
            'mainBottom'   => [
                'component' => self::SORT_FILTER_FAB
            ],
            'no_ads'       => $noAds,
            'screen_title' => $screenTitle,
            'footer'       => [
                'component' => 'mass_action'
            ]
        ];
    }

    public function getDefaultModuleListing($noAds = true, $screenTitle = '')
    {
        return [
            'header'       => [
                'component'  => self::SIMPLE_HEADER,
                'is_listing' => true
            ],
            'content'      => [
                'component' => self::SMART_RESOURCE_LIST
            ],
            'mainBottom'   => [
                'component' => self::SORT_FILTER_FAB
            ],
            'footer' => [
              'component' => self::MASS_ACTION
            ],
            'no_ads'       => $noAds,
            'screen_title' => $screenTitle
        ];
    }

    private function getDefaultModuleDetail()
    {
        return [
            'header'  => [
                'component' => 'item_header'
            ],
            'content' => [
                'component'       => 'item_simple_detail',
                'embedComponents' => [
                    'item_image',
                    'item_title',
                    'item_author',
                    'item_stats',
                    'item_pending',
                    'item_html_content',
                    'item_category',
                    'item_tags',
                    'item_user_tags'
                ]
            ],
            'bottom'  => [
                ['component' => 'item_like_bar']
            ],
        ];
    }

    /**
     * @param ResourceBase $resource
     *
     * @return array
     */
    public function getResourceInfo($resource)
    {
        $result = [];
        if (isset($this->screenMap[$resource->getResourceName()])) {
            /** @var Screen $screen */
            foreach ($this->screenMap[$resource->getResourceName()] as $screen) {
                $result[$screen->getName()] = [
                    'screen' => $this->appAlias . '.' . $screen->getName(),
                ];
            }
        }
        return $result;
    }

    public function getAppImage($imageName = 'no-item')
    {
        $basePath = \Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-mobile-api/assets/images/app-images/';

        return $basePath . $imageName . '.png';
    }

}