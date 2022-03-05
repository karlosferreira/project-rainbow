<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:51 AM
 */

namespace Apps\Core_MobileApi\Version1_6\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Statistic;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class CommentStickerSetResource extends ResourceBase
{
    const RESOURCE_NAME = "comment-sticker-set";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = 'set_id';
    public $module_name = 'comment';

    public $title;

    public $is_active;
    public $is_default;
    public $is_added;
    public $thumbnail_id;
    public $ordering;
    public $full_path;
    public $is_recent_set;

    /**
     * @var Statistic
     */
    public $statistic;

    public $stickers;

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
            ->mapField('title', ['type' => ResourceMetadata::STRING])
            ->mapField('is_active', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_default', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_added', ['type' => ResourceMetadata::BOOL])
            ->mapField('ordering', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_recent_set', ['type' => ResourceMetadata::BOOL])
            ->mapField('thumbnail_id', ['type' => ResourceMetadata::INTEGER]);
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

    public function getStickers()
    {
        $stickers = [];
        if (!empty($this->stickers)) {
            foreach ($this->stickers as $key => $sticker) {
                $stickers[] = CommentStickerResource::populate($sticker)->displayShortFields()->toArray();
            }
        }
        $this->stickers = $stickers;
        return $this->stickers;
    }
    public function setStatistic($statistic)
    {
        $statistic->total_sticker = isset($this->rawData['total_sticker']) ? (int)$this->rawData['total_sticker'] : 0;
        $statistic->used = isset($this->rawData['used']) ? (int)$this->rawData['used'] : 0;
        $this->statistic = $statistic;
    }

    public function getIsAdded()
    {
        if (!isset($this->rawData['is_added'])) {
            $this->is_added = !!Phpfox::getService('comment.stickers')->checkIsAddedSet($this->getId(), Phpfox::getUserId());
        }
        return $this->is_added;
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
            'schema'          => [
                'definition' => [
                    'stickers' => 'comment_sticker[]'
                ],
            ],
            'resource_name'   => $this->getResourceName(),
        ]);
    }
}