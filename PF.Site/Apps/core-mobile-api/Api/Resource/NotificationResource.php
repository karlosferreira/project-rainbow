<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 14/5/18
 * Time: 4:27 PM
 */

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class NotificationResource extends ResourceBase
{
    const RESOURCE_NAME = "notification";
    public $resource_name = self::RESOURCE_NAME;

    public $message;

    /**
     * @var UserResource
     */
    // public $user;


    /**
     * @var UserResource user who make action that create notification
     */
    public $owner;

    public $user;

    public $is_seen;

    public $is_read;

    public $route;

    public $web_link;

    protected $item_type;

    protected $item_id;

    /**
     * @return mixed
     */
    public function getItemId()
    {
        return $this->item_id;
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    public function getWebLink()
    {
        return isset($this->rawData['link']) ? $this->rawData['link'] : $this->link;
    }

    public function getMessage($bolder = true)
    {
        $this->message = html_entity_decode(preg_replace(["/<span[^>]+>(.*?)<\/span>/mi", "/<a([^>]+)>(.*?)<\/a>/mi", "/\"(.*?)\"/mi", "/\[user=\d+\](.+?)\[\/user\]/iu", "/<b>(.*?)<\/b>/mi"], $bolder ? '<b>$1</b>' : '$1', $this->message), ENT_QUOTES);
        $this->message = preg_replace('/(\s){2,}/', ' ', $this->message);
        return $this->message;
    }

    /**
     * @return string
     */
    public function getItemType()
    {
        if (empty($this->item_type) && !empty($this->rawData['type_id'])) {
            $this->item_type = $this->rawData['type_id'];
        }
        return $this->item_type;
    }

    /**
     * @return UserResource
     */
    public function getOwner()
    {
        return null;
    }

    public function getUser()
    {
        if (empty($this->owner)) {
            $this->rawData['resource_name'] = 'user';
            $this->rawData['module_name'] = 'user';

            $this->owner = UserResource::populate($this->rawData);
        }
        return $this->owner;
    }

    /**
     * @param UserResource|array $owner
     *
     * @return $this
     */
    public function setOwner($owner)
    {
        if (is_array($owner)) {
            $owner = UserResource::populate($owner);
        }
        $this->owner = $owner;
        return $this;
    }


    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name' => $this->resource_name,
            'base.urls'     => 'mobile/notification',
            'fab_buttons'   => false,
            'list_view'     => [
                'apiUrl'    => 'mobile/notification',
                'item_view' => 'notification',
                'limit'     => 20,
            ],
            'action_menu'   => [
                ['label' => $l->translate('Delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('is_seen', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_read', ['type' => ResourceMetadata::BOOL])
            ->mapField('item_type', ['type' => ResourceMetadata::STRING])
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER]);
    }
}