<?php

namespace Apps\P_SavedItems\Service\Collection;

use Phpfox;
use Phpfox_Service;

/**
 * Class Collection
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Service\Collection
 */
class Collection extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('saved_collection');
    }

    public function getTotalItemsForCollections($userId = null, $limit = 30)
    {
        $cacheObject = $this->cache();
        $userId = $userId ? $userId : Phpfox::getUserId();
        $cacheId = $cacheObject->set('total_item_collections_' . $userId);
        if (($rows = $cacheObject->get($cacheId, $limit)) === false) {
            $userGroupId = Phpfox::getUserBy('user_group_id');
            $canViewPrivate = Phpfox::getService('user.group.setting')->getGroupParam($userGroupId,
                'core.can_view_private_items', false);
            $unionQuery = Phpfox::getService('saveditems')->getGlobalQuery($canViewPrivate);
            $tempRows = db()->select('COUNT(s.saved_id) AS total_item, sc.collection_id')->from($unionQuery)->join(Phpfox::getT('saved_items'),
                    's',
                    's.item_id = item.item_id AND s.type_id = item.item_type_id')->join(Phpfox::getT('saved_collection_data'),
                    'scd', 'scd.saved_id = s.saved_id')->join(Phpfox::getT('saved_collection'), 'sc',
                    'sc.collection_id = scd.collection_id')->where('sc.user_id = ' . (int)$userId)->group('scd.collection_id')->order('sc.updated_time DESC')->execute('getSlaveRows');
            db()->update($this->_sTable, ['total_item' => 0], 'user_id = ' . (int)$userId);
            if (!empty($tempRows)) {
                $rows = array_combine(array_column($tempRows, 'collection_id'), array_column($tempRows, 'total_item'));
                foreach ($rows as $collection_id => $total_item) {
                    db()->update($this->_sTable, ['total_item' => $total_item],
                        'collection_id = ' . (int)$collection_id . ' AND user_id = ' . (int)$userId);
                }
                $cacheObject->save($cacheId, $rows);
            }
            $this->cache()->remove('saveditems_types_' . (int)$userId);
            $this->cache()->remove('saveditems_recent_updated_collections_' . $userId);
        }
        return $rows;
    }

    public function getRecentUpdate($limit = 3, $cacheTime = 5)
    {
        $cacheObject = $this->cache();
        $cacheId = $cacheObject->set('saveditems_recent_updated_collections_' . Phpfox::getUserId());
        if (1==1 || ($collections = $cacheObject->get($cacheId, $cacheTime)) === false) {
            db()->select('collection_id, name, total_item, updated_time')
                ->from($this->_sTable)
                ->where('user_id = ' . Phpfox::getUserId())
                ->union();

            db()->select('c.collection_id, name, total_item, updated_time')
                ->from($this->_sTable, 'c')
                ->leftJoin(':saved_collection_friend', 'cf', 'c.collection_id = cf.collection_id')
                ->where('cf.friend_id = ' . Phpfox::getUserId())
                ->union();

            $collections = db()->select('collection_id, name, total_item')
                ->unionFrom('c')
                ->order('updated_time DESC')
                ->limit($limit)
                ->execute('getSlaveRows');
            if (!empty($collections)) {
                $cacheObject->save($cacheId, $collections);
            }
        }
        return $collections;
    }

    public function getMyCollections($iUserId = 0)
    {
        $userId = $iUserId? $iUserId : Phpfox::getUserId();
        $cacheObject = $this->cache();
        $cacheId = $cacheObject->set('saved_collections_' . $userId);
        if (($collections = $cacheObject->get($cacheId)) === false) {
            db()->select('c.*')
                        ->from($this->_sTable, 'c')
                        ->where('c.user_id = ' . $userId)
                        ->order('c.updated_time DESC')
                        ->union();

            db()->select('c.*')
                        ->from($this->_sTable, 'c')
                        ->join(':saved_collection_friend', 'cf', 'cf.collection_id = c.collection_id')
                        ->where('cf.friend_id = ' . $userId)
                        ->order('c.updated_time DESC')
                        ->union();

            $collections = db()->select('*')
                        ->unionFrom('c')
                        ->execute('getSlaveRows');

            $cacheObject->save($cacheId, $collections);
            $cacheObject->group('saveditems', $cacheId);
        }

        if (isset($limit)) {
            $collections = array_slice($collections, 0, $limit);
        }

        return $collections;
    }

    public function getPermissions(&$collection)
    {
        $collection['canEdit'] =
            (Phpfox::getUserParam('saveditems.can_edit_collection') && (Phpfox::getUserId() == $collection['user_id'])) ||
            $this->isInCollectionFriendList($collection['collection_id'], Phpfox::getUserId());
        $collection['canDelete'] =
            (Phpfox::getUserParam('saveditems.can_delete_collection') && (Phpfox::getUserId() == $collection['user_id'])) ||
            $this->isInCollectionFriendList($collection['collection_id'], Phpfox::getUserId());
        $collection['canAction'] = $collection['canEdit'] || $collection['canDelete'];
        $collection['isOwner'] = $collection['user_id'] == Phpfox::getUserId();
    }

    public function isInCollectionFriendList($iCollectionId, $iUserId)
    {
        return db()->select('collection_id')
            ->from(':saved_collection_friend')
            ->where('collection_id = ' . $iCollectionId . ' and friend_id = ' . $iUserId)
            ->executeField();
    }

    public function getForEdit($collectionId)
    {
        if (empty($collectionId)) {
            return false;
        }
        $collection = db()->select('*')->from($this->_sTable)->where('collection_id = ' . $collectionId . ' AND user_id = ' . Phpfox::getUserId())->execute('getSlaveRow');
        return $collection;
    }

    public function getByFriend($iCollectionId, $iUserId = 0)
    {
        if (empty($iCollectionId)) {
            return false;
        }
        if (empty($iUserId)) {
            $iUserId = Phpfox::getUserId();
        }
        return db()->select('c.*, cf.friend_id')
            ->from($this->_sTable, 'c')
            ->leftJoin(':saved_collection_friend', 'cf', 'c.collection_id = cf.collection_id')
            ->where('c.collection_id = ' . $iCollectionId . ' AND (user_id = ' . $iUserId . ' OR friend_id = ' . $iUserId . ' OR c.privacy = 0' . ')')
            ->execute('getSlaveRow');
    }

    public function getAddedToCollectionOfSavedItem($savedId)
    {
        return db()->select('COUNT(scd.saved_id)')->from(Phpfox::getT('saved_items'),
                's')->join(Phpfox::getT('saved_collection_data'), 'scd',
                'scd.saved_id = s.saved_id')->where('s.saved_id = ' . (int)$savedId)->execute('getSlaveField');
    }

    public function get($sCond = '', $iPage = 0, $iLimit = 0)
    {
        $aRows = db()->select('*')
            ->from(':saved_collection', 'c');

        if (!empty($sCond)) {
            $aRows->where($sCond);
        }

        if ($iPage > 0 && $iLimit > 0) {
            $aRows->limit($iPage, $iLimit);
        }

        return $aRows->executeRows();
    }

    public function getForBrowse($iUserId = 0, $iLimit = 0, $iPage = 0, &$iCnt = 0)
    {
        $userId = $iUserId? $iUserId : Phpfox::getUserId();

        db()->select('c.*')
            ->from($this->_sTable, 'c')
            ->where('c.user_id = ' . $userId)
            ->order('c.updated_time DESC')
            ->union();

        db()->select('c.*')
            ->from($this->_sTable, 'c')
            ->join(':saved_collection_friend', 'cf', 'cf.collection_id = c.collection_id')
            ->where('cf.friend_id = ' . $userId)
            ->order('c.updated_time DESC')
            ->union();

        $collections = db()->select('*')
            ->unionFrom('c')
            ->forCount();

        if ($iLimit > 0 && $iPage > 0) {
            $collections->limit($iPage, $iLimit);
        }
        $collections = $collections->execute('getSlaveRows');
        $iCnt = db()->forCount()->getCount();

        return $collections;
    }

    public function getCallbackCollections($aUser)
    {
        if (Phpfox::getUserId() == $aUser['user_id']) {
              db()->select('c.*')
                ->from(':saved_collection', 'c')
                ->where('user_id = ' . $aUser['user_id'])
                ->union();

              db()->select('c.*')
                  ->from(':saved_collection', 'c')
                  ->join(':saved_collection_friend', 'cf', 'c.collection_id = cf.collection_id and cf.friend_id = ' . $aUser['user_id'])
                  ->union();

              $aCollections = db()->select('c.*')
                  ->unionFrom('c')
                  ->forCount()
                  ->executeRows();
        } else {
            db()->select('*')
                ->from(':saved_collection', 'c')
                ->where('c.total_item > 0 and privacy = 0 and c.user_id = ' . $aUser['user_id'])
                ->union();

            if (Phpfox::isModule('friend') && Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $aUser['user_id'])) {
                db()->select('*')
                    ->from(':saved_collection', 'c')
                    ->where('c.total_item > 0 and privacy = 1 and c.user_id = ' . $aUser['user_id'])
                    ->union();
            }
            if (Phpfox::isModule('friend') && Phpfox::getService('friend')->isFriendOfFriend($aUser['user_id'])) {
                db()->select('*')
                    ->from(':saved_collection', 'c')
                    ->where('c.total_item > 0 and privacy = 2 and c.user_id = ' . $aUser['user_id'])
                    ->union();
            }

            if (Phpfox::isUser()) {
                db()->select('*')
                    ->from(':saved_collection', 'c')
                    ->where('c.total_item > 0 and privacy = 6 and c.user_id = ' . $aUser['user_id'])
                    ->union();
            }

            db()->select('sc.*')
                ->from(':saved_collection', 'sc')
                ->leftJoin(':saved_collection_friend', 'cf', 'sc.collection_id = cf.collection_id')
                ->join(':privacy', 'p', 'p.item_id = sc.collection_id')
                ->join(':friend_list_data', 'ld', 'ld.list_id = p.friend_list_id')
                ->where('module_id = \'saveditems_collection\' and friend_user_id = ' . Phpfox::getUserId() . ' and sc.privacy = 4 and sc.total_item > 0')
                ->union();

            //Join with the collection been added
            db()->select('sc.*')
                ->from(':saved_collection', 'sc')
                ->join(':saved_collection_friend', 'scf', 'sc.collection_id = scf.collection_id')
                ->join(':user', 'u', 'u.user_id = scf.friend_id')
                ->where('sc.user_id = ' . Phpfox::getUserId() . ' and scf.friend_id = ' . $aUser['user_id'])
                ->union();

            $aCollections = db()->select('*')
                ->unionFrom('collections')
                ->forCount()
                ->execute('getSlaveRows');


        }
        $iCntCollection = db()->forCount()->getCount();
        return [$iCntCollection, $aCollections];
    }

    public function getByCommunity($iCollectionId)
    {
        if (!Phpfox::isUser()) {
            return false;
        }

        if (empty($iCollectionId)) {
            return false;
        }

        return db()->select('c.*, cf.friend_id')
            ->from($this->_sTable, 'c')
            ->leftJoin(':saved_collection_friend', 'cf', 'c.collection_id = cf.collection_id')
            ->where('c.collection_id = ' . $iCollectionId . ' AND c.privacy = 6')
            ->execute('getSlaveRow');
    }

    public function isExistedInCollection($item_type, $item_id, $colection_id)
    {
        return db()->select('scd.saved_id')->from(Phpfox::getT('saved_items'),
            's')->join(Phpfox::getT('saved_collection_data'), 'scd',
            'scd.saved_id = s.saved_id')
            ->where([
                'type_id' => $item_type,
                'item_id' => $item_id,
                'scd.collection_id' => $colection_id
            ])
            ->execute('getSlaveField');
    }
}