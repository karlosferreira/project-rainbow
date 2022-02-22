<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\AccountResource;
use Apps\Core_MobileApi\Api\Resource\ActivityPointResource;
use Apps\Core_MobileApi\Api\Resource\AdResource;
use Apps\Core_MobileApi\Api\Resource\AnnouncementResource;
use Apps\Core_MobileApi\Api\Resource\AttachmentResource;
use Apps\Core_MobileApi\Api\Resource\BlogCategoryResource;
use Apps\Core_MobileApi\Api\Resource\BlogResource;
use Apps\Core_MobileApi\Api\Resource\CommentResource;
use Apps\Core_MobileApi\Api\Resource\EventCategoryResource;
use Apps\Core_MobileApi\Api\Resource\EventInviteResource;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\FeedResource;
use Apps\Core_MobileApi\Api\Resource\FileResource;
use Apps\Core_MobileApi\Api\Resource\ForumAnnouncementResource;
use Apps\Core_MobileApi\Api\Resource\ForumModeratorResource;
use Apps\Core_MobileApi\Api\Resource\ForumPostResource;
use Apps\Core_MobileApi\Api\Resource\ForumResource;
use Apps\Core_MobileApi\Api\Resource\ForumSubscribeResource;
use Apps\Core_MobileApi\Api\Resource\ForumThankResource;
use Apps\Core_MobileApi\Api\Resource\ForumThreadResource;
use Apps\Core_MobileApi\Api\Resource\FriendResource;
use Apps\Core_MobileApi\Api\Resource\GroupAdminResource;
use Apps\Core_MobileApi\Api\Resource\GroupCategoryResource;
use Apps\Core_MobileApi\Api\Resource\GroupInfoResource;
use Apps\Core_MobileApi\Api\Resource\GroupInviteResource;
use Apps\Core_MobileApi\Api\Resource\GroupMemberResource;
use Apps\Core_MobileApi\Api\Resource\GroupPermissionResource;
use Apps\Core_MobileApi\Api\Resource\GroupPhotoResource;
use Apps\Core_MobileApi\Api\Resource\GroupProfileResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Resource\GroupTypeResource;
use Apps\Core_MobileApi\Api\Resource\GroupWidgetResource;
use Apps\Core_MobileApi\Api\Resource\LikeResource;
use Apps\Core_MobileApi\Api\Resource\LinkResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceCategoryResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceInviteResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceInvoiceResource;
use Apps\Core_MobileApi\Api\Resource\MarketplacePhotoResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Resource\MusicAlbumResource;
use Apps\Core_MobileApi\Api\Resource\MusicGenreResource;
use Apps\Core_MobileApi\Api\Resource\MusicPlaylistResource;
use Apps\Core_MobileApi\Api\Resource\MusicSongResource;
use Apps\Core_MobileApi\Api\Resource\PageAdminResource;
use Apps\Core_MobileApi\Api\Resource\PageCategoryResource;
use Apps\Core_MobileApi\Api\Resource\PageInfoResource;
use Apps\Core_MobileApi\Api\Resource\PageInviteResource;
use Apps\Core_MobileApi\Api\Resource\PageMemberResource;
use Apps\Core_MobileApi\Api\Resource\PagePermissionResource;
use Apps\Core_MobileApi\Api\Resource\PagePhotoResource;
use Apps\Core_MobileApi\Api\Resource\PageProfileResource;
use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Resource\PageTypeResource;
use Apps\Core_MobileApi\Api\Resource\PageWidgetResource;
use Apps\Core_MobileApi\Api\Resource\PhotoAlbumResource;
use Apps\Core_MobileApi\Api\Resource\PhotoCategoryResource;
use Apps\Core_MobileApi\Api\Resource\PhotoResource;
use Apps\Core_MobileApi\Api\Resource\PollAnswerResource;
use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\PollResultResource;
use Apps\Core_MobileApi\Api\Resource\QuizResource;
use Apps\Core_MobileApi\Api\Resource\QuizResultResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Resource\SearchResource;
use Apps\Core_MobileApi\Api\Resource\SubscriptionResource;
use Apps\Core_MobileApi\Api\Resource\TagResource;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Resource\VideoCategoryResource;
use Apps\Core_MobileApi\Api\Resource\VideoResource;
use Apps\Core_MobileApi\Api\ResourceInterface;
use Apps\Core_MobileApi\Api\ResourceRoute;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Service\Auth\AuthenticationApi;
use Apps\Core_MobileApi\Version1_6\Api\Resource\CommentStickerResource;
use Apps\Core_MobileApi\Version1_6\Api\Resource\CommentStickerSetResource;
use Apps\Core_MobileApi\Version1_6\Service\CommentStickerApi;
use Phpfox;
use Phpfox_Plugin;

