<?php

namespace Apps\Core_MobileApi\Version1_6\Api\Security\Marketplace;

use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Version1_6\Api\Resource\MarketplaceResource;


class MarketplaceAccessControl extends \Apps\Core_MobileApi\Api\Security\Marketplace\MarketplaceAccessControl
{
    public function isGranted($permission, ResourceBase $resource = null)
    {
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
        switch ($permission) {
            case self::BUY_NOW:
                /** @var MarketplaceResource $resource */
                $granted = $resource && (($resource->is_sell && $resource->owner_have_gateway) || $resource->allow_point_payment);
                break;
            case self::PURCHASE_SPONSOR:
                $granted = \Phpfox::isAppActive('Core_BetterAds') && $this->isGrantedSetting('marketplace.can_purchase_sponsor') && !$this->isGrantedSetting('marketplace.can_sponsor_marketplace') && $resource && $resource->getCanPurchaseSponsor();
                break;
            case self::SPONSOR_IN_FEED:
                $granted = $resource && $resource->getCanSponsorInFeed() && (($isOwner && $this->isGrantedSetting('feed.can_purchase_sponsor')) || $this->isGrantedSetting('feed.can_sponsor_feed'));
                break;
            default:
                $granted = true;
                break;
        }
        // Check Pages/Group permission
        if ($granted && $this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('marketplace.view_browse_marketplace_listings');
                    break;
                case self::ADD:
                    $granted = ($this->appContext->hasPermission('marketplace.share_marketplace_listings')
                        && $this->appContext->hasPermission('marketplace.view_browse_marketplace_listings'));
                    break;
            }
        }
        return $granted;
    }

}