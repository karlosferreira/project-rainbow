<?php


namespace Apps\Core_MobileApi\Version1_6\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Phpfox;

class MarketplaceResource extends \Apps\Core_MobileApi\Api\Resource\MarketplaceResource
{
    public $owner_have_gateway;
    public $owner_can_sell_listing;
    public $owner_can_sell_by_point;

    public $module_id;
    public $item_id;

    protected $canPurchaseSponsor = null;
    protected $canSponsorInFeed = null;
    public $is_sponsored_feed;

    public function __construct($data)
    {
        parent::__construct($data);
        if (Phpfox::isModule('marketplace') && method_exists(Phpfox::getService('marketplace'), 'canSellItemOnMarket') && $this->user != null) {
            list($this->owner_can_sell_listing, $this->owner_have_gateway, $this->owner_can_sell_by_point) = Phpfox::getService('marketplace')->canSellItemOnMarket($this->user->getId());
        }
    }


    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::isAppActive('Core_BetterAds') && Phpfox::getService('marketplace')->canPurchaseSponsorItem($this->getId());
        }
        return $this->canPurchaseSponsor;
    }

    public function getCanSponsorInFeed()
    {
        if ($this->canSponsorInFeed === null) {
            $this->canSponsorInFeed = Phpfox::isModule('feed') && Phpfox::getService('feed')->canSponsoredInFeed('marketplace', $this->getId());
        }
        return $this->canSponsorInFeed;
    }

    public function getIsSponsoredFeed()
    {
        if ($this->is_sponsored_feed === null) {
            $this->is_sponsored_feed = Phpfox::isModule('feed') && is_numeric(Phpfox::getService('feed')->canSponsoredInFeed('marketplace', $this->getId()));
        }
        return $this->is_sponsored_feed;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER]);
    }
}