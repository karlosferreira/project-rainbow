<?php
namespace Apps\P_Reaction\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Phpfox;

class PReactionResource extends ResourceBase
{

    const RESOURCE_NAME = "preaction";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'preaction';

    protected $idFieldName = 'id';

    public $title;
    public $icon;
    public $color;
    public $ordering;
    public $view_id;
    public $is_deleted;
    public $is_active;
    public $is_default;

    public function getTitle()
    {
        $this->title = $this->getLocalization()->translate($this->title);

        return $this->parse->cleanOutput($this->title);
    }
    public function getIcon()
    {
        if (!$this->icon) {
            Phpfox::getService('preaction')->getReactionIcon($this->rawData);
            $this->icon = isset($this->rawData['full_path']) ? $this->rawData['full_path'] : null;
        }
        //Replace svg
        $this->icon = str_replace('.svg','.png', $this->icon);

        return $this->icon;
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'reaction_list'=> [
                'apiUrl'     => 'preaction/reacted-lists',
            ],
            'reaction_tab' => [
                'apiUrl' => 'preaction/reaction-tabs',
                'noItemMessage' => [
                    'image'     => $this->getAppImage('no-item'),
                    'label'     => $l->translate('no_reactions_found')
                ]
            ]
        ]);
    }

    public function getColor()
    {
        return '#'.$this->color;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('is_active', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_default', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_deleted', ['type' => ResourceMetadata::BOOL])
            ->mapField('ordering', ['type' => ResourceMetadata::INTEGER])
            ->mapField('view_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getIsDefault()
    {
        return $this->view_id == 2;
    }

    public function getShortFields()
    {
        return ['resource_name', 'module_name', 'id', 'title', 'icon', 'color', 'ordering', 'is_default'];
    }

    public function getModuleName()
    {
        return $this->module_name;
    }
}