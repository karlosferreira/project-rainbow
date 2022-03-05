<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Phpfox;

class PageInfoResource extends ResourceBase
{
    const RESOURCE_NAME = "page-info";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "page_id";

    public $text;
    public $text_parsed;
    public $description;
    public $reg_method;
    public $total_like;

    public $user;

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

    public function getDescription()
    {
        return strip_tags($this->text_parsed);
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
            ->mapField('total_like', ['type' => ResourceMetadata::INTEGER]);
    }
}