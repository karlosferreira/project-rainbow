<?php

namespace Apps\Core_MobileApi\Api\Security\Music;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\MusicAlbumResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Phpfox;


class MusicAlbumAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const UPLOAD = "upload";

    const FEATURE = "feature";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = "purchase_sponsor";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::EDIT, self::COMMENT,
            self::FEATURE, self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR, self::UPLOAD
        ]);
    }

    /**
     * @inheritdoc
     *
     * @param $resource EventResource
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
        /** @var $resource MusicAlbumResource */
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('music.can_access_music');
                break;
            case self::ADD:
                if (method_exists('\Apps\Core_Music\Service\Album\Album', 'canCreateNewAlbum')) {
                    $granted = Phpfox::getService('music.album')->canCreateNewAlbum(null, false);
                } else {
                    $granted = $this->isGrantedSetting('music.can_add_music_album');
                }
                break;
            case self::EDIT:
                $granted = ($this->isGrantedSetting('music.can_edit_other_music_albums')
                    || ($this->isGrantedSetting('music.can_edit_own_albums') && $isOwner));
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('music.can_delete_other_music_albums')
                    || ($this->isGrantedSetting('music.can_delete_own_music_album') && $isOwner) || ($this->appContext && $this->appContext->isAdmin($this->userContext->getId())));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('music.can_delete_own_music_album');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['music.can_add_comment_on_music_album', 'music.can_access_music']) && (!$resource || !$resource->getIsPending());
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting('music.can_sponsor_album') && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = \Phpfox::isAppActive('Core_BetterAds') && !$this->isGrantedSetting('music.can_sponsor_album') && $this->isGrantedSetting('music.can_purchase_sponsor_album') && $isOwner && $resource && $resource->getCanPurchaseSponsor();
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('music.can_feature_music_albums');
                break;
        }

        // Check Pages/Group permission
        if ($granted && $this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('music.view_browse_music');
                    break;
                case self::ADD:
                    $granted = ($this->appContext->hasPermission('music.share_music')
                        && $this->appContext->hasPermission('music.view_browse_music'));
                    break;
            }
        }
        return $granted;
    }

}