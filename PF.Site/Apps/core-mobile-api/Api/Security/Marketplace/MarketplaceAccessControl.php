<?php

namespace Apps\Core_MobileApi\Api\Security\Marketplace;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class MarketplaceAccessControl extends AccessControl
{
    const EDIT = "edit";
    const MANAGE_PHOTO = "manage_photo";
    const ADD = "add";
    const INVITE = "invite";
    const VIEW_EXPIRED = "view_expired";
    const BUY_NOW = "buy_now";
    const REOPEN = "reopen";

    const FEATURE = "feature";
    const APPROVE = "approve";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = "purchase_sponsor";


    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);
        $this->supports = $this->mergePermissions([
            self::ADD, self::VIEW, self::EDIT, self::COMMENT, self::VIEW_EXPIRED, self::REOPEN,
            self::FEATURE, self::APPROVE, self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR, self::INVITE, self::MANAGE_PHOTO, self::BUY_NOW
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
        /** @var $resource MarketplaceResource */
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('marketplace.can_access_marketplace');
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('marketplace.can_create_listing');
                break;
            case self::EDIT:
            case self::MANAGE_PHOTO:
                $granted = ($this->isGrantedSetting('marketplace.can_edit_other_listing')
                    || ($this->isGrantedSetting('marketplace.can_edit_own_listing') && $isOwner));
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('marketplace.can_delete_other_listings')
                    || ($this->isGrantedSetting('marketplace.can_delete_own_listing') && $isOwner));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('marketplace.can_delete_own_listing');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['marketplace.can_access_marketplace', 'marketplace.can_post_comment_on_listing']) && (!$resource || !$resource->getIsPending());
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting('marketplace.can_sponsor_marketplace') && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = $this->isGrantedSetting('marketplace.can_purchase_sponsor');
                break;
            case self::APPROVE:
                $granted = $this->isGrantedSetting('marketplace.can_approve_listings') && (!$resource || $resource->getIsPending());
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('marketplace.can_feature_listings');
                break;
            case self::INVITE:
                $granted = ($this->isGrantedSetting('marketplace.can_edit_other_listing')
                        || ($this->isGrantedSetting('marketplace.can_edit_own_listing') && $isOwner))
                    && $resource && !$resource->view_id;
                break;
            case self::VIEW_EXPIRED:
                $granted = $this->isGrantedSetting('marketplace.can_view_expired');
                break;
            case self::BUY_NOW:
                /** @var MarketplaceResource $resource */
                $granted = $resource && ($resource->is_sell || $resource->allow_point_payment) && $resource->view_id != 2 && $resource->price != 'free' && !$isOwner;
                break;
            case self::REOPEN:
                $granted = ($this->isGrantedSetting('marketplace.can_reopen_own_expired_listing') && $isOwner) || $this->isGrantedSetting('marketplace.can_reopen_expired_listings');
                break;
            case self::SPONSOR_IN_FEED:
                $granted = ($isOwner && $this->isGrantedSetting('feed.can_purchase_sponsor')) || $this->isGrantedSetting('feed.can_sponsor_feed');
                break;
        }

        return $granted;
    }

}