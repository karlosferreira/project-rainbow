<?php

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class FileResource extends ResourceBase
{
    const RESOURCE_NAME = "file";

    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "file_id";

    public $type;
    public $user;
    public $size;

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
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('size', ['type' => ResourceMetadata::FLOAT]);
    }
}