class NameResource
{
    /**
     * @var array
     */
    private $apiNames;

    /**
     * Define resource API associated with the name of resource
     *
     * @var array
     */
    private $resourceNames;

    /**
     * @var \Phpfox_Cache_Interface
     */
    private $cache;

    private static $singleton;

    private $noModulesList;

    private $specialModules;

    private $supportModules;

    private $moduleIsApps;

    private $objectResources;

    /**
     * NameResource constructor.
     */
    public function __construct()
    {
        $this->apiNames = [
            'mobile.report_reason_api'        => ReportReasonApi::class,
            'mobile.search_api'               => SearchApi::class,
            'mobile.friend_api'               => FriendApi::class,
            'mobile.photo_api'                => PhotoApi::class,
            'mobile.blog_api'                 => BlogApi::class,
            'mobile.blog_category_api'        => BlogCategoryApi::class,
            'mobile.like_api'                 => LikeApi::class,
            'mobile.comment_api'              => CommentApi::class,
            'mobile.video_api'                => VideoApi::class,
            'mobile.notification_api'         => NotificationApi::class,
            'mobile.poll_api'                 => PollApi::class,
            'mobile.poll_result_api'          => PollResultApi::class,
            'mobile.poll_answer_api'          => PollAnswerApi::class,
            'mobile.quiz_api'                 => QuizApi::class,
            'mobile.quiz_result_api'          => QuizResultApi::class,
            'mobile.marketplace_api'          => MarketplaceApi::class,
            'mobile.marketplace_category_api' => MarketplaceCategoryApi::class,
            'mobile.marketplace_photo_api'    => MarketplacePhotoApi::class,
            'mobile.marketplace_invite_api'   => MarketplaceInviteApi::class,
            'mobile.marketplace_invoice_api'  => MarketplaceInvoiceApi::class,
            'mobile.menu_api'                 => MenuApi::class,
            'mobile.event_api'                => EventApi::class,
            'mobile.ping_api'                 => CoreApi::class,
            'mobile.page_api'                 => PageApi::class,
            'mobile.page_type_api'            => PageTypeApi::class,
            'mobile.page_category_api'        => PageCategoryApi::class,
            'mobile.page_profile_api'         => PageProfileApi::class,
            'mobile.page_admin_api'           => PageAdminApi::class,
            'mobile.page_permission_api'      => PagePermissionApi::class,
            'mobile.page_info_api'            => PageInfoApi::class,
            'mobile.page_photo_api'           => PagePhotoApi::class,
            'mobile.page_invite_api'          => PageInviteApi::class,
            'mobile.page_member_api'          => PageMemberApi::class,
            'mobile.page_widget_api'          => PageWidgetApi::class,
            'mobile.account_api'              => AccountApi::class,
            'mobile.event_category_api'       => EventCategoryApi::class,
            'mobile.event_invite_api'         => EventInviteApi::class,
            'mobile.feed_api'                 => FeedApi::class,
            'mobile.forum_api'                => ForumApi::class,
            'mobile.forum_thread_api'         => ForumThreadApi::class,
            'mobile.forum_announcement_api'   => ForumAnnouncementApi::class,
            'mobile.forum_subscribe_api'      => ForumSubscribeApi::class,
            'mobile.forum_moderator_api'      => ForumModeratorApi::class,
            'mobile.forum_post_api'           => ForumPostApi::class,
            'mobile.forum_thank_api'          => ForumThankApi::class,
            'mobile.friend_request_api'       => FriendRequestApi::class,
            'mobile.group_api'                => GroupApi::class,
            'mobile.group_type_api'           => GroupTypeApi::class,
            'mobile.group_category_api'       => GroupCategoryApi::class,
            'mobile.group_profile_api'        => GroupProfileApi::class,
            'mobile.group_admin_api'          => GroupAdminApi::class,
            'mobile.group_member_api'         => GroupMemberApi::class,
            'mobile.group_permission_api'     => GroupPermissionApi::class,
            'mobile.group_info_api'           => GroupInfoApi::class,
            'mobile.group_photo_api'          => GroupPhotoApi::class,
            'mobile.group_invite_api'         => GroupInviteApi::class,
            'mobile.group_widget_api'         => GroupWidgetApi::class,
            'mobile.intl_api'                 => IntlApi::class,
            'mobile.message_api'              => MessageApi::class,
            'mobile.music_song_api'           => MusicSongApi::class,
            'mobile.music_album_api'          => MusicAlbumApi::class,
            'mobile.music_playlist_api'       => MusicPlaylistApi::class,
            'mobile.music_genre_api'          => MusicGenreApi::class,
            'mobile.message_conversation_api' => MessageConversationApi::class,
            'mobile.photo_album_api'          => PhotoAlbumApi::class,
            'mobile.photo_category_api'       => PhotoCategoryApi::class,
            'mobile.report_api'               => ReportApi::class,
            'mobile.video_category_api'       => VideoCategoryApi::class,
            'mobile.user_api'                 => UserApi::class,
            'mobile.attachment_api'           => AttachmentApi::class,
            'mobile.tag_api'                  => TagApi::class,
            'mobile.auth_api'                 => AuthenticationApi::class,
            'mobile.file_api'                 => FileApi::class,
            'mobile.core_api'                 => CoreApi::class,
            'mobile.link_api'                 => LinkApi::class,
            'mobile.friend_tag_api'           => FriendTagApi::class,
            'mobile.ad_api'                   => AdApi::class,
            'mobile.subscribe_api'            => SubscriptionApi::class,
            'mobile.announcement_api'         => AnnouncementApi::class,
            'mobile.comment_sticker'          => CommentStickerApi::class,
            'mobile.activitypoint_api'       => ActivityPointApi::class
        ];

        $this->resourceNames = [
            BlogResource::RESOURCE_NAME                => 'mobile.blog_api',
            UserResource::RESOURCE_NAME                => 'mobile.user_api',
            BlogCategoryResource::RESOURCE_NAME        => 'mobile.blog_category_api',
            AttachmentResource::RESOURCE_NAME          => 'mobile.attachment_api',
            PhotoResource::RESOURCE_NAME               => 'mobile.photo_api',
            PhotoAlbumResource::RESOURCE_NAME          => 'mobile.photo_album_api',
            PhotoCategoryResource::RESOURCE_NAME       => 'mobile.photo_category_api',
            EventResource::RESOURCE_NAME               => 'mobile.event_api',
            EventCategoryResource::RESOURCE_NAME       => 'mobile.event_category_api',
            EventInviteResource::RESOURCE_NAME         => 'mobile.event_invite_api',
            ForumResource::RESOURCE_NAME               => 'mobile.forum_api',
            ForumThreadResource::RESOURCE_NAME         => 'mobile.forum_thread_api',
            ForumAnnouncementResource::RESOURCE_NAME   => 'mobile.forum_announcement_api',
            ForumSubscribeResource::RESOURCE_NAME      => 'mobile.forum_subscribe_api',
            ForumModeratorResource::RESOURCE_NAME      => 'mobile.forum_moderator_api',
            ForumPostResource::RESOURCE_NAME           => 'mobile.forum_post_api',
            ForumThankResource::RESOURCE_NAME          => 'mobile.forum_thank_api',
            TagResource::RESOURCE_NAME                 => 'mobile.tag_api',
            LikeResource::RESOURCE_NAME                => 'mobile.like_api',
            CommentResource::RESOURCE_NAME             => 'mobile.comment_api',
            FeedResource::RESOURCE_NAME                => 'mobile.feed_api',
            FriendResource::RESOURCE_NAME              => 'mobile.friend_api',
            GroupResource::RESOURCE_NAME               => 'mobile.group_api',
            GroupCategoryResource::RESOURCE_NAME       => 'mobile.group_category_api',
            GroupTypeResource::RESOURCE_NAME           => 'mobile.group_type_api',
            GroupPermissionResource::RESOURCE_NAME     => 'mobile.group_permission_api',
            GroupAdminResource::RESOURCE_NAME          => 'mobile.group_admin_api',
            GroupProfileResource::RESOURCE_NAME        => 'mobile.group_profile_api',
            GroupInfoResource::RESOURCE_NAME           => 'mobile.group_info_api',
            GroupPhotoResource::RESOURCE_NAME          => 'mobile.group_photo_api',
            GroupMemberResource::RESOURCE_NAME         => 'mobile.group_member_api',
            GroupInviteResource::RESOURCE_NAME         => 'mobile.group_invite_api',
            GroupWidgetResource::RESOURCE_NAME         => 'mobile.group_widget_api',
            PageResource::RESOURCE_NAME                => 'mobile.page_api',
            PageCategoryResource::RESOURCE_NAME        => 'mobile.page_category_api',
            PageTypeResource::RESOURCE_NAME            => 'mobile.page_type_api',
            PagePermissionResource::RESOURCE_NAME      => 'mobile.page_permission_api',
            PageAdminResource::RESOURCE_NAME           => 'mobile.page_admin_api',
            PageProfileResource::RESOURCE_NAME         => 'mobile.page_profile_api',
            PageInfoResource::RESOURCE_NAME            => 'mobile.page_info_api',
            PagePhotoResource::RESOURCE_NAME           => 'mobile.page_photo_api',
            PageInviteResource::RESOURCE_NAME          => 'mobile.page_invite_api',
            PageMemberResource::RESOURCE_NAME          => 'mobile.page_member_api',
            PageWidgetResource::RESOURCE_NAME          => 'mobile.page_widget_api',
            MarketplaceResource::RESOURCE_NAME         => 'mobile.marketplace_api',
            MarketplaceCategoryResource::RESOURCE_NAME => 'mobile.marketplace_category_api',
            MarketplacePhotoResource::RESOURCE_NAME    => 'mobile.marketplace_photo_api',
            MarketplaceInviteResource::RESOURCE_NAME   => 'mobile.marketplace_invite_api',
            MarketplaceInvoiceResource::RESOURCE_NAME  => 'mobile.marketplace_invoice_api',
            MusicSongResource::RESOURCE_NAME           => 'mobile.music_song_api',
            MusicAlbumResource::RESOURCE_NAME          => 'mobile.music_album_api',
            MusicPlaylistResource::RESOURCE_NAME       => 'mobile.music_playlist_api',
            MusicGenreResource::RESOURCE_NAME          => 'mobile.music_genre_api',
            PollResource::RESOURCE_NAME                => 'mobile.poll_api',
            PollResultResource::RESOURCE_NAME          => 'mobile.poll_result_api',
            PollAnswerResource::RESOURCE_NAME          => 'mobile.poll_answer_api',
            QuizResource::RESOURCE_NAME                => 'mobile.quiz_api',
            QuizResultResource::RESOURCE_NAME          => 'mobile.quiz_result_api',
            VideoResource::RESOURCE_NAME               => 'mobile.video_api',
            VideoCategoryResource::RESOURCE_NAME       => 'mobile.video_category_api',
            SearchResource::RESOURCE_NAME              => 'mobile.search_api',
            AccountResource::RESOURCE_NAME             => 'mobile.account_api',
            FileResource::RESOURCE_NAME                => 'mobile.file_api',
            LinkResource::RESOURCE_NAME                => 'mobile.link_api',
            AdResource::RESOURCE_NAME                  => 'mobile.ad_api',
            SubscriptionResource::RESOURCE_NAME        => 'mobile.subscribe_api',
            AnnouncementResource::RESOURCE_NAME        => 'mobile.announcement_api',
            CommentStickerResource::RESOURCE_NAME      => 'mobile.comment_sticker',
            CommentStickerSetResource::RESOURCE_NAME   => 'mobile.comment_sticker',
            ActivityPointResource::RESOURCE_NAME       => 'mobile.activitypoint_api',
            'v'                                        => 'mobile.video_api',
            'music'                                    => 'mobile.music_song_api',
            'core'                                     => 'mobile.core_api'
        ];

        $this->objectResources = [
            'v' => VideoResource::class,
            'music' => MusicSongResource::class,
        ];

        $this->noModulesList = ['menu', 'account', 'ping', 'intl', 'message', 'auth', 'file'];
        $this->specialModules = [
            'video' => 'v',
            'page'  => 'pages',
            'group' => 'groups'
        ];

        $this->moduleIsApps = [
            'ad'        => 'Core_BetterAds',
            'subscribe' => 'Core_Subscriptions'
        ];
        // 3th party API resource registration
        (($sPlugin = Phpfox_Plugin::get('mobile_api_routing_registration')) ? eval($sPlugin) : false);

        $this->setCache(Phpfox::getLib("cache"));

    }

