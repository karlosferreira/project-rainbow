<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:48 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class ForumThankResource extends ResourceBase
{
    const RESOURCE_NAME = "forum-thank";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "thank_id";
    public $post_id;
    /**
     * Who like this post
     * @var UserResource
     */
    public $user;


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
            ->mapField('thank_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('post_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'urls.base'     => 'mobile/forum-thank',
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
            'list_view'     => [
                'item_view' => 'forum_thank',
            ]
        ]);
    }
}