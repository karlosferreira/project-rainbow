<?php
namespace Apps\P_StatusBg\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class PStatusBgBackgroundResource extends ResourceBase
{
    const RESOURCE_NAME = "pstatusbg-background";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'pstatusbg';

    protected $idFieldName = 'background_id';

    public $collection;
    public $collection_id;
    public $image;
    public $ordering;
    public $is_deleted;
    public $view_id;

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('is_active', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_deleted', ['type' => ResourceMetadata::BOOL])
            ->mapField('collection_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('ordering', ['type' => ResourceMetadata::INTEGER])
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
        ;
    }

    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            $aSizes = Phpfox::getParam('pstatusbg.thumbnail_sizes');
            if ($this->view_id > 0) {
                foreach ($aSizes as $aSize) {
                    $this->image[$aSize] = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/p-status-background/assets/images/default-collection/' . ($aSize == 48 ? str_replace('-min','-sm',$this->rawData['image_path']) : $this->rawData['image_path']);
                }
                return $this->image;
            } else {
                return Image::createFrom([
                    'file' => $this->rawData['image_path'],
                    'server_id' => $this->rawData['server_id'],
                    'path' => 'core.url_pic'
                ], $aSizes, false);
            }
        }
        return isset($this->rawData['full_path']) ? $this->rawData['full_path'] : null;
    }

    public function getShortFields()
    {
        return ['module_name', 'resource_name', 'id', 'image', 'ordering', 'view_id', 'is_active', 'is_deleted', 'collection'];
    }
    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
        ]);
    }

    public function getCollection()
    {
        if ($this->collection_id) {
            $this->collection = PStatusBgCollectionResource::populate([
                'collection_id' => $this->collection_id
            ])->toArray(['module_name', 'resource_name', 'id']);
        }
        return $this->collection;
    }
}
