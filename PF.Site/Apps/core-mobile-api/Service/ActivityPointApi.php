<?php

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\ActivityPoint\ActivityPointForm;
use Apps\Core_MobileApi\Api\Resource\ActivityPointResource;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Security\ActivityPoint\ActivityPointAccessControl;
use Phpfox;
use User_Service_User;

class ActivityPointApi extends AbstractResourceApi implements MobileAppSettingInterface
{
    /** @var User_Service_User */
    protected $userService;

    protected $packageService;
    protected $packageProcessService;

    public function __construct()
    {
        parent::__construct();
        $this->userService = Phpfox::getService('user');
        $this->packageService = Phpfox::getService('activitypoint.package');
        $this->packageProcessService = Phpfox::getService('activitypoint.package.process');
    }

    function findAll($params = [])
    {
        return null;
    }

    function findOne($params)
    {
        $id = $this->resolver->resolveSingle($params, 'user_id');
        if (!$id) {
            $id = $this->resolver->resolveId($params);
        }
        $user = $this->userService->get($id, true);
        if (empty($user['user_id'])) {
            return $this->notFoundError();
        }
        $modules = Phpfox::massCallback('getDashboardActivity');
        $items = [];
        $secondItems = [];
        $secondSection = [
            'invite' => [
                'icon_name' => 'list-plus',
                'icon_family' => 'Lineficon',
                'icon_color' => '#555555'
            ],
            'comment' => [
                'icon_name' => 'comment-o',
                'icon_family' => 'Lineficon',
                'icon_color' => '#555555'
            ],
            'attachment' => [
                'icon_name' => 'paperclip-alt',
                'icon_family' => 'Lineficon',
                'icon_color' => '#555555'
            ]
        ];
        $allMenus = Phpfox::getService('mobile.admincp.menu')->getForBrowse();
        $allSimpleMenus = [];
        if (!empty($allMenus['item'])) {
            foreach ($allMenus['item'] as $menu) {
                $allSimpleMenus[$menu['module_id']] = [
                    'icon_name' => $menu['icon_name'],
                    'icon_family' => $menu['icon_family'],
                    'icon_color' => $menu['icon_color']
                ];
            }
        }
        $defaultIcon = [
            'icon_name' => 'box',
            'icon_family' => 'Lineficon',
            'icon_color' => '#555555'
        ];
        foreach ($modules as $key => $aModule) {
            foreach ($aModule as $sPhrase => $point) {
                $sPhrase = html_entity_decode($sPhrase, ENT_QUOTES);
                if (isset($secondSection[$key])) {
                    $secondItems[] = array_merge([
                        'label' => $sPhrase,
                        'value' => $point
                    ], $secondSection[$key]);
                } else {
                    $subPoint = [
                        'label' => $sPhrase,
                        'value' => $point
                    ];
                    $subPoint = array_merge($subPoint, isset($allSimpleMenus[$key]) ? $allSimpleMenus[$key] : $defaultIcon);
                    $items[] = $subPoint;
                }
            }
        }

        $activities = [
            'id' => (int)$id,
            'total_items' => [
                'label' => $this->getLocalization()->translate('total_items'),
                'value' => $user['activity_total'],
            ],
            'total_points' => [
                'label' => $this->getLocalization()->translate('activity_points'),
                'value' => $user['activity_points'],
            ],
            'items' => $items,
            'addition_items' => $secondItems,
        ];


        return $this->success($activities);
    }

