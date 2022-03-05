<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Adapter\MobileApp\ScreenSetting;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Form\Feed\FeedPostForm;
use Apps\Core_MobileApi\Api\Form\Feed\ShareFeedForm;
use Apps\Core_MobileApi\Api\Form\Type\PrivacyType;
use Apps\Core_MobileApi\Api\Resource\FeedHiddenResource;
use Apps\Core_MobileApi\Api\Resource\FeedResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Request;

class FeedApi extends AbstractResourceApi implements MobileAppSettingInterface
{

    public function __naming()
    {
        return [
            'feed/edit/:id'            => [
                "get"   => "getStatusForEdit",
                "where" => [
                    "id" => "(\d+)",
                ]
            ],
            'feed/share/form'          => [
                'get' => 'formShare'
            ],
            'feed/share'               => [
                'post' => 'shareFeed'
            ],
            'feed/:item_type/:item_id' => [
                "maps"  => [
                    "get" => "findAll",
                ],
                "where" => [
                    'item_id'   => "(\d+)",
                    'item_type' => "([A-Za-z\/]+)"
                ]
            ],
            'feed/post-type'           => [
                'get' => 'getPostTypes',
            ],
            'feed/tagged-friend'       => [
                'get' => 'getTaggedFriends'
            ],
            'feed/hide-feed/:id'       => [
                'post'   => 'hideOneFeed',
                'delete' => 'unHideOneFeed'
            ],
            'feed/hide-all/:id'        => [
                'post'   => 'hideAllFromObject',
                'delete' => 'unHideObject'
            ],
            'feed/manage-hidden'       => [
                'get' => 'getManageHidden'
            ],
            'feed/manage-hidden/:id'   => [
                'get'    => 'getManageHidden',
                'delete' => 'deleteHide'
            ],
            'feed/remove-tag' => [
                'post' => 'removeTag'
            ]
        ];
    }

    /**
     * @param $aRows array of item to process
     * @param string $apiVersion
     */
    public function processRows(&$aRows, $apiVersion = 'mobile')
    {
        foreach ($aRows as $key => $aRow) {
            $item = $this->processRow($aRow, $apiVersion);
            $aRows[$key] = $item->toArray();
        }
    }

    /**
     *
     * Process feed item
     *
     * @param array $feed
     * @param string $apiVersion
     * @return FeedResource
     */
    public function processRow($feed, $apiVersion = 'mobile')
    {
        /** @var FeedResource $resource */
        $resource = $this->populateResource(FeedResource::class, $feed);
        $resource->setEmbedObject($this->feedAttachmentHelper->forListing($feed, $resource, $apiVersion));
        $resource->setItemMenus($this->request()->get('item_type'), $this->request()->get('user_id'));
        if (empty($feed['is_detail'])) {
            $resource->setViewMode(ResourceBase::VIEW_LIST);
        }
        return $resource;
    }

