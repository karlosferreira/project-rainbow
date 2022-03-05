<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class GroupProfileResource extends ResourceBase
{
    const RESOURCE_NAME = "group-profile";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "page_id";
    public $title;
    public $reg_method;
    public $reg_name;
    public $category;
    public $type;
    public $is_liked;
    public $total_member;

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
        return Phpfox::permalink('groups', $this->id);
    }

    public function getCategory()
    {
        if (!empty($this->rawData['category_id'])) {
            $item = NameResource::instance()
                ->getApiServiceByResourceName(GroupCategoryResource::RESOURCE_NAME)
                ->loadResourceById($this->rawData['category_id']);
            if ($item) {
                return GroupCategoryResource::populate($item)->displayShortFields()->toArray();
            }
        }
        return null;
    }

    public function getType()
    {
        if (!empty($this->rawData['type_id'])) {
            $item = NameResource::instance()
                ->getApiServiceByResourceName(GroupTypeResource::RESOURCE_NAME)
                ->loadResourceById($this->rawData['type_id']);
            if ($item) {
                return GroupTypeResource::populate($item)->displayShortFields()->toArray();
            }
        }
        return null;
    }

    public function getRegName()
    {
        if (isset($this->reg_method)) {
            switch ($this->reg_method) {
                case 1:
                    $reg = $this->getLocalization()->translate('closed_group');
                    break;
                case 2:
                    $reg = $this->getLocalization()->translate('secret_group');
                    break;
                default:
                    $reg = $this->getLocalization()->translate('public_group');
                    break;
            }
            return $reg;
        }
        return null;
    }

    public function getTotalMember()
    {
        return isset($this->rawData['total_like']) ? $this->rawData['total_like'] : null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('reg_method', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('total_member', ['type' => ResourceMetadata::INTEGER]);
    }
}