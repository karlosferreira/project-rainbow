<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class PageWidgetResource extends ResourceBase
{
    const RESOURCE_NAME = 'page-widget';
    public $resource_name = self::RESOURCE_NAME;

    public $module_name = 'pages';
    protected $idFieldName = 'widget_id';

    public $title;
    public $url_title;
    public $menu_title;
    public $page_id;

    public $is_block;
    public $text;
    public $description;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getLink()
    {
        return isset($this->rawData['url']) ? $this->rawData['url'] : '';
    }

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName()
        ]);
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['text'])) {
            $this->text = $this->rawData['text'];
        }
        TextFilter::pureHtml($this->text, true);
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        if ($this->description === null && isset($this->rawData['text'])) {
            $this->description = $this->rawData['text'];
        }
        TextFilter::pureText($this->description, 255, true);
        return $this->description;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('title', ['type' => ResourceMetadata::STRING])
            ->mapField('is_block', ['type' => ResourceMetadata::BOOL])
            ->mapField('description', ['type' => ResourceMetadata::STRING])
            ->mapField('text', ['type' => ResourceMetadata::STRING])
            ->mapField('page_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getShortFields()
    {
        return ['title', 'resource_name', 'module_name', 'menu_title', 'description', 'id', 'link', 'creation_date', 'url_title', 'is_block'];
    }
}