    /**
     * @description: handle the job to return data of an item
     *
     * @param array  $aItem
     * @param string $sReturnMode
     * @param array  $fields
     *
     * @return array
     */
    public function getItem($aItem, $sReturnMode = 'public', $fields = [])
    {
        return $aItem;
    }

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $params = $this->resolver
            ->setDefined(['user_id', 'limit', 'page', 'item_type', 'item_id', 'hashtag', 'last_feed_id', 'api_version_name'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1,
                'api_version_name' => 'mobile'
            ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('user_id', 'int', ['min' => 1])
            ->setAllowedTypes('page', 'int', ['min' => 1])
            ->setAllowedTypes('item_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $feedConds = [
            'user_id' => $params['user_id'],
            'limit'   => $params['limit'],
            'page'    => ($params['page'] - 1)
        ];
        // Support hashtag searching
        if (!empty($params['hashtag'])) {
            \Phpfox_Request::instance()->set('hashtagsearch', $params['hashtag']);
        }

        $sponsoredFeed = [];
        $bHasSponsor = false;
        $iSponsorFeedId = 0;
        $iLastFeedId = null;

        (($sPlugin = Phpfox_Plugin::get('mobile.feed_api_find_all_start')) ? eval($sPlugin) : false);

        if ($params['page'] == 1 && empty($params['hashtag'])
            && empty($params['item_type']) && empty($params['item_id']) && empty($params['user_id'])
            && Phpfox::isAppActive('Core_BetterAds') && Phpfox::getParam('ad.multi_ad')) {
            $iAd = Phpfox::getService('ad')->getSponsoredFeed();
            //Load sponsor feed here
            if ($iAd != false) {
                $sponsoredFeed = $this->getFeedService()->get(null, $iAd, 0, false, true, null, $iAd);
                if (isset($sponsoredFeed[0])) {
                    $bHasSponsor = true;
                    $iSponsorFeedId = $sponsoredFeed[0]['feed_id'];
                    $sponsoredFeed[0]['click_ref'] = '@view_sponsor_item';
                    $sponsoredFeed[0]['sponsor_id'] = Phpfox::getService('ad.get')->getFeedSponsors($sponsoredFeed[0]['feed_id']);
                }
            }
        }

        if (empty($params['item_type']) || empty($params['item_id'])) {
            defined('PHPFOX_CHECK_FEEDS_FOR_PAGES') || define('PHPFOX_CHECK_FEEDS_FOR_PAGES', true);
            defined('PHPFOX_CHECK_FEEDS_FOR_GROUPS') || define('PHPFOX_CHECK_FEEDS_FOR_GROUPS', true);
        }

        if ($params['user_id']) {
            if (!Phpfox::getService('user')->isUser($params['user_id'], true)) {
                return $this->error($this->getLocalization()->translate('the_item_cannot_be_found', ['item' => $this->getLocalization()->translate('user__l')]));
            }

            if (Phpfox::getService('user.block')->isBlocked(null, $params['user_id'])
                || !Phpfox::getService('user.privacy')->hasAccess($params['user_id'], 'feed.view_wall')
                || !Phpfox::getService('user.privacy')->hasAccess($params['user_id'], 'profile.view_profile')
            ) {
                return $this->success([]);
            }

            if (empty($aCallback)) {
                $feedConds['include_friend_feeds'] = true;
            }
            defined('PHPFOX_IS_USER_PROFILE') or define('PHPFOX_IS_USER_PROFILE', true);
            Phpfox::getService('user')->get($params['user_id']);
        } else if ($params['item_type'] && $params['item_id']) {

            if (!defined('PHPFOX_REQUEST_FEED_ITEM_TYPE')) {
                define('PHPFOX_REQUEST_FEED_ITEM_TYPE', $params['item_type']);
            }
            if (in_array($params['item_type'], ['pages', 'groups'])) {
                defined('PHPFOX_IS_PAGES_VIEW') or define('PHPFOX_IS_PAGES_VIEW', true);
            }
            if (Phpfox::hasCallback($params['item_type'], 'canGetFeeds') && !Phpfox::callback($params['item_type'] . '.canGetFeeds', $params['item_id'])) {
                return $this->success([]);
            }
            if (Phpfox::hasCallback($params['item_type'], 'getFeedDisplay') == false) {
                return $this->error('Invalid module callback ' . $params['item_type'] . '.getFeedDisplay');
            }
            $callbackModule = Phpfox::callback($params['item_type'] . '.getFeedDisplay', $params['item_id']);
            if (!$callbackModule) {
                $this->notFoundError('Feed display callback item not found');
            }
            $this->getFeedService()->callback($callbackModule);
        }

        if (!empty($params['last_feed_id'])) {
            $iLastFeedId = $params['last_feed_id'];
        }

        $aRows = $this->getFeedService()->get($feedConds, null, 0, false, true, $iLastFeedId, $iSponsorFeedId);

        // get last feed for next page
        if ($lastFeed = end($aRows)) {
            $iLastFeedId = $lastFeed['feed_id'];
        }

        $aRows = array_merge($sponsoredFeed, $aRows);

        (($sPlugin = Phpfox_Plugin::get('mobile.feed_api_find_all_end')) ? eval($sPlugin) : false);

        $this->processRows($aRows, $params['api_version_name']);

        if ($bHasSponsor) {
            foreach ($aRows as $iKey => $aRow) {
                if ($aRow['id'] == $iSponsorFeedId && $iKey != 0) {
                    unset($aRows[$iKey]);
                }
            }
        }

        return $this->success(array_values($aRows), ['pagination' => ['last_feed_id' => (int)$iLastFeedId]]);
    }

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function findOne($params)
    {
        $params = $this->resolver
            ->setDefined(['item_type', 'item_id', 'api_version_name'])
            ->setRequired(['id'])
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->setDefault(['api_version_name' => 'mobile'])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        if ($params['item_type'] != 'user' && $params['item_type'] && $params['item_id']) {

            if (!defined('PHPFOX_REQUEST_FEED_ITEM_TYPE')) {
                define('PHPFOX_REQUEST_FEED_ITEM_TYPE', $params['item_type']);
            }
            if (in_array($params['item_type'], ['pages', 'groups'])) {
                defined('PHPFOX_IS_PAGES_VIEW') or define('PHPFOX_IS_PAGES_VIEW', true);
            }
            if (Phpfox::hasCallback($params['item_type'], 'canGetFeeds') && !Phpfox::callback($params['item_type'] . '.canGetFeeds', $params['item_id'])) {
                return $this->permissionError();
            }
            if (Phpfox::hasCallback($params['item_type'], 'getFeedDisplay') == false) {
                return $this->error('Invalid module callback ' . $params['item_type'] . '.getFeedDisplay');
            }
            $callbackModule = Phpfox::callback($params['item_type'] . '.getFeedDisplay', $params['item_id']);
            if (!$callbackModule) {
                return $this->notFoundError('Feed display callback item not found');
            }
            $this->getFeedService()->callback($callbackModule);
        }
        //Remove ?id= from request to fix feed type = link
        \Phpfox_Request::instance()->set('id', 0);
        $aRows = $this->getFeedService()->get(null, $params['id']);
        if (empty($aRows)) {
            return $this->notFoundError();
        }
        $feed = $aRows[0];
        //check permission to view parent item
        if (!empty($feed['type_id']) && !empty($feed['item_id'])) {
            if (Phpfox::hasCallback($feed['type_id'], 'canViewItem')) {
                $isErrorPass = Phpfox_Error::isPassed();
                if (!Phpfox::callback($feed['type_id'] . '.canViewItem', $feed['item_id'])) {
                    if ($isErrorPass) {
                        //Reset error set by app's callback, it's useless for API
                        Phpfox_Error::reset();
                    }
                    return $this->permissionError();
                }
            } else {
                $typeId = $feed['type_id'] == 'v' ? 'video' : $feed['type_id'];
                $item = NameResource::instance()->getPermissionByResourceName(str_replace('_', '-', $typeId), $feed['item_id'], AccessControl::VIEW, $params['api_version_name']);
                if ($item !== null && !$item) {
                    return $this->permissionError();
                } else {
                    $error = false;
                    $userId = $this->getUser()->getId();
                    if ($feed['user_id'] != $userId) {
                        switch ($feed['privacy']) {
                            case 1:
                            case 2:
                                $userObject = Phpfox::getService('user')->get($feed['user_id']);
                                if ((isset($userObject['is_friend']) && $userObject['is_friend']) || (isset($userObject['is_reverse_friend']) && $userObject['is_reverse_friend'])) {
                                    break;
                                } else if (isset($userObject['is_friend_of_friend']) && $userObject['is_friend_of_friend'] && $feed['privacy'] == 2) {
                                    break;
                                }
                                $error = true;
                                break;
                            case 3:
                                $error = true;
                                break;
                            case 4:
                                // --- Get feeds based on custom friends lists ---
                                if (Phpfox::isUser()) {
                                    if (Phpfox::isModule('privacy')) {
                                        $this->database()->join(Phpfox::getT('privacy'), 'p', 'p.module_id = feed.type_id AND p.item_id = feed.item_id')
                                            ->join(Phpfox::getT('friend_list_data'), 'fld', 'fld.list_id = p.friend_list_id AND fld.friend_user_id = ' . $userId . '');

                                    }
                                    $checkFeedId = $this->database()->select('feed_id')
                                        ->from(Phpfox::getT('feed'), 'feed')
                                        ->where('feed.feed_id = ' . $feed['feed_id'])
                                        ->limit(1)
                                        ->execute('getSlaveField');
                                    if ($checkFeedId) {
                                        break;
                                    }
                                }
                                $error = true;
                                break;
                        }
                    }
                    if ($error) {
                        return $this->permissionError();
                    }
                }
            }
        }
        $feed['is_detail'] = true;
        $item = $this->processRow($feed, $params['api_version_name'])->toArray();

        return $this->success($item);

    }

    /**
     * Post feed
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $form = $this->createForm(FeedPostForm::class);
        $message = '';
        if ($form->isValid() && ($values = $form->getValues())) {
            if (!empty($values['post_as_parent']) && !empty($values['parent_item_type']) && $values['parent_item_type'] == 'pages' && version_compare(Phpfox::getVersion(), "4.7", ">=")) {
                $iPageId = db()->select('p.page_id')->from(':pages', 'p')
                    ->join(':user', 'u', 'u.profile_page_id = p.page_id')
                    ->where([
                        'p.item_type' => 0,
                        'p.page_id' => (int)$values['parent_item_id']
                    ])->executeField();
                // Check exists page.
                if (!$iPageId) {
                    return $this->notFoundError();
                }
                $_REQUEST['custom_pages_post_as_page'] = (int)$iPageId;
            }
            switch ($values['post_type']) {
                case FeedPostForm::STATUS_POST:
                    $id = $this->createUserStatusPost($values);
                    break;
                case FeedPostForm::PHOTO_POST:
                    $id = $this->createPhotoPost($form->getGroupValues("photo"), $values);
                    if ($id) {
                        if ($id !== true) {
                            $message = $this->getLocalization()->translate('photo_successfully_added');
                        } else {
                            $message = $this->getLocalization()->translate('this_item_has_been_successfully_submitted');
                        }
                    }
                    break;
                case FeedPostForm::VIDEO_POST:
                    $id = $this->createVideoPost($form->getGroupValues('video'), $values);
                    switch ($id) {
                        case $id === true:
                            $message = $this->getLocalization()->translate('your_video_has_been_successfully_saved_and_will_be_published_when_we_are_done_processing_it');
                            break;
                        case -1:
                            $message = $this->getLocalization()->translate('video_is_pending_approval');
                            break;
                        case -2:
                        default:
                            $message = $this->getLocalization()->translate('video_successfully_added');
                            break;
                    }
                    break;
                case FeedPostForm::LINK_POST:
                    $id = $this->createLinkPost($form->getGroupValues('link'), $values);
                    break;
            }
            if (isset($_REQUEST['custom_pages_post_as_page'])) {
                unset($_REQUEST['custom_pages_post_as_page']);
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }

        if (!empty($id) && $this->isPassed()) {
            return $this->success(['id' => $id === true || in_array($id, [-1, -2]) ? 0 : (int)$id], [], $message);
        }
        return $this->error($this->getErrorMessage());

    }

    private function createUserStatusPost($values)
    {
        $id = 0;
        if (Phpfox::getLib('parse.format')->isEmpty($values['user_status'])) {
            return $this->error($this->getLocalization()->translate('add_some_text_to_share'));
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_feed_api_create_status_start')) ? eval($sPlugin) : false);

        if (!empty($values['parent_item_type']) && !empty($values['parent_item_id'])) {
            $callback = $this->getPostingCallback($values['parent_item_type'], $values['parent_item_id'], isset($values['post_as_parent']) ? $values['post_as_parent'] : null);
            $values['parent_user_id'] = $values['parent_item_id'];
            $id = $this->getProcessService()->callback($callback)->addComment($values);
        } else if (!empty($values['parent_user_id'])) {
            //Don't need privacy when post on other's wall
            unset($values['privacy']);
            $id = $this->getProcessService()->addComment($values);
        } else if (empty($id)) {
            $id = $this->getUserProcessService()->updateStatus($values);
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_feed_api_create_status_end')) ? eval($sPlugin) : false);

        return $id;
    }

    private function createPhotoPost($photoValues, $params = [])
    {
        //Timeline photo
        $photoValues['type_id'] = 1;
        if (!empty($params['location'])) {
            $photoValues['location'] = $params['location'];
        }

        (($sPlugin = Phpfox_Plugin::get('mobile.service_feed_api_createphotopost_start')) ? eval($sPlugin) : false);

        $this->addExtraParams($photoValues, $params);
        $photoIds = $this->getPhotoApiService()->processCreate($photoValues, true);
        if (empty($photoIds)) {
            return false;
        }
        $iFeedId = $this->parametersBag->get('feed_id');

        (($sPlugin = Phpfox_Plugin::get('mobile.service_feed_api_createphotopost_end')) ? eval($sPlugin) : false);

        return $iFeedId;
    }

    private function createVideoPost($videoValues, $params = [])
    {
        $videoValues['status_info'] = isset($params['user_status']) ? $params['user_status'] : '';

        (($sPlugin = Phpfox_Plugin::get('mobile.service_feed_api_createvideopost_start')) ? eval($sPlugin) : false);

        if (!empty($params['location'])) {
            $videoValues['location_name'] = $params['location']['name'];
            $match = explode(',', $params['location']['latlng']);
            if (isset($match[0]) && isset($match[1])) {
                $videoValues['location_latlng'] = json_encode(['latitude' => floatval($match[0]), 'longitude' => floatval($match[1])]);
            } else {
                $videoValues['location_latlng'] = null;
            }
        }

        $this->addExtraParams($videoValues, $params);
        $id = $this->getVideoApiService()->processCreate($videoValues, true);
        //Get feed
        if (!is_bool($id)) {
            if ($this->getSetting()->getUserSetting('v.pf_video_approve_before_publicly')) {
                //Pending
                return -1;
            }
            if (!$this->getSetting()->getAppSetting('v.pf_video_allow_create_feed_when_add_new_item')) {
                //No feed
                return -2;
            }
            $feedId = $this->getFeedIdByItem($id, 'v', isset($videoValues['module_id']) ? $videoValues['module_id'] : '');
        }

        $iFeedId = !empty($feedId) ? $feedId : $id;

        (($sPlugin = Phpfox_Plugin::get('mobile.service_feed_api_createvideopost_end')) ? eval($sPlugin) : false);

        return $iFeedId;
    }

    private function createLinkPost($linkValues, $params = [])
    {
        $params['link'] = $linkValues;
        if (!empty($params['location'])) {
            $params['location_name'] = $params['location']['name'];
            $params['location_latlng'] = $params['location']['latlng'];
        }

        if (!empty($params['user_status'])) {
            $params['status_info'] = $params['user_status'];
        }
        $this->addExtraParams($params, $params);
        $aCallback = null;
        if (isset($linkValues['module_id']) && Phpfox::hasCallback($linkValues['module_id'], 'addLink')) {
            $params['parent_user_id'] = $linkValues['callback_item_id'] = $linkValues['item_id'];
            $linkValues['callback_module'] = $linkValues['module_id'];
            $aCallback = Phpfox::callback($linkValues['module_id'] . '.addLink', $linkValues);
        }

        // Use $iId or $id for hook, don't change.
        $id = $iId = Phpfox::getService('link.process')->add($params, false, $aCallback);

        //Use for hook, don't remove
        $aVals = array_merge($params, $linkValues);
        (($sPlugin = Phpfox_Plugin::get('link.component_ajax_addviastatusupdate')) ? eval($sPlugin) : false);
        return $iId;
    }

    public function get($param)
    {
        return isset($_REQUEST[$param]) ? $_REQUEST[$param] : null;
    }

    private function addExtraParams(&$values, $params)
    {
        if (!empty($params['privacy']) && empty($params['parent_user_id'])) {
            $values['privacy'] = $params['privacy'];
        }
        if (!empty($params['privacy_list'])) {
            $values['privacy_list'] = $params['privacy_list'];
        }
        if (!empty($params['tagged_friends'])) {
            $values['tagged_friends'] = $params['tagged_friends'];
        }
    }

    /**
     * Get callback info when posting to Pages or group
     *
     * @param      $type
     * @param      $item
     * @param null $posterId
     *
     * @return array|bool|null
     */
    public function getPostingCallback($type, $item, $posterId = null)
    {

        if ($type == "pages") {
            $aPage = Phpfox::getService('pages')->getPage($item);
            if (!isset($aPage['page_id'])) {
                return $this->error($this->getLocalization()->translate("unable_to_find_the_page_you_are_trying_to_comment_on"));
            }
            $sLink = Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
            $aCallback = [
                'module'                => 'pages',
                'table_prefix'          => 'pages_',
                'link'                  => $sLink,
                'email_user_id'         => $aPage['user_id'],
                'subject'               => _p('full_name_wrote_a_comment_on_your_page_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aPage['title']]),
                'message'               => _p('full_name_wrote_a_comment_link',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aPage['title']]),
                'notification'          => ($posterId ? null : 'pages_comment'),
                'notification_post_tag' => 'pages_post_tag',
                'feed_id'               => 'pages_comment',
                'item_id'               => $aPage['page_id'],
                'item_title'            => $aPage['title'],
                'add_tag'               => true
            ];
        } else if ($type == "groups") {
            $aPage = Phpfox::getService('groups')->getPage($item);
            if (!isset($aPage['page_id'])) {
                return $this->error($this->getLocalization()->translate("unable_to_find_the_page_you_are_trying_to_comment_on"));
            }
            $sLink = Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
            $aCallback = [
                'module'                => 'groups',
                'table_prefix'          => 'pages_',
                'link'                  => $sLink,
                'email_user_id'         => $aPage['user_id'],
                'subject'               => _p('full_name_wrote_a_comment_on_your_page_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aPage['title']]),
                'message'               => _p('full_name_wrote_a_comment_link',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aPage['title']]),
                'notification'          => ($posterId ? null : 'groups_comment'),
                'notification_post_tag' => 'groups_post_tag',
                'feed_id'               => 'groups_comment',
                'item_id'               => $aPage['page_id'],
                'item_title'            => $aPage['title'],
                'add_to_main_feed'      => true,
                'add_tag'               => true
            ];
        } else if ($type == "event") {
            $aEvent = Phpfox::getService('event')->getForEdit($item, true);

            if (!isset($aEvent['event_id'])) {
                return $this->error($this->getLocalization()->translate('unable_to_find_the_event_you_are_trying_to_comment_on'));
            }

            $sLink = Phpfox::permalink('event', $aEvent['event_id'], $aEvent['title']);
            $aCallback = [
                'module'                => 'event',
                'table_prefix'          => 'event_',
                'link'                  => $sLink,
                'email_user_id'         => $aEvent['user_id'],
                'subject'               => _p('full_name_wrote_a_comment_on_your_event_title',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'title' => $aEvent['title']]),
                'message'               => _p('full_name_wrote_a_comment_on_your_event_message',
                    ['full_name' => Phpfox::getUserBy('full_name'), 'link' => $sLink, 'title' => $aEvent['title']]),
                'notification'          => 'event_comment',
                'notification_post_tag' => 'event_post_tag',
                'feed_id'               => 'event_comment',
                'item_id'               => $aEvent['event_id']
            ];
        } else {
            // TODO: Extend for other module
            $aCallback = null;
            (($sPlugin = \Phpfox_Plugin::get('mobile.service_feed_api_get_posting_callback')) ? eval($sPlugin) : false);
        }

        return $aCallback;

    }

    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function update($params)
    {
        $id = $this->resolver->resolveId($params);
        /** @var FeedPostForm $form */
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $form = $this->createForm(FeedPostForm::class);
        if ($form->isValid() && ($values = $form->getValues())) {
            switch ($values['post_type']) {
                case FeedPostForm::LINK_POST:
                    $values['feed_id'] = $id;
                    $success = $this->createLinkPost($form->getGroupValues('link'), $values);
                    break;
                case FeedPostForm::VIDEO_POST:
                    list($callback, $success) = $this->processUpdate($id, $values, true);
                    $feed = Phpfox::getService('feed')->getFeed($id, isset($callback['table_prefix']) ? $callback['table_prefix'] : '');
                    if ($feed) {
                        if (empty($values['location'])) {
                            $this->database()->update(Phpfox::getT('video'), ['location_latlng' => '', 'location_name' => ''], ['video_id' => (int)$feed['item_id']]);
                        } else {
                            $match = explode(',', $values['location']['latlng']);
                            $match['latitude'] = floatval($match[0]);
                            $match['longitude'] = floatval($match[1]);
                            $update['location_latlng'] = json_encode([
                                'latitude'  => $match['latitude'],
                                'longitude' => $match['longitude']
                            ]);
                            $update['location_name'] = Phpfox::getLib('parse.input')->clean($values['location']['name']);
                            $this->database()->update(Phpfox::getT('video'), $update, ['video_id' => (int)$feed['item_id']]);
                        }
                    }
                    break;
                case FeedPostForm::PHOTO_POST:
                    list($callback, $success) = $this->processUpdate($id, $values, true);
                    $feed = Phpfox::getService('feed')->getFeed($id, isset($callback['table_prefix']) ? $callback['table_prefix'] : '');
                    if ($feed) {
                        if (empty($values['location'])) {
                            $this->database()->update(Phpfox::getT('photo_info'), ['location_latlng' => '', 'location_name' => ''], ['photo_id' => (int)$feed['item_id']]);
                        } else {
                            $match = explode(',', $values['location']['latlng']);
                            $match['latitude'] = floatval($match[0]);
                            $match['longitude'] = floatval($match[1]);
                            $update['location_latlng'] = json_encode([
                                'latitude'  => $match['latitude'],
                                'longitude' => $match['longitude']
                            ]);
                            $update['location_name'] = Phpfox::getLib('parse.input')->clean($values['location']['name']);
                            $this->database()->update(Phpfox::getT('photo_info'), $update, ['photo_id' => (int)$feed['item_id']]);
                        }
                    }
                    break;
                default:
                    if ($values['post_type'] == FeedPostForm::STATUS_POST && Phpfox::getLib('parse.format')->isEmpty($values['user_status'])) {
                        return $this->error($this->getLocalization()->translate('add_some_text_to_share'));
                    }
                    $success = $this->processUpdate($id, $values);
                    break;
            }
            if ($success) {
                return $this->success([
                    'id' => (int)$id,
                ]);
            } else {
                return $this->error($this->getErrorMessage());
            }
        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
    }

    private function processUpdate($id, $values, $returnCallback = false)
    {
        $values['feed_id'] = $id;

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_feed_api_process_update_start')) ? eval($sPlugin) : false);

        $callback = null;
        if (!empty($values['parent_item_type']) && !empty($values['parent_item_id'])) {
            $callback = $this->getPostingCallback($values['parent_item_type'], $values['parent_item_id'], isset($values['post_as_parent']) ? $values['post_as_parent'] : null);
            $values['parent_user_id'] = $values['parent_item_id'];
            $result = $this->getProcessService()->callback($callback)->addComment($values);
        } else if (!empty($values['parent_user_id'])) {
            $result = $this->getProcessService()->addComment($values);
        } else {
            $result = $this->getUserProcessService()->updateStatus($values);
            if (!$result) {
                return $this->error(_p('update_feed_failed'));
            }
            //Remove location.
            $statusFeed = $this->getFeedService()->getUserStatusFeed([], $id);
            if (!$statusFeed) {
                return $this->notFoundError();
            }
            if (empty($values['location']) && !$returnCallback) {
                $this->database()->update(Phpfox::getT('user_status'), ['location_latlng' => '', 'location_name' => ''], ['status_id' => (int)$statusFeed['item_id']]);
            }
        }

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_feed_api_process_update_end')) ? eval($sPlugin) : false);

        if ($returnCallback) {
            return [$callback, $result];
        }
        return $result;
    }

    /**
     * Update multiple document base on document query
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * Delete a document
     * DELETE: /resource-name/:id
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function delete($params)
    {
        $params = $this->resolver->setRequired(['id'])
            ->setDefined(['module_id', 'item_id'])
            ->setDefault(['module_id' => ''])
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->resolve($params)->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        Phpfox_Request::instance()->set('module', $params['module_id']);
        $result = $this->getProcessService()->deleteFeed($params['id'], $params['module_id'], $params['item_id']);
        if ($result) {
            return $this->success($params);
        }
        return $this->error(_p('delete_feed_failed'));
    }

    /**
     * Get Create/Update document form
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    function form($params = [])
    {
        return $this->success($this->createForm(FeedPostForm::class)->getFormStructure());
    }

    public function formShare($params = [])
    {
        /** @var ShareFeedForm $form */
        $form = $this->createForm(ShareFeedForm::class);
        if (isset($params['post_type'])) {
            $form->setPostType($params['post_type']);
        }
        return $this->success($form->getFormStructure());
    }