    /**
     * @param $resourceName
     *
     * @return ResourceInterface|AbstractResourceApi|mixed
     * @throws \Exception
     */
    public function getApiServiceByResourceName($resourceName)
    {
        // Video type support
        if ($resourceName === "v") {
            return Phpfox::getService('mobile.video_api');
        }
        if (!isset($this->resourceNames[$resourceName])) {
            throw new \Exception("Cannot find resource name: " . $resourceName);
        }
        return Phpfox::getService($this->resourceNames[$resourceName]);
    }

    /**
     * @param $resourceName
     * @param $resourceId
     *
     * @param string $apiVersion
     * @return ResourceBase|boolean
     */
    public function getResourceByResourceName($resourceName, $resourceId, $apiVersion = 'mobile')
    {
        if (!$this->hasApiResourceService($resourceName)) {
            return null;
        }
        /** @var AbstractResourceApi $service */
        $service = (new ApiVersionResolver())->getApiServiceWithVersion($resourceName, [
            'api_version_name' => $apiVersion
        ]);
        /** @var ResourceBase $item */
        $item = $service->loadResourceById($resourceId, true);
        if (!($item instanceof ResourceBase)) {
            return null;
        }
        return $item;

    }

    /**
     * @param $resourceName
     * @param $resourceId
     * @param string $permission
     * @param string $apiVersion
     * @return bool|null
     */
    public function getPermissionByResourceName($resourceName, $resourceId, $permission = AccessControl::VIEW, $apiVersion = 'mobile')
    {
        $item = $this->getResourceByResourceName($resourceName, $resourceId, $apiVersion);
        if ($item === null) {
            return null;
        }
        return Phpfox::getService($this->resourceNames[$resourceName])->getAccessControl()->isGranted($permission, $item);
    }

