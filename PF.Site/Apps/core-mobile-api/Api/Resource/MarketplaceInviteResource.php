<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Phpfox;

class MarketplaceInviteResource extends ResourceBase
{
    const RESOURCE_NAME = "marketplace-invite";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "invite_id";

    public $marketplace_id;
    public $type_id;
    public $visited_id;

    public $invited_email;

    /**
     * @var UserResource
     */
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
        return Phpfox::permalink('marketplace', $this->marketplace_id);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('marketplace_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('type_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('visited_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'item_invite',
            ],
            'resource_name' => $this->getResourceName(),
            'urls.base'     => 'mobile/marketplace-invite',
            'search_input'  => false,
            'list_view'     => [
                'item_view' => 'page_member',
                'noItemMessage'   => [
                    'image'     => $this->getAppImage('no-member'),
                    'label'     => $l->translate('no_members_found')
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
            ],
            'fab_buttons'   => false,
            'can_add'       => false,
        ]);
    }
}