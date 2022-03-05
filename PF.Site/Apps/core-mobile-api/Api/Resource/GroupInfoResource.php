<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Phpfox;

class GroupInfoResource extends ResourceBase
{
    const RESOURCE_NAME = "group-info";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "page_id";

    public $text;
    public $text_parsed;
    public $description;
    public $reg_method;
    public $reg_name;
    public $total_member;

    public $user;

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

    public function getDescription()
    {
        return strip_tags($this->text_parsed);
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
            ->mapField('total_member', ['type' => ResourceMetadata::INTEGER]);
    }
}