<?php

namespace Apps\Core_MobileApi\Api\Security;


use Apps\Core_MobileApi\Adapter\Localization\LocalizationInterface;
use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Form\Type\PrivacyType;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Phpfox;

class AccessControl
{
    // Standard permissions
    const VIEW = 'view';
    const DELETE = "delete";
    const DELETE_OWN = "delete_own";
    const LIKE = "like";
    const SHARE = "share";
    const REPORT = "report";
    const COMMENT = "comment";

    // Check User Context has Admin Access
    const SYSTEM_ADMIN = "system_admin";

    // Check User Context has logged in to system. Ban account should not allowed
    const IS_AUTHENTICATED = "authenticated";

    const EVERY_ONE = 0;
    const FRIENDS_ONLY = 1;
    const FRIENDS_OF_FRIENDS = 2;
    const ONLY_ME = 3;
    const FRIEND_LIST = 4;
    const COMMUNITY = 6;

    protected $supports = [];
    protected $setting;

    protected $parameters = [];

    /**
     * @var UserInterface
     */
    protected $userContext;

    /**
     * @var PermissionInterface|AppContextInterface
     */
    protected $appContext;

    /**
     * @var String
     */
    protected $errorMessage;

    /**
     * @var LocalizationInterface
     */
    protected $localization;

    protected $blacklist = [
        'system_admin'                 => 1,
        'authenticated'                => 1,
        'control_profile_privacy'      => 1,
        'control_notification_privacy' => 1,
        'use_global_search'            => 1,
        'use_invisible_mode'           => 1
    ];

    public function __construct(SettingInterface $setting, UserInterface $userContext)
    {
        $this->setting = $setting;
        $this->userContext = $userContext;
        $this->supports = [self::VIEW, self::SYSTEM_ADMIN, self::IS_AUTHENTICATED, self::LIKE, self::SHARE, self::DELETE, self::DELETE_OWN, self::REPORT];
    }

    /**
     * Check permission is supported or not
     *
     * @param $permission
     *
     * @return bool
     */
    public function isSupports($permission)
    {
        if (!in_array($permission, $this->supports)) {
            return false;
        }
        return true;
    }

    /**
     * Check if permission is granted. Override this function for each Object Type
     *
     * @param                   $permission
     * @param ResourceBase|null $resource requesting Object
     *
     * @return bool
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if ($this->isSupports($permission) == false) {
            return false;
        }
        if ($permission == self::REPORT
            && $resource instanceof ResourceBase
            && isset($resource->user) && $this->userContext->compareWith($resource->getAuthor())) {
            //Owner cannot report
            return false;
        }
        if ($permission == self::SYSTEM_ADMIN) {
            return $this->isGrantedSetting('admincp.has_admin_access');
        }

        // Base on item to determine app context
        if ($resource instanceof ResourceBase
            && property_exists($resource, "module_id") && property_exists($resource, "item_id")) {
            $moduleId = method_exists($resource, 'getModuleId') ? $resource->getModuleId() : $resource->module_id;
            $itemId = method_exists($resource, 'getItemId') ? $resource->getItemId() : $resource->item_id;
            if (!empty($moduleId) && !empty($itemId)) {
                $this->setAppContext(AppContextFactory::create($moduleId, $itemId));
            }
        }

        // Check Item Privacy. In case of Pages and Groups Module, AppContext must implement
        if ($permission == self::VIEW
            && $this->appContext == null
            && $resource instanceof ResourceBase
            && isset($resource->user)
            && $this->userContext instanceof UserInterface) {
            if (!$this->isPrivacyAllowed($resource, $this->userContext)) {
                return false;
            }
        }

        if ($permission == self::IS_AUTHENTICATED) {
            if ($this->getUserContext()->getId() == 0 || !$this->isGrantedSetting('user.can_stay_logged_in')) {
                return false;
            }
        }

        if ($permission == self::LIKE) {
            return Phpfox::isModule('feed') && Phpfox::isModule('like') && (!$resource || !$resource->getIsPending());
        }

        if ($permission == self::COMMENT) {
            return Phpfox::isModule('feed') && Phpfox::isModule('comment');
        }

        if ($permission == self::SHARE) {
            return Phpfox::isModule('feed') && Phpfox::isModule('share') && $this->isGrantedSetting('share.can_share_items') && (!$resource || (!$resource->getIsPending() && $resource->getPrivacy() == PrivacyType::EVERYONE));
        }

        if ($permission == self::REPORT) {
            return Phpfox::isModule('report');
        }

        return true;
    }

    /**
     * Checking permission base on resource privacy
     *
     * @param ResourceBase  $resource the resource which privacy control
     * @param UserInterface $user     the viewer
     *
     * @return bool
     */
    public function isPrivacyAllowed(ResourceBase $resource, UserInterface $user)
    {
        if ($resource instanceof UserResource) {
            return true;
        }
        if ($this->isGrantedSetting('privacy.can_view_all_items')) {
            return true;
        }

        $author = $resource->getAuthor();
        if (is_array($author) || $user->compareWith($author)) {
            return true; // This is owner of the user
        }

        if ($this->isBlocked($author)) {
            return false;
        }

        $privacy = $resource->getAccessPrivacy();
        $isFriendOnlyCommunity = $this->setting->getAppSetting('core.friends_only_community');

        $allowed = false;
        switch ($privacy) {
            case self::EVERY_ONE:
                $allowed = (!$isFriendOnlyCommunity || $this->isFriendWith($user, $author));
                break;
            case self::FRIENDS_ONLY:
                $allowed = $this->isFriendWith($user, $author);
                break;
            case self::FRIENDS_OF_FRIENDS:
                $allowed = $this->isFriendWith($user, $author) || $this->isFriendOfFriend($user, $author);
                break;
            case self::ONLY_ME:
                $allowed = false;
                break;
            case self::FRIEND_LIST:
                $allowed = $this->isInFriendList($resource->getResourceName(), $resource->getId(), $user);
                break;
            case self::COMMUNITY:
                $allowed = $user->getId() > 0 && (!Phpfox::getParam('core.friends_only_community') || $this->isFriendWith($user, $author));
                break;
        }

        return $allowed;
    }

