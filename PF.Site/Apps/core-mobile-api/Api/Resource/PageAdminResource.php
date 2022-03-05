<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Phpfox;

class PageAdminResource extends ResourceBase
{
    const RESOURCE_NAME = "page-admin";
    public $resource_name = self::RESOURCE_NAME;

    public $full_name;
    public $avatar;
    public $user_id;
    public $page_id;
    public $is_featured;


    public function __construct($data)
    {
        parent::__construct($data);
    }


    public function getId()
    {
        $this->id = $this->rawData['page_id'] . ':' . $this->rawData['user_id'];
        return $this->id;
    }

    public function getFullName()
    {
        $this->full_name = isset($this->rawData['full_name']) ? $this->parse->cleanOutput($this->rawData['full_name']) : '';
        return $this->full_name;
    }

    public function getAvatar()
    {
        $image = Image::createFrom([
            'user' => $this->rawData,
        ], ["50_square"]);

        if ($image == null) {
            return Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-mobile-api/assets/images/default-images/user/no_image.png';
        }

        return (!$this->isDetailView() ? (!empty($image->sizes['50_square']) ? $image->sizes['50_square'] : null) : $image->image_url);

    }

    public function getShortFields()
    {
        return ['user_id', 'resource_name', 'id', 'full_name', 'avatar', 'page_id', 'is_featured'];
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return Phpfox::permalink('pages', $this->page_id);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('user_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('page_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'item_admin',
            ],
            'resource_name' => $this->getResourceName(),
            'urls.base'     => 'mobile/page-admin',
            'search_input'  => false,
            'list_view'     => [
                'item_view' => 'page_admin',
                'noItemMessage'   => [
                    'image'     => $this->getAppImage('no-member'),
                    'label'     => $l->translate('no_admins_found')
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

    public function getIsFeatured()
    {
        if ($this->is_featured === null) {
            $this->is_featured = \Phpfox::getService('user')->isFeatured($this->rawData['user_id']);
        }
        return (bool)$this->is_featured;
    }
}