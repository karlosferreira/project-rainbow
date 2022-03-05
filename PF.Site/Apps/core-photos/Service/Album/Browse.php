<?php

namespace Apps\Core_Photos\Service\Album;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

class Browse extends Phpfox_Service
{
    public function __construct()
    {

    }

    public function processRows(&$aRows)
    {
        foreach ($aRows as $iKey => $aRow) {
            Phpfox::getService('photo.album')->getPermissions($aRow);
            if ($aRow['profile_id'] > 0) {
                $aRow['name'] = isset($aRow['album_name']) ? $aRow['album_name'] : _p('user_profile_pictures', ['full_name' => $aRow['full_name']]);
                $aRow['link'] = Phpfox::permalink('photo.album.profile', $aRow['user_id'], $aRow['user_name']);
            } else if ($aRow['cover_id'] > 0) {
                $aRow['name'] = isset($aRow['album_name']) ? $aRow['album_name'] : _p('user_cover_photo', ['full_name' => $aRow['full_name']]);
                $aRow['link'] = Phpfox::permalink('photo.album.cover', $aRow['user_id'], $aRow['user_name']);
            } else if ($aRow['timeline_id'] > 0) {
                $aRow['name'] = isset($aRow['album_name']) ? $aRow['album_name'] : _p('user_timeline_photos', ['full_name' => $aRow['full_name']]);
                $aRow['link'] = Phpfox::permalink('photo.album', $aRow['album_id'], $aRow['name']);
            } else {
                $aRow['link'] = Phpfox::permalink('photo.album', $aRow['album_id'], $aRow['name']);
            }
            if ($aRow['total_photo'] > 0 && empty($aRow['destination'])) {
                $aCover = Phpfox::getService('photo.album.process')->autoCover($aRow['album_id']);
                if (!empty($aCover)) {
                    $aRow['destination'] = $aCover['destination'];
                    $aRow['server_id'] = $aCover['server_id'];
                    $aRow['mature'] = $aCover['mature'];
                }
            }
            $aRows[$iKey] = $aRow;
        }
    }

    /**
     * Create multiple union for browse
     * @param array $params
     */
    public function getMultipleUnions($params = [])
    {
        $tableAlias = $params['alias'];
        $tableName = $params['table'];
        $searchText = Phpfox::getLib('search')->getSearchText();
        $mainConditions = !empty($params['conditions']) ? $params['conditions'] : [];
        $isInPage = defined('PHPFOX_IS_PAGES_VIEW') && PHPFOX_IS_PAGES_VIEW;
        $phraseTexts = [
            'profile' => db()->escape(_p('user_profile_pictures')),
            'cover' => db()->escape(_p('user_cover_photo')),
            'timeline' => db()->escape(_p('user_timeline_photos')),
        ];

        if (!$isInPage && isset($params['sub_query'])) {
            $tempWhere = [];
            if (Phpfox::getParam('photo.display_profile_photo_within_gallery')) {
                $tempWhere[] = $tableAlias . '.profile_id > 0';
            }
            if (Phpfox::getParam('photo.display_cover_photo_within_gallery')) {
                $tempWhere[] = $tableAlias . '.cover_id > 0';
            }
            if (Phpfox::getParam('photo.display_timeline_photo_within_gallery')) {
                $tempWhere[] = $tableAlias . '.timeline_id > 0';
            }
            if (count($tempWhere)) {
                $tempWhere = ['AND (' . implode(' OR ', $tempWhere) . ')'];
            }
        } else {
            $tempWhere = array_merge($mainConditions, ['AND ((' . $tableAlias . '.profile_id > 0 OR ' . $tableAlias . '.cover_id > 0 OR ' . $tableAlias . '.timeline_id > 0)' . (!$isInPage ? ' AND ' . $tableAlias . '.total_photo > 0' : '') .')']);
        }

        db()->select($tableAlias . '.*, CASE WHEN ' . $tableAlias . '.timeline_id > 0 THEN REPLACE("' . $phraseTexts['timeline'] . '", "{full_name}", u.full_name) WHEN ' . $tableAlias . '.profile_id > 0 THEN REPLACE("' . $phraseTexts['profile'] . '", "{full_name}", u.full_name) ELSE REPLACE("' . $phraseTexts['cover'] . '", "{full_name}", u.full_name) END AS album_name')
            ->from(isset($params['sub_query']) ? '(' . $params['sub_query'] . ')' : $tableName, $tableAlias)
            ->join(':user', 'u', 'u.user_id = ' . $tableAlias . '.user_id')
            ->where($tempWhere)
            ->union();

        $normalAlbumConditions = [
            $tableAlias . '.profile_id' => 0,
            $tableAlias . '.cover_id' => 0,
            $tableAlias . '.timeline_id' => 0,
        ];

        db()->select($tableAlias . '.*, ' . $tableAlias . '.name AS album_name')
            ->from(isset($params['sub_query']) ? '(' . $params['sub_query'] . ')' : $tableName, $tableAlias)
            ->where(isset($params['sub_query']) ? $normalAlbumConditions : array_merge($mainConditions, $normalAlbumConditions))
            ->union();

        db()->unionFrom($tableAlias)
            ->where([
                'AND (' . db()->searchKeywords('album_name', $searchText) . ' OR ' . db()->searchKeywords('album_name', html_entity_decode($searchText, ENT_QUOTES, 'UTF-8')) . ')',
            ]);
    }

    public function query()
    {
        db()->select('p.destination, p.server_id, p.mature, ')
            ->leftJoin(Phpfox::getT('photo'), 'p', 'p.album_id = pa.album_id AND pa.view_id = 0 AND p.is_cover = 1');
    }

    /**
     * @param bool $bIsCount remove in v4.6
     * @param bool $bNoQueryFriend
     */
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Phpfox::getService('friend')->queryJoin($bNoQueryFriend)) {
            db()->join(Phpfox::getT('friend'), 'friends',
                'friends.user_id = pa.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }

        if (Phpfox::isModule('like')) {
            db()->leftJoin(Phpfox::getT('like'), 'l',
                'l.type_id = \'photo_album\' AND l.item_id = pa.album_id AND l.user_id = ' . Phpfox::getUserId() . '');
        }
        // END
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod    is the name of the method
     * @param array  $aArguments is the array of arguments of being passed
     *
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('photo.service_album_browse__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}