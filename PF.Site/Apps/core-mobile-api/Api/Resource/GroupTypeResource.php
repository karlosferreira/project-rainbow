<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Service\NameResource;

class GroupTypeResource extends ResourceBase
{
    const RESOURCE_NAME = "group-type";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "type_id";

    public $name;
    public $image;
    public $ordering;
    public $item_type;
    public $total_page;
    /**
     * @var GroupResource
     */
    public $items;

    /**
     * @var GroupCategoryResource
     */
    public $subs;

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
        return $this->isListView() ? 'type_' . $this->rawData['type_id'] : (int)$this->rawData['type_id'];
    }

    public function getItems()
    {
        if (!empty($this->rawData['pages'])) {
            $groups = [];
            foreach ($this->rawData['pages'] as $group) {
                $groupPopulate = GroupResource::populate($group);
                $groupApi = NameResource::instance()->getApiServiceByResourceName(GroupResource::RESOURCE_NAME);
                $groupApi->setHyperlinks($groupPopulate);
                $groups[] = $groupPopulate->lazyLoad(['user'])->setExtra($groupApi->getAccessControl()->getPermissions($groupPopulate))->displayShortFields()->toArray();
            }
            return $groups;
        }
        return null;
    }

    public function getName()
    {
        return $this->parse->cleanOutput($this->getLocalization()->translate($this->name));
    }

    public function getSubs()
    {
        if (!empty($this->rawData['categories'])) {
            $categories = [];
            foreach ($this->rawData['categories'] as $category) {
                $categories[] = GroupCategoryResource::populate($category)->setViewMode(ResourceBase::VIEW_LIST)->toArray();
            }
            return $categories;
        }
        return null;
    }

    public function getTotalPage()
    {
        return isset($this->rawData['pages_count']) ? $this->rawData['pages_count'] : 0;
    }

    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            $image = $this->rawData['image_path'];
        } else {
            $image = 'PF.Site/Apps/core-groups/assets/img/default-category/default_category.png';
        }

        return Image::createFrom([
            'file'      => $image,
            'server_id' => $this->rawData['image_server_id'],
            'path'      => 'core.path_actual'
        ]);
    }

    public function getShortFields()
    {
        return ['id', 'name', 'image'];
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('ordering', ['type' => ResourceMetadata::INTEGER])
            ->mapField('item_type', ['type' => ResourceMetadata::INTEGER])
            ->mapField('total_page', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'resource_name'  => $this->getResourceName(),
            'schema'         => [
                'ref'    => 'type',
                'extras' => ['item_resource_name' => 'groups']
            ],
            'queryKey'       => 'type',
            'searchResource' => 'groups'
        ]);
    }
}
