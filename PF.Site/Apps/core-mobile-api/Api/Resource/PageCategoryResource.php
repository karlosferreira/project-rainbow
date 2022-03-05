<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Service\NameResource;

class PageCategoryResource extends ResourceBase
{
    const RESOURCE_NAME = "page-category";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = "pages";

    protected $idFieldName = "category_id";

    public $name;
    public $page_type;
    public $type_id;
    public $type_name;

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
        return '';
    }

    public function getId()
    {
        return $this->isListView() ? 'category_' . $this->rawData['category_id'] : (int)$this->rawData['category_id'];
    }

    public function getName()
    {
        return $this->parse->cleanOutput($this->getLocalization()->translate($this->name));
    }

    public function getTypeName()
    {
        return $this->parse->cleanOutput($this->getLocalization()->translate($this->type_name));
    }

    public function getShortFields()
    {
        return ['id', 'name', 'resource_name'];
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('type_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('page_type', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        $permission = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME)->getAccessControl()->getPermissions();
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'category'
            ],
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
            'acl' => $permission,
            'moderation_menu' => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }
}