<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 12/4/18
 * Time: 3:07 PM
 */

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Phpfox;

class MarketplacePhotoResource extends ResourceBase
{
    const RESOURCE_NAME = "marketplace-photo";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "image_id";

    public $listing_id;
    public $image;

    public $main;

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return '';
    }

    /**
     * @return mixed
     */
    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            $aSizes = Phpfox::getParam('marketplace.thumbnail_sizes');
            return Image::createFrom([
                'file'      => $this->rawData['image_path'],
                'server_id' => $this->rawData['server_id'],
                'path'      => 'marketplace.url_image'
            ], $aSizes);
        } else {
            return Phpfox::getParam('marketplace.marketplace_default_photo');
        }
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('listing_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('main', ['type' => ResourceMetadata::BOOL]);
    }
}