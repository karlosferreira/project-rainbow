<?php

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class ForumSubscribeResource extends ResourceBase
{
    const RESOURCE_NAME = "forum-subscribe";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "subscribe_id";

    public $thread_id;

    public $forum_id;


    /**
     * @var UserResource
     */
    public $user;

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
            ->mapField('thread_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('forum_id', ['type' => ResourceMetadata::INTEGER]);
    }
}