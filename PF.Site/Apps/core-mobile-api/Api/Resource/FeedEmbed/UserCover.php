<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 9/8/18
 * Time: 3:58 PM
 */

namespace Apps\Core_MobileApi\Api\Resource\FeedEmbed;


use Apps\Core_MobileApi\Api\Resource\PhotoResource;
use Apps\Core_MobileApi\Api\Resource\UserPhotoResource;

class UserCover extends UserPhoto
{
    public function toArray()
    {
        if ($this->typeId == 'user_photo') {
            return UserPhotoResource::populate($this->feedData)->displayShortFields()->toArray();
        }
        /** @var PhotoResource $photo */
        $photo = $this->photoService->loadResourceById($this->feedData['item_id'], true);

        if ($photo) {
            $iFeedId = $this->feedData['feed_id'];
            $cache = storage()->get('photo_parent_feed_' . $iFeedId);
            if ($cache) {
                $iFeedId = $cache->value;
            }
            return [
                'resource_name' => PhotoResource::RESOURCE_NAME,
                'total_photo'   => 1,
                'module_id'     => $photo->module_id,
                'item_id'       => $photo->group_id,
                'feed_id'       => (int)$iFeedId,
                'remain_photo'  => 0,
                'photos'        => [
                    [
                        'id'            => $photo->getId(),
                        'href'          => "photo/{$photo->getId()}",
                        'module_name'   => 'photo',
                        'resource_name' => 'photo',
                        'mature'        => $photo->mature,
                        'width'         => isset($photo->width) ? (int)$photo->width : 0,
                        'height'        => isset($photo->height) ? (int)$photo->height : 0,
                        'image'         => $photo->getImage()->sizes['1024'],
                    ]
                ],
            ];
        }
        return null;
    }
}