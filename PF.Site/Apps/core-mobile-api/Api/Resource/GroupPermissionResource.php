<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class GroupPermissionResource extends ResourceBase
{
    const RESOURCE_NAME = "group-permission";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "page_id";

    public $var_name;
    public $phrase;
    public $is_active;

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

    public function getVarName()
    {
        return $this->rawData['id'];
    }

    public function getId()
    {
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('is_active', ['type' => ResourceMetadata::BOOL]);
    }
}