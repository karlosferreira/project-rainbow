<?php

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Adapter\MobileApp\TabSetting;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Form;
use Apps\Core_MobileApi\Api\Form\Type\AbstractOptionType;
use Apps\Core_MobileApi\Api\Form\Type\CountryStateType;
use Apps\Core_MobileApi\Api\Form\Type\HierarchyType;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Resource\AccountResource;
use Apps\Core_MobileApi\Api\Resource\AttachmentResource;
use Apps\Core_MobileApi\Api\Resource\FileResource;
use Apps\Core_MobileApi\Api\Resource\LinkResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Resource\SearchResource;
use Apps\Core_MobileApi\Api\Resource\TagResource;
use Apps\Core_MobileApi\Version1_7_1\Service\MenuApi as MenuApi171;
use Core\Payment\Trigger;
use Phpfox;
use Phpfox_Plugin;
use Phpfox_Url;

defined('ROUTE_MODULE_DETAIL') or define('ROUTE_MODULE_DETAIL', 'module/detail');
defined('ROUTE_MODULE_HOME') or define('ROUTE_MODULE_HOME', 'module/home');
defined('ROUTE_MODULE_LIST') or define('ROUTE_MODULE_LIST', 'module/list-item');
defined('ROUTE_MODULE_ADD') or define('ROUTE_MODULE_ADD', 'formEdit');
defined('ROUTE_MODULE_EDIT') or define('ROUTE_MODULE_EDIT', 'formEdit');

class CoreApi extends AbstractApi implements MobileAppSettingInterface
{

    protected $specialModules;
    protected $specialUCFirsts;

    public function __construct()
    {
        parent::__construct();
        $this->specialModules = [
            'video' => 'v',
            'page'  => 'pages',
            'group' => 'groups'
        ];
        $this->specialUCFirsts = [
            'by'
        ];
    }

    public function __naming()
    {
        return [
            'core/support-form-types' => [
                'get' => 'getFormTypes',
            ],
            'core/route'              => [
                'get' => 'getRoute',
            ],
            'core/site-settings'      => [
                'get' => 'getSiteSettings',
            ],
            'core/mobile-routes'      => [
                'get' => 'getMobileRoutes',
            ],
            'core/endpoint-urls'      => [
                'get' => 'getEndpointUrls',
            ],
            'core/routes-map'         => [
                'get' => 'getAllRoutesMapping',
            ],
            'core/app-settings'       => [
                'get' => 'getAppSettings',
            ],
            'core/actions'            => [
                'get' => 'getSiteActions',
            ],
            'core/url-to-route'       => [
                'get' => 'parseUrlToRoute',
                'post' => 'parseUrlToRoute'
            ],
            'core/phrase'             => [
                'get' => 'phrases'
            ],
            'ping'                    => [
                'get' => 'ping'
            ],
            'core/status'             => [
                'get' => 'getStatus'
            ],
            'core/gateway'            => [
                'get' => 'getGateway'
            ],
            'core/point-checkout'     => [
                'post' => 'checkoutWithPoints'
            ],
            'ad/checkout'             => [
                'post' => 'checkoutWithPoints'
            ],
            'core/terms-policies'              => [
                'get' => 'getTermsPolicies'
            ],
            'core/privacy'              => [
                'get' => 'getPrivacy'
            ],
        ];
    }

    public function getStatus()
    {
        $coreHelper = Phpfox::getService('core.helper');
        //Get unseen friend request
        if (Phpfox::isModule('friend')) {
            $friendRequest = Phpfox::getService('friend.request')->getUnseenTotal();
            $friendRequest = $coreHelper->shortNumberOver100($friendRequest);
        } else {
            $friendRequest = 0;
        }
        //Get unseen notification
        $notification = (new NotificationApi())->getUnseenTotal();
        $notification = $coreHelper->shortNumberOver100($notification);

        if (Phpfox::isModule('feed')) {
            //Get new feed
            define('PHPFOX_CHECK_FOR_UPDATE_FEED', true);
            define('PHPFOX_CHECK_FOR_UPDATE_FEED_UPDATE', PHPFOX_TIME - 60);
            $aRows = Phpfox::getService('feed')->get(null, null, 0, false, false);
            $feed = $coreHelper->shortNumberOver100(count($aRows));
        }
        $data = [
            'new_notification'   => $notification ? $notification : null,
            'new_chat_message'   => null,
            'new_friend_request' => $friendRequest ? $friendRequest : null,
            'new_feed'           => isset($feed) ? $feed : null
        ];
        return $this->success($data);
    }

