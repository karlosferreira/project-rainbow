<?php

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class TagResource extends ResourceBase
{

    const RESOURCE_NAME = "tag";

    public $resource_name = self::RESOURCE_NAME;

    public $item_id;
    public $category_id;
    public $tag_text;
    public $tag_url;
    public $tag_type;

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('tag_type', ['type' => ResourceMetadata::INTEGER])
            ->mapField('category_id', ['type' => ResourceMetadata::STRING]);
    }

    public function getShortFields()
    {
        return [
            'resource_name',
            'id',
            'tag_text'
        ];
    }

}