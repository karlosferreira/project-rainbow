<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class GroupPhotoResource extends ResourceBase
{
    const RESOURCE_NAME = "group-photo";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "page_id";

    public $image;
    public $covers;
    public $image_path;
    public $image_server_id;
    public $cover_photo_position;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return Phpfox::permalink('groups', $this->id);
    }

    /**
     * @return Image|null
     */
    public function getImage()
    {
        $aSizes = [50, 120, 200];
        return Image::createFrom([
            'file'      => $this->rawData['image_path'],
            'server_id' => $this->rawData['image_server_id'],
            'path'      => 'pages.url_image'
        ], $aSizes);
    }

    /**
     * @return Image|null
     */
    public function getCovers()
    {
        if (!empty($this->rawData['cover_photo_id'])) {
            $cover = NameResource::instance()
                ->getApiServiceByResourceName(PhotoResource::RESOURCE_NAME)
                ->loadResourceById($this->rawData['cover_photo_id']);
            if ($cover) {
                $aSizes = Phpfox::getService('groups')->getPhotoPicSizes();
                return Image::createFrom([
                    'file'      => $cover['destination'],
                    'server_id' => $cover['server_id'],
                    'path'      => 'photo.url_photo'
                ], $aSizes);
            }
        }
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('image_server_id', ['type' => ResourceMetadata::INTEGER]);
    }
}