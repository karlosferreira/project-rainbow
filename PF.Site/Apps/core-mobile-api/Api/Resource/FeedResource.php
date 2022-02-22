<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 2/5/18
 * Time: 11:56 AM
 */

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Adapter\MobileApp\Screen;
use Apps\Core_MobileApi\Adapter\Parse\ParseInterface;
use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Exception\UndefinedResourceName;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Apps\Core_MobileApi\Api\Resource\Object\Statistic;
use Apps\Core_MobileApi\Api\Security\Comment\CommentAccessControl;
use Apps\Core_MobileApi\Service\CommentApi;
use Phpfox;

class FeedResource extends ResourceBase
{

    const RESOURCE_NAME = "feed";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'feed'; // pages, groups, event in some case.


    public $like_type_id;
    public $comment_type_id;

    protected $can_like;
    protected $can_comment;
    protected $can_share;
    protected $can_report;
    protected $can_edit;
    protected $can_delete;
    protected $can_hide;
    protected $can_hide_all;
    protected $can_remove_tag;

    public $item_type;
    public $item_id;
    public $comment_privacy;
    public $info;
    public $status;
    protected $title;
    public $is_liked;

    public $invisible;

    public $tagged_friends;

    public $total_friends_tagged;

    public $location;

    public $sponsor_id;

    public $click_ref;

    /**
     * @var UserResource who created the activity feed
     */
    public $user;

    /**
     * @var UserResource parent user/pages or groups
     */
    public $parent_user;

    /**
     * @var Statistic
     */
    public $statistic;

    /**
     * @var array Related resource the belong to the activity feed
     */
    public $embed_object;

    /**
     * @var
     */
    public $privacy;

    public $like_phrase;

    public $is_shared_feed;

    public $related_comments;

    public $is_hidden;
    public $is_hidden_all;
    public $is_just_hide = false;
    public $is_just_remove_tag = false;

    private static $standardEmbedFields = [
        'resource_name', 'module_name', 'id', 'title', 'description', 'image', 'link', 'creation_date'
    ];

    public function getLink()
    {
        if (isset($this->rawData['feed_link'])) {
            $this->link = $this->rawData['feed_link'];
        }
        return $this->link;
    }

    /**
     * @param array $embed_object
     *
     * @return FeedResource
     */
    public function setEmbedObject($embed_object)
    {
        $this->embed_object = $embed_object;
        return $this;
    }

    /**
     * @param string $itemType
     *
     * @return $this
     */
    public function setItemMenus($itemType = '', $profileId = null)
    {
        $feed = $this->rawData;
        $isParentAdmin = (Phpfox::hasCallback($itemType, 'isAdmin') && \Phpfox::callback($itemType . '.isAdmin', $feed['parent_user_id']));
        if ((
                in_array($feed['type_id'], ['user_status', 'link', 'photo', 'v']) && (empty($feed['custom_data_cache']['module_id']) || !in_array($feed['custom_data_cache']['module_id'], ['pages', 'groups']) || ($itemType == $feed['custom_data_cache']['module_id']))
                && ((Phpfox::getUserParam('feed.can_edit_own_user_status') && $feed['user_id'] == Phpfox::getUserId())
                    || Phpfox::getUserParam('feed.can_edit_other_user_status') || $isParentAdmin)
            )
            || (
                ($feed['type_id'] == "feed_comment" || (strpos($feed['type_id'], '_comment') !== false && $itemType))
                && ($feed['user_id'] == Phpfox::getUserId() || Phpfox::isAdmin() || $isParentAdmin)
            )
        ) {
            $this->can_edit = true;
        }
        $noShowReport = ['friend', 'poke'];
        $aParts = explode('_', $feed['type_id']);
        if (Phpfox::isModule('report')
            && !in_array($feed['type_id'], $noShowReport) && Phpfox::hasCallback($aParts[0], 'getReportRedirect' . (isset($aParts[1]) ? ucfirst($aParts[1]) : ''))
            && $feed['user_id'] != Phpfox::getUserId()
            && !Phpfox::getService('user.block')->isBlocked(null, $feed['user_id'])) {
            $this->can_report = true;
        }
        if ((Phpfox::getUserParam('feed.can_delete_own_feed') && $feed['user_id'] == Phpfox::getUserId())
            || Phpfox::getUserParam('feed.can_delete_other_feeds')
            || (!defined('PHPFOX_IS_PAGES_VIEW')
                && isset($feed['parent_user_id'])
                && (int)$feed['parent_user_id'] == Phpfox::getUserId()) || $isParentAdmin) {
            $this->can_delete = true;
        }
        if ($feed['user_id'] != Phpfox::getUserId() && Phpfox::getParam('feed.enable_hide_feed', true)) {
            $this->can_hide = true;
            if (Phpfox::getUserBy('profile_page_id') == 0) {
                $this->can_hide_all = true;
            }
        }
        (($sPlugin = \Phpfox_Plugin::get('mobile.api_resource_feed_resource_set_item_menus_end')) ? eval($sPlugin) : false);

        return $this;
    }

