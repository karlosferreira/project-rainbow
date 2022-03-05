<?php

namespace Apps\Core_MobileApi\Api\Resource;


use Apps\Core_MobileApi\Service\PhotoApi;
use Apps\Core_MobileApi\Service\UserApi;

class UserPhotoResource extends UserResource
{
    const RESOURCE_NAME = "user_photo";

    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'user';

    public $photo_id;
    public $user_id;

    public $photo;

    public function getId()
    {
        return $this->user_id . ':' . $this->getPhotoId();
    }

    public function getCover($returnDefault = true)
    {
        $coverId = \Phpfox_Database::instance()->select('cover_photo')->from(':user_field')->where('user_id =' . (int)$this->user_id)->execute('getField');
        if (!empty($coverId)) {
            $this->cover = (new UserApi())->getUserCover($coverId, '_1024');
        }
        if (empty($this->cover) && $returnDefault) {
            $this->cover = $this->getDefaultImage(true, 'user');
        }
        return $this->cover;
    }

    public function getPhotoId()
    {
        if ($this->photo_id === null && !empty($this->rawData['item_id'])) {
            $this->photo_id = $this->rawData['item_id'];
        }
        return $this->photo_id;
    }

    public function getPhoto()
    {
        $photo = (new PhotoApi())->loadResourceById($this->getPhotoId(), true);
        if (!empty($photo->id)) {
            $this->photo = [
                'id'            => $photo->getId(),
                'module_name'   => 'photo',
                'resource_name' => 'photo',
                'mature'        => $photo->mature,
                'width'         => isset($photo->width) ? (int)$photo->width : 0,
                'height'        => isset($photo->height) ? (int)$photo->height : 0,
                'image'         => $photo->getImage()->sizes['1024'],
            ];
        }
        return $this->photo;
    }

    public function getAvatar()
    {
        $photo = $this->getPhoto();
        if (!empty($photo['image'])) {
            $this->avatar = $photo['image'];
        }
        return $this->avatar;
    }

    /**
     * Get fields for listing or child resource
     *
     * @return array
     */
    public function getShortFields()
    {
        return [
            'id',
            'module_name',
            'resource_name',
            'full_name',
            'cover',
            'photo',
            'avatar',
        ];
    }

    public function getMobileSettings($params = [])
    {
        return self::createSettingForResource([
            'schema'        => [
                'ref' => 'user_photo',
            ],
            'resource_name' => $this->getResourceName(),
            'fab_buttons'   => false
        ]);
    }
}