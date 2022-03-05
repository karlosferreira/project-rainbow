<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Phpfox;

class SearchResource extends ResourceBase
{
    const RESOURCE_NAME = "search";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "id";

    public $resource_link;
    public $title;
    public $item_type_id;
    public $item_name;
    public $icon_name;
    public $creation_date;

    public $image;

    /** @var  $user UserResource */
    public $user;
    private $specialType;
    private $useAvatar;
    private $defaultImages;

    public function __construct($data)
    {
        parent::__construct($data);
        $this->specialType = [
            'forum' => 'forum-thread',
        ];
        $this->useAvatar = [
            'user'   => '',
            'pages'  => 'mobile.page_api',
            'groups' => 'mobile.group_api'
        ];
        $this->defaultImages = [
            'photo_album'    => 'photo-album',
            'event'          => 'event',
            'marketplace'    => 'marketplace',
            'music_song'     => 'music-song',
            'music'          => 'music-song',
            'music_album'    => 'music-album',
            'music_playlist' => 'music-playlist',
            'v'              => 'video'
        ];
    }

    /**
     * Get detail url
     *
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    public function getResourceLink()
    {
        return sprintf('%s/%s', $this->_mapSpecialType($this->rawData['item_type_id']), $this->rawData['item_id']);
    }


    public function getHref()
    {
        return sprintf('%s/%s', $this->_mapSpecialType($this->rawData['item_type_id']), $this->rawData['item_id']);
    }

    public function getId()
    {
        return sprintf('%s:%s', $this->_mapSpecialType($this->rawData['item_type_id']), $this->rawData['item_id']);
    }

    public function getImage()
    {
        if (!empty($this->rawData['item_display_photo'])) {
            $image = null;
            $re = '/src="([^"]+)"/';
            if (preg_match($re, $this->rawData['item_display_photo'], $matches)) {
                $image = $matches[1];
            }

            if (isset($this->defaultImages[$this->rawData['item_type_id']]) && (preg_match('/PF\.Site\/(Apps|flavors)\/.*\/assets\//', $image) || strpos($image, 'no_image.png') !== false || strpos($image, 'nocover.png') !== false)) { // update default image for core apps
                $basePath = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-mobile-api/assets/images/default-images/';
                $resourceName = $this->defaultImages[$this->rawData['item_type_id']];
                return $basePath . $resourceName . '/no_image.png';
            }
            return $image;
        } else if (isset($this->useAvatar[$this->rawData['item_type_id']])) {
            $service = $this->useAvatar[$this->rawData['item_type_id']];
            if ($service) {
                $images = Phpfox::getService($service)->loadResourceById($this->rawData['item_id'], true)->getImage();
                if (!empty($images)) {
                    if (is_string($images)) {
                        return $images;
                    }
                    return $images->sizes['200'];
                } else {
                    return null;
                }
            }
            return $this->user->getAvatar();
        }
        return null;
    }

    public function getTitle()
    {
        return $this->parse->cleanOutput($this->rawData['item_title']);
    }

    public function getCreationDate()
    {
        return $this->convertDatetime($this->rawData['item_time_stamp']);
    }

    public function getItemName()
    {
        return html_entity_decode($this->rawData['item_name'], ENT_QUOTES);
    }

    private function _mapSpecialType($type)
    {
        return isset($this->specialType[$type]) ? $this->specialType[$type] : $type;
    }

    public function getMobileSettings($params = [])
    {
        $aMenus = Phpfox::massCallback('getSearchTitleInfo');
        $results = [];
        foreach ($aMenus as $sKey => $aMenu) {
            $results[] = [
                'value' => $sKey,
                'label' => $this->parse->cleanOutput($aMenu['name']),
            ];
        }
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'list_view'     => [
                'item_view' => 'search_item',
                'noItemMessage'   => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search')
                ],
            ],
            'fab_buttons'   => [
                [
                    'label'         => $this->getLocalization()->translate('filter'),
                    'icon'          => 'filter',
                    'action'        => Screen::ACTION_FILTER_BY,
                    'resource_name' => $this->getResourceName(),
                    'queryKey'      => 'view',
                ],
            ],
            'filter_menu'   => [
                'title'    => $this->getLocalization()->translate('filter_by'),
                'queryKey' => 'view',
                'options'  => $results,
            ],
        ]);
    }
}