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

class MusicSongResource extends ResourceBase
{
    const RESOURCE_NAME = "music-song";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "song_id";

    public $title;

    public $description;
    public $text;

    public $module_id;
    public $item_id;

    public $duration;
    public $song_path;

    public $image;

    public $view_id;
    public $is_featured;
    public $is_sponsor;
    public $is_liked;
    public $is_friend;
    public $is_pending;

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


    public $genres = [];

    public $tags;

    public $album;

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];

    protected $canPurchaseSponsor = null;
    protected $canSponsorInFeed = null;
    public $is_sponsored_feed;

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
        return Phpfox::permalink('music', $this->id, $this->title);
    }

    /**
     * @return array
     */
    public function getGenres()
    {
        if (empty($this->genres)) {
            $this->genres = NameResource::instance()
                ->getApiServiceByResourceName(MusicGenreResource::RESOURCE_NAME)
                ->getBySongId($this->id);
        } else {
            $this->genres = array_map(function ($item) {
                return MusicGenreResource::populate($item)->displayShortFields()->toArray();
            }, $this->genres);
        }
        return $this->genres;
    }

    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        $tag = Phpfox::getService('tag')->getTagsById('music_song', $this->id);
        if (!empty($tag[$this->id])) {
            $tags = [];
            foreach ($tag[$this->id] as $tag) {
                $tags[] = TagResource::populate($tag)->displayShortFields()->toArray();
            }
            return $tags;
        }
        return null;
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['description'])) {
            $this->text = TextFilter::pureHtml($this->rawData['description'], true);
        }
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
            $sizes = Phpfox::getParam('music.thumbnail_sizes');
            return Image::createFrom([
                'file'      => $this->rawData['image_path'],
                'server_id' => $this->rawData['server_id'],
                'path'      => 'music.url_image'
            ], $sizes);
        } else {
            return $this->getDefaultImage();
        }
    }

    /**
     * @return PhotoAlbumResource | mixed
     * @throws \Exception
     */
    public function getAlbum()
    {
        if (empty($this->album) && empty($this->rawData['album_detail'])) {
            $album = NameResource::instance()
                ->getApiServiceByResourceName(MusicAlbumResource::RESOURCE_NAME)
                ->loadResourceById($this->rawData['album_id']);
            return MusicAlbumResource::populate($album)->toArray([
                'id', 'image', 'name', 'year', 'statistic', 'user', 'is_featured', 'is_sponsor', 'is_like', 'creation_date', 'privacy',
                'item_id', 'module_id', 'resource_name'
            ]);
        }
        return $this->album;
    }

    public function setStatistic($statistic)
    {
        $statistic->total_play = isset($this->rawData['total_play']) ? (int)$this->rawData['total_play'] : 0;
        $statistic->total_score = isset($this->rawData['total_score']) ? $this->rawData['total_score'] : 0;
        $statistic->total_rating = isset($this->rawData['total_rating']) ? $this->rawData['total_rating'] : 0;
        $this->statistic = $statistic;
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->id, 'music_song');
        }
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()
            ->getApiServiceByResourceName($this->resource_name)
            ->getAccessControl()->getPermissions();

        $l = $this->getLocalization();

        $actionMenu = [
            ['label' => $l->translate('add_to_playlists'), 'value' => 'music-song/add-to-playlist', 'show' => '!is_pending'],
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
            ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
            ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
        ];

        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 3, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 4, 0, [['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 5, 0, [['label' => $l->translate('sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => '!is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
            array_splice($actionMenu, 6, 0, [['label' => $l->translate('remove_sponsor_in_feed'), 'value' => Screen::ACTION_SPONSOR_IN_FEED, 'show' => 'is_sponsored_feed&&!is_pending', 'acl' => 'can_sponsor_in_feed']]);
        }

        return self::createSettingForResource([
            'acl'                  => $permission,
            'schema'               => [
                'definition' => [
                    'genres' => 'music_genre[]'
                ],
            ],
            'resource_name'        => $this->getResourceName(),
            'forms'                => [
                'addItem'        => [
                    'apiUrl'      => UrlUtility::makeApiUrl('music-song/form'),
                    'headerTitle' => $l->translate('add_songs'),
                ],
                'editItem'       => [
                    'apiUrl'      => UrlUtility::makeApiUrl('music-song/form/:id'),
                    'headerTitle' => $l->translate('update_song'),
                ],
                'addToPlaylists' => [
                    'apiUrl'      => UrlUtility::makeApiUrl('music-playlist/song-form/:id'),
                    'headerTitle' => $l->translate('add_to_playlist'),
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'music_song'
                    ]
                ],
                'sponsorInFeed' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'music_song',
                        'is_sponsor_feed' => 1
                    ]
                ],
            ],
            'search_input'         => [
                'placeholder' => $l->translate('search_songs'),
            ],
            'detail_view'          => [
                'component_name' => 'music_song_detail',
            ],
            'list_view'            => [
                'item_view'       => 'music_song',
                'noItemMessage'   => [
                    'image' => $this->getAppImage(),
                    'label' => $l->translate('no_songs_found'),
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
            ],
            'manage_playlist_song' => [
                'item_view'     => 'music_playlist_song_item',
                'noItemMessage' => $l->translate('no_songs_found'),
            ],
            'app_menu'             => [
                ['label' => $l->translate('all_songs'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_songs'), 'params' => ['initialQuery' => ['view' => 'my']]],
                ['label' => $l->translate('friends_songs'), 'params' => ['initialQuery' => ['view' => 'friend']]],
                ['label' => $l->translate('pending_songs'), 'params' => ['initialQuery' => ['view' => 'pending']], 'acl' => 'can_approve'],
            ],
            'action_menu'          => $actionMenu,
            'moderation_menu'      => [
                ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEMS, 'style' => 'primary', 'show' => 'view==pending', 'acl' => 'can_approve'],
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    public function getShortFields()
    {
        return [
            'id', 'resource_name', 'title', 'creation_date', 'image', 'song_path', 'item_id', 'module_id', 'view_id',
            'is_featured', 'is_sponsor', 'is_liked', 'statistic', 'user', 'extra', 'genres', 'is_pending'
        ];
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::isAppActive('Core_BetterAds') && Phpfox::getService('music.album')->canPurchaseSponsorItem($this->getId(), 'music_song', 'music_song', 'song_id');
        }
        return $this->canPurchaseSponsor;
    }

    public function getCanSponsorInFeed()
    {
        if ($this->canSponsorInFeed === null) {
            $this->canSponsorInFeed = Phpfox::isModule('feed') && Phpfox::getService('feed')->canSponsoredInFeed('music_song', $this->getId());
        }
        return $this->canSponsorInFeed;
    }

    public function getIsSponsoredFeed()
    {
        if ($this->is_sponsored_feed === null) {
            $this->is_sponsored_feed = Phpfox::isModule('feed') && is_numeric(Phpfox::getService('feed')->canSponsoredInFeed('music_song', $this->getId()));
        }
        return $this->is_sponsored_feed;
    }

    public function getUrlMapping($url, $queryArray)
    {
        preg_match('/\/(genre|browse)\/(\d+|playlist|album)?[\/?]?/', $url, $match);
        if (Phpfox::isModule('music') && (isset($match[1]) || isset($queryArray['view']))) {
            $name = '';
            $resourceName = $this->resource_name;
            $categoryId = 0;
            $view = isset($queryArray['view']) ? $queryArray['view'] : '';
            if (!empty($match[1]) && isset($match[2])) {
                switch ($match[1]) {
                    case 'genre':
                        $categoryId = $match[2];
                        $category = Phpfox::getService('music.genre')->getGenre($match[2]);
                        $name = isset($category['name']) ? $this->getLocalization()->translate($category['name']) : '';
                        break;
                    case 'browse':
                        if (!is_numeric($match[2])) {
                            $resourceName = $match[2] == 'playlist' ? MusicPlaylistResource::populate([])->getResourceName()  : MusicAlbumResource::populate([])->getResourceName();
                            $view = in_array($view, ['my-playlist', 'my-album'])  ? 'my' : $view;
                        }
                        break;
                }
            }
            return [
                'routeName' => 'module/home',
                'params'    => [
                    'module_name'   => 'music',
                    'resource_name' => $resourceName,
                    'header_title'  => $name,
                    'filter_title'  => $name,
                    'query'         => [
                        'genre' => (int)$categoryId,
                        'q'        => isset($queryArray['search']['search']) ? $queryArray['search']['search'] : '',
                        'view'     => $view
                    ]
                ]
            ];
        }
        if (preg_match('/music\/(\d+)\//', $url, $match)) {
            return [
                'routeName' => 'viewItemDetail',
                'params'    => [
                    'id'            => (int)$match[1],
                    'module_name'   => 'music',
                    'resource_name' => $this->resource_name
                ]
            ];
        }
        return null;
    }
}