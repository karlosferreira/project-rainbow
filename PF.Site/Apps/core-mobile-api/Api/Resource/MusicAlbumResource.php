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

class MusicAlbumResource extends ResourceBase
{
    const RESOURCE_NAME = "music-album";
    public $resource_name = self::RESOURCE_NAME;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "album_id";

    public $name;

    public $description;
    public $text;
    public $year;

    public $image;

    public $view_id;
    public $is_featured;
    public $is_sponsor;
    public $is_liked;
    public $is_friend;
    public $is_pending;

    public $module_id;
    public $item_id;
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
    public $tags;

    /**
     * @var AttachmentResource[]
     */
    public $attachments = [];
    protected $canPurchaseSponsor = null;

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
        return Phpfox::permalink('music.album', $this->id, $this->name);
    }


    public function getTags()
    {
        if (!Phpfox::isModule('tag')) {
            return null;
        }
        $tag = Phpfox::getService('tag')->getTagsById('music_album', $this->id);
        if (!empty($tag[$this->id])) {
            return $tag[$this->id];
        }
        return null;
    }

    public function getText()
    {
        if ($this->text === null && isset($this->rawData['text'])) {
            $this->text = $this->rawData['text'];
        }
        TextFilter::pureHtml($this->text, true);
        return $this->text;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        if ($this->description === null && isset($this->rawData['text'])) {
            $this->description = $this->rawData['text'];
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

    public function setStatistic($statistic)
    {
        $statistic->total_track = isset($this->rawData['total_track']) ? (int)$this->rawData['total_track'] : 0;
        $statistic->total_play = isset($this->rawData['total_play']) ? (int)$this->rawData['total_play'] : 0;
        $statistic->total_score = isset($this->rawData['total_score']) ? (int)$this->rawData['total_score'] : 0;
        $statistic->total_rating = isset($this->rawData['total_rating']) ? (int)$this->rawData['total_rating'] : 0;
        $this->statistic = $statistic;
    }

    public function getSongs()
    {
        if (!empty($this->rawData['is_detail']) && $this->getAccessControl()) {
            $songs = Phpfox::getService('music.album')->getTracks($this->rawData['user_id'], $this->id, false);
            if ($songs) {
                $results = [];
                foreach ($songs as $key => $song) {
                    $song['album_detail'] = true;
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
        return [
            'id', 'image', 'name', 'year', 'statistic', 'user', 'is_featured', 'is_sponsor', 'is_like', 'creation_date', 'privacy',
            'item_id', 'module_id', 'resource_name', 'extra', 'is_pending'
        ];
    }

    public function getAttachments()
    {
        if (isset($this->rawData['total_attachment']) && $this->rawData['total_attachment'] > 0 && !empty($this->rawData['is_detail'])) {
            return NameResource::instance()
                ->getApiServiceByResourceName(AttachmentResource::RESOURCE_NAME)->getAttachmentsBy($this->id, 'music_album');
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
            ->mapField('is_featured', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_sponsor', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_friend', ['type' => ResourceMetadata::BOOL])
            ->mapField('is_pending', ['type' => ResourceMetadata::BOOL])
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getMobileSettings($params = [])
    {
        $permission = NameResource::instance()->getApiServiceByResourceName($this->resource_name)->getAccessControl()->getPermissions();
        $l = $this->getLocalization();
        $actionMenu = [
            ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_ITEM, 'acl' => 'can_edit'],
            ['label' => $l->translate('approve'), 'value' => Screen::ACTION_APPROVE_ITEM, 'show' => 'is_pending', 'acl' => 'can_approve'],
            ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => '!is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_FEATURE_ITEM, 'show' => 'is_featured&&!is_pending', 'acl' => 'can_feature'],
            ['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_sponsor'],
            ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
            ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete'],
        ];

        if (isset($params['versionName']) && $params['versionName'] != 'mobile' && version_compare($params['versionName'], 'v1.7', '>=')) { // for mobile version >= 1.7
            array_splice($actionMenu, 3, 0, [['label' => $l->translate('sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => '!is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
            array_splice($actionMenu, 4, 0, [['label' => $l->translate('remove_sponsor'), 'value' => Screen::ACTION_PURCHASE_SPONSOR_ITEM, 'show' => 'is_sponsor&&!is_pending', 'acl' => 'can_purchase_sponsor']]);
        }
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
                    'apiUrl'      => UrlUtility::makeApiUrl('music-album/form'),
                    'headerTitle' => $l->translate('share_songs'),
                ],
                'editItem' => [
                    'apiUrl'      => UrlUtility::makeApiUrl('music-album/form/:id'),
                    'headerTitle' => $l->translate('update_album'),
                ],
                'purchaseSponsorItem' => [
                    'apiUrl' => UrlUtility::makeApiUrl('ad/form'),
                    'headerTitle' => $l->translate('sponsor_item'),
                    'use_query' => [
                        'section' => 'music_album'
                    ]
                ],
            ],
            'search_input'    => [
                'placeholder' => $l->translate('search_albums'),
            ],
            'detail_view'     => [
                'component_name' => 'music_album_detail',
            ],
            'list_view'       => [
                'noItemMessage'   => [
                    'image'     => $this->getAppImage(),
                    'label'     => $l->translate('no_albums_found')
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'numColumns'      => 2,
                'layout'          => Screen::LAYOUT_GRID_VIEW,
                'item_view'       => 'music_album',
            ],
            'app_menu'        => [
                ['label' => $l->translate('all_albums'), 'params' => ['initialQuery' => ['view' => '']]],
                ['label' => $l->translate('my_albums'), 'params' => ['initialQuery' => ['view' => 'my']]],
            ],
            'action_menu' => $actionMenu,
            'moderation_menu' => [
                ['label' => $l->translate('feature'), 'value' => Screen::ACTION_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('remove_feature'), 'value' => Screen::ACTION_REMOVE_FEATURE_ITEMS, 'style' => 'primary', 'show' => 'view!=pending', 'acl' => 'can_feature'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEMS, 'style' => 'danger', 'acl' => 'can_delete'],
            ]
        ]);
    }

    public function getFeedDisplay()
    {
        $this->setDisplayFields(['id', 'resource_name', 'name', 'description', 'image', 'privacy', 'year']);
        $embed = $this->toArray();
        $embed['total_play'] = isset($this->rawData['total_play']) ? (int)$this->rawData['total_play'] : 0;
        $embed['total_track'] = isset($this->rawData['total_track']) ? (int)$this->rawData['total_track'] : 0;
        $embed['total_view'] = isset($this->rawData['total_view']) ? (int)$this->rawData['total_view'] : 0;
        return $embed;
    }

    public function getIsPending()
    {
        $this->is_pending = !!$this->view_id;
        return $this->is_pending;
    }

    public function getCanPurchaseSponsor()
    {
        if ($this->canPurchaseSponsor === null) {
            $this->canPurchaseSponsor = Phpfox::isAppActive('Core_BetterAds') && Phpfox::getService('music.album')->canPurchaseSponsorItem($this->getId(), 'music_album', 'music_album', 'album_id');
        }
        return $this->canPurchaseSponsor;
    }
}