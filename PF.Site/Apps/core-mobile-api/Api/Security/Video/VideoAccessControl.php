<?php

namespace Apps\Core_MobileApi\Api\Security\Video;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class VideoAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";

    const FEATURE = "feature";
    const APPROVE = "approve";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = "purchase_sponsor";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::EDIT, self::COMMENT,
            self::FEATURE, self::APPROVE, self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR
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
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('pf_video_view');
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('pf_video_share');
                break;
            case self::EDIT:
                $granted = ($this->isGrantedSetting('pf_video_edit_all_video')
                    || ($this->isGrantedSetting('pf_video_edit_own_video') && $isOwner));
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('pf_video_delete_all_video')
                        || ($this->isGrantedSetting('pf_video_delete_own_video') && $isOwner)) || ($this->appContext && $this->appContext->isAdmin($this->userContext->getId()));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('pf_video_delete_own_video');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['pf_video_view', 'pf_video_comment']) && (!$resource || !$resource->getIsPending());
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting('can_sponsor_v') && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = $this->isGrantedSetting('v.can_purchase_sponsor') && $isOwner && !$this->isGrantedSetting('can_sponsor_v') && \Phpfox::isAppActive('Core_BetterAds') && $resource && $resource->getCanPurchaseSponsor();
                break;
            case self::SPONSOR_IN_FEED:
                $granted = $resource && $resource->getCanSponsorInFeed() && (($isOwner && $this->isGrantedSetting('feed.can_purchase_sponsor')) || $this->isGrantedSetting('feed.can_sponsor_feed'));
                break;
            case self::APPROVE:
                $granted = $this->isGrantedSetting('pf_video_approve') && (!$resource || $resource->getIsPending());
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('pf_video_feature');
                break;
        }

        // Check Pages/Group permission
        if ($granted && $this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('pf_video.view_browse_videos');
                    break;
                case self::ADD:
                    $granted = ($this->appContext->hasPermission('pf_video.view_browse_videos')
                        && $this->appContext->hasPermission('pf_video.share_videos'));
                    break;
            }
        }

        return $granted;
    }

}