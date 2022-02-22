<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service\Helper;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Resource\CommentResource;
use Apps\Core_MobileApi\Api\Resource\FeedEmbed\CustomRelation;
use Apps\Core_MobileApi\Api\Resource\FeedEmbed\FeedEmbed;
use Apps\Core_MobileApi\Api\Resource\FeedEmbed\StatusComment;
use Apps\Core_MobileApi\Api\Resource\FeedEmbed\UserCover;
use Apps\Core_MobileApi\Api\Resource\FeedEmbed\UserPhoto;
use Apps\Core_MobileApi\Api\Resource\FeedResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Service\ApiVersionResolver;
use Apps\Core_MobileApi\Service\CommentApi;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;

class FeedAttachmentHelper
{
    /**
     * @var array extend feed embed types
     */
    protected $embedTypes;

    protected $specialShare;

    protected $specialFeeds;

    public function __construct()
    {
        $this->embedTypes = [
            'user_photo'         => UserPhoto::class,
            'user_cover'         => UserCover::class,
            'custom_relation'    => CustomRelation::class,
            'groups_photo'       => UserPhoto::class,
            'groups_cover_photo' => UserCover::class,
            'pages_photo'        => UserPhoto::class,
            'pages_cover_photo'  => UserCover::class,
            'groups_comment'     => StatusComment::class,
            'pages_comment'      => StatusComment::class,
            'event_comment'      => StatusComment::class,
            'feed_comment'       => StatusComment::class
        ];
        $this->specialShare = [
            'pages',
            'groups'
        ];

        $this->specialFeeds = [
            'pages_itemLiked'  => 'pages',
            'groups_itemLiked' => 'groups'
        ];

    }

    /**
     *
     * Get feed embed item base on feed data
     *
     * @param array $feedItem
     * @param FeedResource $feedResource
     * @param string $apiVersion
     * @return array
     * @throws \Exception
     */
    public function forListing($feedItem, &$feedResource, $apiVersion = 'mobile')
    {
        $typeId = (string)$feedItem['type_id'];
        $typeId = isset($this->specialFeeds[$typeId]) ? $this->specialFeeds[$typeId] : $typeId;
        $embedData = isset($feedItem['custom_data_cache']) ? $feedItem['custom_data_cache'] : [];

        $router = NameResource::instance();
        $versionResolver = new ApiVersionResolver();
        $service = null;

        // All comment type, the embed object is Parent Item of the comment
        if (strpos($typeId, "_comment") !== false
            && !isset($this->embedTypes[$typeId])
            && !($feedItem['parent_user_id'] > 0 && $typeId == "feed_comment")) {
            /** @var CommentApi $commentApi */
            $commentApi = Phpfox::getService("mobile.comment_api");

            /** @var CommentResource $comment */
            $comment = $commentApi->loadResourceById($feedItem['item_id'], true);

            if ($comment
                && $router->hasApiResourceService($comment->getItemType())
                && ($service = $versionResolver->getApiServiceWithVersion($comment->getItemType(), [
                    'api_version_name' => $apiVersion
                ]))) {

                if ($service instanceof AbstractResourceApi) {
                    $resource = $service->loadResourceById($comment->getItemId(), true);
                    if ($resource && $resource instanceof ResourceBase) {
                        return $resource->getFeedDisplay();
                    }
                }
            }
        } else {
            if (!empty($feedItem['parent_feed_id']) && !empty($feedItem['parent_module_id'])) {
                // Share feed case
                $typeId = isset($this->specialFeeds[$feedItem['parent_module_id']]) ? $this->specialFeeds[$feedItem['parent_module_id']] : $feedItem['parent_module_id'];
                $itemId = $feedItem['parent_feed_id'];

                //Parent feed
                $parentFeed = Phpfox::getService('feed')->getParentFeedItem($typeId, $itemId);
                if (isset($parentFeed['privacy']) && $parentFeed['privacy']
                    && !Phpfox::getService('privacy')->check($parentFeed['type_id'], $parentFeed['item_id'], $parentFeed['user_id'], $parentFeed['privacy'], null, true)
                ) {
                    return [];
                }
                if (empty($parentFeed)) {
                    $parentFeed = [
                        'feed_id' => 0,
                        'item_id' => $itemId,
                        'type_id' => $typeId
                    ];
                }
                $parentFeedItem = Phpfox::callback($typeId . '.getActivityFeed', $parentFeed, null, true);
                if ($parentFeedItem && !isset($parentFeedItem['type_id'])) {
                    $parentFeedItem['type_id'] = $typeId;
                    $parentFeedItem['item_id'] = $itemId;
                } elseif ($parentFeedItem === false || $parentFeedItem === null) {
                    return [];
                }

                if (in_array($typeId, $this->specialShare)) {
                    $embedData = $parentFeedItem;
                } else {
                    $miniFeed = Phpfox::getService('mobile.feed_api')->processRow(
                        is_array($parentFeedItem) ? array_merge($parentFeed, $parentFeedItem) : $parentFeed);
                    if ($miniFeed instanceof ResourceBase) {
                        return $miniFeed->displayShortFields()->toArray();
                    } else {
                        return $miniFeed;
                    }
                }
            } else {
                $itemId = $feedItem['item_id'];
            }

            $resourceName = str_replace("_", "-", $typeId);

            // Manage response data from object
            if (isset($this->embedTypes[$typeId])) {
                return (new $this->embedTypes[$typeId]($feedItem, $typeId));
            } /** @var ActivityFeedInterface $service */
            else if ($router->hasApiResourceService($resourceName)
                && ($service = $versionResolver->getApiServiceWithVersion($resourceName, [
                    'api_version_name' => $apiVersion
                ]))) {

                // Can customize Activity feed display by implement this Interface
                if ($service instanceof ActivityFeedInterface) {
                    if (method_exists($service, 'updateFeedResource')) {
                        //This function allow modify FeedResource
                        $service->updateFeedResource($feedResource);
                    }
                    return $service->getFeedDisplay($feedItem, $embedData);
                }

                // If can load resource by ID, return
                if ($service instanceof AbstractResourceApi) {
                    $resource = $service->loadResourceById($itemId, true);
                    if ($resource && $resource instanceof ResourceBase) {
                        return $resource->getFeedDisplay();
                    }
                }

            }
        }

        return null;
    }


    /**
     *
     * Other modules/apps can add more feed type to generate feed embed content
     *
     * @see FeedEmbed for more information of implementation
     *
     * @param string $itemType
     * @param string $embedClassName the child class of `FeedEmbed`
     *
     * @codeCoverageIgnore
     */
    public function addEmbedTypes($itemType, $embedClassName)
    {
        $this->embedTypes[$itemType] = $embedClassName;
    }

    /**
     *
     * Get all extended embed types
     *
     * @return array
     *
     * @codeCoverageIgnore
     */
    public function getEmbedTypes()
    {
        return $this->embedTypes;
    }

}