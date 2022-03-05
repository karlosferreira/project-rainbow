<?php

namespace Apps\Core_MobileApi\Adapter\MobileApp;

use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Phpfox;
use Phpfox_Request;

class MobileApp
{

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
     * @var mixed
     */
    private $tabView = [];

    /**
     * @var string
     */
    private $homePageLayout = null;

    /**
     * @var array
     */
    private $headerMenuOptions = [];

    /**
     * @var array
     */
    private $tabViewOptions = [];

    /**
     * @var array define new templates
     */
    private $templates = [];

    /**
     * @var string
     */
    private $mainResourceName;

    /**
     * @var string
     */
    private $homeResourceName;

    /**
     * @var ResourceBase
     */
    private $mainResource;

    /**
     * @var ResourceBase
     */
    private $homeResource;

    /**
     * @var ResourceBase
     */
    private $categoryResource;

    /**
     * @var ResourceBase
     */
    private $typeResource;


    /**
     * @var array
     */
    private $addItemOptions = [];

    /**
     * @var array
     */
    private $parameters;


    private $versionName = 'mobile';

    /**
     * MobileApp constructor.
     *
     * @param        $alias
     * @param array  $parameters
     * @param string $versionName
     */
    public function __construct($alias, array $parameters = [], $versionName = 'mobile')
    {
        $this->appAlias = $alias;
        $this->parameters = $parameters;
        $this->setVersionName($versionName);

        $this->init($versionName);
    }

