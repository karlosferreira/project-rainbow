<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Service\NameResource;

class AnnouncementResource extends ResourceBase
{
    const RESOURCE_NAME = "announcement";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = "announcement";

    public $title;

    public $description;

    public $text;

    public $show_in_dashboard;

    public $style;

    public $icon_image;

    public $icon_font;

    public $can_be_closed;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    public function getTitle()
    {
        if (!empty($this->rawData['subject_var'])) {
            $this->title = $this->parse->cleanOutput($this->rawData['subject_var']);
        }
        return $this->title;
    }

    public function getDescription()
    {
        if (isset($this->rawData['intro_var'])) {
            $this->description = TextFilter::pureHtml($this->rawData['intro_var'], true);
        }
        return $this->description;
    }

    public function getText()
    {
        if (isset($this->rawData['content_var'])) {
            $this->text = TextFilter::pureHtml($this->rawData['content_var'], true);
        }
        return $this->text;
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return \Phpfox::getLib('url')->makeUrl('announcement.view', ['id' => $this->id]);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('title', ['type' => ResourceMetadata::STRING])
            ->mapField('can_be_closed', ['type' => ResourceMetadata::BOOL]);
    }

    public function getShortFields()
    {
        return ['id', 'resource_name', 'module_name', 'title', 'description', 'creation_date', 'modification_date', 'link', 'extra', 'icon_image', 'icon_font', 'style'];
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        return self::createSettingForResource([
            'acl'           => $permission,
            'resource_name' => $this->getResourceName(),
            'action_menu'   => []
        ]);
    }
}