    /**
     * Check if exist resource API
     *
     * @param $resourceName
     *
     * @return bool
     */
    public function hasApiResourceService($resourceName)
    {
        return (isset($this->resourceNames[$resourceName]) || ($resourceName === 'v'));
    }

    public static function instance()
    {
        if (!self::$singleton) {
            self::$singleton = new self();
        }
        return self::$singleton;
    }

    public function getApiNames()
    {
        return $this->apiNames;
    }

    public function generateEndpointUrls($prefix)
    {
        $cacheId = $this->getCache()->set("mobile_api_endpoint_urls");
        if (false && (defined("PHPFOX_DEBUG") && PHPFOX_DEBUG == true) || !($routesCached = $this->getCache()->get($cacheId))) {
            $combined = [];
            foreach ($this->apiNames as $service => $apiName) {
                $instance = new $apiName();
                if (method_exists($instance, $method = '__naming')) {
                    $combined[$service] = $instance->{$method}();
                }
            }

            $routes = [];

            foreach ($combined as $service => $combine) {
                if (!$combine) {
                    continue;
                }
                foreach ($combine as $path => $config) {
                    if (isset($config['maps'])) {
                        $routes["$prefix/$path"] = array_merge(['api_service' => $service], $config);
                    } else {
                        $routes["$prefix/$path"] = [
                            'api_service' => $service,
                            'maps'        => $config,
                        ];
                    }

                }
            }

            foreach ($this->resourceNames as $resourceName => $api) {
                $routing = new ResourceRoute($resourceName, $api);

                $routeMaps = $routing->getRouteMap();
                foreach ($routeMaps as $route => $config) {
                    $path = (empty($prefix) ? $route : $prefix . "/" . $route);
                    $routes[$path] = $config;
                }
            }

            $this->getCache()->save($cacheId, $routes);
            return $routes;
        }

        return $routesCached;
    }

