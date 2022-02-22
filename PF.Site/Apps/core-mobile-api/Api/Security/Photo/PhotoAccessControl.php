<?php

namespace Apps\Core_MobileApi\Api\Security\Photo;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\PhotoResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class PhotoAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";

    const FEATURE = "feature";
    const APPROVE = "approve";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = 'purchase_sponsor';
    const DOWNLOAD = "download";
    const SEARCH = 'search';
    const SET_ALBUM_COVER = "set_album_cover";
    const SET_PROFILE_AVATAR = "set_profile_avatar";
    const SET_PROFILE_COVER = "set_profile_cover";
    const SET_PARENT_COVER = "set_parent_cover";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::EDIT, self::COMMENT, self::SEARCH,
            self::FEATURE, self::APPROVE, self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR, self::DOWNLOAD,
            self::SET_ALBUM_COVER, self::SET_PROFILE_AVATAR, self::SET_PROFILE_COVER, self::SET_PARENT_COVER
        ]);
    }

    /**
     * @inheritdoc
     *
     * @param $resource PhotoResource
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (in_array($permission, [self::IS_AUTHENTICATED, self::SYSTEM_ADMIN])) {
            return parent::isGranted($permission);
        }
        if (in_array($permission, [self::LIKE, self::SHARE, self::REPORT])) {
            return parent::isGranted($permission, $resource);
        }
        if (!parent::isGranted($permission, $resource)) {
            return false;
        }
        $isOwner = false;
        // Item Owner always able to do any permission
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }

        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('photo.can_view_photos');
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('photo.can_upload_photos');
                break;
            case self::EDIT:
                $granted = ($this->isGrantedSetting('photo.can_edit_other_photo')
                    || ($this->isGrantedSetting('photo.can_edit_own_photo') && $isOwner));
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('photo.can_delete_other_photos')
                    || ($this->isGrantedSetting('photo.can_delete_own_photo') && $isOwner) || ($this->appContext && $this->appContext->isAdmin($this->userContext->getId())));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('photo.can_delete_own_photo');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['photo.can_view_photos', 'photo.can_post_on_photos']) && (!$resource || !$resource->getIsPending());
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting('photo.can_sponsor_photo') && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = $this->isGrantedSetting('photo.can_purchase_sponsor') && !$this->isGrantedSetting('photo.can_sponsor_photo') && $resource && \Phpfox::isAppActive('Core_BetterAds') && $resource->getCanPurchaseSponsor();
                break;
            case self::SPONSOR_IN_FEED:
                $granted = $resource && $resource->getCanSponsorInFeed() && (($isOwner && $this->isGrantedSetting('feed.can_purchase_sponsor')) || $this->isGrantedSetting('feed.can_sponsor_feed'));
                break;
            case self::APPROVE:
                $granted = $this->isGrantedSetting('photo.can_approve_photos') && (!$resource || $resource->getIsPending());
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('photo.can_feature_photo');
                break;
            case self::DOWNLOAD:
                $granted = $this->isGrantedSetting('photo.can_download_user_photos') && $resource && $resource->allow_download;
                break;
            case self::SEARCH:
                $granted = $this->isGrantedSetting('photo.can_search_for_photos');
                break;
            case self::SET_ALBUM_COVER:
                /** @var $resource PhotoResource * */
                $granted = $resource && $resource->album_id && !$resource->is_cover && ($this->isGrantedSetting('photo.can_edit_other_photo_albums') || ($this->isGrantedSetting('photo.can_edit_own_photo_album') && $isOwner));
                break;
            case self::SET_PROFILE_AVATAR:
                $iAvatarId = storage()->get('user/avatar/' . $this->userContext->getId());
                /** @var $resource PhotoResource * */
                $granted = $resource && $isOwner && $resource->id != $iAvatarId;
                break;
            case self::SET_PROFILE_COVER:
                $iCoverId = storage()->get('user/cover/' . $this->userContext->getId());
                /** @var $resource PhotoResource * */
                $granted = $resource && $isOwner && $resource->id != $iCoverId;
                break;
            case self::SET_PARENT_COVER:
                $granted = $resource && $resource->group_id > 0 && (
                        ($resource->module_id == 'pages' && \Phpfox::isAppActive('Core_Pages') && $this->isGrantedSetting('pages.can_add_cover_photo_pages') &&
                            (\Phpfox::getService('pages.facade')->getItems()->isAdmin($resource->group_id) || \Phpfox::isAdmin() || $this->isGrantedSetting('pages.can_edit_all_pages'))
                        )
                        ||
                        ($resource->module_id == 'groups' && \Phpfox::isAppActive('PHPfox_Groups') && $this->isGrantedSetting('groups.pf_group_add_cover_photo') &&
                            (\Phpfox::getService('groups.facade')->getItems()->isAdmin($resource->group_id) || \Phpfox::isAdmin() || $this->isGrantedSetting('groups.can_edit_all_pages'))
                        )
                    );
                break;
        }

        // Check Pages/Group permission
        if ($granted && $this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('photo.view_browse_photos');
                    break;
                case self::ADD:
                    $granted = ($this->appContext->hasPermission('photo.share_photos')
                        && $this->appContext->hasPermission('photo.can_view_photos'));
                    break;
            }
        }

        return $granted;
    }

}