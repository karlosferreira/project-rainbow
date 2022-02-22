<?php

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class ForumModeratorResource extends ResourceBase
{
    const RESOURCE_NAME = "forum-moderator";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "moderator_id";
    /**
     * @var UserResource
     */
    public $user;
    public $forum_id;
    public $access;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * @return mixed
     */
    public function getLink()
    {
        // TODO: Implement getLink() method.
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('forum_id', ['type' => ResourceMetadata::INTEGER]);
    }

}