    /**
     * Share a post
     *
     * @param $post
     *
     * @return array|bool|mixed
     */
    public function shareFeed($post)
    {
        /** @var ShareFeedForm $form */
        $form = $this->createForm(ShareFeedForm::class);
        if ($form->isValid() && $post = $form->getValues()) {
            if ($post['post_type'] == 'friend') {
                if (!isset($post['friends']) || (isset($post['friends']) && !count($post['friends']))) {
                    \Phpfox_Error::set($this->getLocalization()->translate('select_a_friend_to_share_this_with_dot'));
                } else {
                    $iCnt = 0;
                    foreach ($post['friends'] as $friendId) {
                        $vals = [
                            'user_status'      => $post['post_content'],
                            'parent_user_id'   => $friendId,
                            'parent_feed_id'   => $post['feed_id'],
                            'parent_module_id' => $post['module_id'],
                            'is_share'         => true
                        ];

                        if (Phpfox::getService('user.privacy')->hasAccess($friendId, 'feed.share_on_wall')
                            && Phpfox::getUserParam('profile.can_post_comment_on_profile')) {
                            $iCnt++;
                            Phpfox::getService('feed.process')->addComment($vals);
                        }
                    }

                    $message = $this->getLocalization()->translate('successfully_shared_this_item_on_your_friends_wall');
                    if (!$iCnt) {
                        \Phpfox_Error::set($this->getLocalization()->translate('unable_to_share_this_post_due_to_privacy_settings'));

                    }
                }
            } else {
                // Check if is a parent feed.
                $feed = $this->getFeedService()->getForItem($post['module_id'], $post['feed_id']);
                if (!empty($feed) && !empty($feed['parent_module_id']) && !empty($feed['parent_feed_id'])) {
                    $post['feed_id'] = $feed['parent_feed_id'];
                    $post['module_id'] = $feed['parent_module_id'];
                }
                $vals = [
                    'user_status'                => $post['post_content'],
                    'privacy'                    => $post['privacy'],
                    'privacy_list'               => !empty($post['privacy_list']) ? $post['privacy_list'] : [],
                    'privacy_comment'            => '0',
                    'parent_feed_id'             => $post['feed_id'],
                    'parent_module_id'           => $post['module_id'],
                    'no_check_empty_user_status' => true,
                ];
                Phpfox::getService('user.process')->updateStatus($vals);
                $message = $this->getLocalization()->translate('successfully_shared_this_item');
            }

            if ($this->isPassed()) {
                return $this->success([], [], isset($message) ? $message : '');
            }

        } else {
            return $this->validationParamsError($form->getInvalidFields());
        }
        return $this->error($this->getErrorMessage());
    }