    /**
     * @param $prefix
     *
     * @return array|bool
     * @throws \Exception
     */
    public function generateRestfulRoute($prefix)
    {
        $checkWithPrefix = $prefix;
        $prefix = ':api_version_name';

        $cacheId = $this->getCache()->set("mobile_api_routing");

        //Reset main log
        if (defined("PHPFOX_MOBILE_CLEAR_LOG") && PHPFOX_MOBILE_CLEAR_LOG == true) {
            $fp = fopen(PHPFOX_DIR . 'file/log/main.log', 'w');
            fwrite($fp, '');
            fclose($fp);
        }
        defined('PHPFOX_IS_AJAX') or define('PHPFOX_IS_AJAX', true);

        if ((defined("PHPFOX_DEBUG") && PHPFOX_DEBUG == true) || !($routesCached = $this->getCache()->get($cacheId))) {
            $combined = [];
            foreach ($this->apiNames as $service => $apiName) {
                $realName = str_replace('mobile.', '', $service);
                $moduleId = explode('_', $realName);
                if (isset($moduleId[0]) && !in_array($moduleId[0], $this->noModulesList)
                    && !Phpfox::isModule(isset($this->specialModules[$moduleId[0]]) ? $this->specialModules[$moduleId[0]] : $moduleId[0])) {
                    continue;
                }
                if (isset($moduleId[0]) && isset($this->moduleIsApps[$moduleId[0]]) && !Phpfox::isAppActive($this->moduleIsApps[$moduleId[0]])) {
                    continue;
                }
                $instance = new $apiName();
                if (method_exists($instance, $method = '__naming')) {
                    $combined[$service] = $instance->{$method}();
                }
            }

            $routes = [];

            foreach ($combined as $service => $combine) {
                if (!$combine) {
                    continue;
                }
                foreach ($combine as $path => $config) {
                    if (isset($config['maps'])) {
                        $routes["$prefix/$path"] = array_merge([
                            'api_service' => $service,
                        ], $config);
                    } else {
                        $routes["$prefix/$path"] = [
                            'api_service' => $service,
                            'maps'        => $config,
                        ];
                    }

                }
            }

            foreach ($this->resourceNames as $resourceName => $api) {
                $realName = str_replace('mobile.', '', $api);
                $moduleId = explode('_', $realName);
                if (isset($moduleId[0]) && !in_array($moduleId[0], $this->noModulesList)
                    && !Phpfox::isModule(isset($this->specialModules[$moduleId[0]]) ? $this->specialModules[$moduleId[0]] : $moduleId[0])) {
                    continue;
                }
                if (isset($moduleId[0]) && isset($this->moduleIsApps[$moduleId[0]]) && !Phpfox::isAppActive($this->moduleIsApps[$moduleId[0]])) {
                    continue;
                }
                $routing = new ResourceRoute($resourceName, $api);

                $routeMaps = $routing->getRouteMap();
                foreach ($routeMaps as $route => $config) {
                    $path = (empty($prefix) ? $route : $prefix . "/" . $route);
                    $routes[$path] = $config;
                }
            }

            // Add wildcat routes for fallback
            $routes["{$prefix}/*"] = [
                "api_service" => "mobile.core_api",
                "maps"        => [
                    "get"    => "fallbackCall",
                    "post"   => "fallbackCall",
                    "put"    => "fallbackCall",
                    "delete" => "fallbackCall"
                ]
            ];

            // Support Api Version Name: mobile, v1, v1.3 ...
            $API_VERSION_REGEXP = '^' . $checkWithPrefix . '|(v[0-9\.]+)';
            foreach ($routes as $key => $config) {
                if (!isset($routes[$key]['where'])) {
                    $routes[$key]['where'] = [];
                }
                $routes[$key]['where']['api_version_name'] = $API_VERSION_REGEXP;

                if ($routes[$key]['api_service']) {
                    $routes[$key]['actual_api_service'] = $routes[$key]['api_service'];
                    $routes[$key]['api_service'] = 'mobile.api_version_resolver';
                }
            }
            $this->getCache()->save($cacheId, $routes);
            return $routes;
        }

        return $routesCached;

    }

