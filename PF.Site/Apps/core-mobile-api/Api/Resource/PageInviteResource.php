<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Phpfox;

class PageInviteResource extends ResourceBase
{
    const RESOURCE_NAME = "page-invite";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "invite_id";

    public $page_id;
    public $type_id;
    public $visited_id;

    public $invited_email;

    /**
     * @var UserResource
     */
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
        return Phpfox::permalink('pages', $this->page_id);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('page_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('type_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('visited_id', ['type' => ResourceMetadata::INTEGER]);
    }
}