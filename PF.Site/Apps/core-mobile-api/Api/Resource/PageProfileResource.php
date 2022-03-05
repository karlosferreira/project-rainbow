<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class PageProfileResource extends ResourceBase
{
    const RESOURCE_NAME = "page-profile";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "page_id";
    public $title;
    public $reg_method;
    public $category;
    public $type;
    public $is_liked;
    public $total_like;

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
        return Phpfox::permalink('pages', $this->id);
    }

    public function getCategory()
    {
        if (!empty($this->rawData['category_id'])) {
            $item = NameResource::instance()
                ->getApiServiceByResourceName(PageCategoryResource::RESOURCE_NAME)
                ->loadResourceById($this->rawData['category_id']);
            if ($item) {
                return PageCategoryResource::populate($item)->toArray();
            }
        }
        return null;
    }

    public function getType()
    {
        if (!empty($this->rawData['type_id'])) {
            $item = NameResource::instance()
                ->getApiServiceByResourceName(PageTypeResource::RESOURCE_NAME)
                ->loadResourceById($this->rawData['type_id']);
            if ($item) {
                return PageTypeResource::populate($item)->toArray();
            }
        }
        return null;
    }

    public function getTotalLike()
    {
        return isset($this->rawData['total_like']) ? $this->rawData['total_like'] : null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('reg_method', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('total_like', ['type' => ResourceMetadata::INTEGER]);
    }
}