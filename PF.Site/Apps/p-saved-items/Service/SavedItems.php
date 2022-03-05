<?php

namespace Apps\P_SavedItems\Service;

use Phpfox;
use Phpfox_Service;
use Phpfox_Plugin;

/**
 * Class SavedItems
 * @copyright [PHPFOX_COPYRIGHT]
 * @author phpFox LLC
 * @package Apps\P_SavedItems\Service
 */
class SavedItems extends Phpfox_Service
{
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('saved_items');
    }

    public function getSpecialResourceNamesForAddingSaveAction()
    {
        $types = ['video', 'quiz', 'poll', 'music_album'];

        (($sPlugin = Phpfox_Plugin::get('saveditems.service_saveditems_getspecialresourcenamesforaddingsaveaction')) ? eval($sPlugin) : false);

        return $types;
    }

    public function isItemBelongedToCollection($params)
    {
        $check = null;
        $conditions = [];
        if (isset($params['saved_id'])) {
            $conditions = ['saved_id' => (int)$params['saved_id']];
        } elseif (isset($params['type_id']) && isset($params['item_id'])) {
            $savedId = db()->select('saved_id')->from($this->_sTable)->where([
                'user_id' => Phpfox::getUserId(),
                'type_id' => $params['type_id'],
                'item_id' => (int)$params['item_id'],
            ])->execute('getSlaveField');
            if (!empty($savedId)) {
                $conditions = ['saved_id' => (int)$savedId];
            }
        }

        if (!empty($conditions)) {
            $check = db()->select('COUNT(collection_id)')->from(Phpfox::getT('saved_collection_data'))->where($conditions)->execute('getSlaveField');
            $check = !!$check;
        }

        return $check;
    }

    public function getExceptionalTypes()
    {
        $exceptionalTypes = [
            'groups_photo',
            'pages_photo',
            'pages_cover_photo',
            'user_photo',
            'pages_itemLiked',
            'user_cover',
            'groups_cover_photo',
        ];

        (($sPlugin = Phpfox_Plugin::get('saveditems.service_saveditems_get_exceptional_types')) ? eval($sPlugin) : false);

        return $exceptionalTypes;
    }

    /**
     * @param $params
     */
    public function getItem($params, $getForMobile = false)
    {
        if ($getForMobile) {
            $moduleId = '';
            $typeId = '';
            $itemId = '';
            $section = '';
            if (isset($params['saved_id'])) {
                $tempItem = db()->select('type_id, item_id')->from($this->_sTable)->where('saved_id = ' . (int)$params['saved_id'])->execute('getSlaveRow');
                if (!empty($tempItem)) {
                    $typeCount = explode('_', $tempItem['type_id']);
                    $moduleId = $typeCount[0];
                    $section = isset($typeCount[1]) ? $typeCount[1] : '';
                    $typeId = $tempItem['type_id'];
                    $itemId = $tempItem['item_id'];
                }
            }

            $exceptionalTypes = $this->getExceptionalTypesForGlobalSearch();

            if (!empty($typeId) && !empty($itemId) && (Phpfox::hasCallback($moduleId,
                        'globalUnionSearch') || in_array($typeId, $exceptionalTypes))) {

                if (in_array($typeId, $exceptionalTypes)) {
                    $this->__getGlobalSearchForSpecialTypes('', true, $typeId);
                    db()->unionFrom('item');
                } else {
                    Phpfox::callback($moduleId . '.globalUnionSearch', '');
                    $query = trim(db()->unionFrom('item', true)->execute('null'));
                    $query = str_replace("\n", ' ', $query);
                    if (!empty($query) && preg_match("/^FROM\((.*)\) AS item$/", $query, $match) && !empty($match[1])) {
                        $query = trim($match[1]);
                        $queryArray = explode('UNION ALL', $query);
                        $specialTypes = $this->getSpecialTypesForReplacement();
                        $unionQueryTemp = $this->parseQueryForSpecialTypes($queryArray, $specialTypes);
                        $query = '(' . trim($unionQueryTemp, ' UNION ALL ') . ') AS item';
                        db()->from($query);
                    }
                }

                $item = db()->select('item.*, saveditems.saved_id, saveditems.unopened, saveditems.link, ' . Phpfox::getUserField())->join(Phpfox::getT('saved_items'),
                    'saveditems',
                    'saveditems.type_id = item.item_type_id AND saveditems.item_id = item.item_id')->join(Phpfox::getT('user'),
                    'u',
                    'u.user_id = item.item_user_id')->where('saveditems.user_id = ' . Phpfox::getUserId() . ' AND saveditems.type_id = "' . $typeId . '" AND saveditems.item_id = ' . (int)$itemId)->limit(1)->execute('getSlaveRow');

                if (empty($item)) {
                    return false;
                }

                $item = $this->__processRow($item);

                if (Phpfox::hasCallback($moduleId, 'getSavedInformation')) {
                    $item['extra'] = Phpfox::callback($moduleId . '.getSavedInformation', [
                        'section' => $section,
                        'item_id' => $item['item_id'],
                    ]);
                }

                $specialAppTypes = $this->getSpecialTypesForReplacement();
                $specialAppTypes = array_combine(array_values($specialAppTypes), array_keys($specialAppTypes));

                switch ($item['item_type_id']) {
                    case 'pages_comment':
                    {
                        $specialType = 'pages';
                        break;
                    }
                    case 'groups_comment':
                    {
                        $specialType = 'groups';
                        break;
                    }
                    case 'event_comment':
                    {
                        $specialType = 'event';
                        break;
                    }
                    default:
                        $specialType = isset($specialAppTypes[$item['item_type_id']]) ? $specialAppTypes[$item['item_type_id']] : $item['item_type_id'];
                        break;
                }

                if (Phpfox::hasCallback($specialType, 'getSearchInfo')) {
                    $searchInfo = Phpfox::callback($specialType . '.getSearchInfo', $item);
                    if (empty($item['extra']['photo']) && !empty($searchInfo['item_display_photo']) && preg_match('/src=(\'|\")([\S]+)(\'|\")/',
                            $searchInfo['item_display_photo'], $match) && !empty($match[2])) {
                        $searchInfo['item_display_photo'] = $match[2];
                    }
                } else {
                    $searchInfo = Phpfox::getService('saveditems')->getSearchInfo($specialType, $item);
                }

                $item = array_merge($item, $searchInfo);
                if (!empty($item['extra']['photo'])) {
                    $item['item_display_photo'] = $item['extra']['photo'];
                }

                if (empty($item['item_name'])) {
                    if (Phpfox::hasCallback($specialType, 'getSearchTitleInfo')) {
                        $titleInfo = Phpfox::callback($specialType . '.getSearchTitleInfo');
                        $item['item_name'] = $titleInfo['name'];
                    } else {
                        $item['item_name'] = \Core\Lib::phrase()->isPhrase($item['item_type_id']) ? _p($item['item_type_id']) : (\Core\Lib::phrase()->isPhrase($specialType) ? _p($specialType) : _p('saveditems_type_' . $specialType));
                    }
                }

                $collections = $this->getCollectionRelatedToSavedItem($item['saved_id']);
                if (!empty($collections[$item['saved_id']])) {
                    $item['collections'] = $collections[$item['saved_id']];
                }

                return $item;
            }
        } else {
            $conditions = [];

            if (isset($params['saved_id'])) {
                $conditions = ['saved_id' => (int)$params['saved_id']];
            } elseif (isset($params['type_id']) && isset($params['item_id'])) {
                $conditions = [
                    'user_id' => isset($params['user_id']) ? (int)$params['user_id'] : Phpfox::getUserId(),
                    'type_id' => $params['type_id'],
                    'item_id' => $params['item_id'],
                ];
            }

            if (!empty($conditions)) {
                return db()->select('*')->from($this->_sTable)->where($conditions)->execute('getSlaveRow');
            }
        }

        return false;
    }

    /**
     * Support core apps to get more information in case these apps have not implemented this function yet.
     * @param $type
     * @param $item
     * @return array
     */
    public function getSearchInfo($type, $item)
    {
        $data = [];

        switch ($type) {
            case 'music_playlist':
                if (!empty($item['item_photo'])) {
                    $displayPhoto = Phpfox::getLib('image.helper')->display([
                        'server_id' => $item['item_photo_server'],
                        'file' => $item['item_photo'],
                        'path' => 'music.url_image',
                        'suffix' => '_500_square',
                        'return_url' => true,
                    ]);
                } else {
                    $displayPhoto = Phpfox::getParam('music.default_playlist_photo');
                }

                $data = [
                    'item_display_photo' => $displayPhoto,
                ];
                break;
        }

        return $data;
    }

    public function getCountReladtedCollectionToSavedItem($savedId)
    {
        $count = db()->select('COUNT(scd.collection_id)')->from(Phpfox::getT('saved_collection'),
            'sc')->join(Phpfox::getT('saved_collection_data'), 'scd',
            'scd.collection_id = sc.collection_id')->where('scd.saved_id  = ' . (int)$savedId)->execute('getSlaveField');
        return $count;
    }

    public function getCollectionRelatedToSavedItem($savedId)
    {
        $collectionParsed = [];
        $collections = db()->select('scd.saved_id, scd.collection_id, sc.name')->from(Phpfox::getT('saved_collection'),
            'sc')->join(Phpfox::getT('saved_collection_data'), 'scd',
            'scd.collection_id = sc.collection_id')->where('scd.saved_id ' . (is_array($savedId) ? 'IN (' . implode(',',
                    $savedId) . ')' : ' = ' . (int)$savedId))->order('sc.updated_time DESC, sc.name ASC')->execute('getSlaveRows');
        if (!empty($collections)) {
            foreach ($collections as $collection) {
                $savedId = $collection['saved_id'];
                unset($collection['saved_id']);
                $collectionParsed[$savedId][] = $collection;
            }
        }
        return $collectionParsed;
    }

    public function getStatisticByType($collectionId = null)
    {
        $cacheObject = $this->cache();
        $cacheId = $cacheObject->set('saveditems_types_' . Phpfox::getUserId() . ($collectionId ? '_collection_' . $collectionId : ''));
        $limit = 60; //minutes
        if (($types = $cacheObject->get($cacheId, $limit)) === false) {
            $unionQuery = $this->__getGlobalQuery();
            db()->from($unionQuery)
                ->join(Phpfox::getT('saved_items'), 'saveditems', 'saveditems.type_id = item.item_type_id AND saveditems.item_id = item.item_id');
            if (!empty($collectionId)) {
                db()->join(Phpfox::getT('saved_collection_data'), 'scd', 'scd.saved_id = saveditems.saved_id')
                    ->join(Phpfox::getT('saved_collection'), 'sc', 'sc.collection_id = scd.collection_id AND sc.collection_id = ' . (int)$collectionId);
            }
            $groupBySavedIdQuery = '(' . db()->select('item.item_type_id, item.item_id')
                    ->where('saveditems.user_id = ' . Phpfox::getUserId())->group('saveditems.saved_id')->execute(null) . ')';
            $types = db()->select('COUNT(saveditems.saved_id) AS total_item, saveditems.type_id')
                ->from($groupBySavedIdQuery, 'item')
                ->join(Phpfox::getT('saved_items'), 'saveditems',
                'saveditems.type_id = item.item_type_id AND saveditems.item_id = item.item_id AND saveditems.user_id = ' . Phpfox::getUserId())
                ->order('saveditems.type_id ASC')
                ->group('saveditems.type_id')
                ->execute('getSlaveRows');
            if (!empty($types)) {
                $specialTypes = $this->getSpecialTypesForReplacement();
                $specialTypes = array_combine(array_values($specialTypes), array_keys($specialTypes));
                foreach ($types as $key => $type) {
                    switch ($type['type_id']) {
                        case 'pages_comment':
                        {
                            $typeName = 'pages';
                            break;
                        }
                        case 'groups_comment':
                        {
                            $typeName = 'groups';
                            break;
                        }
                        case 'event_comment':
                        {
                            $typeName = 'event';
                            break;
                        }
                        default:
                            $typeName = isset($specialTypes[$type['type_id']]) ? $specialTypes[$type['type_id']] : $type['type_id'];
                            break;
                    }

                    if (Phpfox::hasCallback($typeName, 'getSearchTitleInfo')) {
                        $data = Phpfox::callback($typeName . '.getSearchTitleInfo');
                        $types[$key]['type_name'] = $data['name'];
                    } else {
                        $types[$key]['type_name'] = \Core\Lib::phrase()->isPhrase($type['type_id']) ? _p($type['type_id']) : (\Core\Lib::phrase()->isPhrase($typeName) ? _p($typeName) : _p('saveditems_type_' . $typeName));
                    }
                }
                $cacheObject->save($cacheId, $types);
            }

        }

        return $types;
    }

    public function getLinkById($savedId)
    {
        $row = db()->select('type_id, item_id, link')
            ->from($this->_sTable)
            ->where('saved_id = ' . (int)$savedId)
            ->execute('getSlaveRow');

        if (!empty($row)) {
            $link = !empty($row['link']) ? $row['link'] : '';
            if (Phpfox::hasCallback($row['type_id'], 'getLink')) {
                $link = Phpfox::callback($row['type_id'] . '.getLink', ['item_id' => $row['item_id']]);
            }
            return $link;
        }

        return false;
    }

    public function isSaved($typeId, $itemId, $getId = false)
    {
        $isSaved = db()->select('saved_id')->from($this->_sTable)->where('user_id = ' . Phpfox::getUserId() . ' AND type_id = "' . $typeId . '" AND item_id = ' . (int)$itemId)->execute('getSlaveField');
        return $getId ? (int)$isSaved : !!$isSaved;
    }

    public function isUnopened($savedId)
    {
        $row = db()->select('saved_id, unopened')->from($this->_sTable)->where([
            'saved_id' => (int)$savedId,
            'unopened' => 1,
        ])->execute('getSlaveRow');
        if (isset($row['saved_id'])) {
            return (int)$row['unopened'];
        }
        return null;
    }

    public function buildSectionMenu()
    {
        $aFilterMenu = [
            _p('saveditems_all_saved_items') => '',
        ];

        $collections = Phpfox::getService('saveditems.collection')->getMyCollections();
        if (Phpfox::getUserParam('saveditems.can_create_collection') || !empty($collections)) {
            $aFilterMenu[_p('saveditems_my_collections')] = 'saved.collections';
        }

        Phpfox::getLib('template')->buildSectionMenu('saved', $aFilterMenu);
    }

    public function query($limit = 10, $params = [])
    {
        $requestObject = $this->request();
        $status = isset($params['sort']) && in_array($params['sort'],
            ['unopened', 'opened']) ? $params['sort'] : $requestObject->get('status');
        $when = isset($params['when']) ? $params['when'] : $requestObject->get('when');
        $search = $requestObject->get('search');
        $page = $requestObject->get('page', 1);

        $count = 0;
        $items = [];
        $searchText = $this->preParse()->clean(!empty($search['text']) ? $search['text'] : (!empty($params['q']) ? $params['q'] : ''));

        $sort = '';
        if (isset($params['sort']) && !in_array($params['sort'], ['unopened', 'opened'])) {
            switch ($params['sort']) {
                case 'latest':
                    $sort = 'saveditems.time_stamp DESC';
                    break;
                case 'oldest':
                    $sort = 'saveditems.time_stamp ASC';
                    break;
                default:
                    break;
            }
        } else {
            $sort = $this->search()->getSort();
        }

        $unionQuery = $this->__getGlobalQuery($searchText);

        (($sPlugin = Phpfox_Plugin::get('saveditems.service_saveditems_start')) ? eval($sPlugin) : false);

        if (!empty($unionQuery)) {
            $conds = [];
            switch ($status) {
                case 'opened':
                {
                    $conds[] = ' AND saveditems.unopened = 0';
                    break;
                }
                case 'unopened':
                {
                    $conds[] = ' AND saveditems.unopened = 1';
                    break;
                }
                default:
                {
                    break;
                }
            }

            switch ($when) {
                case 'this-month':
                    $conds[] = ' AND saveditems.time_stamp >= \'' . Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->getThisMonth()) . '\'';
                    $lastDayOfMonth = Phpfox::getLib('date')->mktime(23, 59, 59, date('n'),
                        Phpfox::getLib('date')->lastDayOfMonth(date('n')), date('Y'));
                    $conds[] = ' AND saveditems.time_stamp <= \'' . Phpfox::getLib('date')->convertToGmt($lastDayOfMonth) . '\'';
                    break;
                case 'this-week':
                    $conds[] = ' AND saveditems.time_stamp >= ' . (int)Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->getWeekStart());
                    $conds[] = ' AND saveditems.time_stamp <= ' . (int)Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->getWeekEnd());
                    break;
                case 'today':
                    $endDay = Phpfox::getLib('date')->mktime(23, 59, 0, Phpfox::getTime('m'), Phpfox::getTime('d'),
                        Phpfox::getTime('Y'));
                    $conds[] = ' AND (saveditems.time_stamp >= \'' . Phpfox::getLib('date')->mktime(0, 0, 0,
                            Phpfox::getTime('m'), Phpfox::getTime('d'),
                            Phpfox::getTime('Y')) . '\' AND saveditems.time_stamp < \'' . Phpfox::getLib('date')->convertToGmt($endDay) . '\')';
                    break;
                default:
                    break;
            }

            if (($type = $requestObject->get('type')) && ($type != 'all')) {
                $conds[] = ' AND (saveditems.type_id = "' . $type . '")';
            }

            $bInCollectionView = false;
            if (($requestObject->get('req2') == 'collection' && ($collectionId = $requestObject->get('req3')))) {
                $bInCollectionView = true;
            }

            if (!$bInCollectionView) {
                $conds[] = ' AND saveditems.user_id = ' . Phpfox::getUserId();
            }

            $selectFields = 'item.*, saveditems.unopened, saveditems.saved_id, saveditems.link, ' . Phpfox::getUserField();
            if ($bInCollectionView) {
                $selectFields .= ' , saved_collection_data.user_id as added_user_id';
            }
            (($sPlugin = Phpfox_Plugin::get('saveditems.service_saveditems_middle')) ? eval($sPlugin) : false);

            db()->select('COUNT(DISTINCT(saveditems.item_id))')->from($unionQuery)->join(Phpfox::getT('saved_items'),
                'saveditems',
                'saveditems.type_id = item.item_type_id AND saveditems.item_id = item.item_id')->where($conds);
            if ((!empty($params['collection_id']) && ($collectionId = $params['collection_id'])) || $bInCollectionView) {
                db()->join(Phpfox::getT('saved_collection_data'), 'saved_collection_data',
                    'saved_collection_data.saved_id = saveditems.saved_id')->join(Phpfox::getT('saved_collection'),
                    'saved_collection',
                    'saved_collection.collection_id = saved_collection_data.collection_id AND saved_collection.collection_id = ' . (int)$collectionId);
            }

            $count = db()->execute('getSlaveField');

            if ($count > 0) {
                db()->select($selectFields)->from($unionQuery)->join(Phpfox::getT('user'), 'u',
                    'u.user_id = item.item_user_id')->join(Phpfox::getT('saved_items'), 'saveditems',
                    'saveditems.type_id = item.item_type_id AND saveditems.item_id = item.item_id')
                    ->where($conds)
                    ->limit($page,
                        $limit)->order($sort ? $sort : 'saveditems.time_stamp DESC');

                if ((!empty($params['collection_id']) && ($collectionId = $params['collection_id'])) || $bInCollectionView) {
                    db()->join(Phpfox::getT('saved_collection_data'), 'saved_collection_data',
                        'saved_collection_data.saved_id = saveditems.saved_id')->join(Phpfox::getT('saved_collection'),
                        'saved_collection',
                        'saved_collection.collection_id = saved_collection_data.collection_id AND saved_collection.collection_id = ' . (int)$collectionId);
                }

                $items = db()->group('saveditems.saved_id')->execute('getSlaveRows');

                $itemIds = array_column($items, 'saved_id');
                $collectionParsed = [];
                if (!empty($itemIds)) {
                    $collectionParsed = $this->getCollectionRelatedToSavedItem($itemIds);
                }

                foreach ($items as $key => $item) {
                    $item = $this->__checkPrivacy($item);
                    if (empty($item)) {
                        continue;
                    }
                    if ($bInCollectionView) {
                        $item['in_collection'] = true;
                    }
                    $item = $items[$key] = $this->__processRow($item);
                    $typeCount = explode('_', $item['item_type_id']);
                    $section = count($typeCount) == 2 ? $typeCount[1] : '';
                    $module = $typeCount[0];
                    if (Phpfox::hasCallback($module, 'getSavedInformation')) {
                        $items[$key]['extra'] = Phpfox::callback($module . '.getSavedInformation', [
                            'section' => $section,
                            'item_id' => $item['item_id'],
                        ]);
                    }

                    $specialAppTypes = $this->getSpecialTypesForReplacement();
                    $specialAppTypes = array_combine(array_values($specialAppTypes), array_keys($specialAppTypes));

                    switch ($item['item_type_id']) {
                        case 'pages_comment':
                        {
                            $specialType = 'pages';
                            break;
                        }
                        case 'groups_comment':
                        {
                            $specialType = 'groups';
                            break;
                        }
                        case 'event_comment':
                        {
                            $specialType = 'event';
                            break;
                        }
                        default:
                            $specialType = isset($specialAppTypes[$item['item_type_id']]) ? $specialAppTypes[$item['item_type_id']] : $item['item_type_id'];
                            break;
                    }

                    if (Phpfox::hasCallback($specialType, 'getSearchInfo')) {
                        $searchInfo = Phpfox::callback($specialType . '.getSearchInfo', $item);
                        if (empty($items[$key]['extra']['photo']) && !empty($searchInfo['item_display_photo']) && preg_match('/src=(\'|\")([\S]+)(\'|\")/',
                                $searchInfo['item_display_photo'], $match) && !empty($match[2])) {
                            $searchInfo['item_display_photo'] = $match[2];
                        }
                    } else {
                        $searchInfo = Phpfox::getService('saveditems')->getSearchInfo($specialType, $item);
                    }

                    $items[$key] = array_merge($items[$key], $searchInfo);
                    if (!empty($items[$key]['extra']['photo'])) {
                        $items[$key]['item_display_photo'] = $items[$key]['extra']['photo'];
                    }

                    if (empty($items[$key]['item_name'])) {
                        if (Phpfox::hasCallback($specialType, 'getSearchTitleInfo')) {
                            $titleInfo = Phpfox::callback($specialType . '.getSearchTitleInfo');
                            $items[$key]['item_name'] = $titleInfo['name'];
                        } else {
                            $items[$key]['item_name'] = \Core\Lib::phrase()->isPhrase($item['item_type_id']) ? _p($item['item_type_id']) : (\Core\Lib::phrase()->isPhrase($specialType) ? _p($specialType) : _p('saveditems_type_' . $specialType));
                        }
                    }


                    if (!empty($collectionParsed[$item['saved_id']])) {
                        $itemCollections = $collectionParsed[$item['saved_id']];
                        $collectionsId = array_column($itemCollections, 'collection_id');
                        $defaultCollection = array_shift($itemCollections);
                        $items[$key]['collections'] = [
                            'default' => $defaultCollection,
                            'count' => !empty($itemCollections) ? count($itemCollections) : 0,
                            'other_collections' => !empty($itemCollections) ? $itemCollections : [],
                            'id' => $collectionsId,
                        ];
                    }

                    $items[$key]['item_title_parsed'] = Phpfox::getLib('url')->cleanTitle(Phpfox::getLib('parse.output')->clean(strip_tags($item['item_title'])));
                    $items[$key]['item_title'] = Phpfox::getLib('parse.output')->clean(strip_tags($item['item_title']));
                    $items[$key]['link_parsed'] = urlencode($item['link']);

                    if ($bInCollectionView) {
                        $aAddedUser = Phpfox::getService('user')->get($items[$key]['added_user_id']);
                        if ($aAddedUser) {
                            $items[$key]['added_user'] = $aAddedUser;
                        } else {
                            unset($items[$key]);
                        }
                    }
                }
            }

            (($sPlugin = Phpfox_Plugin::get('saveditems.service_saveditems_end')) ? eval($sPlugin) : false);
        }

        return [$count, $items];
    }

    public function getGlobalQuery($canViewPrivate, $searchText = '')
    {
        return $this->__getGlobalQuery($searchText, $canViewPrivate);
    }

    public function getExceptionalTypesForGlobalSearch()
    {
        return [
            'user_status',
            'link',
            'pages_comment',
            'groups_comment',
            'event_comment',
            'forum_post',
            'music_playlist',
        ];
    }

    public function __getGlobalSearchForSpecialTypes($searchText = '', $getSeparated = false, $typeId = null)
    {
        if ($getSeparated) {
            switch ($typeId) {
                case 'user_status':
                    db()->select('item.status_id AS item_id, item.content AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'user_status\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server, item.privacy AS item_privacy')->from(Phpfox::getT('user_status'),
                        'item')->join(Phpfox::getT('user'), 'u',
                        'u.user_id = item.user_id')->where(db()->searchKeywords('item.content',
                            $searchText) . ' AND item.privacy = 0')->union();
                    break;
                case 'link':
                    db()->select('item.link_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'link\' AS item_type_id, item.image AS item_photo, NULL AS item_photo_server, item.privacy AS item_privacy')->from(Phpfox::getT('link'),
                        'item')->join(Phpfox::getT('user'), 'u',
                        'u.user_id = item.user_id')->where(db()->searchKeywords('item.title',
                            $searchText) . ' AND item.privacy = 0')->union();
                    break;
                case 'pages_comment':
                    db()->select('item.feed_comment_id AS item_id, item.content AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'pages_comment\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server, item.privacy AS item_privacy')->from(Phpfox::getT('pages_feed_comment'),
                        'item')->join(Phpfox::getT('user'), 'u',
                        'u.user_id = item.user_id')->join(Phpfox::getT('pages_feed'), 'pf',
                        'pf.item_id = item.feed_comment_id AND pf.type_id = "pages_comment"')->where(db()->searchKeywords('item.content',
                            $searchText) . ' AND item.privacy = 0')->union();
                    break;
                case 'groups_comment':
                    db()->select('item.feed_comment_id AS item_id, item.content AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'groups_comment\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server, item.privacy AS item_privacy')->from(Phpfox::getT('pages_feed_comment'),
                        'item')->join(Phpfox::getT('user'), 'u',
                        'u.user_id = item.user_id')->join(Phpfox::getT('pages_feed'), 'pf',
                        'pf.item_id = item.feed_comment_id AND pf.type_id = "groups_comment"')->where(db()->searchKeywords('item.content',
                            $searchText) . ' AND item.privacy = 0')->union();
                    break;
                case 'event_comment':
                    db()->select('item.feed_comment_id AS item_id, item.content AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'event_comment\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server, item.privacy AS item_privacy')->from(Phpfox::getT('event_feed_comment'),
                        'item')->join(Phpfox::getT('user'), 'u',
                        'u.user_id = item.user_id')->where(db()->searchKeywords('item.content',
                            $searchText) . ' AND item.privacy = 0')->union();
                    break;
                case 'forum_post':
                    db()->select('item.post_id AS item_id, item_text.text_parsed AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'forum_post\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server, 0 AS item_privacy')->from(Phpfox::getT('forum_post'),
                        'item')->join(Phpfox::getT('user'), 'u',
                        'u.user_id = item.user_id')->join(Phpfox::getT('forum_post_text'), 'item_text',
                        'item.post_id = item_text.post_id')->where(db()->searchKeywords('item_text.text_parsed',
                        $searchText))->union();
                    break;
                case 'music_playlist':
                    db()->select('item.playlist_id AS item_id, item.name AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'music_playlist\' AS item_type_id, item.image_path AS item_photo, item.server_id AS item_photo_server, item.privacy AS item_privacy')->from(Phpfox::getT('music_playlist'),
                        'item')->join(Phpfox::getT('user'), 'u',
                        'u.user_id = item.user_id')->where('item.view_id = 0 AND item.privacy = 0 AND ' . db()->searchKeywords('item.name',
                            $searchText))->union();
                    break;
                default:
                    break;
            }
        } else {
            $query = [];

            if (!Phpfox::hasCallback('user_status', 'globalUnionSearch')) {
                $userStatusQuery = '(SELECT item.status_id AS item_id, item.content AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'user_status\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server' . ' FROM ' . Phpfox::getT('user_status') . ' AS item JOIN ' . Phpfox::getT('user') . ' AS u ON u.user_id = item.user_id WHERE ' . db()->searchKeywords('item.content',
                        $searchText) . ' AND item.privacy = 0)';
                $query[] = $userStatusQuery;
            }

            if (!Phpfox::hasCallback('link', 'globalUnionSearch')) {
                $linkQuery = '(SELECT item.link_id AS item_id, item.title AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'link\' AS item_type_id, item.image AS item_photo, NULL AS item_photo_server' . ' FROM ' . Phpfox::getT('link') . ' AS item JOIN ' . Phpfox::getT('user') . ' AS u ON u.user_id = item.user_id WHERE ' . db()->searchKeywords('item.title',
                        $searchText) . ' AND item.privacy = 0)';
                $query[] = $linkQuery;
            }

            if (Phpfox::isAppActive('Core_Pages') && !Phpfox::hasCallback('pages_comment', 'globalUnionSearch')) {
                $pagesStatusQuery = '(SELECT item.feed_comment_id AS item_id, item.content AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'pages_comment\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server' . ' FROM ' . Phpfox::getT('pages_feed_comment') . ' AS item JOIN ' . Phpfox::getT('user') . ' AS u ON u.user_id = item.user_id JOIN ' . Phpfox::getT('pages_feed') . ' AS pf ON pf.item_id = item.feed_comment_id AND pf.type_id = "pages_comment" WHERE ' . db()->searchKeywords('item.content',
                        $searchText) . ' AND item.privacy = 0)';
                $query[] = $pagesStatusQuery;
            }
            if (Phpfox::isAppActive('PHPfox_Groups') && !Phpfox::hasCallback('groups_comment', 'globalUnionSearch')) {
                $groupsStatusQuery = '(SELECT item.feed_comment_id AS item_id, item.content AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'groups_comment\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server' . ' FROM ' . Phpfox::getT('pages_feed_comment') . ' AS item JOIN ' . Phpfox::getT('user') . ' AS u ON u.user_id = item.user_id JOIN ' . Phpfox::getT('pages_feed') . ' AS pf ON pf.item_id = item.feed_comment_id AND pf.type_id = "groups_comment" WHERE ' . db()->searchKeywords('item.content',
                        $searchText) . ' AND item.privacy = 0)';
                $query[] = $groupsStatusQuery;
            }
            if (Phpfox::isAppActive('Core_Events') && !Phpfox::hasCallback('event_comment', 'globalUnionSearch')) {
                $eventStatusQuery = '(SELECT item.feed_comment_id AS item_id, item.content AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'event_comment\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server' . ' FROM ' . Phpfox::getT('event_feed_comment') . ' AS item JOIN ' . Phpfox::getT('user') . ' AS u ON u.user_id = item.user_id WHERE ' . db()->searchKeywords('item.content',
                        $searchText) . ' AND item.privacy = 0)';
                $query[] = $eventStatusQuery;
            }

            if (Phpfox::isAppActive('Core_Forums') && !Phpfox::hasCallback('forum_post', 'globalUnionSearch')) {
                $forumPostQuery = '(SELECT item.post_id AS item_id, item_text.text_parsed AS item_title , item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'forum_post\' AS item_type_id, NULL AS item_photo, NULL AS item_photo_server' . ' FROM ' . Phpfox::getT('forum_post') . ' AS item JOIN ' . Phpfox::getT('user') . ' AS u ON u.user_id = item.user_id JOIN ' . Phpfox::getT('forum_post_text') . ' AS item_text ON item.post_id = item_text.post_id WHERE ' . db()->searchKeywords('item_text.text_parsed',
                        $searchText) . ')';
                $query[] = $forumPostQuery;
            }

            if (Phpfox::isAppActive('Core_Music') && !Phpfox::hasCallback('music_playlist', 'globalUnionSearch')) {
                $musicPlaylistStatusQuery = '(SELECT item.playlist_id AS item_id, item.name AS item_title, item.time_stamp AS item_time_stamp, item.user_id AS item_user_id, \'music_playlist\' AS item_type_id, item.image_path AS item_photo, item.server_id AS item_photo_server' . ' FROM ' . Phpfox::getT('music_playlist') . ' AS item JOIN ' . Phpfox::getT('user') . ' AS u ON u.user_id = item.user_id WHERE item.view_id = 0 AND item.privacy = 0 AND ' . db()->searchKeywords('item.name',
                        $searchText) . ')';
                $query[] = $musicPlaylistStatusQuery;
            }

            return $query;
        }
    }

    private function __getGlobalQuery($searchText = '', $canViewPrivate = null)
    {
        $specialTypes = $this->getSpecialTypesForReplacement();

        Phpfox::massCallback('globalUnionSearch', $searchText);
        $unionQuery = trim(db()->unionFrom('item', true)->execute(null));
        $unionQuery = str_replace("\n", ' ', $unionQuery);
        if (!empty($unionQuery) && preg_match("/^FROM\((.*)\) AS item$/", $unionQuery, $match) && !empty($match[1])) {
            $unionQuery = trim($match[1]);
            $queryArray = explode('UNION ALL', $unionQuery);
            $canViewPrivate = isset($canViewPrivate) ? $canViewPrivate : Phpfox::getUserParam('core.can_view_private_items');

            $additionalQueryArray = $this->__getGlobalSearchForSpecialTypes($searchText);

            (($sPlugin = Phpfox_Plugin::get('saveditems.service_saveditems__getglobalquery_start')) ? eval($sPlugin) : false);

            if (!empty($additionalQueryArray)) {
                $queryArray = array_merge($queryArray, $additionalQueryArray);
            }

            if ($canViewPrivate) {
                $unionQueryTemp = '';
                foreach ($queryArray as $query) {
                    $query = trim($query);
                    preg_match('/^\(SELECT(.*)FROM(.*)WHERE(.*)\)$/', $query, $queryMatch);
                    $from = trim($queryMatch[2]);
                    $fromParsedArray = explode('AS', $from);
                    $parts = explode(' ', trim($fromParsedArray[1]));
                    $mainAlias = $parts[0];
                    $hasPrivacy = false;
                    $where = preg_replace_callback('/' . $mainAlias . '\.privacy[ ]+\=[ ]+0/',
                        function () use ($mainAlias, &$hasPrivacy) {
                            $hasPrivacy = true;
                            return $mainAlias . '.privacy IN (0,1,2,3,4)';
                        }, trim($queryMatch[3]));

                    $where = preg_replace_callback('/' . $mainAlias . '\.(module_id|group_id)([ ]+)?(IS|is|\=|!\=)([ ]+)?[0-9a-zA-Z\'"_]+/',
                        function () {
                            return '1=1';
                        }, $where);

                    $where = trim($where, ' AND ');

                    $select = preg_replace_callback('/(\'|\")([a-z_]+)(\'|\")[ ]+AS[ ]+item_type_id/',
                        function ($match) use ($specialTypes) {
                            return '\'' . (isset($specialTypes[$match[2]]) ? $specialTypes[$match[2]] : $match[2]) . '\' AS item_type_id';
                        }, trim($queryMatch[1]));

                    $select .= ', ' . ($hasPrivacy ? $mainAlias . '.privacy AS item_privacy' : '0 AS item_privacy');
                    $unionQueryTemp .= '(SELECT ' . $select . ' FROM ' . $from . (!empty($where) ? ' WHERE ' . $where : '') . ') UNION ALL ';
                }
                $unionQuery = trim($unionQueryTemp, ' UNION ALL ');
            } else {
                $userQuery = $friendQuery = $friendOfFriendsQuery = $customQuery = $publicQuery = '';
                $friendOnly = Phpfox::getParam('core.friends_only_community');
                foreach ($queryArray as $query) {
                    $query = trim($query);
                    preg_match('/^\(SELECT(.*)FROM(.*)WHERE(.*)\)$/', $query, $queryMatch);
                    $from = trim($queryMatch[2]);
                    $where = trim($queryMatch[3]);
                    $fromParsedArray = explode('AS', $from);
                    $parts = explode(' ', trim($fromParsedArray[1]));
                    $mainAlias = $parts[0];
                    $hasPrivacy = false;

                    $where = preg_replace_callback('/' . $mainAlias . '\.(module_id|group_id)([ ]+)?(IS|is|\=|!\=)([ ]+)?[0-9a-zA-Z\'"_]+/',
                        function () {
                            return '1=1';
                        }, $where);
                    $where = trim($where, ' AND ');

                    $select = preg_replace_callback('/(\'|\")([a-z_]+)(\'|\")[ ]+AS[ ]+item_type_id/',
                        function ($match) use ($specialTypes) {
                            return '\'' . (isset($specialTypes[$match[2]]) ? $specialTypes[$match[2]] : $match[2]) . '\' AS item_type_id';
                        }, trim($queryMatch[1]));

                    //Current User
                    $whereTemp = preg_replace_callback('/' . $mainAlias . '\.privacy[ ]+\=[ ]+0/',
                        function () use (&$hasPrivacy, $mainAlias) {
                            $hasPrivacy = true;
                            return $mainAlias . '.privacy IN (1,2,3,4)';
                        }, $where);

                    $select .= ', ' . ($hasPrivacy ? $mainAlias . '.privacy AS item_privacy' : '0 AS item_privacy');

                    $userQuery .= '(SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . (!empty($whereTemp) ? $whereTemp . ' AND ' : '') . $mainAlias . '.user_id = ' . Phpfox::getUserId() . ') UNION ALL ';
                    //Friend
                    $whereTemp = preg_replace_callback('/' . $mainAlias . '\.privacy[ ]+\=[ ]+0/',
                        function () use ($mainAlias) {
                            return $mainAlias . '.privacy IN (1,2)';
                        }, $where);
                    $friendQuery .= '(SELECT ' . $select . ' FROM ' . $from . ' JOIN ' . Phpfox::getT('friend') . ' AS friend_sub_query ON friend_sub_query.is_page = 0 AND friend_sub_query.user_id = ' . $mainAlias . '.user_id AND friend_sub_query.friend_user_id = ' . Phpfox::getUserId() . (!empty($whereTemp) ? ' WHERE ' . $whereTemp : '') . ') UNION ALL ';
                    //Friend of Friends
                    if (!$friendOnly) {
                        $whereTemp = preg_replace_callback('/' . $mainAlias . '\.privacy[ ]+\=[ ]+0/',
                            function () use ($mainAlias) {
                                return $mainAlias . '.privacy IN (2)';
                            }, $where);
                        $whereTemp = strtr('friend_sub_query.friend_user_id IN (SELECT friend_user_id from :friend WHERE is_page=0 AND user_id=:user_id)',
                                [
                                    ':friend' => Phpfox::getT('friend'),
                                    ':user_id' => intval(Phpfox::getUserId()),
                                ]) . (!empty($whereTemp) ? ' AND ' . $whereTemp : '');
                        $friendOfFriendsQuery .= '(SELECT ' . $select . ' FROM ' . $from . ' JOIN ' . Phpfox::getT('friend') . ' AS friend_sub_query ON friend_sub_query.is_page = 0 AND friend_sub_query.user_id = ' . $mainAlias . '.user_id WHERE ' . $whereTemp . ') UNION ALL ';
                    }
                    //Custom Privacy
                    $selectArray = explode(',', $select);
                    $itemTypeIdField = '';
                    $moduleId = '';
                    foreach ($selectArray as $textSelect) {
                        if (preg_match('/item_id/', $textSelect)) {
                            $textCount = explode('AS', $textSelect);
                            $itemTypeIdField = count($textCount) == 2 ? trim($textCount[0]) : '';
                        }
                        if (preg_match('/item_type_id/', $textSelect)) {
                            $textCount = explode('AS', $textSelect);
                            $moduleId = count($textCount) == 2 ? trim($textCount[0]) : '';
                        }
                    }
                    if (!empty($itemTypeIdField) && !empty($moduleId)) {
                        $fromTemp = $from . ' JOIN ' . Phpfox::getT('privacy') . ' AS privacy_sub_query ON privacy_sub_query.module_id = "' . trim(trim($moduleId,
                                '\''),
                                '"') . '" AND privacy_sub_query.item_id = ' . trim($itemTypeIdField) . ' JOIN ' . Phpfox::getT('friend_list_data') . ' AS friend_list_data_sub_query ON friend_list_data_sub_query.list_id = privacy_sub_query.friend_list_id AND friend_list_data_sub_query.friend_user_id = ' . Phpfox::getUserId();
                        $whereTemp = preg_replace_callback('/' . $mainAlias . '\.privacy[ ]+\=[ ]+0/',
                            function () use ($mainAlias) {
                                return $mainAlias . '.privacy IN (4)';
                            }, $where);
                        $customQuery .= '(SELECT ' . $select . ' FROM ' . $fromTemp . (!empty($whereTemp) ? ' WHERE ' . $whereTemp : '') . ') UNION ALL ';
                    }
                    //Public
                    $whereTemp = preg_replace_callback('/' . $mainAlias . '\.privacy[ ]+\=[ ]+0/',
                        function () use ($mainAlias) {
                            return $mainAlias . '.privacy IN (0)';
                        }, $where);
                    if ($friendOnly) {
                        $publicQuery .= '(SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . (!empty($whereTemp) ? $whereTemp . ' AND ' : '') . $mainAlias . '.user_id != ' . Phpfox::getUserId() . ') UNION ALL ';
                        $publicQuery .= '(SELECT ' . $select . ' FROM ' . $from . ' WHERE ' . (!empty($whereTemp) ? $whereTemp . ' AND ' : '') . $mainAlias . '.user_id = ' . Phpfox::getUserId() . ') UNION ALL ';
                    } else {
                        $publicQuery .= '(SELECT ' . $select . ' FROM ' . $from . (!empty($whereTemp) ? ' WHERE ' . $whereTemp : '') . ') UNION ALL ';
                    }
                }
                $unionQuery = trim($userQuery . $customQuery . $friendOfFriendsQuery . $friendQuery . $publicQuery,
                    ' UNION ALL');
            }
        }

        return !empty($unionQuery) ? '(' . $unionQuery . ') AS item' : '';
    }

    public function getSpecialTypesForReplacement()
    {
        return [
            'music' => 'music_song',
        ];
    }

    //TODO: Discuss if we can check group privacy
    private function __checkPrivacy($item)
    {
        return $item;
    }

    private function __processRow($item)
    {
        //special case with name of photo albums
        if ($item['item_type_id'] == 'photo_album' && preg_match('/(\'profile_pictures\'|\'timeline_photos\'|\'cover_photo\')/',
                $item['item_title'])) {
            if (preg_match('/\'profile_pictures\'/', $item['item_title'])) {
                $title = _p('user_profile_pictures', ['full_name' => $item['full_name']]);
            } elseif (preg_match('/\'timeline_photos\'/', $item['item_title'])) {
                $title = _p('user_timeline_photos', ['full_name' => $item['full_name']]);
            } elseif (preg_match('/\'cover_photo\'/', $item['item_title'])) {
                $title = _p('user_cover_photo', ['full_name' => $item['full_name']]);
            }

            if (!empty($title)) {
                $item['item_title'] = $title;
            }
        }
        //Permission
        $item['canShare'] = Phpfox::isModule('share') && Phpfox::getUserParam('share.can_share_items') && isset($item['item_privacy']) && $item['item_privacy'] == 0 && !Phpfox::getService('user.block')->isBlocked(null,
                $item['item_user_id']);
        $item['canSave'] = Phpfox::isUser() && Phpfox::getUserParam('saveditems.can_save_item');
        $item['canDoAction'] = $item['canShare'] || $item['canSave'];
        $item['canUnsaved'] = $this->getUnsavePermission($item['saved_id'], Phpfox::getUserId());
        //extra information
        if (!empty($item['in_collection'])) {
            $sLink = Phpfox::getLib('url')->makeUrl('saved',
                ['saved_id' => $item['saved_id']]);
        } else {
            $sLink = Phpfox::getLib('url')->makeUrl('saved',
                ['saved_id' => $item['saved_id'], 'unopened' => $item['unopened']]);
        }
        $item['saved_link'] = $sLink;
        return $item;
    }

    public function getUnsavePermission($iSaveItemId, $iUserId)
    {
        $bIsOwner = false;

        $aSaved = $this->get('saved_id = ' . $iSaveItemId);
        $bIsUserAddedItem = $aSaved['user_id'] == $iUserId;

        if ($this->request()->get('req2') == 'collection' && !empty($iCollectionId = $this->request()->get('req3'))) {
            $aCollection = Phpfox::getService('saveditems.collection')->getByFriend($iCollectionId, $iUserId);
            $bIsOwner = $aCollection['user_id'] == $iUserId;
        }
        return $bIsUserAddedItem || $bIsOwner;
    }

    public function get($sConds = '')
    {
        $aRows = db()->select('*')
            ->from(':saved_items');

        if (!empty($sConds)) {
            $aRows->where($sConds);
        }

        return $aRows->executeRow();
    }

    public function parseQueryForSpecialTypes($queryArray, $specialTypes)
    {
        $unionQueryTemp = '';
        foreach ($queryArray as $queryText) {
            $queryText = trim($queryText);
            preg_match('/^\(SELECT(.*)FROM(.*)WHERE(.*)\)$/', $queryText, $queryMatch);
            $from = trim($queryMatch[2]);
            $fromParsedArray = explode('AS', $from);
            $parts = explode(' ', trim($fromParsedArray[1]));
            $mainAlias = $parts[0];
            $hasPrivacy = false;
            $where = preg_replace_callback('/' . $mainAlias . '\.privacy[ ]+\=[ ]+0/',
                function () use ($mainAlias, &$hasPrivacy) {
                    $hasPrivacy = true;
                    return $mainAlias . '.privacy IN (0,1,2,3,4)';
                }, trim($queryMatch[3]));

            $where = preg_replace_callback('/' . $mainAlias . '\.(module_id|group_id)([ ]+)?(IS|is|\=|!\=)([ ]+)?[0-9a-zA-Z\'"_]+/',
                function () {
                    return '1=1';
                }, $where);

            $where = trim($where, ' AND ');

            $select = preg_replace_callback('/(\'|\")([a-z_]+)(\'|\")[ ]+AS[ ]+item_type_id/',
                function ($match) use ($specialTypes) {
                    return '\'' . (isset($specialTypes[$match[2]]) ? $specialTypes[$match[2]] : $match[2]) . '\' AS item_type_id';
                }, trim($queryMatch[1]));
            if (strpos($select, 'AS item_privacy') === false) {
                $select .= ', ' . ($hasPrivacy ? $mainAlias . '.privacy AS item_privacy' : '0 AS item_privacy');
            }

            $unionQueryTemp .= '(SELECT ' . $select . ' FROM ' . $from . (!empty($where) ? ' WHERE ' . $where : '') . ') UNION ALL ';
        }
        return $unionQueryTemp;
    }
}