    function loadResourceById($id, $returnResource = false)
    {
        // TODO: Implement loadResourceById() method.
    }

    /**
     * @return object|\Feed_Service_Feed
     */
    private function getFeedService()
    {
        return Phpfox::getService('feed');
    }

    /**
     * @return object|\Feed_Service_Tag
     */
    private function getFeedTagService()
    {
        return Phpfox::getService('feed.tag');
    }

    /**
     * @return \Feed_Service_Process
     */
    private function getProcessService()
    {
        return Phpfox::getService("feed.process");
    }

    /**
     * @return \User_Service_Process
     */
    private function getUserProcessService()
    {
        return Phpfox::getService('user.process');
    }

    /**
     * @return PhotoApi
     */
    private function getPhotoApiService()
    {
        return Phpfox::getService("mobile.photo_api");
    }

    /**
     * @return VideoApi
     */
    private function getVideoApiService()
    {
        return Phpfox::getService("mobile.video_api");
    }

    public function getPostTypes($params)
    {
        return $this->success((new CoreApi())->getPostTypes());
    }

    public function getAppSetting($params)
    {
        $l = $this->getLocalization();
        return new MobileApp('feed', [
            'title'           => $l->translate('feed'),
            'main_resource'   => new FeedResource([]),
            'other_resources' => [
                new FeedHiddenResource([])
            ]
        ], isset($params['api_version_name']) ? $params['api_version_name'] : 'mobile');
    }

