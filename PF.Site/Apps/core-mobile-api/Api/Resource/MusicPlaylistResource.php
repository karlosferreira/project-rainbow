<?php


namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Form\Validator\Filter\TextFilter;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\Object\Privacy;
use Apps\Core_MobileApi\Api\Resource\Object\Statistic;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class MusicPlaylistResource extends ResourceBase
{
    const RESOURCE_NAME = "music-playlist";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'music';

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "playlist_id";

    public $name;

    public $description;
    public $text;

    public $image;

    public $view_id = 0;

    public $is_liked;

    public $is_friend;
    /**
     * @var Statistic
     */
    public $statistic;

    /**
     * @var Privacy
     */
    public $privacy;

    /**
     * @var UserResource
     */
    public $user;
    /**
     * @var MusicSongResource
     */
    public $songs;
    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

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
        return Phpfox::permalink('music.playlist', $this->id, $this->name);
    }


    public function getTags()
    {
        return null;
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['description'])) {
            $this->text = $this->rawData['description'];
        }
        TextFilter::pureHtml($this->text, true);
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        if ($this->description === null && isset($this->rawData['description'])) {
            $this->description = $this->rawData['description'];
        }
        TextFilter::pureText($this->description, null, true);
        return $this->description;
    }

    /**
     * @return Image|array|string
     */
    public function getImage()
    {
        if (!empty($this->rawData['image_path'])) {
            if (empty($this->image)) {
                $sizes = Phpfox::getParam('music.thumbnail_sizes', [200]);
                $this->image = Image::createFrom([
                    'file'      => $this->rawData['image_path'],
                    'server_id' => $this->rawData['server_id'],
                    'path'      => 'music.url_image'
                ], $sizes);
            }
            return $this->image;
        } else {
            return $this->getDefaultImage();
        }
    }

    public function setStatistic($statistic)
    {
        $statistic->total_track = isset($this->rawData['total_track']) ? (int)$this->rawData['total_track'] : 0;
        $this->statistic = $statistic;
    }

    public function getSongs()
    {
        if (!empty($this->rawData['is_detail']) && $this->getAccessControl()) {
            $songs = Phpfox::getService('music.playlist')->getAllSongs($this->id, true);
            if ($songs) {
                $results = [];
                foreach ($songs as $key => $song) {
                    $song['playlist_detail'] = true;
                    $songResource = MusicSongResource::populate($song);
                    $results[] = $songResource->setExtra($this->getAccessControl()->getPermissions($songResource))->toArray();
                }
                return $results;
            }
        }
        return null;
    }

    public function getShortFields()
    {
        return ['id', 'image', 'name', 'statistic', 'user', 'is_liked', 'creation_date', 'privacy', 'resource_name', 'extra', 'description', 'is_friend', 'attachments', 'songs'];
    }

    public function getAttachments()
    {
        if (!empty($this->rawData['total_attachment']) && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->id, 'music_playlist');
        }
        return null;
    }

    public function getIsLiked()
    {
        return empty($this->is_liked) ? false : $this->is_liked;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()
            ->getApiServiceByResourceName($this->resource_name)
            ->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'acl'             => $permission,
            'schema'          => [
                'definition' => [
                    'songs' => 'music_song[]'
                ],
            ],
            'resource_name'   => $this->resource_name,
            'forms'           => [
                'addItem'  => [
                    'apiUrl'      => UrlUtility::makeApiUrl('music-playlist/form'),
                    'headerTitle' => $l->translate('create_playlist'),
                ],
                'editItem' => [
                    'apiUrl'      => UrlUtility::makeApiUrl('music-playlist/form/:id'),
                    'headerTitle' => $l->translate('update_playlist'),
                ],
            ],
            'search_input'    => [
                'placeholder' => $l->translate('search_playlists_dot'),
            ],
            'detail_view'     => [
                'component_name' => 'music_playlist_detail',
            ],
            'list_view'       => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_playlists_found'),
                    'sub_label' => $l->translate('start_adding_items_by_create_new_stuffs'),
                    'action'    => !empty($permission['can_add']) ? [
                        'resource_name' => $this->getResourceName(),
                        'module_name'   => $this->getModuleName(),
                        'value'         => Screen::ACTION_ADD,
                        'label'         => $l->translate('add_new_item')
                    ] : null
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'numColumns'      => 2,
                'layout'          => Screen::LAYOUT_GRID_VIEW,
                'item_view'       => 'music_playlist',
            ],
            'app_menu'        => [
                ['label' => $l->translate('all_music_playlists'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_playlists'), 'params' => ['initialQuery' => ['view' => 'my']]],
            ],
            'action_menu'     => [
                ['label' => $l->translate('manage_songs'), 'value' => 'music-playlist/songs', 'acl' => 'can_edit'],
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
                ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
            ],
            'sort_menu'       => [
                'title'    => $l->translate('sort_by'),
                'queryKey' => 'sort',
                'options'  => [
                    ['label' => $l->translate('latest'), 'value' => 'latest'],
                    ['label' => $l->translate('most_viewed'), 'value' => 'most_viewed'],
                    ['label' => $l->translate('most_songs'), 'value' => 'most_song'],
                ]
            ],
            'moderation_menu' => [
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    public function getFeedDisplay()
    {
        $this->setDisplayFields(['id', 'resource_name', 'name', 'description', 'image', 'privacy']);
        $embed = $this->toArray();
        $embed['total_track'] = isset($this->rawData['total_track']) ? (int)$this->rawData['total_track'] : 0;
        $embed['total_view'] = isset($this->rawData['total_view']) ? (int)$this->rawData['total_view'] : 0;
        return $embed;
    }

    public function getIsPending()
    {
        return false;
    }
}