    function create($params)
    {
        $this->denyAccessUnlessGranted(ActivityPointAccessControl::ADD, null, null, $this->getLocalization()->translate('activity_point_permission_purchase_packages'));
        /** @var ActivityPointForm $form */
        $form = $this->createForm(ActivityPointForm::class);
        $packages = $this->getPointPackages();
        $form->setPackages($packages);
        if ($form->isValid() && $values = $form->getValues()) {
            $package = $this->packageService->getPackage($values['point_package']);
            if (empty($package)) {
                return $this->notFoundError();
            }
            $price = unserialize($package['price']);
            $defaultCurrency = $this->getLocalization()->getDefaultCurrency();
            $purchaseId = $this->packageProcessService->createPurchase($package['package_id'], $this->getUser()->getId(), $defaultCurrency, $package['points']);
            $pendingPurchase = [
                'title' => $this->getLocalization()->translate($package['title']),
                'item_number' => 'activitypoint|'. $purchaseId,
                'resource_name' => ActivityPointResource::populate([])->getResourceName(),
                'module_name' => ActivityPointResource::populate([])->getModuleName(),
                'user_id' => $this->getUser()->getId(),
                'price' => $price[$defaultCurrency],
                'price_text' => html_entity_decode($this->getLocalization()->getCurrency($price[$defaultCurrency], $defaultCurrency)),
                'sub_description' => $package['points'] . ' ' . $this->getLocalization()->translate($package['points'] == 1 ? 'activitypoint_point' : 'activitypoint_points_lowercase'),
                'currency_id' => $defaultCurrency,
                'image' => !empty($package['image_path']) ? Image::createFrom([
                    'file' => $package['image_path'],
                    'path' => 'activitypoint.url_image',
                    'suffix' => '_120',
                    'server_id' => $package['server_id'],
                    'return_url' => true
                ])->image_url : Image::createFrom([
                    'file' => 'default_package.jpg',
                    'path' => 'activitypoint.url_asset_images',
                    'suffix' => '',
                    'server_id' => 0,
                    'return_url' => true
                ])->image_url,
                'allow_point' => false
            ];
            return $this->success([
                'id'               => (int)$purchaseId,
                'pending_purchase' => $pendingPurchase
            ]);
        } else {
            return $this->error($form->getInvalidFields());
        }
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
        $this->denyAccessUnlessGranted(ActivityPointAccessControl::ADD, null, null, $this->getLocalization()->translate('activity_point_permission_purchase_packages'));
        /** @var ActivityPointForm $form */
        $form = $this->createForm(ActivityPointForm::class, [
            'title'  => 'purchase_activity_points',
            'method' => 'post',
            'action' => UrlUtility::makeApiUrl('activitypoint')
        ]);
        $packages = $this->getPointPackages();
        if (empty($packages)) {
            return $this->error($this->getLocalization()->translate('activitypoint_no_packages_available'));
        }
        $form->setPackages($packages);
        return $this->success($form->getFormStructure());
    }

    function approve($params)
    {
        return null;
    }

    function feature($params)
    {
        return null;
    }

    function sponsor($params)
    {
        return null;
    }

    function loadResourceById($id, $returnResource = false)
    {
        return null;
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        return new MobileApp('activitypoint', [
            'title' => $l->translate('activity_points'),
            'home_view' => 'tab',
            'main_resource' => new ActivityPointResource([]),
            'other_resources' => []
        ]);
    }

    /**
     * @param $param
     *
     * @return ScreenSetting|array
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     */
    public function getScreenSetting($param)
    {
        $screenSetting = new ScreenSetting('activitypoint', [
            'name' => 'activity_points'
        ]);
        $resourceName = ActivityPointResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME, $screenSetting->getDefaultModuleHome(true));
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING, $screenSetting->getDefaultModuleListing(true));
        $detailHeader = [
            'component' => ScreenSetting::SIMPLE_HEADER,
            'title' => 'activity_points'
        ];
        if ($this->getAccessControl()->isGrantedSetting('activitypoint.can_purchase_points')) {
            $detailHeader['rightButtons'] = [[
                'icon' => 'plus',
                'action' => Screen::ACTION_ADD,
                'params' => ['resource_name' => $resourceName]
            ]];
        }
        $screenSetting->addSetting($resourceName, 'viewItemActivityPoint', [
            ScreenSetting::LOCATION_HEADER => $detailHeader,
            ScreenSetting::LOCATION_MAIN => [
                'component' => 'item_activity_point_view',
            ],
            'no_ads' => true
        ]);
        return $screenSetting;
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl = new ActivityPointAccessControl($this->getSetting(), $this->getUser());
        return true;
    }

    public function getPointPackages()
    {
        $packages = $this->packageService->getPackages();
        $results = [];
        if (!empty($packages)) {
            $defaultCurrency = $this->getLocalization()->getDefaultCurrency();
            foreach ($packages as $package) {
                $price = unserialize($package['price']);
                $defaultPrice = isset($price[$defaultCurrency]) ? $price[$defaultCurrency] : null;
                $pointsInfo = $package['points'] . ' ' . $this->getLocalization()->translate($package['points'] == 1 ? 'activitypoint_point' : 'activitypoint_points_lowercase');
                $results[] = [
                    'value' => (int)$package['package_id'],
                    'label' => $this->getLocalization()->translate($package['title']),
                    'points' => (float)$package['points'],
                    'is_free' => false,
                    'image' => !empty($package['image_path']) ? Image::createFrom([
                        'file' => $package['image_path'],
                        'path' => 'activitypoint.url_image',
                        'suffix' => '_120',
                        'server_id' => $package['server_id'],
                        'return_url' => true
                    ])->image_url : Image::createFrom([
                        'file' => 'default_package.jpg',
                        'path' => 'activitypoint.url_asset_images',
                        'suffix' => '',
                        'server_id' => 0,
                        'return_url' => true
                    ])->image_url,
                    'price' => $price,
                    'init_cost_info' => $pointsInfo . ' - ' . html_entity_decode($this->getLocalization()->getCurrency($defaultPrice, $defaultCurrency), ENT_QUOTES)
                ];
            }
        }
        return $results;
    }
}