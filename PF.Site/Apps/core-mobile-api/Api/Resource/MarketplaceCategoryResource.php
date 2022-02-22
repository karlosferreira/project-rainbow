<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 12/4/18
 * Time: 3:07 PM
 */

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class MarketplaceCategoryResource extends ResourceBase
{
    const RESOURCE_NAME = "marketplace-category";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "category_id";

    public $name;
    public $name_url;
    public $used;
    public $ordering;
    public $is_active;
    public $subs;
    public $parent_id;

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return Phpfox::permalink('marketplace.category', $this->id, $this->name);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->parse->cleanOutput($this->getLocalization()->translate($this->name));
    }

    public function getSubs()
    {
        if (!empty($this->rawData['sub'])) {
            $subs = [];
            foreach ($this->rawData['sub'] as $sub) {
                $subs[] = MarketplaceCategoryResource::populate($sub)->displayShortFields()->toArray();
            }
            return $subs;
        }
        return null;
    }

    public function getShortFields()
    {
        return ['id', 'name', 'subs', 'resource_name'];
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('used', ['type' => ResourceMetadata::INTEGER])
            ->mapField('ordering', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_active', ['type' => ResourceMetadata::BOOL])
            ->mapField('parent_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        $permission = NameResource::instance()->getApiServiceByResourceName(MarketplaceResource::RESOURCE_NAME)->getAccessControl()->getPermissions();
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'category'
            ],
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false,
            'acl' => $permission,
            'moderation_menu'  => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }
}