    public function getAppSettings($params)
    {
        $cacheLib = Phpfox::getLib('cache');
        $versionName = isset($params['api_version_name']) ? $params['api_version_name'] : 'mobile';
        $cacheId = $cacheLib->set("mobile_app_settings_{$this->getUserGroupId()}_{$this->getLanguageId()}_{$versionName}");
        $cacheLib->group('mobile', $cacheId);
        if (!($settings = $cacheLib->getLocalFirst($cacheId))) {
            $resources = NameResource::instance()->getResourceNames(true);

            $settings = Phpfox::getService("mobile.mobile_app_helper")->getAppSettings($resources, $params);

            (($sPlugin = Phpfox_Plugin::get('mobile.core_api_get_app_settings')) ? eval($sPlugin) : false);

            $cacheLib->saveBoth($cacheId, $settings);
            $cacheLib->group('settings', $cacheId);
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.core_api_get_app_settings_no_cache')) ? eval($sPlugin) : false);

        return $this->success($settings);
    }

    public function getSiteActions($params)
    {
        $cacheLib = Phpfox::getLib('cache');
        $versionName = isset($params['api_version_name']) ? $params['api_version_name'] : 'mobile';
        $cacheId = $cacheLib->set("mobile_site_actions_{$this->getUserGroupId()}_{$this->getLanguageId()}_{$versionName}");
        $cacheLib->group('mobile', $cacheId);
        if (!($settings = $cacheLib->getLocalFirst($cacheId))) {
            $resources = NameResource::instance()->getResourceNames(true);

            $settings = Phpfox::getService("mobile.mobile_app_helper")->getActions($resources, $params);

            (($sPlugin = Phpfox_Plugin::get('mobile.core_api_get_site_actions')) ? eval($sPlugin) : false);

            $cacheLib->saveBoth($cacheId, $settings);
            $cacheLib->group('settings', $cacheId);
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.core_api_get_site_actions_no_cache')) ? eval($sPlugin) : false);

        return $this->success($settings);
    }

    /**
     * @param $param
     *
     * @return MobileApp
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     */
    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        return new MobileApp('core', [
            'title'           => $l->translate('general'),
            'other_resources' => [
                new SearchResource([]),
                new FileResource([]),
                new AccountResource([]),
                new AttachmentResource([]),
                new TagResource([]),
                new LinkResource([])
            ],
        ]);
    }

    /**
     * Api fallback method. This api is called if no api mapped found
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     */
    public function fallbackCall()
    {
        return $this->notFoundError("Unknown API request");
    }

    public function getEndpointUrls()
    {

        $resourceNaming = new NameResource();
        $apiRoutes = $resourceNaming->generateEndpointUrls('mobile');
        return $this->success($apiRoutes);
    }

    public function getDefaultActionMenu()
    {
        $l = $this->getLocalization();
        return [
            'options' => [
                ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
                ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
                ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
            ],
        ];
    }

    public function getDefaultSortMenu()
    {
        $l = $this->getLocalization();
        return [
            'title'    => $l->translate('sort_by'),
            'queryKey' => 'sort',
            'options'  => [
                ['label' => $l->translate('latest'), 'value' => 'latest'],
                ['label' => $l->translate('most_viewed'), 'value' => 'most_viewed'],
                ['label' => $l->translate('most_liked'), 'value' => 'most_liked'],
                ['label' => $l->translate('most_discussed'), 'value' => 'most_discussed'],
            ],
        ];
    }

    public function getDefaultFilterMenu()
    {
        $l = $this->getLocalization();
        return [
            'title'    => $l->translate('filter_by'),
            'queryKey' => 'when',
            'options'  => [
                ['label' => $l->translate('all_time'), 'value' => 'all-time'],
                ['label' => $l->translate('this_month'), 'value' => 'this-month'],
                ['label' => $l->translate('this_week'), 'value' => 'this-week'],
                ['label' => $l->translate('today'), 'value' => 'today'],
            ],
        ];
    }

    public function getPostTypes()
    {
        $userId = $this->getUser()->getId();
        if (!$userId || !Phpfox::isModule('feed')) {
            return [];
        }
        $postOptions[] = $this->getPostOption('status');
        if (Phpfox::isAppActive('Core_Photos') && $this->getSetting()->getUserSetting('photo.can_upload_photos')) {
            $postOptions[] = $this->getPostOption('photo');
        }
        if (Phpfox::isAppActive('PHPfox_Videos') && $this->getSetting()->getUserSetting('v.pf_video_share')
            && $this->getSetting()->getUserSetting('v.pf_video_view')) {
            $postOptions[] = $this->getPostOption('video');
        }

        if ($this->getSetting()->getAppSetting('feed.enable_check_in') && $this->getSetting()->getAppSetting('core.google_api_key')) {
            $postOptions[] = $this->getPostOption('checkin');
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.service_core_api_getposttypes_end')) ? eval($sPlugin) : false);

        return $postOptions;
    }

    public function getPostOption($type)
    {
        $postOptions = [
            'status'  => [
                'value'       => 'post.status',
                'label'       => $this->getLocalization()->translate('status'),
                'description' => $this->getLocalization()->translate('write_something'),
                'icon'        => 'quotes-right',
                'icon_color'  => '#0f81d8',
            ],
            'photo'   => [
                'value'       => 'post.photo',
                'label'       => $this->getLocalization()->translate('photo'),
                'description' => $this->getLocalization()->translate('say_something_about_this_photo'),
                'icon'        => 'photos',
                'icon_color'  => '#48c260',
            ],
            'video'   => [
                'value'       => 'post.video',
                'label'       => $this->getLocalization()->translate('videos'),
                'description' => $this->getLocalization()->translate('say_something_about_this_video'),
                'icon'        => 'videocam',
                'icon_color'  => '#ffac00',
            ],
            'checkin' => [
                'value'       => 'post.checkin',
                'label'       => $this->getLocalization()->translate('check_in'),
                'description' => '',
                'icon'        => 'checkin',
                'icon_color'  => '#f05d28',
            ],
        ];
        $postOption = [];
        if (isset($postOptions[$type])) {
            $postOption = $postOptions[$type];
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.service_core_api_getpostoption_end')) ? eval($sPlugin) : false);

        return $postOption;
    }

    public function getSiteSettings($params)
    {
        $userId = $this->getUser()->getId();
        $cacheLib = Phpfox::getLib('cache');
        $versionName = isset($params['api_version_name']) ? $params['api_version_name'] : 'mobile';
        $cacheId = $cacheLib->set("mobile_site_settings_{$this->getUserGroupId()}_{$this->getLanguageId()}_{$versionName}");
        $cacheLib->group('mobile', $cacheId);
        if (!($data = $cacheLib->getLocalFirst($cacheId))) {
            $data['screen_setting'] = $this->getScreenSettings($params);
            $data['post_types'] = $this->getPostTypes();
            $data['general'] = $this->_getGeneralSetting();
            $data['mainMenu'] = ($versionName == 'mobile' || version_compare($versionName, 'v1.7', '<=')) ? (new MenuApi())->getMainMenu() : (new MenuApi171())->getMainMenu($versionName);
            $data['share'] = $this->_getShareSettings();
            $data['no_images'] = $this->getNoImages();
            $data['default'] = [
                'filter_menu' => $this->getDefaultFilterMenu(),
                'sort_menu'   => $this->getDefaultSortMenu(),
                'action_menu' => $this->getDefaultActionMenu(),
            ];
            $data['tab_setting'] = $this->getTabSetting();

            (($sPlugin = Phpfox_Plugin::get('mobile.service_core_api_site_settings')) ? eval($sPlugin) : false);

            $cacheLib->saveBoth($cacheId, $data);
            $cacheLib->group('settings', $cacheId);
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.service_core_api_site_settings_no_cache')) ? eval($sPlugin) : false);

        // no apply cache
        $data['chat'] = $this->_getChatSettings();
        $data['firebase'] = [
            'password_hash' => md5($userId . Phpfox::getParam('core.salt')),
            'old_password_hash' => base64_encode(base64_encode($userId))
        ];

        return $this->success($data);
    }

    public function getScreenSettings($param = [])
    {
        $resources = NameResource::instance()->getResourceNames(true);

        $screenSettings = Phpfox::getService("mobile.mobile_app_helper")
            ->getScreenSettings($resources, $param);
        if (!isset($param['screen_only']) || !$param['screen_only']) {
            $screenSettings = Phpfox::getService('mobile.ad-config')->getAllConfigsToSetting($screenSettings);
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.core_api_get_screen_settings')) ? eval($sPlugin) : false);

        return $screenSettings;
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('core', []);
        $resourceName = '';
        $embedComponents = [];
        if (Phpfox::isAppActive('Core_Announcement')) {
            $embedComponents = [
                [
                    'component'     => 'announcement_list_view',
                    'title'         => $l->translate('announcement'),
                    'resource_name' => 'announcement',
                    'module_name'   => 'announcement'
                ]
            ];
        }
        if (Phpfox::isModule('feed')) {
            $embedComponents[] = ['component' => 'stream_composer'];
            $embedComponents[] = ['component' => 'stream_pending_status'];
        }
        $screenSetting->addSetting($resourceName, 'home', [
            'header'       => [
                'component'    => 'home_header',
                'androidTitle' => 'home'
            ],
            'right'        => [
                [
                    'component'     => 'simple_list_block',
                    'module_name'   => 'friend',
                    'resource_name' => 'friend',
                    'title'         => $l->translate('friends')
                ],
                [
                    'component'     => 'simple_list_block',
                    'module_name'   => 'photo',
                    'resource_name' => 'photo_album',
                    'title'         => $l->translate('album'),
                    'limit'         => 4
                ]
            ],
            'content'      => [
                'component'       => 'stream_profile_feeds',
                'embedComponents' => $embedComponents
            ],
            'screen_title' => $l->translate('Core') . ' > ' . $l->translate('feed_steams')
        ]);
        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            'home' => 'core.index-member',
        ];
    }

    private function getNoImages()
    {
        return [
            'no-conversation' => $this->getAppImage('no-conversation'),
            'no-notification' => $this->getAppImage('no-notification'),
            'no-result' => $this->getAppImage('no-result')
        ];
    }

    public function getAppImage($imageName = 'no-item')
    {
        $basePath = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-mobile-api/assets/images/app-images/';

        return $basePath . $imageName . '.png';
    }

    private function _getGeneralSetting()
    {
        list($smallLogo,) = Phpfox::getService('mobile.admincp.setting')->getAppLogo();

        $bAllowVideoUploading = false;

        if (Phpfox::isAppActive('PHPfox_Videos')) {
            $iMethodUpload = setting('pf_video_method_upload');
            if (setting('pf_video_support_upload_video') && (
                ($iMethodUpload == 1 && setting('pf_video_key') && setting('pf_video_s3_key')) ||
                ($iMethodUpload == 0 && setting('pf_video_ffmpeg_path')) ||
                ($iMethodUpload == 2 && setting('pf_video_mux_token_id') && setting('pf_video_mux_token_secret'))
                )) {
                $bAllowVideoUploading = true;
            }
        }

        $setting = $this->getSetting();

        $data = [
            'logo_url'                    => '',        // string: site logo url
            'app_small_logo_url'          => $smallLogo,        // string: header logo url,
            'login_type'                  => $setting->getAppSetting('user.login_type'),   // string: login by email/or username ?
            'can_register'                => !!$setting->getAppSetting('user.allow_user_registration') && !$setting->getAppSetting('user.invite_only_community'),
            'can_login_by_facebook'       => !!setting('m9_facebook_enabled'),
            'can_login_by_apple'          => !!$setting->getAppSetting('mobile.mobile_enable_apple_login'),
            'can_login_by_google'         => !!$setting->getAppSetting('core.enable_register_with_google') && $setting->getAppSetting('core.google_oauth_client_id'),
            'google_api_key'              => $setting->getAppSetting('core.google_api_key'),
            'google_oauth_id'             => $setting->getAppSetting('core.google_oauth_client_id'),
            'enable_tag_friends'          => !!$setting->getAppSetting('feed.enable_tag_friends', 1),
            'enable_check_in'             => !!$setting->getAppSetting('feed.enable_check_in'),
            'enable_hide_feed'            => !!$setting->getAppSetting('feed.enable_hide_feed', 1),
            'enable_upload_video'         => !!$bAllowVideoUploading,
            'site_url'                    => $this->makeUrl(''),
            'min_char_global_search'      => $setting->getAppSetting('core.min_character_to_search', 2),
            'allow_activity_point'        => Phpfox::isAppActive('Core_Activity_Points') && $setting->getAppSetting('activitypoint.enable_activity_points'),
            'allow_registration_sms'      => !!$setting->getAppSetting('core.registration_sms_enable'),
            'allow_registration_phone'    => !!$setting->getAppSetting('core.enable_register_with_phone_number'),
            'photo.photo_max_upload_size' => Phpfox::getLib('file')->getLimit($setting->getUserSetting('photo.photo_max_upload_size') / 1024) * 1024,
            'enable_comment_sticker'      => Phpfox::isAppActive('Core_Comments') && class_exists('Apps\Core_Comments\Service\Stickers\Stickers') && !!$setting->getAppSetting('comment.comment_enable_sticker'),
            'enable_comment_photo'        => Phpfox::isAppActive('Core_Comments') && !!$setting->getAppSetting('comment.comment_enable_photo'),
            'enable_comment_emoticon'     => Phpfox::isAppActive('Core_Comments') && !!$setting->getAppSetting('comment.comment_enable_emoticon'),
            'datetime_format'             => [
                'global_update_time'           => $setting->getAppSetting('core.global_update_time'),
                'extended_global_time_stamp'   => $setting->getAppSetting('core.extended_global_time_stamp'),
                'feed_display_time_stamp'      => $setting->getAppSetting('feed.feed_display_time_stamp'),
                'user_dob_month_day'           => $setting->getAppSetting('user.user_dob_month_day'),
                'conver_time_to_string'        => $setting->getAppSetting('core.conver_time_to_string'),
                'enable_locale'                => false //Use device locale
            ],
        ];

        foreach ([
                     'photo.max_images_per_upload',
                 ] as $key) {
            $data[$key] = (int)$setting->getUserSetting($key);
        }

        return $data;
    }


    private function _getChatSettings()
    {

        if (Phpfox::isApps('P_ChatPlus')) {
            $chatPlusServerUrl = setting('p_chatplus_server');
            if ($chatPlusServerUrl) {
                return [
                    'enable'      => true,
                    'server'      => rtrim($chatPlusServerUrl, '/'),
                    'server_type' => 'chatplus',
                ];
            }
        }

        $path = '';
        // generate token
        if (!defined('PHPFOX_IM_TOKEN') || !PHPFOX_IM_TOKEN) {
            if (setting('pf_im_node_server_key')) {
                $imToken = md5(strtotime('today midnight') . setting('pf_im_node_server_key'));
                $lifeTime = time() + 86400;
            } else {
                $imToken = '';
                $lifeTime = '';
            }
            $server = setting('pf_im_node_server');
            $useFoxIM = false;
        } else {
            $aTokenData = storage()->get('im_host_token');
            $imToken = PHPFOX_IM_TOKEN;
            $server = rtrim(setting('pf_im_node_server'), '/');
            $path = '/socket.io/';
            if (isset($aTokenData->value->expired)) {
                $lifeTime = $aTokenData->value->expired;
            } else {
                $lifeTime = time() + 86400;
            }
            $useFoxIM = true;
        }
        //Get ban filter
        $filters = Phpfox::getService('ban')->getFilters('word');
        $banFilter = [];
        $banUser = [];
        if (is_array($filters)) {
            foreach ($filters as $filter) {
                $banFilter[$filter['find_value']] = html_entity_decode($filter['replacement']);
                $userGroupsAffected = $filter['user_groups_affected'];
                if (is_array($userGroupsAffected) && !empty($userGroupsAffected)) {
                    foreach ($userGroupsAffected as $userGroup) {
                        if ($userGroup['user_group_id'] == Phpfox::getUserBy('user_group_id')) {
                            if ($filter['return_user_group'] !== null) {
                                $banUser[$filter['find_value']] = $filter['ban_id'];
                            }
                            break;
                        }
                    }
                }
            }
        }
        return [
            'enable'              => Phpfox::isAppActive('PHPfox_IM'),
            'server'              => $server, // socket server url,
            'path'                => $path,
            'query'               => ['token' => $imToken, 'EIO' => 3, 'host' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost'], // generated token
            'life_time'           => (int)$lifeTime, // expired token timestamp,
            'use_phpfox_im'       => $useFoxIM,
            'allow_non_friends'   => (bool)setting('pf_im_allow_non_friends', 0),
            'server_type'         => setting('pf_im_chat_server', 'nodejs'),
            'user_id'             => (int)$this->getUser()->getId(), // current user id
            'time_delete_message' => setting('pf_time_to_delete_message') * 86400000,
            'total_conversations' => (int)setting('pf_total_conversations'),
            'algolia_app_id'      => setting('pf_im_algolia_app_id'),
            'algolia_api_key'     => setting('pf_im_algolia_api_key'),
            'firebase_server_key' => setting('mobile.mobile_firebase_server_key'),
            'firebase_sender_id'  => setting('mobile.mobile_firebase_sender_id'),
            'banned_words'        => $banFilter,
            'ban_users'           => $banUser
        ];
    }

    public function getMobileRoutes()
    {
        return [
            'blog/add' => [
                'type'           => 'createFormEditScreen',
                'loadFormApiUrl' => 'mobile/blog/form',
                'formAction'     => null,
                'formName'       => 'blog/add',
            ],
        ];
    }

    private function _getShareSettings()
    {
        return [
            'menu' => [
                'title'   => null,
                'message' => null,
                'options' => [
                    ['label' => $this->getLocalization()->translate('share_on_your_wall'), 'value' => 'share.wall'],
                    ['label' => $this->getLocalization()->translate('share_on_friend_s_wall'), 'value' => 'share.friend'],
                    ['label' => $this->getLocalization()->translate('share_on_social'), 'value' => 'share.social'],
                ],

            ],
        ];
    }

    /**
     * Check service is alive
     *
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    public function ping()
    {
        $phrases = $this->resolver->resolveSingle(null, 'p');
        $pong = [
            'status' => 'success',
            'data'   => [
                "site_status"  => ($this->getSetting()->getAppSetting('core.site_is_offline') ? 'offline' : "online"),
                "site_name"    => $this->getSetting()->getAppSetting("core.site_title"),
                "site_title"   => $this->getSetting()->getAppSetting("core.global_site_title"),
                "home_url"     => UrlUtility::makeHomeUrl(),
                "api_endpoint" => UrlUtility::apiEndpoint(),
                "copyright"    => $this->getSetting()->getAppSetting("core.site_copyright"),
            ],
        ];
        if (!empty($phrases)) {
            $ps = [];
            foreach ($phrases as $phrase) {
                $ps[$phrase] = $this->getLocalization()->translate($phrase);
            }
            $pong['data']['phrases'] = $ps;
        }
        header('Content-Type: application/json');
        echo json_encode($pong);
        $this->isUnitTest() ?: exit();
    }

    public function getAll()
    {
        $friendRequestCount = $messageCount = $notificationCount = 0;
        if (Phpfox::isModule('friend')) {
            $friendRequestCount = Phpfox::getService('friend.request')->getUnseenTotal();
        }
        if (Phpfox::isAppActive('Core_Messages')) {
            $messageCount = Phpfox::getService('mail')->getUnseenTotal();
        }
        if (Phpfox::isModule('notification')) {
            $notificationCount = Phpfox::getService('notification')->getUnseenTotal();
        }

        $data = [
            'update_count'         => 0,
            'friend_request_count' => $friendRequestCount,
            'message_count'        => $messageCount,
            'notification_count'   => $notificationCount,
        ];

        return $this->success($data);
    }

    /**
     * @param $params
     *
     * @return mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\ErrorException
     */
    public function getFormTypes($params)
    {
        $pathToCheck = PHPFOX_PARENT_DIR . "PF.Site/Apps/core-mobile-api/Api/Form/Type";
        $pathToCheck = str_replace("/", PHPFOX_DS, $pathToCheck); // Window path compatible
        $form = $this->createForm(Form::class, [
            'title'       => 'All fields example Form',
            'description' => 'Description of form',
            'method'      => 'post',
            'action'      => 'example/end-point',
        ]);
        foreach (glob($pathToCheck . '/*.php') as $file) {
            $className = basename($file, '.php');
            $class = "Apps\\Core_MobileApi\\Api\\Form\\Type\\" . $className;
            if (class_exists($class) && $class != AbstractOptionType::class) {
                $className = str_replace("type", "", strtolower($className));
                $options = [
                    'label'       => "$className label",
                    'description' => "$className text description",
                    // 'value_default' => 'default_value',
                    // 'value' => 'current_value',
                    // 'required' => false,
                ];

                if (is_subclass_of(new $class, "Apps\\Core_MobileApi\\Api\\Form\\Type\\AbstractOptionType")) {
                    $options['options'] = [
                        [
                            'value' => 1,
                            'label' => 'Options is required for type extend AbstractChoiceTypes Only',
                        ],
                    ];
                }
                if ($class == HierarchyType::class) {
                    $options['options'] = [
                        [
                            'value' => 1,
                            'label' => 'Parents label',
                        ],
                    ];
                    $options['suboptions'] = [
                        1 => [
                            [
                                'value' => 1,
                                'label' => 'Children label',
                            ],
                        ],
                    ];
                }
                if ($class == CountryStateType::class) {
                    $options['value'] = ['US', '1'];
                    $options['options'] = [
                        [
                            'value' => 'US',
                            'label' => 'Parents label',
                        ],
                    ];
                    $options['suboptions'] = [
                        'US' => [
                            [
                                'value' => 1,
                                'label' => 'Children label',
                            ],
                        ],
                    ];
                }
                $options['metadata'] = true;

                $form->addField($className, $class, $options);
            }
        }

        if (isset($params['type'])) {
            $sField = strtolower($params['type']);
            if (!$form->isField($sField)) {
                return $this->notFoundError();
            }
            return $this->success($form->getField($sField)->getStructure());
        }

        return $this->success($form->getFormStructure());
    }

    public function getRoute($params)
    {
        return $this->success(NameResource::instance()->getRoutingTable("mobile"));
    }

    public function getAllRoutesMapping()
    {
        $mapping = [];

        $supportResource = [
            BlogApi::class,
            EventApi::class,
            ForumApi::class,
            ForumPostApi::class,
            ForumThreadApi::class,
            GroupApi::class,
            GroupInfoApi::class,
            GroupMemberApi::class,
            GroupProfileApi::class,
            MusicAlbumApi::class,
            MusicSongApi::class,
            MusicPlaylistApi::class,
            PageApi::class,
            PageInfoApi::class,
            PageMemberApi::class,
            PageInfoApi::class,
            PhotoAlbumApi::class,
            PhotoApi::class,
            PollApi::class,
            QuizApi::class,
            UserApi::class,
            VideoApi::class,
        ];

        (($sPlugin = Phpfox_Plugin::get('mobile.service_coreapi_getallroutesmapping_start')) ? eval($sPlugin) : false);

        foreach ($supportResource as $apiResource) {
            $instance = new $apiResource();
            if (method_exists($instance, $method = 'getRouteMap')) {
                $mapping = array_merge($mapping, $instance->$method());
            }
        }
        return $this->success($mapping);
    }

    /**
     * @param string|array $params
     * @param bool $returnArray
     *
     * @return mixed
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     * @throws \Exception
     */
    public function parseUrlToRoute($params, $returnArray = false)
    {
        if (!$returnArray) {
            //Fix case "url" contain multiple params joined by "&"
            $params = array_filter($params, function ($key) {
                return !in_array($key, ['api_version_name', 'access_token', 'do']);
            }, ARRAY_FILTER_USE_KEY);
            $url = '';
            foreach ($params as $key => $param) {
                if ($key == 'url') {
                    $url .= $param;
                } else {
                    $url .= '&' . $key . '=' . $param;
                }
            }
        } else {
            if (is_array($params)) {
                $url = isset($params['url']) ? $params['url'] : null;
            } else {
                $url = $params;
            }
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.service_coreapi_parseUrlToRoute_start')) ? eval($sPlugin) : false);

        if (empty($url)) {
            $this->notFoundError();
        }
        $nameResource = NameResource::instance();

        //Replace host url without http/https
        $url = preg_replace('/(http|https):\/\//', '', $url);
        $corePath = preg_replace('/(http|https):\/\//', '', Phpfox::getParam('core.path'));
        $relativePath = trim(str_replace(trim($corePath, '/'), '', $url), '/');

        if (empty($relativePath)) {
            if ($returnArray) {
                return ['routeName' => 'home'];
            } else {
                return $this->success(['routeName' => 'home']);
            }
        }
        $pathPart = explode('/', preg_replace('/\?(.*)/', '', $relativePath));
        $extra = isset($pathPart[1]) ? $pathPart[1] : '';
        $query = $this->parseQueryParams($relativePath);
        $isExtraResource = $extra ? $nameResource->hasApiResourceService($extra) : false;
        $data = [];
        $pathPart[0] = isset($this->specialModules[$pathPart[0]]) ? $this->specialModules[$pathPart[0]] : $pathPart[0];
        $pathPart[0] = Phpfox_Url::instance()->reverseRewrite($pathPart[0]); // support rewrite url
        $objectResources = $nameResource->getObjectResources();
        //Check vanity_url
        if (db()->tableExists(Phpfox::getT('pages_url'))) {
            $vanity = $this->database()->select('pu.page_id, p.item_type')
                ->from(':pages_url', 'pu')
                ->join(':pages', 'p', 'p.page_id = pu.page_id')
                ->where('vanity_url = \'' . $pathPart[0] . '\'')
                ->execute('getRow');
        }
        if (!empty($vanity)) {
            $pathPart[0] = $vanity['item_type'] == 0 ? 'pages' : 'groups';
            $extra = $vanity['page_id'];
        }
        $skipObjectResource = false;
        if ($nameResource->hasApiResourceService($pathPart[0]) && in_array($pathPart[0], $nameResource->getSupportModules()) && $apiResource = $nameResource->getApiServiceByResourceName($pathPart[0])) {
            if ($apiResource instanceof MobileAppSettingInterface) {
                $mainResource = $apiResource->getAppSetting([])->getParam('main_resource');
                if ($mainResource && $mainResource instanceof ResourceBase) {
                    $data = $mainResource->getUrlMapping($relativePath, $query);
                    $skipObjectResource = !empty($data) && is_array($data);
                }
            }
        }
        if (!$skipObjectResource && isset($objectResources[$pathPart[0]])) {
            /**
             * @var ResourceBase $objResource
             */
            $objResource = (new $objectResources[$pathPart[0]]([]));
            $data = $objResource->getUrlMapping($relativePath, $query);
        }
        if (empty($data) || !is_array($data)) {
            if (Phpfox::isModule($pathPart[0]) && $nameResource->hasApiResourceService($pathPart[0])) {
                if ($pathPart[0] == 'link' || !empty($query['link-id'])) {
                    $linkId = !empty($query['link-id']) ? $query['link-id'] : $extra;
                    $link = Phpfox::getService('link')->getLinkById($linkId);
                    if ($link) {
                        $feedPrefix = $link['module_id'] === 'groups' ? 'pages' : $link['module_id'];
                        $feed = $this->getFeedFromItem('link', $linkId, $feedPrefix);
                        if ($feed) {
                            $data = [
                                'routeName' => 'viewItemDetail',
                                'params'    => [
                                    'module_name'   => 'feed',
                                    'resource_name' => 'feed',
                                    'id'            => (int)$feed['feed_id'],
                                    'query'         => [
                                        'item_type' => $link['module_id'] ? $link['module_id'] : null,
                                        'item_id'   => $link['item_id'] ? (int)$link['item_id'] : null
                                    ]
                                ]
                            ];
                        }
                    }
                } elseif (!empty($query['comment-id'])) {
                    $feedPrefix = $pathPart[0] === 'groups' ? 'pages' : $pathPart[0];
                    $feed = $this->getFeedFromItem($pathPart[0] . '_comment', $query['comment-id'], $feedPrefix);
                    if ($feed) {
                        $data = [
                            'routeName' => 'viewItemDetail',
                            'params'    => [
                                'module_name'   => 'feed',
                                'resource_name' => 'feed',
                                'id'            => (int)$feed['feed_id'],
                                'query'         => [
                                    'item_type' => $pathPart[0],
                                    'item_id'   => (int)(isset($feed['parent_user_id']) ? $feed['parent_user_id'] : $query['comment-id'])
                                ]
                            ]
                        ];
                    }
                } elseif (is_numeric($extra) && isset($pathPart[2]) && $nameResource->hasApiResourceService($pathPart[2])) {
                    $data = $this->parseUrlListingOnParentApp($pathPart, $extra, $query);
                } elseif (is_numeric($extra)) {
                    //Go to item detail
                    $data = [
                        'routeName' => 'viewItemDetail',
                        'params'    => [
                            'id'            => (int)$extra,
                            'module_name'   => $pathPart[0],
                            'resource_name' => $pathPart[0]
                        ],
                    ];
                } else {
                    $data = [
                        'routeName' => 'module/home',
                        'params'    => [
                            'module_name'   => $pathPart[0],
                            'resource_name' => $pathPart[0],
                            'query' => [
                                'view' => isset($query['view']) ? $query['view'] : '',
                                'q' => isset($query['search']['search']) ? $query['search']['search'] : ''
                            ]
                        ],
                    ];
                }
            } else {
                $user = Phpfox::getService('user')->getByUserName($pathPart[0]);
                if ($user) {
                    //Redirect to status detail
                    $data = [
                        'routeName' => 'viewItemDetail',
                        'params'    => [
                            'module_name'   => 'user',
                            'resource_name' => 'user',
                            'id'            => (int)$user['user_id'],
                        ]
                    ];
                    if (!empty($query['feed'])) {
                        $data = [
                            'routeName' => 'viewItemDetail',
                            'params'    => [
                                'module_name'   => 'feed',
                                'resource_name' => 'feed',
                                'id'            => (int)$query['feed'],
                            ]
                        ];
                    } elseif (!empty($query['status-id']) || !empty($query['comment-id'])) {
                        $itemId = !empty($query['status-id']) ? $query['status-id'] : $query['comment-id'];
                        $itemType = !empty($query['status-id']) ? 'user_status' : 'feed_comment';
                        $feed = $this->getFeedFromItem($itemType, $itemId);
                        if ($feed) {
                            $data = [
                                'routeName' => 'viewItemDetail',
                                'params'    => [
                                    'module_name'   => 'feed',
                                    'resource_name' => 'feed',
                                    'id'            => (int)$feed['feed_id'],
                                ]
                            ];
                        }
                    } elseif (!empty($query['link-id'])) {
                        $feed = $this->getFeedFromItem('link', $query['link-id']);
                        if ($feed) {
                            $data = [
                                'routeName' => 'viewItemDetail',
                                'params'    => [
                                    'module_name'   => 'feed',
                                    'resource_name' => 'feed',
                                    'id'            => (int)$feed['feed_id'],
                                ]
                            ];
                        }
                    } elseif (!empty($query['poke-id'])) {
                        $feed = $this->getFeedFromItem('poke', $query['poke-id']);
                        if ($feed) {
                            $data = [
                                'routeName' => 'viewItemDetail',
                                'params'    => [
                                    'module_name'   => 'feed',
                                    'resource_name' => 'feed',
                                    'id'            => (int)$feed['feed_id'],
                                ]
                            ];
                        }
                    } else if (!$user['profile_page_id']) {
                        //User profile
                        if (!$extra) {
                            $data = [
                                'routeName' => 'viewItemDetail',
                                'params'    => [
                                    'module_name'   => 'user',
                                    'resource_name' => 'user',
                                    'id'            => (int)$user['user_id'],
                                ]
                            ];
                        } elseif ($isExtraResource) {
                            $data = $this->parseUrlListingOnProfile($extra, $pathPart, $user, $query);
                        }
                    } else {
                        $module = Phpfox::getService('pages')->isPage($pathPart[0]) ? 'pages' : (Phpfox::getService('groups')->isPage($pathPart[0]) ? 'groups' : $extra);
                        //Is pages/groups
                        $data = [
                            'routeName' => $isExtraResource ? 'viewItemListing' : 'viewItemDetail',
                            'params'    => [
                                'module_name'   => $module,
                                'resource_name' => $isExtraResource ? $extra : $module,
                            ]
                        ];
                        if ($isExtraResource) {
                            $data['params']['query'] = [
                                'module_id' => $module,
                                'item_id'   => (int)$user['profile_page_id'],
                            ];
                        } else {
                            $data['params']['id'] = (int)$user['profile_page_id'];
                        }
                    }
                }
            }
        }
        (($sPlugin = Phpfox_Plugin::get('mobile.service_coreapi_parseUrlToRoute_end')) ? eval($sPlugin) : false);

        //If can't get route, return original url
        if (empty($data) || is_string($data)) {
            $data = ['url' => $url];
        }

        if ($returnArray) {
            return $data;
        }
        return $this->success($data);


    }

    /**
     * @param        $sModule
     * @param        $iItemId
     * @param string $prefix
     *
     * @return array|bool
     */
    private function getFeedFromItem($sModule, $iItemId, $prefix = null)
    {
        $aRow = $this->database()->select('*')
            ->from(!empty($prefix) ? Phpfox::getT($prefix . '_feed') : Phpfox::getT('feed'))
            ->where('type_id = \'' . $this->database()->escape($sModule) . '\' AND item_id = ' . (int)$iItemId)
            ->executeRow();

        if (isset($aRow['feed_id'])) {
            return $aRow;
        }

        return false;
    }

    private function parseQueryParams($url)
    {
        $query = parse_url($url, PHP_URL_QUERY);
        parse_str($query, $queryArray);
        preg_match_all('/([^\/?&]+)_([^\/?&]+)/', $url, $matchForceParam);
        if (!empty($matchForceParam[1]) && !empty($matchForceParam[2])) {
            foreach ($matchForceParam[1] as $index => $paramName) {
                $queryArray[$paramName] = $matchForceParam[2][$index];
            }
        }
        return $queryArray;
    }

    public function phrases()
    {
        $languageId = $this->getLanguageId();
        $cacheLib = Phpfox::getLib('cache');
        $cacheId = $cacheLib->set('mobile_phrases_' . $languageId);

        if (!($phrases = $cacheLib->getLocalFirst($cacheId))) {
            $mobilePhrases = $this->mobilePhrases();
            $phrases = [];
            foreach ($mobilePhrases as $mobilePhrase) {
                $phrases[$mobilePhrase] = $this->getLocalization()->translate($mobilePhrase, [], $languageId);
            }
            // update special uppercase character first
            foreach ($this->specialUCFirsts as $mobilePhrase) {
                $phrases[$mobilePhrase] = ucfirst($phrases[$mobilePhrase]);
            }
            $cacheLib->saveBoth($cacheId, $phrases);
            $cacheLib->group('locale', $cacheId);
        }
        $direction = 'ltr';
        $languageName = '';
        $language = $this->getLanguage();
        if ($language) {
            $direction = $language['direction'];
            $languageName = $this->getLocalization()->translate($language['title']);
        }

        return $this->success([
            'locale'    => $languageId,
            'name'      => $languageName,
            'direction' => $direction,
            'messages'  => $phrases
        ]);
    }

    private function mobilePhrases()
    {
        $phrasesList = [];
        $phrasesListData = file_get_contents(dirname(dirname(__FILE__)) . '/mobilePhrase.json');
        if (!empty($phrasesListData)) {
            $phrasesList = json_decode($phrasesListData, true);
            $phrasesList = array_keys($phrasesList);
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.service_coreapi_mobilePhrases')) ? eval($sPlugin) : false);

        return $phrasesList;
    }

    protected function getLanguageId()
    {
        $languageId = $this->getUser()->language_id;
        if (!$languageId) {
            $languageId = \Phpfox_Locale::instance()->autoLoadLanguage();
        }
        return $languageId;
    }

    public function getLanguage()
    {
        $languageId = $this->getLanguageId();
        $cacheLib = Phpfox::getLib('cache');
        $sLangId = $cacheLib->set(['locale', 'language_' . $languageId]);
        if (!($aLanguage = $cacheLib->get($sLangId))) {
            $aLanguage = db()->select('*')
                ->from(Phpfox::getT('language'))
                ->where("language_id = '" . db()->escape($languageId) . "'")
                ->execute('getRow');
        }
        return $aLanguage;
    }

    protected function getUserGroupId()
    {
        return Phpfox::getUserBy('user_group_id');
    }

    public function getGateway($params)
    {
        $params = $this->resolver
            ->setDefined(['price', 'currency', 'seller', 'allow_point', 'allow_gateway', 'item_number', 'extra'])
            ->setDefault([
                'allow_point'   => true,
                'allow_gateway' => true
            ])
            ->resolve($params)->getParameters();

        //Core support PayPal only
        $conditions = [
            'ag.is_active' => 1,
            'ag.gateway_id' => 'paypal'
        ];

        (($sPlugin = Phpfox_Plugin::get('mobile.service_core_api_get_gateway_query')) ? eval($sPlugin) : false);

        if ($params['allow_gateway']) {
            $gateways = $this->database()
                ->select('ag.*')
                ->from(':api_gateway', 'ag')
                ->where($conditions)
                ->execute('getSlaveRows');
        } else {
            $gateways = [];
        }

        $userGateways = [];
        if (!empty($params['seller'])) {
            $userGateways = Phpfox::getService('api.gateway')->getUserGateways((int)$params['seller']);
        }
        $results = [];
        if ($gateways) {
            foreach ($gateways as $gateway) {
                $data = [];
                if ($gateway['gateway_id'] == 'paypal') {
                    $clientId = $this->getSetting()->getAppSetting('mobile.mobile_paypal_client_id');
                    $secretId = $this->getSetting()->getAppSetting('mobile.mobile_paypal_secret_id');
                    if (empty($clientId) || empty($secretId)) {
                        continue;
                    }
                    $data = [
                        'gateway_id' => $gateway['gateway_id'],
                        'title' => html_entity_decode($gateway['title'], ENT_QUOTES),
                        'description' => html_entity_decode($gateway['description'], ENT_QUOTES),
                        'return_url' => Phpfox::getLib('url')->makeUrl('mobile.gateway.callback-success.' . $gateway['gateway_id']),
                        'cancel_url' => Phpfox::getLib('url')->makeUrl('mobile.gateway.callback-fail.' . $gateway['gateway_id']),
                        'sandbox' => !!$gateway['is_test'],
                        'client_id' => $clientId,
                        'secret_id' => $secretId,
                    ];
                }

                (($sPlugin = Phpfox_Plugin::get('mobile.service_core_api_get_gateway_looping')) ? eval($sPlugin) : false);

                if (!empty($params['seller'])) {
                    if (
                        isset($userGateways[$gateway['gateway_id']]) && !empty($userGateways[$gateway['gateway_id']]['gateway'])
                        && ($gateway['gateway_id'] != 'paypal' || (!empty($userGateways[$gateway['gateway_id']]['gateway']['merchant_id']) && !empty($userGateways[$gateway['gateway_id']]['gateway']['paypal_email'])))
                    ) {
                        $data['setting'] = $userGateways[$gateway['gateway_id']]['gateway'];
                        $results[] = $data;
                    }
                } elseif (count($data)) {
                    $results[] = $data;
                }
            }
        }
        if ($params['allow_point']) {
            $this->getPointGateway($params, $results);
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.service_core_api_get_gateway')) ? eval($sPlugin) : false);

        return $this->success($results);
    }

    private function getPointGateway($params, &$gateways)
    {
        if (isset($params['currency'], $params['price']) && Phpfox::isAppActive('Core_Activity_Points') && Phpfox::getUserParam('activitypoint.can_purchase_with_activity_points')) {
            $iUserId = (int)$this->getUser()->getId();
            $totalPoints = (int)$this->database()
                ->select('activity_points')
                ->from(Phpfox::getT('user_activity'))
                ->where(['user_id' => $iUserId])
                ->execute('getSlaveField');
            $setting = Phpfox::getParam('activitypoint.activity_points_conversion_rate');
            $currency = $params['currency'];
            if (isset($setting[$currency]) && is_numeric($setting[$currency])) {
                $conversion = ($setting[$currency] != 0 ? ($params['price'] / $setting[$currency]) : 0);
                if ($totalPoints >= $conversion) {
                    $gateways[] = [
                        'gateway_id'  => 'activitypoints',
                        'title'       => $this->getLocalization()->translate('activity_points'),
                        'description' => $this->getLocalization()->translate('purchase_points_info', ['yourpoints' => number_format($totalPoints), 'yourcost' => number_format($conversion)]),
                        'notify_url'  => ''
                    ];
                }
            }
        }
    }

    public function checkoutWithPoints($params)
    {
        $params = $this->resolver
            ->setRequired(['price', 'currency', 'gateway_id', 'item_number'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        if ($params['gateway_id'] == 'activitypoints' && Phpfox::isAppActive('Core_Activity_Points') && Phpfox::getUserParam('activitypoint.can_purchase_with_activity_points')) {
            $aParts = explode('|', $params['item_number']);
            if ($aReturn = Phpfox::getService('activitypoint.process')->purchaseWithPoints($aParts[0], $aParts[1],
                $params['price'], $params['currency'])
            ) {
                return $this->success([], [], $this->getLocalization()->translate('purchase_successfully_completed_dot'));
            }
        }
        return $this->permissionError($this->getErrorMessage());
    }

    public function callbackBillingPlan($response)
    {
        $recurringSubscription = false;
        if (!empty($response['event_type']) && in_array($response['event_type'], ['BILLING.SUBSCRIPTION.UPDATED', 'BILLING.SUBSCRIPTION.RE-ACTIVATED', 'BILLING.SUBSCRIPTION.CANCELLED', 'PAYMENT.SALE.COMPLETED', 'PAYMENT.SALE.DENIED', 'PAYMENT.CAPTURE.DENIED', 'PAYMENT.CAPTURE.COMPLETED'])) {
            $resource = $response['resource'];
            if (in_array($response['event_type'], ['PAYMENT.SALE.COMPLETED', 'PAYMENT.SALE.DENIED']) && !empty($resource['billing_agreement_id'])) {
                //Should fetch agreement id first
                $agreement = $this->getPayPalAgreement($resource['billing_agreement_id']);
                if (empty($agreement)) {
                    //Add Log
                    Phpfox::log('Can\'t get Agreement');
                    Phpfox::getService('api.gateway.process')->addLog('paypal', Phpfox::endLog());
                    return false;
                }
                $price = isset($resource['amount']['total']) ? $resource['amount']['total'] : 0;
                $responseStatus = isset($resource['state']) ? $resource['state'] : $agreement['state'];
                $invoice = isset($resource['description']) ? $resource['description'] : $agreement['description'];
                $recurringSubscription = true;
            } else if (in_array($response['event_type'], ['PAYMENT.CAPTURE.DENIED', 'PAYMENT.CAPTURE.COMPLETED'])) {
                $price = isset($resource['amount']['value']) ? $resource['amount']['value'] : ($response['amount']['total'] ? $response['amount']['total'] : 0);
                $responseStatus = isset($resource['status']) ? $resource['status'] : (isset($response['state']) ? $response['state'] : '');
                $invoice = isset($resource['invoice_id']) ? $resource['invoice_id'] : '';
            } else {
                $responseStatus = isset($resource['state']) ? $resource['state'] : (isset($resource['status']) ? $resource['status'] : '');
                $price = isset($resource['agreement_details']) ? $resource['agreement_details']['last_payment_amount']['value'] : 0;
                $invoice = isset($resource['description']) ? $resource['description'] : '';
                $recurringSubscription = true;
            }
            //Support renew manual for subscription
            $invoice = preg_replace('/-renew\|(\d){10}$/', '', $invoice);

            $this->processPaypalBillingCallback($invoice, $responseStatus, $price, $recurringSubscription);
        } else {
            return $this->callbackPaymentApi($_REQUEST);
        }
        return true;
    }

    private function getPayPalAgreement($id)
    {
        $payPal = $this->database()
            ->select('ag.*')
            ->from(':api_gateway', 'ag')
            ->where('ag.is_active = 1 && ag.gateway_id = "paypal"')
            ->execute('getRow');
        if (!$payPal || !$this->getSetting()->getAppSetting('mobile.mobile_paypal_client_id') || !$this->getSetting()->getAppSetting('mobile.mobile_paypal_secret_id')) {
            return false;
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $payPal['is_test'] ? 'https://api.sandbox.paypal.com/v1/oauth2/token' : 'https://api.paypal.com/v1/oauth2/token');
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERPWD, $this->getSetting()->getAppSetting('mobile.mobile_paypal_client_id') . ":" . $this->getSetting()->getAppSetting('mobile.mobile_paypal_secret_id'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");

        $result = curl_exec($ch);
        $result = $result !== false ? json_decode($result, true) : [];
        curl_close($ch);
        if (isset($result['access_token'])) {
            $authorization = 'Authorization: Bearer ' . $result['access_token'];
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', $authorization]);
            curl_setopt($ch, CURLOPT_URL, ($payPal['is_test'] ? 'https://api.sandbox.paypal.com/v1/payments/billing-agreements/' : 'https://api.paypal.com/v1/payments/billing-agreements/') . '/' . $id);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSLVERSION, 6);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response = curl_exec($ch);
            curl_close($ch);
            return $response !== false ? json_decode($response, true) : false;
        }
        return false;
    }

    /**
     * @param $invoice
     * @param $responseStatus
     * @param $price
     * @param $recurringSubscription
     */
    private function processPaypalBillingCallback($invoice, $responseStatus, $price, $recurringSubscription = false)
    {
        $parts = explode('|', $invoice);
        if (substr($parts[0], 0, 5) == '@App/') {
            $isApp = true;
            Phpfox::log('Is an APP.');
        } else {
            $isApp = Phpfox::isAppAlias($parts[0]);
        }

        if ($isApp || Phpfox::isModule($parts[0])) {
            if ($isApp || (Phpfox::isModule($parts[0]) && Phpfox::hasCallback($parts[0], 'paymentApiCallback'))) {
                $status = null;
                if (!empty($responseStatus)) {
                    switch (strtolower($responseStatus)) {
                        case 'active':
                        case 'completed':
                            $status = 'completed';
                            break;
                        case 'pending':
                            $status = 'pending';
                            break;
                        case 'suspended':
                        case 'cancelled':
                        case 'expired':
                        case 'denied':
                            $status = 'cancel';
                            break;
                    }
                    if ($status !== null) {
                        Phpfox::log('Executing module callback');

                        $params = [
                            'gateway'     => 'paypal',
                            'ref'         => '',
                            'status'      => $status,
                            'item_number' => $parts[1],
                            'total_paid'  => $price
                        ];

                        if ($isApp && !Phpfox::isAppAlias($parts[0])) {
                            $callback = str_replace('@App/', '', $parts[0]);
                            Phpfox::log('Running app callback on: ' . $callback);
                            Trigger::event($callback, $params);
                        } else {
                            Phpfox::callback($parts[0] . '.paymentApiCallback', $params);
                        }

                        header('HTTP/1.1 200 OK');
                    }
                }
            }
        }
        //Add Log
        Phpfox::getService('api.gateway.process')->addLog('paypal', Phpfox::endLog());
    }

    public function callbackPaymentApi($response)
    {
        Phpfox::log('Starting PayPal callback');
        // Read the post from PayPal system and add 'cmd'
        $req = 'cmd=_notify-validate';
        // Loop through each of the variables posted by PayPal
        foreach ($response as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }
        Phpfox::log('Attempting callback');
        // Post back to PayPal system to validate
        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Host: " . (true ? 'www.sandbox.paypal.com' : 'www.paypal.com') . "\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Content-Length: " . strlen($req) . "\r\n";
        $header .= "Connection: Close\r\n\r\n";
        $fp = fsockopen((true ? 'ssl://www.sandbox.paypal.com' : 'www.paypal.com'), (true ? 443 : 80), $error_no, $error_msg, 30);
        fputs($fp, $header . $req);
        $bVerified = false;
        while (!feof($fp)) {
            $res = fgets($fp, 1024);
            $res = strtoupper($res);
            if (strpos($res, 'VERIFIED') == 0) {
                $bVerified = true;
                break;
            }
        }
        fclose($fp);

        if ($bVerified === true) {
            $aParts = explode('|', $response['invoice']);
            if (substr($aParts[0], 0, 5) == '@App/') {
                $isApp = true;
                Phpfox::log('Is an APP.');
            } else {
                $isApp = Phpfox::isAppAlias($aParts[0]);
            }

            if ($isApp || Phpfox::isModule($aParts[0])) {
                if ($isApp || (Phpfox::isModule($aParts[0]) && Phpfox::hasCallback($aParts[0], 'paymentApiCallback'))) {
                    $sStatus = null;
                    if (isset($response['payment_status'])) {
                        switch ($response['payment_status']) {
                            case 'Completed':
                                $sStatus = 'completed';
                                break;
                            case 'Pending':
                                $sStatus = 'pending';
                                break;
                            case 'Refunded':
                            case 'Reversed':
                                $sStatus = 'cancel';
                                break;
                        }
                    }
                    if (isset($response['txn_type'])) {
                        switch ($response['txn_type']) {
                            case 'subscr_cancel':
                            case 'subscr_failed':
                                $sStatus = 'cancel';
                                break;
                        }
                    }

                    if ($sStatus !== null) {
                        Phpfox::log('Executing module callback');

                        $params = [
                            'gateway'     => 'paypal',
                            'ref'         => $response['txn_id'],
                            'status'      => $sStatus,
                            'item_number' => $aParts[1],
                            'total_paid'  => (isset($response['mc_gross']) ? $response['mc_gross'] : null)
                        ];

                        if ($isApp && !Phpfox::isAppAlias($aParts[0])) {
                            $callback = str_replace('@App/', '', $aParts[0]);
                            Phpfox::log('Running app callback on: ' . $callback);
                            Trigger::event($callback, $params);
                        } else {
                            Phpfox::callback($aParts[0] . '.paymentApiCallback', $params);
                        }

                        header('HTTP/1.1 200 OK');
                    } else {
                        Phpfox::log('Status is NULL. Nothing to do');
                    }
                } else {
                    Phpfox::log('Module callback is not valid.');
                }
            } else {
                Phpfox::log('Module is not valid.');
            }
        } else {
            Phpfox::log('Callback FAILED');
        }
        //Add Log
        Phpfox::getService('api.gateway.process')->addLog('paypal', Phpfox::endLog());
    }

    public function getTabSetting()
    {
        $tabSetting = (new TabSetting());

        (($sPlugin = Phpfox_Plugin::get('mobile.core_api_get_tab_setting')) ? eval($sPlugin) : false);

        return $tabSetting->getTabSetting();
    }

    public function getTermsPolicies()
    {
        $page = Phpfox::getService('page')->getPage(2);
        if (empty($page)) {
            return $this->notFoundError();
        }
        return $this->success([
            'content' => TextFilter::pureHtml($page['text'])
        ]);
    }

    public function getPrivacy()
    {
        $page = Phpfox::getService('page')->getPage(1);
        if (empty($page)) {
            return $this->notFoundError();
        }
        return $this->success([
            'content' => TextFilter::pureHtml($page['text'])
        ]);
    }

    /**
     * @param array|bool $pathPart
     * @param int|string $extra
     * @param mixed $query
     * @return array
     */
    private function parseUrlListingOnParentApp($pathPart, $extra, $query)
    {
        //Go to app listing
        $defaultMenu = [];
        $headerTitle = '';
        if ($pathPart[0] == 'pages' && Phpfox::isAppActive('Core_Pages')) {
            $api = (new PageApi());
            $defaultMenu = $api->defaultProfileMenu();
            $parentItem = $api->loadResourceById($extra);
        } elseif ($pathPart[0] == 'groups' && Phpfox::isAppActive('PHPfox_Groups')) {
            $api = (new GroupApi());
            $defaultMenu = $api->defaultProfileMenu();
            $parentItem = $api->loadResourceById($extra);
        }
        if (!empty($defaultMenu) && isset($parentItem['page_id'])) {
            $moduleId = $pathPart[2];
            $extraItem = array_values(array_filter($defaultMenu, function ($item) use ($moduleId) {
                return $item['module_id'] == $moduleId;
            }));
            if (!empty($pathPart[3])) {
                $tempResourceName = $pathPart[2] . '-' . preg_replace('/s$/', '', $pathPart[3]);
                $tempExtraItem = array_values(array_filter($extraItem, function ($item) use ($tempResourceName) {
                    return $item['resource_name'] == $tempResourceName;
                }));
                if (isset($tempExtraItem[0])) {
                    $extraItem = $tempExtraItem;
                }
            }
            $headerTitle = isset($extraItem[0]) ? $this->getLocalization()->translate('full_name_s_item', ['full_name' => $parentItem['title'], 'item' => $this->getLocalization()->translate($extraItem[0]['label'])]) : '';
        }
        return [
            'routeName' => 'viewItemListing',
            'params'    => [
                'module_name'   => $pathPart[2],
                'resource_name' => isset($extraItem[0]) ? str_replace('-', '_', $extraItem[0]['resource_name']) : $pathPart[2],
                'header_title' => $headerTitle,
                'query'         => [
                    'module_id'    => $pathPart[0],
                    'item_id'      => (int)$extra,
                    'q'            => isset($query['search']['search']) ? $query['search']['search'] : ''
                ],
            ],
        ];
    }

    /**
     * @param mixed $extra
     * @param array|bool $pathPart
     * @param array|int|string $user
     * @param mixed $query
     * @return array
     */
    private function parseUrlListingOnProfile($extra, $pathPart, $user, $query)
    {
        //Get header title
        $userProfile = (new UserApi())->defaultProfileMenu();
        $extraItem = array_values(array_filter($userProfile, function ($item) use ($extra) {
            return $item['module_name'] == $extra;
        }));
        if (!empty($pathPart[2])) {
            $tempResourceName = $pathPart[1] . '-' . preg_replace('/s$/', '', $pathPart[2]);
            $tempExtraItem = array_values(array_filter($extraItem, function ($item) use ($tempResourceName) {
                return $item['resource_name'] == $tempResourceName;
            }));
            if (isset($tempExtraItem[0])) {
                $extraItem = $tempExtraItem;
            }
        }
        $headerTitle = isset($extraItem[0]) ? $this->getLocalization()->translate('full_name_s_item', ['full_name' => $user['full_name'], 'item' => $this->getLocalization()->translate($extraItem[0]['label'])]) : '';
        return [
            'routeName' => 'viewItemListing',
            'params'    => [
                'module_name'   => $extra,
                'resource_name' => isset($extraItem[0]) ? str_replace('-', '_', $extraItem[0]['resource_name']) : $extra,
                'header_title'  => $headerTitle,
                'query'         => [
                    'profile_id' => (int)$user['user_id'],
                    'user_id'    => (int)$this->getUser()->getId(),
                    'q'          => isset($query['search']['search']) ? $query['search']['search'] : ''
                ],
            ]
        ];
    }
}