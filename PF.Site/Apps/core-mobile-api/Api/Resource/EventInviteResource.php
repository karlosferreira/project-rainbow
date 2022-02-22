<?php


namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Phpfox;

class EventInviteResource extends ResourceBase
{
    const RESOURCE_NAME = "event-invite";
    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = 'invite_id';

    public $event_id;
    public $type_id;
    public $rsvp_id;

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
     * @return mixed
     */
    public function getLink()
    {
        return Phpfox::permalink('event', $this->event_id);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('event_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('type_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('rsvp_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'search_input'  => false,
            'list_view'     => [
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
        ]);
    }
}