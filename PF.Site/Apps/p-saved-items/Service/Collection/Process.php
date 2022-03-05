<?php

namespace Apps\P_SavedItems\Service\Collection;

use Phpfox;
use Phpfox_Service;
use Phpfox_Url;

/**
 * Class Process
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Service\Collection
 */
class Process extends Phpfox_Service
{
    protected $_collectionDataTable;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('saved_collection');
        $this->_collectionDataTable = Phpfox::getT('saved_collection_data');
    }

    public function add($aVals)
    {
        $insert = [
            'user_id' => Phpfox::getUserId(),
            'name' => htmlspecialchars($aVals['title']),
            'created_time' => PHPFOX_TIME,
            'updated_time' => PHPFOX_TIME,
            'privacy' => $aVals['privacy'] ? $aVals['privacy'] : 0,
        ];
        $id = db()->insert($this->_sTable, $insert);

        if (Phpfox::isModule('privacy') && $aVals['privacy'] == 4) {
            Phpfox::getService('privacy.process')->add('saveditems_collection', $id, (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
        }

        db()->insert(':saved_collection_friend', [
            'collection_id' => $id,
            'friend_id' => Phpfox::getUserId(),
        ]);

        $this->_removeMyCollectionCache();

        return $id;
    }

    private function _removeMyCollectionCache()
    {
        $this->cache()->removeGroup('saveditems');
    }

    public function update($aVals)
    {
        $update = [
            'name' => htmlspecialchars($aVals['title']),
            'updated_time' => PHPFOX_TIME,
            'privacy' => $aVals['privacy'] ? $aVals['privacy'] : 0,
        ];
        db()->update($this->_sTable, $update, 'collection_id = ' . (int)$aVals['id']);

        if (Phpfox::isModule('privacy') && $aVals['privacy'] == 4) {
            Phpfox::getService('privacy.process')->update('saveditems_collection', $aVals['id'], (isset($aVals['privacy_list']) ? $aVals['privacy_list'] : []));
        }
        $this->_removeMyCollectionCache();
        $this->cache()->remove('saveditems_recent_updated_collections_' . Phpfox::getUserId());
        return true;
    }

    public function delete($collectionId)
    {
        if (db()->delete($this->_sTable,
                'collection_id = ' . (int)$collectionId) . ' AND user_id = ' . Phpfox::getUserId()) {
            db()->delete($this->_collectionDataTable, 'collection_id = ' . (int)$collectionId);
            $this->_removeMyCollectionCache();
            $this->cache()->remove('saveditems_recent_updated_collections_' . Phpfox::getUserId());
            return true;
        }
        return false;
    }

    public function processSavedItem($collectionId, $savedId, $add = true)
    {
        $check = db()->select('COUNT(*)')
            ->from($this->_collectionDataTable)
            ->where('saved_id = ' . (int)$savedId . ' AND collection_id = ' . (int)$collectionId)
            ->execute('getSlaveField');
        if ($add) {
            if (!$check) {
                $iUserId = Phpfox::getUserId();
                $aCollections = Phpfox::getService('saveditems.collection')->get('collection_id = ' . (int)$collectionId);
                $aCollection =  array_shift($aCollections);
                $aSavedItem = Phpfox::getService('saveditems')->get(['saved_id' => $savedId]);
                if (!$aCollection || !$iUserId || !$aSavedItem) {
                    return false;
                }

                //if items already existed in collection
                if ($bExisted = Phpfox::getService('saveditems.collection')->isExistedInCollection($aSavedItem['type_id'], $aSavedItem['item_id'], $collectionId)) {
                    \Phpfox_Error::set(_p('saveditems_item_already_existed_in_collection_title', ['title' => $aCollection['name']]));
                    return false;
                }

                if (($aCollection['user_id'] != $iUserId)
                    && (!$aAlreadySaved = Phpfox::getService('saveditems')->get(['saved_id' => $savedId, 'user_id' => $iUserId]))) {
                    $aInsert = [
                        'type_id' => $aSavedItem['type_id'],
                        'item_id' => $aSavedItem['item_id'],
                        'link' => $aSavedItem['link'],
                        'is_save' => 1
                    ];
                    $savedId = Phpfox::getService('saveditems.process')->save($aInsert);
                    if (!$savedId) {
                        return false;
                    }
                }
                db()->insert($this->_collectionDataTable,
                    ['saved_id' => (int)$savedId, 'collection_id' => (int)$collectionId, 'user_id' => $iUserId]);
                db()->update($this->_sTable,
                    [
                        'updated_time' => PHPFOX_TIME,
                    ],
                    'collection_id = ' . (int)$collectionId, false);
                db()->updateCounter('saved_collection', 'total_item', 'collection_id', $collectionId);
                $savedItem = db()->select('s.saved_id, s.type_id, s.item_id, sc.image_path')
                    ->from(Phpfox::getT('saved_items'), 's')
                    ->join(Phpfox::getT('saved_collection_data'), 'scd', 'scd.saved_id = s.saved_id')->join(Phpfox::getT('saved_collection'), 'sc',
                    'sc.collection_id = scd.collection_id AND sc.collection_id = ' . (int)$collectionId)
                    ->where('s.saved_id = ' . (int)$savedId)
                    ->execute('getSlaveRow');
                if (!empty($savedItem)) {
                    $this->updateCollectionCover($collectionId, $savedItem);
                }
                $this->cache()->remove('saveditems_recent_updated_collections_' . $iUserId);
                return true;
            }
        } else {
            if ($check && (db()->delete($this->_collectionDataTable,
                    ['saved_id' => (int)$savedId, 'collection_id' => (int)$collectionId]))) {
                db()->updateCounter('saved_collection', 'total_item', 'collection_id', $collectionId, true);
                $this->cache()->remove('saveditems_recent_updated_collections_' . Phpfox::getUserId());
                $lastSavedItem = db()->select('s.saved_id, s.type_id, s.item_id, sc.image_path')->from(Phpfox::getT('saved_items'),
                    's')->join($this->_collectionDataTable, 'scd',
                    's.saved_id = scd.saved_id AND scd.collection_id = ' . (int)$collectionId)->join($this->_sTable,
                    'sc',
                    'sc.collection_id = scd.collection_id AND sc.collection_id = ' . (int)$collectionId)->order('s.time_stamp DESC')->limit(1)->execute('getSlaveRow');
                if (!empty($lastSavedItem)) {
                    $this->updateCollectionCover($collectionId, $lastSavedItem);
                } else {
                    $this->resetCollectionCover($collectionId);
                }
                return true;
            }
        }
        return false;
    }

    public function updateCollectionCover($collectionId, $savedItem = null)
    {
        $savedItemFolder = PHPFOX_DIR . 'file' . PHPFOX_DS . 'pic' . PHPFOX_DS . 'saveditems' . PHPFOX_DS;
        if (!is_dir($savedItemFolder)) {
            @mkdir($savedItemFolder, 0777, true);
            @chmod($savedItemFolder, 0777);
        }
        if (!empty($savedItem)) {
            $typeCount = explode('_', $savedItem['type_id']);
            $section = count($typeCount) == 2 ? $typeCount[1] : '';
            $module = $typeCount[0];
            $photo = '';
            $specialTypes = Phpfox::getService('saveditems')->getSpecialTypesForReplacement();
            $specialTypes = array_combine(array_values($specialTypes), array_keys($specialTypes));
            $replacedType = isset($specialTypes[$savedItem['type_id']]) ? $specialTypes[$savedItem['type_id']] : $savedItem['type_id'];
            $usingGlobalQuery = false;
            if (Phpfox::hasCallback($module, 'getSavedInformation')) {
                $extraInfo = Phpfox::callback($module . '.getSavedInformation', [
                    'section' => $section,
                    'item_id' => $savedItem['item_id'],
                ]);
                if (!empty($extraInfo['photo'])) {
                    $photo = $extraInfo['photo'];
                }
            } elseif (Phpfox::hasCallback($module, 'globalUnionSearch') && Phpfox::hasCallback($replacedType,
                    'getSearchInfo')) {
                Phpfox::callback($module . '.globalUnionSearch', '');
                $usingGlobalQuery = true;
            } elseif (($exceptionalTypes = Phpfox::getService('saveditems')->getExceptionalTypesForGlobalSearch()) && in_array($savedItem['type_id'],
                    $exceptionalTypes)) {
                Phpfox::getService('saveditems')->__getGlobalSearchForSpecialTypes('', true, $savedItem['type_id']);
                $usingGlobalQuery = true;
            }

            if ($usingGlobalQuery) {
                db()->unionFrom('item', true);
                $query = trim(db()->execute(''));
                $query = str_replace("\n", ' ', $query);
                if (!empty($query) && preg_match("/^FROM\((.*)\) AS item$/", $query, $match) && !empty($match[1])) {
                    $query = trim($match[1]);
                    $queryArray = explode('UNION ALL', $query);
                    $specialTypes = Phpfox::getService('saveditems')->getSpecialTypesForReplacement();
                    $unionQueryTemp = Phpfox::getService('saveditems')->parseQueryForSpecialTypes($queryArray, $specialTypes);
                    $query = '(' . trim($unionQueryTemp, ' UNION ALL ') . ') AS item';
                    db()->from($query);
                }
                $item = db()->select('item.*')->join(Phpfox::getT('saved_items'), 's',
                    's.item_id = item.item_id AND s.type_id = item.item_type_id AND s.saved_id = ' . (int)$savedItem['saved_id'])->execute('getSlaveRow');
                if (!empty($item)
                    && Phpfox::hasCallback($replacedType, 'getSearchInfo')
                    && ($extraInfo = Phpfox::callback($replacedType . '.getSearchInfo', $item))) {
                    $extraInfo = Phpfox::callback($replacedType . '.getSearchInfo', $item);
                    if (!empty($extraInfo['item_display_photo']) && (preg_match('/src=(\'|\")([\S]+)(\'|\")/',
                            $extraInfo['item_display_photo'], $match)) && !empty($match[2])) {
                        $photo = $match[2];
                    }
                } elseif (($data = Phpfox::getService('saveditems')->getSearchInfo($item['item_type_id'], $item)) && !empty($data['item_display_photo'])) {
                    $photo = $data['item_display_photo'];
                } elseif (!empty($item['item_photo'])) {
                    $photo = $item['item_photo'];
                }

                if (substr($photo, 0, 17) == '//img.youtube.com') {
                    $photo = 'https:' . $photo;
                }
            }

            if (!empty($photo) && $newFile = $this->_clonePhoto($photo)) {
                db()->update(Phpfox::getT('saved_collection'),
                    ['image_path' => $newFile['image_path'], 'image_server_id' => $newFile['server_id']],
                    'collection_id = ' . (int)$collectionId);
                if (!empty($savedItem['image_path'])) {
                    if (file_exists($savedItemFolder . $savedItem['image_path'])) {
                        @unlink($savedItemFolder . $savedItem['image_path']);
                    }

                    if (!empty($savedItem['image_server_id'])) {
                        Phpfox::getLib('cdn')->setServerId($savedItem['image_server_id']);
                        Phpfox::getLib('cdn')->remove($savedItemFolder . $savedItem['image_path']);
                    }

                }
            } else {
                db()->update(Phpfox::getT('saved_collection'), ['image_path' => '', 'image_server_id' => 0],
                    'collection_id = ' . (int)$collectionId);
                if (!empty($savedItem['image_path'])) {
                    if (file_exists($savedItemFolder . $savedItem['image_path'])) {
                        @unlink($savedItemFolder . $savedItem['image_path']);
                    }
                    if (!empty($savedItem['image_server_id'])) {
                        Phpfox::getLib('cdn')->setServerId($savedItem['image_server_id']);
                        Phpfox::getLib('cdn')->remove($savedItemFolder . $savedItem['image_path']);
                    }
                }
            }
        }
    }

    private function _clonePhoto($photo)
    {
        $urlParts = parse_url($photo);

        if (!empty($urlParts['host'])) {
            if ($urlParts['host'] == Phpfox::getParam('core.host')) {
                if (strpos(trim($urlParts['path'], '/'), 'PF.Base') === 0) {
                    $documentRoot = PHPFOX_PARENT_DIR;
                } else {
                    $documentRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/') . PHPFOX_DS;
                }
                $realPath = $documentRoot . ltrim($urlParts['path'], '/');
                if (file_exists($realPath)) {
                    $fileExtension = pathinfo($realPath, PATHINFO_EXTENSION);
                }
            } else {
                $fileExtension = pathinfo($photo, PATHINFO_EXTENSION);
                $realPath = $photo;
            }

            if (!empty($realPath) && !empty($fileExtension) && in_array($fileExtension, ['jpg', 'jpeg', 'png', 'gif'])) {
                $fileContent = fox_get_contents($realPath);
                if (!empty($fileContent)) {
                    $savedItemFolder = PHPFOX_DIR . 'file' . PHPFOX_DS . 'pic' . PHPFOX_DS . 'saveditems' . PHPFOX_DS;
                    $fileName = md5('saveditems_collection_' . uniqid() . PHPFOX_TIME);
                    $filePath = $savedItemFolder . $fileName . '.' . $fileExtension;
                    file_put_contents($filePath, $fileContent);
                    if (file_exists($filePath)) {
                        $serverId = 0;
                        if (Phpfox::getLib('cdn')->put($filePath)) {
                            $serverId = Phpfox::getLib('cdn')->getServerId();
                        }

                        return [
                            'image_path' => $fileName . '.' . $fileExtension,
                            'server_id' => $serverId,
                        ];
                    }
                }
            }
        }

        return false;
    }

    public function resetCollectionCover($collectionId)
    {
        $collection = db()->select('collection_id, image_path, image_server_id')
            ->from(Phpfox::getT('saved_collection'))
            ->where(['collection_id' => $collectionId])
            ->execute('getSlaveRow');
        if (empty($collection['collection_id'])) {
            return false;
        }

        if (db()->update(Phpfox::getT('saved_collection'), ['image_path' => '', 'image_server_id' => 0], ['collection_id' => $collectionId])) {
            $savedItemFolder = PHPFOX_DIR . 'file' . PHPFOX_DS . 'pic' . PHPFOX_DS . 'saveditems' . PHPFOX_DS;
            if (!empty($collection['image_path'])) {
                if (file_exists($savedItemFolder . $collection['image_path'])) {
                    @unlink($savedItemFolder . $collection['image_path']);
                }
                if (!empty($collection['image_server_id'])) {
                    Phpfox::getLib('cdn')->setServerId($collection['image_server_id']);
                    Phpfox::getLib('cdn')->remove($savedItemFolder . $collection['image_path']);
                }
            }
        }
    }

    public function addFriendsListToCollection($iFriends, $iCollectionId)
    {
        $iOldFriendIds = array_column(Phpfox::getService('saveditems.friend')->getFriendInCollection($iCollectionId), 'user_id');
        foreach ($iFriends as $iFriend) {
            //Add new if exist in iFriends but not exist in aOldFriendIds
            if (!in_array($iFriend, $iOldFriendIds)) {
                $aInsertData[] = [
                    'friend_id' => $iFriend,
                    'collection_id' => $iCollectionId,
                ];
            }
        }

        foreach ($iOldFriendIds as $iOldFriendId) {
            if (!in_array($iOldFriendId, $iFriends)) {
                $aDeleteIds[] = $iOldFriendId;
            }
        }

        if (!empty($aInsertData)) {
            db()->multiInsert(Phpfox::getT('saved_collection_friend'), ['friend_id', 'collection_id'], $aInsertData);
            $aCols = Phpfox::getService('saveditems.collection')->get('collection_id = ' . $iCollectionId);
            $aCollection = array_shift($aCols);
            if (!empty($aCollection['collection_id'])) {
                $aUser = Phpfox::getService('user')->get($aCollection['user_id']);
                foreach ($aInsertData as $aInsert) {
                    //Send Notification
                    if (Phpfox::isModule('notification')) {
                        Phpfox::getService('notification.process')->add('saveditems_collection_addfriend',
                            $aInsert['collection_id'], $aInsert['friend_id']);
                    }

                    //Send Mail
                    Phpfox::getLib('mail')->to($aInsert['friend_id'])
                        ->subject([
                            'saveditems_full_name_add_you_to_saved_item_collection', [
                                'full_name' => $aUser['full_name'],
                            ],
                        ])
                        ->message([
                            'saveditems_full_name_add_you_to_saved_item_collection_name_link', [
                                'full_name' => $aUser['full_name'],
                                'name' => $aCollection['name'],
                                'link' => Phpfox_Url::instance()->makeUrl('saved.collection.' . $iCollectionId),
                            ],
                        ])
                        ->notification('saveditems.enable_email_notification')
                        ->send();
                }

                return true;
            }
        }

        if (!empty($aDeleteIds)) {
            $sDelete = 'friend_id IN (' . implode(', ', $aDeleteIds) . ') AND collection_id = ' . $iCollectionId;
            return db()->delete(Phpfox::getT('saved_collection_friend'), $sDelete);
        }

        return false;
    }

    public function removeFriendFromCollection($iFriendId, $iCollectionId)
    {
        $aCollection = Phpfox::getService('saveditems.collection')->getForEdit($iCollectionId);

        $aFriend = Phpfox::getService('user')->get($iFriendId);

        if ($aCollection && $aFriend) {
            Phpfox::getService('saveditems.friend.process')->removeFriend($iFriendId, $iCollectionId);
        } else {
            return false;
        }
    }


}