    /**
     * @param array $templates
     *
     * @return MobileApp
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
        return $this;
    }

    protected function addParams(array $params)
    {
        foreach ($params as $name => $value) {
            $this->screens[$name] = $value;
        }
    }

    protected function init($versionName = 'mobile')
    {

        if (($resource = $this->getParam('home_resource')) && $resource instanceof ResourceBase) {
            $this->homeResourceName = $resource->getResourceName();
            $this->homeResource = $resource;
            $this->configContentResource($resource, 'home_resource', $versionName);
        }


        if (($resource = $this->getParam('main_resource')) && $resource instanceof ResourceBase) {
            $this->mainResourceName = $resource->getResourceName();
            $this->mainResource = $resource;
            $this->configContentResource($resource, 'main_resource', $versionName);
        }

        if (($resource = $this->getParam('category_resource'))) {
            if (is_array($resource)) {
                foreach ($resource as $ownRes => $res) {
                    if ($res instanceof ResourceBase) {
                        $this->configContentResource($res, null, $versionName);
                    }
                }
            } else if ($resource instanceof ResourceBase) {
                $this->configContentResource($resource, 'category_resource', $versionName);
            }
            $this->categoryResource = $resource;
        }

        if (($resource = $this->getParam('type_resource')) && $resource instanceof ResourceBase) {
            $this->typeResource = $resource;
            $this->configContentResource($resource, 'type_resource', $versionName);
        }

        if (($resources = $this->getParam('other_resources')) && is_array($resources)) {
            foreach ($resources as $resource) {
                if ($resource instanceof ResourceBase) {
                    $this->configContentResource($resource, null, $versionName);
                }
            }
        }
    }

    /**
     * @param string $versionName
     */
    public function setVersionName($versionName)
    {
        if ($versionName == 'mobile') {
            $predictVersion = Phpfox_Request::instance()->get('req2');
            if ($predictVersion != 'mobile' && strpos($predictVersion, 'v') !== false) {
                $versionName = $predictVersion;
            }
        }
        $this->versionName = $versionName;
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
     * @param string $resourceName
     *
     * @return array
     */
    public function getDefaultFabButtons($resourceName)
    {
        return [
            [
                'label'         => 'sort',
                'icon'          => 'alignleft',
                'resource_name' => "$resourceName",
                'action'        => Screen::ACTION_SORT_BY,
                'queryKey'      => 'sort',
            ],
            [
                'label'         => 'filter',
                'icon'          => 'filter',
                'resource_name' => $resourceName,
                'action'        => Screen::ACTION_FILTER_BY,
                'queryKey'      => 'when',
            ],
        ];
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
     * @param string $name
     * @param mixed  $params
     *
     * @return $this
     */
    public function addSetting($name, $params)
    {
        $this->settings[$name] = $params;
        return $this;
    }

    public function getDefaultActionMenu()
    {
        return [
            'default' => true,
        ];
    }

    public function getDefaultSortMenu()
    {
        return [
            'default' => true,
        ];
    }

    public function getDefaultFilterMenu()
    {
        return [
            'default' => true,
        ];
    }

    /**
     * @return array
     */
    public function toSettings()
    {
        $setting = [];

        $this->beforeToSettings();

        foreach ($this->resources as $name => $item) {
            $setting['resources'][$name] = $item;
        }

        foreach ($this->settings as $name => $item) {
            $setting['parameters'][$name] = $item;
        }

        if (!empty($this->templates)) {
            $setting['layout_templates'] = [];
            foreach ($this->templates as $name => $value) {
                $setting['layout_templates'][$this->appAlias . ".$name"] = $value;
            }
        }

        return $setting;
    }

    protected function beforeToSettings()
    {
        $homeView = $this->getParam('home_view');

        if ($this->categoryResource
            && empty($this->screens['category_menu'])
            && empty($this->settings['category_menu'])) {
            if ($this->versionName == 'mobile') {
                if (is_array($this->categoryResource)) {
                    //Only support one category
                    $this->categoryResource = array_shift($this->categoryResource);
                }
                $categoryRefUrl = $this->categoryResource->getMobileSettings()->getParam('apiUrl');

                if ($this->categoryResource && empty($this->screens['category_menu'])) {
                    $this->addSetting('category_menu', [
                        'title'    => $this->categoryResource->getMobileSettings()->getParam('title', Phpfox::getService(LocalizationInterface::class)->translate('Categories')),
                        'queryKey' => $this->categoryResource->getMobileSettings()->getParam('queryKey', 'category'),
                        'ref'      => $categoryRefUrl,
                    ]);
                }
            } else {
                $categoryMenus = [];
                if (is_array($this->categoryResource)) {
                    foreach ($this->categoryResource as $resourceName => $categoryRes) {
                        if ($categoryRes instanceof ResourceBase) {
                            $categoryMenus[$resourceName] = [
                                'title'          => $categoryRes->getMobileSettings()->getParam('title', Phpfox::getService(LocalizationInterface::class)->translate('Categories')),
                                'queryKey'       => $categoryRes->getMobileSettings()->getParam('queryKey', 'category'),
                                'searchResource' => $categoryRes->getMobileSettings()->getParam('searchResource', $resourceName),
                                'ref'            => $categoryRes->getMobileSettings()->getParam('apiUrl'),
                            ];
                        }
                    }
                } else {
                    $categoryRefUrl = $this->categoryResource->getMobileSettings()->getParam('apiUrl');
                    $categoryMenus[$this->mainResourceName] = [
                        'title'          => $this->categoryResource->getMobileSettings()->getParam('title', Phpfox::getService(LocalizationInterface::class)->translate('Categories')),
                        'queryKey'       => $this->categoryResource->getMobileSettings()->getParam('queryKey', 'category'),
                        'searchResource' => $this->categoryResource->getMobileSettings()->getParam('searchResource', $this->mainResourceName),
                        'ref'            => $categoryRefUrl,
                    ];
                }
                $this->addSetting('category_menu', $categoryMenus);
            }
        }

        $this->addSetting('app', [
            'home_view'         => $homeView ? $homeView : 'menu',
            'main_resource'     => $this->mainResource ? $this->mainResource->getResourceName() : null,
            'home_resource'     => $this->homeResource ? $this->homeResource->getResourceName() : ($this->mainResource ? $this->mainResource->getResourceName() : null),
            'category_resource' => $this->categoryResource && !is_array($this->categoryResource) ? $this->categoryResource->getResourceName() : null,
            'type_resource'     => $this->typeResource ? $this->typeResource->getResourceName() : null,
        ]);

        $this->addSetting('main_resource', $this->mainResourceName);
        $this->addSetting('home_resource', $this->homeResourceName ? $this->homeResourceName : $this->mainResourceName);
        $this->addSetting('resourceNames', array_unique($this->resourceNames));
        return $this;
    }

    /**
     * @param ResourceBase $resource
     * @param null         $type
     * @param string       $versionName
     *
     * @return $this
     */
    public function configContentResource(ResourceBase $resource, $type = null, $versionName = 'mobile')
    {
        $resourceName = $resource->getResourceName();
        $moduleName = $this->getAppAlias();
        $bag = $resource->getMobileSettings(['versionName' => $versionName]);

        //Check duplicate resources
        if (in_array($resourceName, $this->resourceNames)) {
            return $this;
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.adapter_mobile_app_config_content_resource_start')) ? eval($sPlugin) : false);

        $this->resourceNames[] = $resourceName;

        if ($bag->getParam('can_add', true)) {
            $this->addItemOptions[] = [
                'icon'   => $bag->getParam('add.icon', 'plus'),
                'label'  => $bag->getParam('add.label', 'Add'),
                'action' => Screen::ACTION_ADD,
                'params' => ['config_name' => "{$resourceName}.add", 'module_name' => $moduleName, 'resource_name' => $resourceName],
            ];
        }

        if (($membershipMenu = $bag->getParam('membership_menu'))) {
            $this->addSetting("{$resourceName}.membership_menu", [
                'options' => $membershipMenu,
            ]);
        }

        if ($bag->getParam('can_search', true)) {
            $this->addSetting("{$resourceName}.search_form", [
                'apiUrl'        => $bag->getParam('urls.search_form'),
                'headerTitle'   => 'filters',
                'module_name'   => $this->getAppAlias(),
                'resource_name' => $resourceName,
            ]);
        }


        if ($bag->getParam('can_edit', true)) {
            $this->addSetting("{$resourceName}.edit", [
                'apiUrl'      => $bag->getParam('urls.form_edit'),
                'headerTitle' => 'edit',
            ]);
        }
        if ($bag->getParam('can_add', true)) {
            $this->addSetting("{$resourceName}.add", [
                'apiUrl'      => $bag->getParam('urls.form_add'),
                'headerTitle' => 'add',
            ]);
        }

        if ($bag->getParam('can_delete', true)) {
            $this->addSetting("{$resourceName}.delete", [
                'apiUrl'          => $bag->getParam('urls.delete'),
                'confirm_message' => 'are_you_sure',
            ]);
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.adapter_mobile_app_config_content_resource_end')) ? eval($sPlugin) : false);

        $this->addResources("${resourceName}", $bag->toArray());
        return $this;
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


}