    /**
     * @return mixed
     */
    public function getInfo()
    {
        if (empty($this->info)) {
            if (isset($this->rawData['feed_info'])) {
                $this->info = $this->rawData['feed_info'];
            } else if (!empty($this->rawData['parent_module_id']) && !empty($this->rawData['parent_feed_id'])) {
                $this->is_shared_feed = true;
                $this->info = $this->getLocalization()->translate('shared');
            }
        }
        return html_entity_decode($this->info, ENT_QUOTES);
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        if ($this->status === null && isset($this->rawData['feed_status'])) {
            $this->status = $this->parse->feedStrip($this->rawData['feed_status']);
        }
        $this->status = str_replace('+', '[PLUS_SIGN]', $this->status);
        $this->status = urldecode($this->parse->parseTwaEmoji($this->status));
        $this->status = $this->parseMention($this->status);
        $this->status = html_entity_decode(html_entity_decode($this->status, ENT_QUOTES), ENT_QUOTES);
        return str_replace('[PLUS_SIGN]', '+', $this->status);
    }

    /**
     * @return FeedResource
     */
    public function getTitle()
    {
        if ($this->title === null && isset($this->rawData['feed_title'])) {
            $this->title = $this->parse->cleanOutput($this->rawData['feed_title']);
        }
        return $this->title;
    }

    /**
     * @return string
     */
    public function getItemType()
    {
        if ((empty($this->item_type) || is_numeric($this->item_type)) && !empty($this->rawData['type_id'])) {
            $this->item_type = $this->rawData['type_id'];
        }
        return $this->item_type;
    }

    public function getItemId()
    {
        if (in_array($this->like_type_id, [
            'pages_liked', 'pages_created', 'groups_liked', 'groups_created'
        ])) {
            return $this->id;
        }
        if (!empty($this->rawData['like_item_id'])) {
            $this->item_id = $this->rawData['like_item_id'];
        }
        return (int)$this->item_id;
    }

    public function getIsLiked()
    {
        return !empty($this->rawData['feed_is_liked']);
    }

    public function getLikePhrase()
    {
        return !empty($this->rawData['feed_like_phrase']) ? html_entity_decode($this->rawData['feed_like_phrase'], ENT_QUOTES) : null;
    }

    public function getLikes()
    {
        if (!empty($this->likes)) {
            $likes = [];
            foreach ($this->likes as $like) {
                $likes[] = LikeResource::populate($like)->toArray();
            }
            return $likes;
        }
        return null;
    }

