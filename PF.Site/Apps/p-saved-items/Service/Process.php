<?php

namespace Apps\P_SavedItems\Service;

use Phpfox;
use Phpfox_Service;

/**
 * Class Process
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Service
 */
class Process extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('saved_items');
    }

    public function addItemToCollectionsForMobile($savedId, $collectionIds)
    {
        if (!empty($savedId)) {
            $colectionDataTable = Phpfox::getT('saved_collection_data');
            $collectionTable = Phpfox::getT('saved_collection');
            $savedId = (int)$savedId;
            $clearCollectionCache = false;

            $collectionIds = array_filter($collectionIds, function ($value) {
                return !empty($value) && ((int)$value > 0);
            });

            $collectionIds = !empty($collectionIds) ? $collectionIds : [];

            $existedCollectionIds = db()->select('collection_id')->from($colectionDataTable)->where('saved_id = ' . $savedId)->execute('getSlaveRows');
            $existedCollectionIds = !empty($existedCollectionIds) ? array_column($existedCollectionIds,
                'collection_id') : [];

            if (!empty($collectionIds) && ($insertedCollectionIds = array_diff($collectionIds,
                    $existedCollectionIds))) {
                foreach ($insertedCollectionIds as $insertedCollectionId) {
                    db()->insert($colectionDataTable,
                        ['saved_id' => $savedId, 'collection_id' => (int)$insertedCollectionId, 'user_id' => Phpfox::getUserId()]);
                    db()->update($collectionTable, ['updated_time' => PHPFOX_TIME, 'total_item' => 'total_item + 1'],
                        'collection_id = ' . (int)$insertedCollectionId, false);
                    $savedItem = db()->select('s.saved_id, s.type_id, s.item_id, sc.image_path')->from($this->_sTable,
                        's')->join($colectionDataTable, 'scd', 'scd.saved_id = s.saved_id')->join($collectionTable,
                        'sc',
                        'sc.collection_id = scd.collection_id AND sc.collection_id = ' . (int)$insertedCollectionId)->where('s.saved_id = ' . (int)$savedId)->execute('getSlaveRow');
                    if (!empty($savedItem)) {
                        Phpfox::getService('saveditems.collection.process')->updateCollectionCover($insertedCollectionId,
                            $savedItem);
                    }
                }
                $clearCollectionCache = true;
            }

            if ($deletedCollectionIds = array_diff($existedCollectionIds, $collectionIds)) {
                foreach ($deletedCollectionIds as $deletedCollectionId) {
                    if ((db()->delete($colectionDataTable,
                        ['saved_id' => $savedId, 'collection_id' => (int)$deletedCollectionId]))) {
                        db()->update($collectionTable, ['total_item' => 'total_item - 1'],
                            'collection_id = ' . (int)$deletedCollectionId, false);
                        $lastSavedItem = db()->select('s.saved_id, s.type_id, s.item_id, sc.image_path')->from($this->_sTable, 's')
                            ->join($colectionDataTable, 'scd', 's.saved_id = scd.saved_id')
                            ->join($collectionTable, 'sc', 'sc.collection_id = scd.collection_id')
                            ->order('s.time_stamp DESC')
                            ->where(['sc.collection_id' => (int)$deletedCollectionId])
                            ->execute('getSlaveRow');

                        if (!empty($lastSavedItem)) {
                            Phpfox::getService('saveditems.collection.process')->updateCollectionCover($deletedCollectionId,
                                $lastSavedItem);
                        } else {
                            Phpfox::getService('saveditems.collection.process')->resetCollectionCover($deletedCollectionId);
                        }
                    }
                }
                $clearCollectionCache = true;
            }

            if ($clearCollectionCache) {
                $currentUserId = Phpfox::getUserId();
                $this->cache()->remove('saveditems_recent_updated_collections_' . $currentUserId);
                $this->cache()->remove('saved_collections_' . $currentUserId);
            }

            return true;
        }

        return false;
    }

    public function processItemStatus($savedId, $status = 0)
    {
        if (!in_array((int)$status, [0, 1])) {
            return false;
        }
        return db()->update($this->_sTable, ['unopened' => $status], 'saved_id = ' . (int)$savedId);
    }

    public function markAsOpened($savedId, $redirect = false)
    {
        db()->update($this->_sTable, ['unopened' => 0], 'saved_id = ' . (int)$savedId);
        if ($redirect) {
            return Phpfox::getService('saveditems')->getLinkById($savedId);
        }
        return true;
    }

    public function save($params)
    {
        $isSave = isset($params['is_save']) ? $params['is_save'] : true;
        $userId = !empty($params['user_id']) ? (int)$params['user_id'] : Phpfox::getUserId();
        $valid = false;
        $moduleId = '';
        $section = '';
        $id = 0;
        $exceptionTypes = Phpfox::getService('saveditems')->getExceptionalTypes();
        $usingSavedId = $savedId = !empty($params['saved_id']) ? $params['saved_id'] : false;
        $removeItemFromCollection = false;

        if (!empty($params['type_id']) && !empty($params['item_id']) && !in_array($params['type_id'],
                $exceptionTypes)) {
            $typeCount = explode('_', $params['type_id']);
            if (!empty($typeCount[0]) && Phpfox::isModule($typeCount[0])) {
                $valid = true;
                $moduleId = $typeCount[0];
                $section = !empty($typeCount[1]) ? $typeCount[1] : '';
            }
        }

        if (!$valid && !empty($params['saved_id'])) {
            $row = db()->select('type_id, item_id')->from($this->_sTable)->where('saved_id = ' . (int)$params['saved_id'])->execute('getSlaveRow');
            if (!empty($row)) {
                $typeCount = explode('_', $row['type_id']);
                if (!in_array($row['type_id'],
                        $exceptionTypes) && !empty($typeCount[0]) && Phpfox::isModule($typeCount[0])) {
                    $moduleId = $typeCount[0];
                    $section = !empty($typeCount[1]) ? $typeCount[1] : '';
                    $params = array_merge($params, $row);
                    $savedId = $params['saved_id'];
                    $valid = true;
                }
            }
        }

        (($sPlugin = \Phpfox_Plugin::get('saveditems.service_saveditems_process_save__start')) ? eval($sPlugin) : false);

        if ($valid) {
            if (!$isSave) {
                if (!$usingSavedId) {
                    $savedId = db()->select('saved_id')->from($this->_sTable)->where('type_id = "' . $params['type_id'] . '" AND item_id = ' . (int)$params['item_id'] . ' AND user_id = ' . (int)$userId)->execute('getSlaveField');
                }
                if (!empty($savedId)) {
                    if (!empty($params['collection_id'])) {
                        if (db()->delete(Phpfox::getT('saved_collection_data'), [
                            'saved_id' => (int) $savedId,
                            'collection_id' => (int) $params['collection_id']
                        ])) {

                            db()->update(Phpfox::getT('saved_collection'), ['total_item' => 'IF(total_item >= 1, total_item - 1, 0)'], ['collection_id' => (int) $params['collection_id']], false);

                            $lastSavedItem = db()->select('s.saved_id, s.type_id, s.item_id, sc.image_path')
                                ->from($this->_sTable, 's')
                                ->join(Phpfox::getT('saved_collection_data'), 'scd', 's.saved_id = scd.saved_id')
                                ->join(Phpfox::getT('saved_collection'), 'sc', 'sc.collection_id = scd.collection_id')
                                ->where(['sc.collection_id' => (int) $params['collection_id']])
                                ->order('s.time_stamp DESC')
                                ->execute('getSlaveRow');

                            if (!empty($lastSavedItem)) {
                                Phpfox::getService('saveditems.collection.process')->updateCollectionCover($params['collection_id'], $lastSavedItem);
                            } else {
                                Phpfox::getService('saveditems.collection.process')->resetCollectionCover($params['collection_id']);
                            }
                            $removeItemFromCollection = true;
                        }
                    } elseif (db()->delete($this->_sTable,
                        'saved_id = ' . (int)$savedId . ' AND user_id = ' . (int)$userId)) {
                        $collections = db()->select('collection_id')->from(Phpfox::getT('saved_collection_data'))->where('saved_id = ' . (int)$savedId)->execute('getSlaveRows');
                        if (!empty($collections)) {
                            db()->delete(Phpfox::getT('saved_collection_data'),
                                'saved_id = ' . $savedId . ' AND collection_id IN (' . implode(',',
                                    array_column($collections, 'collection_id')) . ')');
                            db()->update(Phpfox::getT('saved_collection'), ['total_item' => 'total_item - 1'],
                                'collection_id IN (' . implode(',', array_column($collections, 'collection_id')) . ')',
                                false);

                            db()->select('MAX(s.saved_id) AS saved_id, scd.collection_id')->from($this->_sTable,
                                's')->join(Phpfox::getT('saved_collection_data'), 'scd',
                                'scd.saved_id = s.saved_id')->where('scd.collection_id IN (' . implode(',',
                                    array_column($collections,
                                        'collection_id')) . ')')->group('scd.collection_id')->union()->unionFrom('s');
                            $relatedSavedItems = db()->select('saveditems.saved_id, saveditems.type_id, saveditems.item_id, sc.image_path, sc.collection_id')->join($this->_sTable,
                                'saveditems',
                                'saveditems.saved_id = s.saved_id')->join(Phpfox::getT('saved_collection'), 'sc',
                                'sc.collection_id = s.collection_id')->execute('getSlaveRows');
                            $relatedSavedItemsParsed = [];
                            foreach ($relatedSavedItems as $relatedSavedItem) {
                                $relatedSavedItemsParsed[$relatedSavedItem['collection_id']] = $relatedSavedItem;
                            }
                            foreach ($collections as $collection) {
                                if (!empty($relatedSavedItemsParsed[$collection['collection_id']])) {
                                    Phpfox::getService('saveditems.collection.process')->updateCollectionCover($collection['collection_id'],
                                        $relatedSavedItemsParsed[$collection['collection_id']]);
                                } else {
                                    Phpfox::getService('saveditems.collection.process')->resetCollectionCover($collection['collection_id']);
                                }
                            }
                        }
                    }

                    $this->cache()->remove('saveditems_recent_updated_collections_' . $userId);

                    if ($removeItemFromCollection) {

                        $this->cache()->remove('saved_collections_' . $userId);
                        return true;
                    }

                    $id = $savedId;
                }
            } else {
                $check = db()->select('saved_id')->from($this->_sTable)->where('user_id = ' . (int)$userId . ' AND type_id = "' . $params['type_id'] . '" AND item_id = ' . (int)$params['item_id'])->execute('getSlaveField');
                if (!$check) {
                    $insert = [
                        'user_id' => $userId,
                        'type_id' => $params['type_id'],
                        'item_id' => (int)$params['item_id'],
                        'link' => !empty($params['link']) ? $params['link'] : '',
                        'time_stamp' => PHPFOX_TIME
                    ];
                    $id = db()->insert($this->_sTable, $insert);
                }
            }

            if ($id && $moduleId) {
                if (Phpfox::hasCallback($moduleId, 'saveItem')) {
                    Phpfox::callback($moduleId . '.saveItem', [
                        'user_id' => $userId,
                        'section' => $section,
                        'item_id' => (int)$params['item_id'],
                        'is_save' => $isSave
                    ]);
                }
                $this->cache()->remove('saveditems_types_' . $userId);

                (($sPlugin = \Phpfox_Plugin::get('saveditems.service_saveditems_process_save__action')) ? eval($sPlugin) : false);

                return $id;
            }
        }

        (($sPlugin = \Phpfox_Plugin::get('saveditems.service_saveditems_process_save__end')) ? eval($sPlugin) : false);

        return false;
    }
}