    public function getStatusForEdit($params)
    {
        $params = $this->resolver->setDefined(['id', 'item_id', 'item_type', 'module'])
            ->setRequired(['id'])
            ->setAllowedTypes('id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->resolve($params)->getParameters();
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $callback = [];

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_feed_api_get_status_for_edit_start')) ? eval($sPlugin) : false);

        if (!empty($params['item_type']) && !in_array($params['module'], ['link', 'photo', 'v', 'video'])) {
            $module = $params['item_type'] == 'groups' ? 'pages' : $params['item_type'];
            $callback = [
                'module'       => $module,
                'table_prefix' => $module . '_',
                'item_id'      => $params['item_id'] ? $params['item_id'] : $params['id']
            ];
        }
        $feed = $this->getFeedService()->getUserStatusFeed($callback, $params['id'], false);

        if (!$feed) {
            $this->notFoundError();
        }
        $isParentAdmin = Phpfox::hasCallback($params['item_type'], 'isAdmin') && \Phpfox::callback($params['item_type'] . '.isAdmin', $feed['parent_user_id']);
        if ((in_array($feed['type_id'], ['user_status', 'link', 'photo', 'v'])
                && (($this->getSetting()->getUserSetting('feed.can_edit_own_user_status') && $feed['user_id'] == Phpfox::getUserId())
                    || $this->getSetting()->getUserSetting('feed.can_edit_other_user_status') || $isParentAdmin))
            || (strpos($feed['type_id'], '_comment') !== false && ($feed['user_id'] == Phpfox::getUserId() || Phpfox::isAdmin() || $isParentAdmin))) {
            $finalPrivacy = (int)$feed['privacy'];
            if ($feed['privacy'] == PrivacyType::CUSTOM) {
                $privacy = \Phpfox::getService('privacy')->get($feed['type_id'], $feed['item_id']);
                if (count($privacy)) {
                    $finalPrivacy = array_map(function ($value) {
                        return (int)$value['friend_list_id'];
                    }, $privacy);
                }
            }
            $item = [
                'feed_id'   => (int)$params['id'],
                'item_id'   => (int)$params['item_id'],
                'item_type' => $params['item_type'],
                'item'      => [
                    'status_text'     => html_entity_decode(html_entity_decode($feed['feed_status'], ENT_QUOTES), ENT_QUOTES),
                    'id'              => (int)$feed['feed_id'],
                    'parent_user_id'  => empty($params['item_type']) && empty($params['item_id']) ? (int)$feed['parent_user_id'] : 0,
                    'privacy'         => $finalPrivacy,
                    'tagged_friends'  => $this->_getAllTaggedFriends($feed['type_id'], $feed['item_id']),
                    'privacy_options' => (new PrivacyType())->getDefaultPrivacy(false),
                    'link'            => isset($feed['feed_link_actual']) ? $feed['feed_link_actual'] : ''
                ],
            ];
            (($sPlugin = \Phpfox_Plugin::get('mobile.service_feed_api_get_status_for_edit_end')) ? eval($sPlugin) : false);

            return $this->success($item);
        }
        return $this->permissionError();
    }

    public function getFeedIdByItem($itemId, $typeId, $module = null)
    {
        if ($module && Phpfox::hasCallback($module, 'getFeedDetails')) {
            $aCallback = Phpfox::callback($module . '.getFeedDetails', $itemId);
        }
        return $this->database()->select('feed_id')
            ->from(Phpfox::getT(isset($aCallback['table_prefix']) ? $aCallback['table_prefix'] : '') . 'feed')
            ->where('item_id = ' . (int)$itemId . ' AND type_id = \'' . $typeId . '\'')
            ->execute('getField');
    }

    private function _getAllTaggedFriends($item_type, $item_id)
    {
        $items = $this->getFeedTagService()->getTaggedUsers($item_id, $item_type);
        $results = [];
        if (count($items)) {
            $results = array_map(function ($user) {
                return UserResource::populate($user)->toArray(['id', 'resource_name', 'full_name', 'avatar', 'user_name']);
            }, $items);
        }

        return $results;
    }

    public function getTaggedFriends($params)
    {
        $params = $this->resolver->setDefined(['item_id', 'item_type', 'page', 'limit'])
            ->setRequired(['item_id', 'item_type'])
            ->setAllowedTypes('item_id', 'int')
            ->setDefault([
                'page'  => 1,
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE
            ])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $items = $this->getFeedTagService()->getTaggedUsers($params['item_id'], $params['item_type'], false, $params['page'], $params['limit']);
        $results = [];
        if (count($items)) {
            $results = array_map(function ($user) {
                $resource = UserResource::populate($user);
                return $resource->setExtra((new UserApi())->getAccessControl()->getPermissions($resource))->toArray([
                    'id', 'resource_name', 'full_name', 'avatar', 'is_owner', 'friendship', 'statistic', 'friend_id', 'is_featured', 'extra', 'user_name'
                ]);
            }, $items);
        }

        return $this->success($results);
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }

    public function getScreenSetting($param)
    {
        $l = $this->getLocalization();
        $screenSetting = new ScreenSetting('feed', []);
        $resourceName = FeedResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => ['component' => 'feed_header'],
            ScreenSetting::LOCATION_MAIN   => ['component' => 'feed_detail'],
            'screen_title'                 => $l->translate('feed') . ' > ' . $l->translate('feed') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        return $screenSetting;
    }

    public function hideOneFeed($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $id = $this->resolver->setRequired(['id'])->resolveId($params);
        if (!$id) {
            return $this->notFoundError();
        }
        $userId = $this->getUser()->getId();
        if (Phpfox::getService('feed.hide')->add($userId, $id, 'feed')) {
            return $this->success([]);
        }
        return $this->error();
    }

    public function hideAllFromObject($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $id = $this->resolver->setRequired(['id'])->resolveId($params);
        $feed = $this->getFeedService()->getFeed($id);
        if (!$feed) {
            return $this->notFoundError();
        }
        $feedUserId = $feed['user_id'];
        $userId = $this->getUser()->getId();
        if (Phpfox::getService('feed.hide')->add($userId, $feedUserId, 'user')) {
            return $this->success([]);
        }
        return $this->error();
    }

    public function unHideOneFeed($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $id = $this->resolver->setRequired(['id'])->resolveId($params);
        $feed = $this->getFeedService()->getFeed($id);
        if (!$feed) {
            return $this->notFoundError();
        }
        $userId = $this->getUser()->getId();
        if (Phpfox::getService('feed.hide')->delete($userId, $feed['feed_id'], 'feed')) {
            return $this->success([]);
        }
        return $this->error();
    }

    public function unHideObject($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $id = $this->resolver->setRequired(['id'])->resolveId($params);
        $feed = $this->getFeedService()->getFeed($id);
        if (!$feed) {
            return $this->notFoundError();
        }
        $feedUserId = $feed['user_id'];
        $userId = $this->getUser()->getId();
        if (Phpfox::getService('feed.hide')->delete($userId, $feedUserId, 'user')) {
            return $this->success([]);
        }
        return $this->error();
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return [
            'feed/hide-feed'     => [
                'method'    => 'post',
                'url'       => 'mobile/feed/hide-feed/:id',
                'data'      => 'id',
                'new_state' => 'is_hidden=true,is_hide_one=true,is_just_hide=true',
            ],
            'feed/un-hide-feed'  => [
                'method'    => 'delete',
                'url'       => 'mobile/feed/hide-feed/:id',
                'data'      => 'id',
                'new_state' => 'is_hidden=false',
            ],
            'feed/hide-all'      => [
                'method'    => 'post',
                'url'       => 'mobile/feed/hide-all/:id',
                'data'      => 'id',
                'new_state' => 'is_hidden_all=true,is_hide_one=false,is_just_hide=true',
            ],
            'feed/next-hide-all' => [
                'method'    => 'post',
                'url'       => 'mobile/feed/hide-all/:id',
                'data'      => 'id',
                'new_state' => 'is_hidden_all=true,is_hide_one=false,is_just_hide=true,no_undo=true',
            ],
            'feed/un-hide-all'   => [
                'method'    => 'delete',
                'url'       => 'mobile/feed/hide-all/:id',
                'data'      => 'id',
                'new_state' => 'is_hidden_all=false',
            ],
            'feed/remove-tag'     => [
                'method'    => 'post',
                'url'       => 'mobile/feed/remove-tag',
                'data'      => 'id,item_id,item_type',
                'new_state' => 'is_just_remove_tag=true,can_remove_tag=false,no_undo=true'
            ],
        ];
    }

    public function getManageHidden($params)
    {
        $params = $this->resolver->setDefined(['id', 'type', 'page', 'limit', 'q'])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('limit', 'int')
            ->setAllowedTypes('id', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $userId = empty($params['id']) ? $this->getUser()->getId() : $params['id'];
        $cond = '';
        if ($params['q'] != '') {
            $cond .= " AND user.full_name LIKE '%" . $params['q'] . "%'";
        }
        if ($params['type'] != '') {
            if ($params['type'] == 'friend')
                $cond .= ' AND user.profile_page_id = 0';
            else if ($params['type'] == 'page')
                $cond .= ' AND user.profile_page_id > 0 AND page.item_type = 0';
            else if ($params['type'] == 'group')
                $cond .= ' AND user.profile_page_id > 0 AND page.item_type = 1';
        }
        list($cnt, $hiddens) = Phpfox::getService('feed.hide')->getHiddenUsers($userId, $params['type'], $cond, $params['page'], $params['limit']);

        $results = [];
        if ($cnt) {
            foreach ($hiddens as $key => $hidden) {
                $results[] = FeedHiddenResource::populate($hidden)->toArray();
            }
        }
        return $this->success($results);
    }

    public function deleteHide($params)
    {
        $id = $this->resolver->setRequired(['id'])->resolveId($params);
        if (!$id) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $hideItem = $this->database()->select('fh.*')->from(':feed_hide', 'fh')->where(['fh.hide_id' => $id])->execute('getRow');
        if (!$hideItem) {
            return $this->notFoundError();
        }
        if (Phpfox::getService('feed.hide')->delete($this->getUser()->getId(), $hideItem['item_id'], $hideItem['type_id'])) {
            return $this->success([], [], $this->getLocalization()->translate('unhide_successfully'));
        }
        return $this->error();
    }

    public function removeTag($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $params = $this->resolver->setRequired(['id', 'item_id', 'item_type'])
            ->setAllowedTypes('id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        if (class_exists('Feed_Service_Tag') && Phpfox::getService('feed.tag')->removeTag($this->getUser()->getId(), $params['item_id'], $params['item_type'])) {
            return $this->success([
                'can_remove_tag' => false
            ],[], $this->getLocalization()->translate('you_wont_be_tagged_in_this_post_anymore'));
        }
        return $this->error();
    }
}