    public function isInFriendList($module, $itemId, UserInterface $user)
    {
        $canView = true;
        if ($user->getRawId()) {
            $iCheck = (int)db()->select('COUNT(privacy_id)')
                ->from(':privacy', 'p')
                ->join(':friend_list_data', 'fld', 'fld.list_id = p.friend_list_id AND fld.friend_user_id = ' . $user->getRawId())
                ->where([
                    'p.module_id' => $module,
                    'p.item_id'   => $itemId
                ])->execute('getSlaveField');
            if ($iCheck === 0) {
                $canView = false;
            }
        } else {
            $canView = false;
        }
        return $canView;
    }

    public function isBlocked(UserInterface $owner)
    {
        return Phpfox::getService('user.block')->isBlocked(null, $owner->getRawId());
    }

    public function isFriendWith(UserInterface $viewer, UserInterface $owner)
    {
        return Phpfox::isModule('friend') ? Phpfox::getService('friend')->isFriend($owner->getRawId(), $viewer->getRawId()) : false;
    }

    public function isFriendOfFriend(UserInterface $viewer, UserInterface $owner)
    {
        return Phpfox::isModule('friend') ? Phpfox::getService('friend')->isFriendOfFriend($owner->getRawId()) : false;
    }

    /**
     * Check granted User Group setting (This is Phpfox User Group SettingService)
     *
     * @param array|string $settings List of setting key to check
     *
     * @return bool
     */
    public function isGrantedSetting($settings)
    {
        if (is_string($settings)) {
            $settings = [$settings];
        }
        foreach ($settings as $setting) {
            if (!$this->setting->getUserSetting($setting)) {
                return false;
            }
        }
        return true;
    }

    /**
     * @return UserInterface
     */
    public function getUserContext()
    {
        return $this->userContext;
    }

    /**
     * @param mixed $appContext
     *
     * @return AccessControl
     */
    public function setAppContext($appContext)
    {
        $this->appContext = $appContext;
        return $this;
    }

    /**
     * Get all permissions
     *
     * @param ResourceBase|null $resource
     *
     * @return array
     */
    public function getPermissions(ResourceBase $resource = null)
    {
        $permissions = [];
        foreach ($this->supports as $support) {
            if (isset($this->blacklist[$support])) continue;
            $permissions["can_" . $support] = $this->isGranted($support, $resource);
        }
        return $permissions;
    }

    /**
     * @return mixed
     */
    public function getParameters()
    {
        return $this->parameters;
    }

    /**
     * @param mixed $parameters
     */
    public function setParameters($parameters)
    {
        $this->parameters = $parameters;
    }

    /**
     * @param array $permissions
     *
     * @return array
     */
    public function mergePermissions($permissions = [])
    {
        return array_unique(array_merge($this->supports, $permissions));
    }

    /**
     * @param $message
     * @return void
     */
    public function setErrorMessage($message)
    {
        $this->errorMessage = $message;
    }

    /**
     * @return String
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @return LocalizationInterface|object
     */
    protected function getLocalization()
    {
        if (!$this->localization) {
            $this->localization = Phpfox::getService(LocalizationInterface::class);
        }
        return $this->localization;
    }
}