    /**
     * @return mixed
     * @throws UndefinedResourceName
     */
    public function getExtra()
    {
        if ($this->can_comment) {
            $commentAccessControl = new CommentAccessControl(Phpfox::getService(SettingInterface::class), UserResource::populate(Phpfox::getService("user")->get(Phpfox::getUserId())));
            $commentAccessControl->setParameters([
                'item_type' => $this->item_type,
                'item_id'   => $this->item_id
            ]);
            $this->can_comment = $commentAccessControl->isGranted(CommentAccessControl::ADD);
        }

        $this->extra = [
            'can_like'     => $this->can_like,
            'can_comment'  => $this->can_comment,
            'can_share'    => $this->can_share,
            'can_report'   => !!$this->can_report,
            'can_edit'     => !!$this->can_edit,
            'can_delete'   => !!$this->can_delete,
            'can_hide'     => !!$this->can_hide,
            'can_hide_all' => !!$this->can_hide_all,
            'can_remove_tag' => !!$this->can_remove_tag
        ];
        return $this->extra;
    }

    /**
     * @return Statistic
     */
    public function getStatistic()
    {
        $this->statistic->total_like = (int)$this->rawData['feed_total_like'];
        return $this->statistic;
    }

    public function getTaggedFriends()
    {
        if (!empty($this->rawData['friends_tagged'])) {
            $friends = [];
            foreach ($this->rawData['friends_tagged'] as $friend) {
                $friends[] = UserResource::populate($friend)->displayShortFields()->toArray();
            }
            return $friends;
        }
        return null;
    }

    public function getLocation()
    {
        if (empty($this->location)) {
            $data = [];
            if (!empty($this->rawData['location_name'])) {
                $data['address'] = $this->parse->cleanOutput($this->rawData['location_name']);
            }
            if (!empty($this->rawData['location_latlng'])) {
                $data['lat'] = $this->rawData['location_latlng']['latitude'];
                $data['lng'] = $this->rawData['location_latlng']['longitude'];
            }

            $this->location = (!empty($data) ? $data : null);
        }

        return $this->location;
    }

    /**
     *
     * Output embed object for feed item.
     *
     * @return array
     */
    public function getEmbedObject()
    {
        if ($this->embed_object === null) {
            return $this->formatFeedEmbedObject();
        }
        return $this->embed_object;
    }

