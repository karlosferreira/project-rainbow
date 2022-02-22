<?php
namespace Apps\P_StatusBg\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\P_StatusBg\Service\Api\PStatusBgBackgroundApi;

defined('PHPFOX') or exit('NO DICE!');

class PStatusBgCollectionResource extends ResourceBase
{
    const RESOURCE_NAME = "pstatusbg-collection";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'pstatusbg';

    protected $idFieldName = 'collection_id';

    public $title;
    public $is_active;
    public $is_deleted;
    public $main_image_id;

    /** @var $image = PStatusBgBackgroundResource */
    public $image;
    public $view_id;
    public $backgrounds;

    public function getTitle()
    {
        $this->title = $this->getLocalization()->translate($this->title);

        return $this->parse->cleanOutput($this->title);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('title', ['type' => ResourceMetadata::STRING])
            ->mapField('is_active', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_deleted', ['type' => ResourceMetadata::BOOL])
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('main_image_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'schema' => [
                'definition' => [
                    'backgrounds' => 'pstatusbg_background[]'
                ],
            ]
        ]);
    }

    public function getShortFields()
    {
        return ['module_name', 'id', 'resource_name', 'image', 'view_id', 'is_active', 'is_deleted', 'backgrounds', 'title'];
    }

    public function getImage()
    {
        if (!empty($this->main_image_id)) {

            $this->image = (new PStatusBgBackgroundApi())->loadResourceById($this->main_image_id, true);
            if ($this->image) {
                return $this->image->getImage();
            }
        }
        return $this->image;
    }

    public function getBackgrounds()
    {
        if ($this->backgrounds == null) {
            $this->backgrounds = (new PStatusBgBackgroundApi())->getBackgroundsByCollection($this->id);
        }
        return $this->backgrounds;
    }
}
