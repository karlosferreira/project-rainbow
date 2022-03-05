<?php

namespace Apps\Core_MobileApi\Api\Security\User;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Phpfox;


class UserAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const ADD_FRIEND = "add_friend";
    const NOTIFICATION_PRIVACY = 'control_notification_privacy';
    const PROFILE_PRIVACY = 'control_profile_privacy';
    const USE_GLOBAL_SEARCH = 'use_global_search';
    const USE_INVISIBLE_MODE = 'use_invisible_mode';
    const BLOCK = 'block';
    //Privacy setting
    const VIEW_PROFILE = "view_profile";
    const VIEW_WALL = "view_wall";
    const VIEW_FRIEND = "view_friend";
    const VIEW_PHOTO = "view_photo";
    const VIEW_PROFILE_INFO = "view_profile_info";
    const VIEW_BASIC_INFO = "view_basic_info";
    const SHARE_ON_WALL = "share_on_wall";
    const VIEW_LOCATION = "view_location";
    const VIEW_REMOVE_FRIEND_LINK = "view_remove_friend_link";
    const CHANGE_COVER = "change_cover";
    const REMOVE_COVER = "remove_cover";
    const POKE = 'poke';

    const FEATURE = 'feature';

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD,
            self::EDIT,
            self::PROFILE_PRIVACY,
            self::NOTIFICATION_PRIVACY,
            self::USE_GLOBAL_SEARCH,
            self::USE_INVISIBLE_MODE,
            self::ADD_FRIEND,
            self::VIEW_PROFILE,
            self::VIEW_WALL,
            self::VIEW_FRIEND,
            self::VIEW_PHOTO,
            self::VIEW_PROFILE_INFO,
            self::SHARE_ON_WALL,
            self::VIEW_LOCATION,
            self::VIEW_BASIC_INFO,
            self::BLOCK,
            self::VIEW_REMOVE_FRIEND_LINK,
            self::CHANGE_COVER,
            self::REMOVE_COVER,
            self::POKE,
            self::FEATURE
        ]);
    }


    /**
     * @param              $permission
     * @param ResourceBase|UserResource $resource
     *
     * @return bool|mixed
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if ($permission == self::IS_AUTHENTICATED || $permission == self::SYSTEM_ADMIN) {
            return parent::isGranted($permission);
        }
        if (!parent::isGranted($permission, $resource)) {
            return false;
        }
        if ($resource) {
            $isBlocked = Phpfox::getService('user.block')->isBlocked(null, $resource->getId());
        } else {
            $isBlocked = false;
        }
        $granted = false;
        switch ($permission) {
            case self::ADD:
                $granted = $this->setting->getAppSetting('user.allow_user_registration');
                break;
            case self::EDIT:
                $granted = $resource && ($this->getUserContext()->getId() > 0
                        && $this->getUserContext()->compareWith($resource)) || $this->isGrantedSetting('user.can_edit_users');
                break;

            // Case browse users, search user
            case self::VIEW:
                $granted = !$isBlocked && $this->isGrantedSetting('user.can_browse_users_in_public');
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('admincp.has_admin_access')
                    && $this->service()->isAdminUser($resource->getId()));
                break;
            case self::NOTIFICATION_PRIVACY:
                $granted = (parent::isGranted(self::IS_AUTHENTICATED)
                    && $this->isGrantedSetting('user.can_control_notification_privacy'));
                break;
            case self::PROFILE_PRIVACY:
                $granted = (parent::isGranted(self::IS_AUTHENTICATED)
                    && $this->isGrantedSetting('user.can_control_profile_privacy'));
                break;
            case self::ADD_FRIEND:
                $granted = !$isBlocked && $this->isGrantedSetting('friend.can_add_friends')
                    && (!$resource || $this->privacy()->hasAccess($resource->getId(), 'friend.send_request'));
                break;
            case self::REPORT:
                $granted = $resource && $this->userContext->getId() > 0 && !$this->userContext->compareWith($resource);
                break;
            case self::USE_GLOBAL_SEARCH:
                $granted = $this->isGrantedSetting('search.can_use_global_search');
                break;
            case self::USE_INVISIBLE_MODE:
                $granted = $this->isGrantedSetting('user.hide_from_browse');
                break;
            //Privacy setting
            case self::VIEW_PROFILE:
                /** @var UserResource $resource */
                $granted = !(Phpfox::isModule('friend') && $resource && $this->getUserContext()->getId() != $resource->getId() && !$this->isGrantedSetting('user.can_override_user_privacy') && Phpfox::getParam('friend.friends_only_profile') && $resource->getFriendship() != UserResource::FRIENDSHIP_IS_FRIEND) && ($this->isGrantedSetting('profile.can_view_users_profile')
                        && $this->privacy()->hasAccess($resource->getId(), 'profile.view_profile'));
                break;
            case self::VIEW_WALL:
                $granted = Phpfox::isModule('feed') && $this->privacy()->hasAccess($resource->getId(), 'feed.view_wall');
                break;
            case self::VIEW_FRIEND:
                $granted = Phpfox::isModule('friend') && $this->privacy()->hasAccess($resource->getId(), 'friend.view_friend');
                break;
            case self::VIEW_PHOTO:
                $granted = Phpfox::isAppActive('Core_Photos') && $this->privacy()->hasAccess($resource->getId(), 'photo.display_on_profile');
                break;
            case self::VIEW_PROFILE_INFO:
                $granted = $this->privacy()->hasAccess($resource->getId(), 'profile.profile_info') && $this->isGranted(self::VIEW_PROFILE, $resource);
                break;
            case self::SHARE_ON_WALL:
                $granted = Phpfox::isModule('feed') && $this->privacy()->hasAccess($resource->getId(), 'feed.share_on_wall');
                break;
            case self::VIEW_LOCATION:
                $granted = $this->privacy()->hasAccess($resource->getId(), 'profile.view_location');
                break;
            case self::VIEW_BASIC_INFO:
                $granted = $this->privacy()->hasAccess($resource->getId(), 'profile.basic_info');
                break;
            case self::BLOCK:
                /** @var UserResource $resource */
                $granted = (!$resource || Phpfox::getUserGroupParam($resource->getGroupId(), 'user.can_be_blocked_by_others')) && $this->isGrantedSetting('user.can_block_other_members');
                break;
            case self::VIEW_REMOVE_FRIEND_LINK:
                $granted = $this->isGrantedSetting('friend.link_to_remove_friend_on_profile');
                break;
            case self::CHANGE_COVER:
                $granted = $this->isGrantedSetting('profile.can_change_cover_photo');
                break;
            case self::REMOVE_COVER:
                /** @var $resource UserResource $granted */
                $granted = $resource && $resource->getId() == $this->getUserContext()->getId() && $resource->getCover(false);
                break;
            case self::POKE:
                $granted = $resource && Phpfox::isAppActive('Core_Poke') && PhpFox::getService('poke')->canSendPoke($resource->getId()) && $this->privacy()->hasAccess($resource->getId(), 'poke.can_send_poke');
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('user.can_feature');
                break;
        }

        return $granted;
    }

    /**
     * @return \User_Service_Privacy_Privacy
     */
    protected function privacy()
    {
        return Phpfox::getService('user.privacy');
    }

    /**
     * @return \User_Service_User
     */
    protected function service()
    {
        return Phpfox::getService('user');
    }
}