    /**
     * @return UserResource|array|null
     * @throws UndefinedResourceName
     */
    public function getParentUser()
    {
        if (!empty($this->rawData['parent_user'])) {
            $profilePageId = !empty($this->rawData['parent_user']['parent_profile_page_id']) ? $this->rawData['parent_user']['parent_profile_page_id'] : 0;
            if ($profilePageId) {
                $serverId = isset($this->rawData['parent_user']['user_parent_server_id']) ? $this->rawData['parent_user']['user_parent_server_id'] : 0;
            } else {
                $serverId = isset($this->rawData['parent_user']['parent_user_server_id']) ? $this->rawData['parent_user']['parent_user_server_id'] : 0;
            }
            $user = UserResource::populate([
                'full_name'       => isset($this->rawData['parent_user']['parent_full_name']) ? $this->rawData['parent_user']['parent_full_name'] : '',
                'user_name'       => isset($this->rawData['parent_user']['parent_user_name']) ? $this->rawData['parent_user']['parent_user_name'] : '',
                'server_id'       => $serverId,
                'profile_page_id' => $profilePageId,
                'user_id'         => isset($this->rawData['parent_user']['parent_user_id']) ? $this->rawData['parent_user']['parent_user_id'] : '',
                'user_image'      => isset($this->rawData['parent_user']['parent_user_image']) ? $this->rawData['parent_user']['parent_user_image'] : '',
            ]);
            $type = '';
            if ($profilePageId) {
                $type = Phpfox::getLib('pages.facade')->getPageItemType($profilePageId);
            }
            $this->parent_user = [
                'resource_name' => !$type ? UserResource::RESOURCE_NAME : $type,
                'module_name'   => !$type ? UserResource::RESOURCE_NAME : $type,
                'user_name'     => $user->getUserName(),
                'full_name'     => $user->getFullName(),
                'id'            => intval($profilePageId ? $profilePageId : $this->rawData['parent_user']['parent_user_id']),
            ];
            if ($type && empty($user->rawData['user_image'])) {
                $this->parent_user['avatar'] = $type == 'pages' ? PageResource::populate([])->getDefaultImage() : GroupResource::populate([])->getDefaultImage();
            } else if ($avatar = $user->getAvatar()) {
                $this->parent_user['avatar'] = $avatar;
            }
        } else {
            $this->parent_user = null;
        }
        return $this->parent_user;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('comment_type_id', ['type' => ResourceMetadata::STRING])
            ->mapField('can_comment', ['type' => ResourceMetadata::BOOL])
            ->mapField('can_like', ['type' => ResourceMetadata::BOOL])
            ->mapField('can_share', ['type' => ResourceMetadata::BOOL])
            ->mapField('like_type_id', ['type' => ResourceMetadata::STRING])
            ->mapField('item_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('total_friends_tagged', ['type' => ResourceMetadata::INTEGER])
            ->mapField('is_liked', ['type' => ResourceMetadata::BOOL]);
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        if (empty($params['versionName']) || in_array($params['versionName'], ['mobile', 'v1.4'])) {
            $actionMenu = [
                ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
                ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_USER_STATUS, 'style' => '', 'acl' => 'can_edit'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete']
            ];
        } else {
            $actionMenu = [
                ['label' => $l->translate('report'), 'value' => Screen::ACTION_REPORT_ITEM, 'show' => '!is_owner', 'acl' => 'can_report',],
                ['label' => $l->translate('hide'), 'value' => 'feed/hide-feed', 'style' => '', 'acl' => 'can_hide', 'show' => 'query=={}'],
                ['label' => $l->translate('hide_all_from_this_owner'), 'value' => 'feed/hide-all', 'style' => '', 'acl' => 'can_hide_all', 'show' => 'query=={}'],
                ['label' => $l->translate('edit'), 'value' => Screen::ACTION_EDIT_USER_STATUS, 'style' => '', 'acl' => 'can_edit'],
                ['label' => $l->translate('delete'), 'value' => Screen::ACTION_DELETE_ITEM, 'style' => 'danger', 'acl' => 'can_delete']
            ];
            if (class_exists('Feed_Service_Tag')) {
                array_splice($actionMenu, 3, 0, [['label' => $l->translate('remove_tag_upper'), 'value' => 'feed/remove-tag', 'style' => '', 'acl' => 'can_remove_tag']]);
            }
        }
        return self::createSettingForResource([
            'resource_name' => $this->resource_name,
            'base.urls'     => 'mobile/feed',
            'detail_view'   => ['component_name' => 'feed_detail'],
            'fab_buttons'   => false,
            'list_view'     => [
                'layout'    => Screen::LAYOUT_LIST_CARD_VIEW,
                'item_view' => 'feed',
                'apiUrl'    => 'mobile/feed',
                'limit'     => 20,
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'noItemMessage'   => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
            ],
            'forms'         => [
                'shareWall'   => [
                    'apiUrl'      => 'mobile/feed/share/form',
                    'headerTitle' => $l->translate('share_on_your_wall'),
                    'succeedAction' => '@app/SHARE_ITEM/SUCCESS'
                ],
                'shareFriend' => [
                    'apiUrl'      => 'mobile/feed/share/form',
                    'headerTitle' => $l->translate('share_on_friend_s_wall'),
                    'succeedAction' => '@app/SHARE_ITEM/SUCCESS'
                ]
            ],
            'settings'         => [
                'datetime_format' => Phpfox::getParam('feed.feed_display_time_stamp')
            ],
            'action_menu'   => $actionMenu
        ]);
    }

    private function formatFeedEmbedObject()
    {
        if (!empty($this->rawData['custom_data_cache'])) {
            $result = [];
            foreach (self::$standardEmbedFields as $field) {
                if (!empty($this->rawData['custom_data_cache'][$field])) {
                    $result[$field] = $this->rawData['custom_data_cache'][$field];
                }
            }
            return $result;
        }
        return null;
    }

    public function getUser()
    {
        if ($this->rawData['profile_page_id']) {
            $type = Phpfox::getLib('pages.facade')->getPageItemType($this->rawData['profile_page_id']);
            $item = db()->select('is_featured, is_sponsor')->from(':pages')->where(['page_id' => (int)$this->rawData['profile_page_id']])->executeRow();
            $owner = [
                'resource_name' => $type == 'groups' ? GroupResource::RESOURCE_NAME : PageResource::RESOURCE_NAME,
                'module_name'   => $type == 'groups' ? GroupResource::RESOURCE_NAME : PageResource::RESOURCE_NAME,
                'full_name'     => $this->parse->cleanOutput($this->rawData['full_name']),
                'id'            => (int)$this->rawData['profile_page_id'],
                'is_featured'   => isset($item['is_featured']) ? (bool)$item['is_featured'] : false,
                'is_sponsor'    => isset($item['is_sponsor']) ? (bool)$item['is_sponsor'] : false
            ];
            $avatar = Image::createFrom([
                'user' => $this->rawData,
            ], ["50_square"]);
            if (!empty($avatar)) {
                $owner['avatar'] = $avatar->sizes['50_square'];
            } else {
                $owner['avatar'] = $type == 'groups' ? GroupResource::populate([])->getDefaultImage() : PageResource::populate([])->getDefaultImage();
            }
            return $owner;
        } else {
            return UserResource::populate($this->rawData)->toArray(['full_name', 'id', 'resource_name', 'avatar', 'is_featured', 'user_name', 'is_blocked']);
        }
    }

    public function getModificationDate()
    {
        return null;
    }

    public function getShortFields()
    {
        return ['resource_name', 'module_name', 'like_type_id', 'comment_type_id', 'item_type', 'item_id', 'user', 'embed_object', 'privacy', 'creation_date', 'info', 'status', 'location', 'total_friends_tagged', 'tagged_friends', 'parent_user'];
    }

    public function getRelatedComments()
    {
        if (isset($this->comment_type_id) && $this->item_id) {
            $this->related_comments = (new CommentApi())->getRelatedComment($this->comment_type_id, $this->item_id);
        }
        return $this->related_comments;
    }

    public function getIsHidden()
    {
        if ($this->is_hidden === null && Phpfox::getService('feed.hide') !== null) {
            $this->is_hidden = Phpfox::getService('feed.hide')->isHidden(Phpfox::getUserId(), $this->getId(), 'feed');
        }
        return $this->is_hidden && empty($this->rawData['sponsor_id']);
    }

    public function getIsHiddenAll()
    {
        if ($this->is_hidden_all === null && Phpfox::getService('feed.hide') !== null) {
            $this->is_hidden_all = Phpfox::getService('feed.hide')->isHidden(Phpfox::getUserId(), $this->user->getId(), 'user');
        }
        return $this->is_hidden_all && empty($this->rawData['sponsor_id']);
    }

    protected function parseMention($text)
    {
        // Parse groups/pages mentions
        if (Phpfox::isModule('groups')) {
            $text = preg_replace_callback('/\[group=(\d+)\].+?\[\/group\]/u', function ($matches) {
                return Phpfox::getService(ParseInterface::class)->parseGroupMention($matches[1]);
            }, $text);
        }
        if (Phpfox::isModule('pages')) {
            $text = preg_replace_callback('/\[page=(\d+)\].+?\[\/page\]/u', function ($matches) {
                return Phpfox::getService(ParseInterface::class)->parsePageMention($matches[1]);
            }, $text);
        }
        return $text;
    }

    public function getUrlMapping($url, $queryArray)
    {
        $result = $url;
        preg_match('/feed\/(\d+)?/', $result, $match);
        if (isset($queryArray['item_id'])) {
            $queryArray['item_id'] = (int)$queryArray['item_id'];
        }
        if (!empty($match[1])) {
            return [
                'routeName' => 'viewItemDetail',
                'params'    => [
                    'module_name'   => $this->module_name,
                    'resource_name' => $this->resource_name,
                    'id'            => (int)$match[1],
                    'query' => $queryArray
                ]
            ];
        }
        return $result;
    }
}