    public function getRoutingTable($prefix = 'mobile')
    {
        $route = $this->generateRestfulRoute($prefix);

        $table = [];
        foreach ($route as $map => $config) {
            $methods = [];
            foreach ($config['maps'] as $methodName => $methodMap) {
                $methods[] = [
                    'method' => $methodName,
                    'auth'   => (isset($config['auth']) && $config['auth'] == false ? false : true)
                ];
            }
            $table [$map] = $methods;
        }
        ksort($table);
        return $table;
    }

    /**
     * @return \Phpfox_Cache_Interface
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @param \Phpfox_Cache_Interface $cache
     */
    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return array
     *
     * @param $bActive
     */
    public function getResourceNames($bActive = false)
    {
        $resourceNames = $this->resourceNames;
        if ($bActive) {
            foreach ($resourceNames as $service => $apiName) {
                $realName = str_replace('mobile.', '', $apiName);
                $moduleId = explode('_', $realName);
                if (isset($moduleId[0]) && !in_array($moduleId[0], $this->noModulesList)
                    && !Phpfox::isModule(isset($this->specialModules[$moduleId[0]]) ? $this->specialModules[$moduleId[0]] : $moduleId[0])
                ) {
                    unset($resourceNames[$service]);
                }
                if (isset($moduleId[0]) && isset($this->moduleIsApps[$moduleId[0]]) && !Phpfox::isAppActive($this->moduleIsApps[$moduleId[0]])) {
                    unset($resourceNames[$service]);
                }
            }
        }
        return $resourceNames;
    }

