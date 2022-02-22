<?php

namespace Apps\Core_MobileApi\Api\Security\Photo;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\PhotoAlbumResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Phpfox;


class PhotoAlbumAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const UPLOAD = "upload";
    const FEATURE = "feature";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = 'purchase_sponsor';
    const SEARCH = 'search';

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::EDIT, self::COMMENT, self::UPLOAD,
            self::FEATURE, self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR, self::SEARCH
        ]);
    }

    /**
     * @inheritdoc
     * @param $resource PhotoAlbumResource
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
                $granted = $this->isGrantedSetting('photo.can_view_photos') && $this->isGrantedSetting('photo.can_view_photo_albums');
                break;
            case self::ADD:
                $limitation = $this->setting->getUserSetting('photo.max_number_of_albums');
                $albumTotal = (int)Phpfox::getService('photo.album')->getMyAlbumTotal();
                $granted = ($limitation === '' || (int)$limitation > $albumTotal) && $this->isGrantedSetting('photo.can_create_photo_album');
                break;
            case self::EDIT:
                $granted = ($this->isGrantedSetting('photo.can_edit_other_photo_albums')
                    || ($this->isGrantedSetting('photo.can_edit_own_photo_album') && $isOwner));
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('photo.can_delete_other_photo_albums')
                    || ($this->isGrantedSetting('photo.can_delete_own_photo_album') && $isOwner) || ($this->appContext && $this->appContext->isAdmin($this->userContext->getId())));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('photo.can_delete_own_photo_album');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['photo.can_view_photos', 'photo.can_post_on_albums']);
                break;
            case self::UPLOAD:
                /** @var PhotoAlbumResource $resource */
                $granted = $resource && $isOwner && !$resource->profile_id && !$resource->cover_id && !$resource->timeline_id;
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting('photo.can_sponsor_album') && Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = Phpfox::isAppActive('Core_BetterAds') && $this->isGrantedSetting('photo.can_purchase_sponsor_album') && !$this->isGrantedSetting('photo.can_sponsor_album') && $isOwner && $resource && $resource->getCanPurchaseSponsor();
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('photo.can_feature_photo_album');
                break;
            case self::SEARCH:
                $granted = $this->isGrantedSetting('photo.can_search_for_photos');
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