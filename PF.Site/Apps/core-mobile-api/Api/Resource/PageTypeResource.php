<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Service\NameResource;

class PageTypeResource extends ResourceBase
{
    const RESOURCE_NAME = "page-type";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "type_id";

    public $name;
    public $image;
    public $ordering;
    public $item_type;
    public $total_page;
    /**
     * @var PageResource
     */
    public $items;

    /**
     * @var PageCategoryResource
     */
    public $subs;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getId()
    {
        return $this->isListView() ? 'type_' . $this->rawData['type_id'] : (int)$this->rawData['type_id'];
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return '';
    }

    public function getItems()
    {
        if (!empty($this->rawData['pages'])) {
            $pages = [];
            foreach ($this->rawData['pages'] as $group) {
                $pagePopulate = PageResource::populate($group);
                $pageApi = NameResource::instance()->getApiServiceByResourceName(PageResource::RESOURCE_NAME);
                $pageApi->setHyperlinks($pagePopulate);
                $pages[] = $pagePopulate->lazyLoad(['user'])->setExtra($pageApi->getAccessControl()->getPermissions($pagePopulate))->displayShortFields()->toArray();
            }
            return $pages;
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
                $categories[] = PageCategoryResource::populate($category)->setViewMode(ResourceBase::VIEW_LIST)->toArray();
            }
            return $categories;
        }
        return null;
    }

    public function getTotalPage()
    {
        return isset($this->rawData['pages_count']) ? (int)$this->rawData['pages_count'] : (int)$this->getPagesCount($this->getId());
    }

    private function getPagesCount($typeId)
    {
        return \Phpfox_Database::instance()->select('count(*)')
            ->from(':pages')
            ->where(['type_id' => $typeId, 'view_id' => 0])
            ->executeField();
    }

    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            $image = $this->rawData['image_path'];
        } else {
            $image = 'PF.Site/Apps/core-pages/assets/img/default-category/default_category.png';
        }

        return Image::createFrom([
            'file'      => $image,
            'server_id' => $this->rawData['image_server_id'],
            'path'      => 'core.path_actual'
        ]);
    }

    public function getShortFields()
    {
        return ['id', 'name', 'image', 'total_page'];
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
                'extras' => ['item_resource_name' => 'pages']
            ],
            'queryKey'       => 'type',
            'searchResource' => 'pages'
        ]);
    }
}
