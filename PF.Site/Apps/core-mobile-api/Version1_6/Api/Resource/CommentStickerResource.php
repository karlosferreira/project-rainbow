<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:51 AM
 */

namespace Apps\Core_MobileApi\Version1_6\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Service\NameResource;

class CommentStickerResource extends ResourceBase
{
    const RESOURCE_NAME = "comment-sticker";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = 'sticker_id';
    public $module_name = 'comment';

    public $set_id;

    public $ordering;

    public $view_only;

    public $is_deleted;

    public $full_path;

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
            ->mapField('is_deleted', ['type' => ResourceMetadata::BOOL])
            ->mapField('view_only', ['type' => ResourceMetadata::BOOL])
            ->mapField('ordering', ['type' => ResourceMetadata::INTEGER])
            ->mapField('set_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getFullPath()
    {
        if (!empty($this->full_path)) {
            if (strpos($this->full_path, 'data-src') !== false) {
                $this->full_path = preg_replace('/<*img[^>]*data-src*=*["\']([^"\']*)?["\'].*/', '$1', $this->full_path);
            } else {
                $this->full_path = preg_replace('/<*img[^>]*src*=*["\']([^"\']*)?["\'].*/', '$1', $this->full_path);
            }
        }
        return $this->full_path;
    }
    public function getShortFields()
    {
        return ['id', 'module_name', 'resource_name', 'full_path', 'ordering', 'is_deleted', 'view_only'];
    }

    /**
     * @param array $params
     * @return \Apps\Core_MobileApi\Adapter\MobileApp\SettingParametersBag
     * @throws \Exception
     */
    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()
            ->getApiServiceByResourceName($this->resource_name)
            ->getAccessControl()->getPermissions();
        return self::createSettingForResource([
            'acl'             => $permission,
            'resource_name'   => $this->getResourceName(),
        ]);
    }
}