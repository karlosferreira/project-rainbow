<?php

namespace Apps\Core_MobileApi\Api\Security\Music;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\EventResource;
use Apps\Core_MobileApi\Api\Resource\MusicPlaylistResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Phpfox;


class MusicPlaylistAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const UPLOAD = "upload";
    const OWNER = "owner";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([self::ADD, self::EDIT, self::COMMENT, self::UPLOAD, self::OWNER, self::DELETE]);
    }

    /**
     * @inheritdoc
     *
     * @param $resource EventResource
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (in_array($permission, [self::IS_AUTHENTICATED, self::SYSTEM_ADMIN])) {
            return parent::isGranted($permission);
        }
        if (in_array($permission, [self::LIKE, self::SHARE, self::REPORT])) {
            return parent::isGranted($permission, $resource);
        }
        if (!parent::isGranted($permission, $resource)) {
            return false;
        }
        $isOwner = false;
        // Item Owner always able to do any permission
        /** @var $resource MusicPlaylistResource */
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('music.can_access_music');
                break;
            case self::ADD:
                if (method_exists('\Apps\Core_Music\Service\Playlist\Playlist', 'canCreateNewPlaylist')) {
                    $granted = Phpfox::getService('music.playlist')->canCreateNewPlaylist(null, false);
                } else {
                    $granted = $this->isGrantedSetting(['music.can_access_music', 'music.can_add_music_playlist']);
                }
                break;
            case self::EDIT:
                $granted = $this->isGrantedSetting('music.can_access_music') && ($this->isGrantedSetting('music.can_edit_other_music_playlists')
                        || ($this->isGrantedSetting('music.can_edit_own_playlists') && $isOwner));
                break;
            case self::DELETE:
                $granted = $this->isGrantedSetting('music.can_access_music') && ($this->isGrantedSetting('music.can_delete_other_music_playlists')
                        || ($this->isGrantedSetting('music.can_delete_own_music_playlist') && $isOwner));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('music.can_delete_own_music_playlist');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['music.can_access_music', 'music.can_add_comment_on_music_playlist']) && (!$resource || !$resource->getIsPending());
                break;
            case self::OWNER:
                $granted = $this->isGrantedSetting('music.can_access_music') && $isOwner;
                break;
        }
        return $granted;
    }

}