    /**
     * @return mixed
     */
    public function getSupportModules()
    {
        if (!$this->supportModules) {
            $this->setSupportModules($this->apiNames);
        }
        return $this->supportModules;
    }

    /**
     * @param mixed $supportModules
     */
    private function setSupportModules($supportModules)
    {
        $supported = [];
        foreach ($supportModules as $api => $resource) {
            $realName = str_replace('mobile.', '', $api);
            $moduleId = explode('_', $realName);
            if (empty($moduleId)) {
                continue;
            }
            $moduleName = isset($this->specialModules[$moduleId[0]]) ? $this->specialModules[$moduleId[0]] : $moduleId[0];
            if (isset($moduleName) && !in_array($moduleName, $this->noModulesList)
                && !Phpfox::isModule($moduleName)
            ) {
                continue;
            }

            if (isset($this->moduleIsApps[$moduleName]) && !Phpfox::isAppActive($this->moduleIsApps[$moduleName])) {
                continue;
            }

            if (in_array($moduleName, $supported)) {
                continue;
            }
            $supported[] = $moduleName;
        }
        //Merge some default modules
        $supported = array_merge($supported, ['custom', 'poke', 'track', 'profile', 'link', 'report', 'notification', 'rss']);
        $this->supportModules = $supported;
    }

    /**
     * @return array
     */
    public function getObjectResources()
    {
        return $this->objectResources;
    }

}