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
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Resource\FriendFeedResource;
use Apps\Core_MobileApi\Api\Resource\FriendListItemResource;
use Apps\Core_MobileApi\Api\Resource\FriendListResource;
use Apps\Core_MobileApi\Api\Resource\FriendMentionResource;
use Apps\Core_MobileApi\Api\Resource\FriendRequestResource;
use Apps\Core_MobileApi\Api\Resource\FriendResource;
use Apps\Core_MobileApi\Api\Resource\FriendSearchResource;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Resource\PageResource;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Friend_Service_List_Process;
use Friend_Service_Process;
use Phpfox;
use Phpfox_Plugin;

class FriendApi extends AbstractResourceApi implements MobileAppSettingInterface, ActivityFeedInterface
{

    public function __naming()
    {
        return [
            'friend/list'      => [
                'get'  => 'findAllList',
                'post' => 'createFriendList',
            ],
            'friend/list/item' => [
                'get'    => 'getFriendInList',
                'post'   => 'addFriendToList',
                'delete' => 'deleteFriendInList',
            ],
            "friend/list/:id"  => [
                "maps"  => [
                    "get"    => 'findFriendList',
                    "delete" => 'deleteFriendList',
                    'put'    => 'updateFriendList',
                ],
                "where" => [
                    'id' => '(\d+)',
                ],
            ],
            'friend/search'    => [
                'get' => 'searchFriend'
            ],
            'friend/mutual'    => [
                'get' => 'findAllMutualFriends'
            ],
            'friend/mentions'  => [
                'get' => 'findFriendToMention'
            ]
        ];
    }

    public function processRow($friend, $resource = 'friend')
    {
        switch ($resource) {
            case 'friend-feed':
                $forResource = FriendFeedResource::class;
                break;
            case 'friend-list':
                $forResource = FriendListItemResource::class;
                break;
            default:
                $forResource = FriendResource::class;
                break;
        }
        $resource = $this->populateResource($forResource, $friend);
        if ($this->request()->get('format') == FriendResource::MINIMAL_FORMAT) {
            $resource->setOutputFormat(FriendResource::MINIMAL_FORMAT);
            return $resource;
        }
        list($iTotal, $aMutual) = $this->getFriendService()->getMutualFriends($friend['friend_user_id'], 3, false);
        $lists = $this->getFriendListService()->getListForUser($friend['friend_user_id']);
        $resource->setLists($this->getActiveList($lists));
        $resource->setMutualFriends(['total' => $iTotal, 'friends' => $aMutual]);

        return $resource;
    }

    private function getActiveList($lists)
    {
        $newLists = null;
        foreach ($lists as $list) {
            if (!empty($list['is_active'])) {
                $newLists[] = $list;
            }
        }
        return $newLists;
    }

    /**
     * All user's friend List
     *
     * @param $params
     *
     * @return array|bool
     */
    public function findAllList($params = [])
    {
        $params = $this->resolver->setDefined(['q'])->resolve($params)->getParameters();
        if (!$this->getUser()->getId()) {
            return $this->success([]);
        }
        $conds = '';
        if (!empty($params['q'])) {
            $conds = ' AND fl.name LIKE "%' . $params['q'] . '%"';
        }
        $list = $this->getLists($conds);
        $list = array_map(function ($item) {
            return FriendListResource::populate($item)->toArray();
        }, $list);
        return $this->success($list);
    }

    private function getLists($conds = '')
    {
        return $this->database()->select('fl.list_id, fl.name, COUNT(fld.friend_user_id) AS used')
            ->from(':friend_list', 'fl')
            ->leftJoin(Phpfox::getT('friend_list_data'), 'fld', 'fld.list_id = fl.list_id')
            ->where('fl.user_id = ' . (int)$this->getUser()->getId() . $conds)
            ->group('fl.list_id')
            ->order('fl.name ASC')
            ->execute('getSlaveRows');
    }

    /**
     * All user's friend List
     *
     * @param $params
     *
     * @return array|bool|void
     */
    public function findFriendList($params = [])
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $list = Phpfox::getService("friend.list")
            ->getList($params['id'], Phpfox::getUserId());
        if (empty($list)) {
            return $this->notFoundError();
        }
        $list = FriendListResource::populate($list)->toArray();
        return $this->success($list);
    }

    public function getFriendInList($params)
    {
        return $this->findAll($params);
    }

    /**
     * List all friend of a user ofr current user
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $params = $this->resolver
            ->setDefined(['limit', 'q', 'page', 'user_id', 'list_id', 'format', 'is_add_list', 'view', 'owner_id'])
            ->setDefault([
                'page'        => 1,
                'limit'       => 50,
                'is_add_list' => false,
            ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int', ['min' => 1])
            ->setAllowedValues('format', [FriendResource::MINIMAL_FORMAT])
            ->resolve($params)
            ->getParameters();
        if (!empty($params['view']) && $params['view'] == 'mutual') {
            return $this->findAllMutualFriends($params);
        }
        if (!empty($params['is_add_list']) && $this->getFriendListService()->reachedLimit()) {
            return $this->permissionError($this->getLocalization()->translate('you_have_reached_your_limit'));
        }
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $userId = (!empty($params['user_id']) ? $params['user_id'] : $this->getUser()->getId());
        if (!Phpfox::getService('user.privacy')->hasAccess($userId, 'friend.view_friend')
            || Phpfox::getService('user.block')->isBlocked($userId, $this->getUser()->getId())) {
            return $this->success([]);
        }
        $search = '';
        if (strlen(trim($params['q'])) > 0) {
            $search = ' AND u.full_name LIKE \'%' . (trim($params['q'])) . '%\' ';
        }

        $cond = ['AND friend.is_page = 0 AND friend.user_id = ' . $userId . $search];
        $sort = 'friend.friend_id DESC';
        $friends = [];


        if ((int)$params['list_id'] > 0) {
            $this->database()->innerJoin(Phpfox::getT('friend_list_data'), 'fld',
                'fld.list_id = ' . (int)$params['list_id'] . ' AND fld.friend_user_id = friend.friend_user_id');
        }

        $iCnt = $this->database()->select('COUNT(DISTINCT u.user_id)')
            ->from(Phpfox::getT('friend'), 'friend')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = friend.friend_user_id AND u.status_id = 0')
            ->where($cond)
            ->execute('getSlaveField');

        if ($iCnt) {
            if ((int)$params['list_id'] > 0) {
                $this->database()->select('fld.list_id, ');
                $this->database()->innerJoin(Phpfox::getT('friend_list_data'), 'fld',
                    'fld.list_id = ' . (int)$params['list_id'] . ' AND fld.friend_user_id = friend.friend_user_id');
            }
            $friends = $this->database()->select('uf.dob_setting, friend.friend_id, friend.friend_user_id, friend.is_top_friend, friend.time_stamp, ' . Phpfox::getUserField())
                ->from(Phpfox::getT('friend'), 'friend')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = friend.friend_user_id AND u.status_id = 0')
                ->join(Phpfox::getT('user_field'), 'uf', 'u.user_id = uf.user_id')
                ->where($cond)
                ->limit($params['page'], (int)$params['limit'], $iCnt)
                ->order($sort)
                ->group('u.user_id')
                ->execute('getSlaveRows');
        }

        $this->processRows($friends, !empty($params['list_id']) ? 'friend-list' : 'friend');

        return $this->success($friends);
    }

    /**
     * @param $aRows array of item to process
     * @param $resource
     */
    public function processRows(&$aRows, $resource = 'friend')
    {
        foreach ($aRows as $key => $aRow) {
            $item = $this->processRow($aRow, $resource);
            $aRows[$key] = $item->toArray();
        }
    }

    public function findAllMutualFriends($params = [])
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $params = $this->resolver
            ->setDefined(['owner_id', 'page', 'limit'])
            ->setRequired(['user_id'])
            ->setAllowedTypes('user_id', 'int')
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setDefault([
                'page'  => 1,
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('owner_id', 'int')->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $ownerId = $params['owner_id'] ? $params['owner_id'] : $this->getUser()->getId();
        if ($ownerId == $params['user_id']) {
            return $this->validationParamsError(['user_id', 'owner_id']);
        }
        $cond = [];
        $cond[] = 'AND friend.user_id = ' . $ownerId;

        list(, $friends) = $this->getFriendService()->get($cond, 'friend.time_stamp DESC', $params['page'],
            $params['limit'], true, false, false, $params['user_id']);

        $this->processRows($friends);

        return $this->success($friends);
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
        // TODO: Implement findOne() method.
    }

    /**
     * Create new document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        // TODO: Implement create() method.
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
        // TODO: Implement update() method.
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
     * Un-friend
     * @route /friend/:id
     *
     * @param $params ['id'] Friend resource ID
     * @param $params ['friend_user_id'] The friend user ID
     *
     * @return mixed
     * @throws \Exception
     */
    function delete($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $params = $this->resolver
            ->setDefined(['id', 'friend_user_id', 'ignore_error'])
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->setAllowedTypes('friend_user_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (empty($params['id']) && empty($params['friend_user_id'])) {
            return $this->validationParamsError(['id', 'friend_user_id']);
        }

        if ($params['friend_user_id']) {
            $success = $this->getProcessService()->delete($params['friend_user_id'], false);
        } else {
            $success = $this->getProcessService()->delete($params['id'], true);
        }

        if ($success) {
            return $this->success([]);
        } else if ($params['ignore_error']) {
            return $this->success([]);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    /**
     * Create New Fiend List
     *
     * @param $params
     *
     * @return mixed
     */
    public function createFriendList($params)
    {
        $params = $this->resolver
            ->setDefined(['friends'])
            ->setAllowedTypes('friends', 'array')
            ->setRequired(['name'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        if (Phpfox::getLib('parse.format')->isEmpty(trim($params['name']))) {
            return $this->error($this->getLocalization()->translate('provide_a_name_for_your_list'));
        }
        if (Phpfox::getService('friend.list')->isFolder($params['name'])) {
            return $this->error($this->getLocalization()->translate('folder_already_use'));
        }
        if ($this->getFriendListService()->reachedLimit()) {
            return $this->permissionError($this->getLocalization()->translate('you_have_reached_your_limit'));
        }

        $id = $this->getListProcessService()
            ->add(stripcslashes($params['name']));

        if ($this->isPassed()) {
            if (!empty($params['friends'])) {
                foreach ($params['friends'] as $friend) {
                    if (!is_numeric($friend)) {
                        return $this->validationParamsError(['friends']);
                    }
                }
                if ($this->getListProcessService()->addFriendsTolist($id, $params['friends'])) {
                    return $this->success([
                        'id' => $id,
                    ]);
                }
            } else {
                return $this->success([
                    'id' => $id,
                ]);
            }
        }

        return $this->error($this->getErrorMessage());
    }

    /**
     * Update friend list
     *
     * @param $params
     *
     * @return mixed
     */
    public function updateFriendList($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $params = $this->resolver
            ->setRequired(['name', 'id'])
            ->resolve($params)->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        if (Phpfox::getLib('parse.format')->isEmpty(trim($params['name']))) {
            return $this->error($this->getLocalization()->translate('provide_a_name_for_your_list'));
        }
        if (Phpfox::getService('friend.list')->isFolder($params['name'], $params['id'])) {
            return $this->error($this->getLocalization()->translate('folder_already_use'));
        }
        $result = $this->getListProcessService()
            ->update($params['id'],
                stripcslashes($params['name']));

        if ($this->isPassed() && $result) {
            return $this->success([
                'id' => $params['id'],
            ], [], $this->getLocalization()->translate('list_successfully_edited'));
        }

        return $this->error($this->getErrorMessage());
    }

    public function addFriendToList($params)
    {
        $params = $this->resolver
            ->setDefined(['friend_user_id', 'friends'])
            ->setRequired(['list_id'])
            ->setAllowedTypes('list_id', 'int', ['min' => 1])
            ->setAllowedTypes('friends', 'array')
            ->setAllowedTypes('friend_user_id', 'int')
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        if (empty($params['friends']) && empty($params['friend_user_id'])) {
            return $this->validationParamsError(['friends', 'friend_user_id']);
        }

        if (!Phpfox::getService('friend.list')->getList($params['list_id'], Phpfox::getUserId())) {
            return $this->notFoundError();
        }

        $result = false;
        if (!empty($params['friends'])) {
            foreach ($params['friends'] as $friend) {
                if (!is_numeric($friend)) {
                    return $this->validationParamsError(['friends']);
                }
            }
            $result = $this->getListProcessService()->addFriendsTolist($params['list_id'], $params['friends']);
        }
        if (!empty($params['friend_user_id'])) {
            $result = $this->getListProcessService()->addFriendsTolist($params['list_id'], $params['friend_user_id']);
        }
        if ($this->isPassed() && $result) {
            $this->cache()->remove('friend_list_' . $params['list_id']);
            return $this->success([], [], $this->getLocalization()->translate('added_friends_to_list_successfully'));
        }

        return $this->error($this->getErrorMessage());
    }

    /**
     * Delete a friend list
     *
     * @param $params ['id'] The FriendList id
     *
     * @return mixed
     */
    public function deleteFriendList($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $success = $this->getListProcessService()->delete($params['id']);
        if ($success) {
            return $this->success([]);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    /**
     * Remove friend out of a list
     *
     * @param $params
     *
     * @return mixed
     */
    public function deleteFriendInList($params)
    {
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $params = $this->resolver
            ->setRequired(['list_id', 'friend_user_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $success = $this->getListProcessService()->removeFriendsFromlist($params['list_id'], $params['friend_user_id']);
        if ($success) {
            $this->cache()->remove('friend_list_' . $params['list_id']);
            return $this->success([]);
        } else {
            return $this->error($this->getErrorMessage());
        }
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
        // TODO: Implement form() method.
    }

    function loadResourceById($id, $returnResource = false, $isFeed = false)
    {
        $friend = $this->database()->select('uf.dob_setting, uf.city_location, uf.country_child_id, friend.friend_id, friend.friend_user_id, friend.is_top_friend, friend.time_stamp, p.photo_id as cover_photo_exists, u.custom_gender, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('friend'), 'friend')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = friend.user_id AND u.status_id = 0')
            ->join(Phpfox::getT('user_field'), 'uf', 'u.user_id = uf.user_id')
            ->leftJoin(Phpfox::getT('photo'), 'p', 'p.photo_id = uf.cover_photo')
            ->where([
                'friend.friend_id = ' . (int)$id
            ])
            ->group('u.user_id')
            ->execute('getSlaveRow');
        if (empty($friend)) {
            return null;
        }
        if ($friend['cover_photo_exists']) {
            $friend['cover'] = (new UserApi())->getUserCover($friend['cover_photo_exists'], '_1024');
        }
        if ($returnResource) {
            if ($isFeed) {
                $friend['friend_user_id'] = $friend['user_id'];
                $friend = $this->getFriendShip($friend['user_id'], $friend);
            }
            $friend = $this->processRow($friend, $isFeed);
        }

        return $friend;
    }


    private function getFriendShip($userId, $values)
    {
        $currentUser = $this->getUser()->getId();
        $values['is_friend'] = false;
        $values['is_friend_of_friend'] = false;
        $values['is_friend_request'] = false;
        if ($currentUser && Phpfox::isModule('friend') && $currentUser != $userId) {
            $values['is_friend'] = $this->getFriendService()->isFriend($currentUser, $userId);
            $values['is_friend_of_friend'] = ($this->getFriendService()->isFriendOfFriend($userId) ? true : false);
            if (!$values['is_friend']) {
                $iRequestId = Phpfox::getService('friend.request')->isRequested($currentUser, $userId, true, true);
                if ($iRequestId) {
                    $values['is_friend_request'] = 2;
                    $aRequest = Phpfox::getService('friend.request')->getRequest($iRequestId, true);
                    $values['is_ignore_request'] = ($aRequest) ? $aRequest['is_ignore'] : false;
                } else {
                    $values['is_friend_request'] = $values['is_ignore_request'] = false;
                }
                $values['is_friend_request_id'] = $iRequestId;
                if (!$values['is_friend_request']) {
                    $iRequestId = Phpfox::getService('friend.request')->isRequested($values['user_id'], $currentUser, true, true);
                    $values['is_friend_request'] = ($iRequestId ? 3 : false);
                    $values['is_friend_request_id'] = $iRequestId;
                }
            }
        }
        return $values;
    }

    /**
     * @return Friend_Service_Process|mixed
     */
    private function getProcessService()
    {
        return Phpfox::getService('friend.process');
    }

    /**
     * @return Friend_Service_List_Process|mixed
     */
    private function getListProcessService()
    {
        return Phpfox::getService('friend.list.process');
    }

    /**
     * @return \Friend_Service_List_List
     */
    private function getFriendListService()
    {
        return Phpfox::getService('friend.list');
    }

    /**
     * @return \Friend_Service_Friend
     */
    private function getFriendService()
    {
        return Phpfox::getService('friend');
    }

    public function getActions()
    {
        return [
            'friend/unfriend'            => [
                'method'    => 'delete',
                'url'       => 'mobile/friend',
                'data'      => 'friend_user_id=:user, ignore_error=1',
                'new_state' => 'friendship=0',
            ],
            'friend/cancel_request'      => [
                'method'    => 'delete',
                'url'       => 'mobile/friend/request',
                'data'      => 'friend_user_id=:user',
                'new_state' => 'friendship=0',
            ],
            'friend/add_request'         => [
                'method'    => 'post',
                'url'       => 'mobile/friend/request',
                'data'      => 'friend_user_id=:user, ignore_error=1',
                'new_state' => 'friendship=3'
            ],
            'friend/accept_request'      => [
                'url'       => 'mobile/friend/request',
                'method'    => 'put',
                'data'      => 'action=approve, friend_user_id=:user, ignore_error=1',
                'new_state' => 'is_deleted=true',
            ],
            'friend/deny_request'        => [
                'url'       => 'mobile/friend/request',
                'method'    => 'put',
                'data'      => 'action=deny, friend_user_id=:user, ignore_error=1',
                'new_state' => 'is_ignore=true',
            ],
            'friend/accept_relationship' => [
                'url'       => 'mobile/friend/request',
                'method'    => 'put',
                'data'      => 'action=approve, friend_user_id=:user, ignore_error=1, relation_data_id=:relation_data_id',
                'new_state' => 'is_deleted=true'
            ],
            'friend/deny_relationship'   => [
                'url'       => 'mobile/friend/request',
                'method'    => 'put',
                'data'      => 'action=deny, friend_user_id=:user, ignore_error=1, relation_data_id=:relation_data_id',
                'new_state' => 'is_deleted=true'
            ],
            'friend/list/edit_name'      => [
                'routeName' => 'formEdit',
                'params'    => [
                    'module_name'   => 'friend',
                    'resource_name' => 'friend_list',
                    'formType'      => 'edit_name',
                ]
            ],
            'friend/list/delete'         => [
                'method' => 'delete',
                'url'    => 'mobile/friend/list/:id',
            ],
            'friend/list/item/delete'    => [
                'method' => 'delete',
                'url'    => 'mobile/friend/list/:list_id',
            ],
        ];
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('friend', [
            'title'           => $l->translate('friends'),
            'home_view'       => 'menu',
            'main_resource'   => new FriendResource([]),
            'other_resources' => [
                new FriendListItemResource([]),
                new FriendRequestResource([]),
                new FriendListResource([]),
                new FriendSearchResource([]),
                new FriendFeedResource([]),
                new FriendMentionResource([])
            ],
            'expose_actions'  => $this->getActions(),
        ]);
        $resourceName = (new FriendResource([]))->getResourceName();
        $headerButtons[$resourceName] = [];
        if ($this->getSetting()->getUserSetting('friend.can_add_folders') && !$this->getFriendListService()->reachedLimit()) {
            $headerButtons[$resourceName][] = [
                'icon'   => 'plus',
                'action' => '@friend/add-list',
                'params' => [
                    'resource_name' => $resourceName,
                ]
            ];
        }
        $headerButtons[(new FriendListResource([]))->getResourceName()] = $headerButtons[$resourceName];
        $app->addSetting('home.header_buttons', $headerButtons);

        return $app;
    }

    /**
     * Get for display on activity feed
     *
     * @param $param
     * @param $item array of data get from database
     *
     * @return array
     */
    public function getFeedDisplay($param, $item)
    {
        $resource = $this->loadResourceById($param['item_id'], true, true);
        if (!$resource) {
            //Get feed by owner
            if (isset($param['force_user']) && $param['force_user']['user_id'] == $param['parent_user_id']) {
                //Get user id as embed
                $userId = $param['user_id'];
            } else {
                $userId = $param['parent_user_id'];
            }
            $friend = $this->database()->select('u.*, uf.dob_setting, uf.city_location, uf.country_child_id, uf.total_friend, p.photo_id as cover_photo_exists')
                ->from(Phpfox::getT('user'), 'u')
                ->join(Phpfox::getT('user_field'), 'uf', 'u.user_id = uf.user_id')
                ->leftJoin(Phpfox::getT('photo'), 'p', 'p.photo_id = uf.cover_photo')
                ->where('u.user_id = ' . (int)$userId)
                ->execute('getSlaveRow');
            if ($friend['cover_photo_exists']) {
                $friend['cover'] = (new UserApi())->getUserCover($friend['cover_photo_exists'], '_1024');
            }
            if ($friend) {
                $friend['friend_user_id'] = $friend['user_id'];
                $friend = $this->getFriendShip($friend['user_id'], $friend);
                $resource = $this->processRow($friend, 'friend-feed');
            }
        }
        if ($resource) {
            return $resource->displayShortFields()->toArray();
        }
        return null;
    }

    /**
     * Support search friend to invite
     *
     * @param $params
     *
     * @return array|bool|mixed
     */
    public function searchFriend($params)
    {
        $params = $this->resolver
            ->setDefined(['q', 'limit', 'page', 'online', 'item_type', 'item_id', 'for_friend_list'])
            ->setRequired(['item_type', 'item_id'])
            ->setDefault([
                'page'            => 1,
                'limit'           => 50,
                'online'          => 0,
                'for_friend_list' => 0
            ])
            ->setAllowedTypes('online', 'int', ['min' => 0])
            ->setAllowedTypes('item_id', 'int', ['min' => 1])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $service = NameResource::instance()->getApiServiceByResourceName($params['item_type']);

        if (!is_callable([$service, 'searchFriendFilter'])) {
            return $this->error();
        }
        $userId = $this->getUser()->getId();
        if (!$userId) {
            return $this->permissionError();
        }

        $conditions = [];
        $conditions[] = 'AND friend.is_page = 0';
        $oDb = \Phpfox_Database::instance();

        if ($params['q']) {
            $conditions[] = 'AND (u.full_name LIKE \'%' . $oDb->escape($params['q']) . '%\' OR (u.email LIKE \'%' . $oDb->escape($params['q']) . '@%\' OR u.email = \'' . $oDb->escape($params['q']) . '\'))';
        }

        list(, $friends) = Phpfox::getService('friend')->get($conditions, 'u.full_name ASC', $params['page'], $params['limit'], true, false, (bool)$params['online'], $userId);

        $result = [];
        if (count($friends)) {
            if ($params['for_friend_list'] && $params['item_type'] == 'friend') {
                $friends = $this->searchFriendFilter($params['item_id'], $friends, true);
            } else {
                $friends = $service->searchFriendFilter($params['item_id'], $friends);
            }
            foreach ($friends as $key => $friend) {
                $data = UserResource::populate($friend)->toArray(['id', 'full_name', 'avatar', 'is_featured', 'user_name']);
                $data['disable'] = false;
                if (isset($friend['is_active'])) {
                    $data['disable'] = true;
                    $data['status'] = $friend['is_active'];
                } else {
                    $data['status'] = '';
                }
                $result[] = $data;
            }
        }

        return $this->success($result);
    }

    public function searchFriendFilter($id, $friends, $forList = true)
    {
        if ($forList) {
            $friendInList = $this->getFriendListService()->getUsersByListId($id);
            if ($friendInList && count($friendInList)) {
                $friendIds = array_map(function ($value) {
                    return $value['user_id'];
                }, $friendInList);
                foreach ($friends as $iKey => $friend) {
                    if (in_array($friend['user_id'], $friendIds)) {
                        $friends[$iKey]['is_active'] = $this->getLocalization()->translate('is_added');
                        continue;
                    }
                }
            }
        }
        return $friends;
    }

    public function createAccessControl()
    {
        $this->accessControl = new UserAccessControl($this->getSetting(), $this->getUser());
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
        $screenSetting = new ScreenSetting('friend', []);
        $resourceName = FriendResource::populate([])->getResourceName();
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_HOME);
        $screenSetting->addSetting($resourceName, ScreenSetting::MODULE_LISTING);
        $screenSetting->addSetting($resourceName, 'mutualFriends', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'mutual_friends'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'      => ScreenSetting::SMART_RESOURCE_LIST,
                'module_name'    => 'friend',
                'resource_name'  => $resourceName,
                'list_view_name' => 'mutual_friend'
            ],
            'no_ads'                       => true
        ]);

        $screenSetting->addSetting($resourceName, 'userFriends', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'friends'
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component' => ScreenSetting::SMART_TABS,
                'tabs'      => [
                    [
                        'label'         => 'friends',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module_name'   => 'friend',
                        'resource_name' => $resourceName,
                        'item_view'     => 'friend',
                        'search'        => true,
                        'use_query'     => ['user_id' => ':user_id']
                    ],
                    [
                        'label'         => 'mutual_friends',
                        'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                        'module_name'   => 'friend',
                        'resource_name' => $resourceName,
                        'item_view'     => 'friend',
                        'search'        => true,
                        'use_query'     => ['user_id' => ':user_id', 'view' => 'mutual']
                    ],
                ]
            ],
            'no_ads'                       => true
        ]);

        $screenSetting->addSetting($resourceName, 'mainFriendRequest', [
            ScreenSetting::LOCATION_HEADER => [
                'component' => ScreenSetting::SIMPLE_HEADER,
                'title'     => 'friend_requests',
                'back'      => false
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'          => ScreenSetting::SMART_RESOURCE_LIST,
                'module_name'        => 'friend',
                'resource_name'      => FriendRequestResource::populate([])->getResourceName(),
                'listEmptyComponent' => [
                    'component' => 'empty_friend_request',
                    'image'     => $screenSetting->getAppImage('no-friend-request'),
                    'label'     => 'no_requests',
                    'sub_label' => 'you_don_have_any_new_friend_request',
                    'action'    => [
                        'label' => 'find_friends'
                    ],
                ]
            ],
            'no_ads'                       => true
        ]);
        $friendListResource = FriendListResource::populate([])->getResourceName();
        $screenSetting->addSetting($friendListResource, ScreenSetting::MODULE_DETAIL, [
            ScreenSetting::LOCATION_HEADER => [
                'component'    => 'item_header',
                'useItemTitle' => true
            ],
            ScreenSetting::LOCATION_MAIN   => [
                'component'     => ScreenSetting::SMART_RESOURCE_LIST,
                'module_name'   => 'friend',
                'resource_name' => 'friend_list_item',
                'use_query'     => ['list_id' => ':id']
            ],
            'screen_title'                 => $l->translate('friends') . ' > ' . $l->translate('friend_list') . ' - ' . $l->translate('mobile_detail_page')
        ]);
        return $screenSetting;
    }

    public function screenToController()
    {
        return [
            ScreenSetting::MODULE_HOME    => 'friend.index',
            ScreenSetting::MODULE_LISTING => 'friend.index'
        ];
    }

    public function findFriendToMention($params)
    {
        if (!Phpfox::isModule('pages') && !Phpfox::isModule('groups')) {
            return $this->findAll($params);
        }
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $params = $this->resolver
            ->setDefined(['limit', 'q', 'page'])
            ->setDefault([
                'page'  => 1,
                'limit' => 50
            ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int', ['min' => 1])
            ->setAllowedValues('format', [FriendResource::MINIMAL_FORMAT])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $search = '';
        if (strlen(trim($params['q'])) > 0) {
            $search = ' AND u.full_name LIKE \'%' . (trim($params['q'])) . '%\' ';
        }

        $userId = $this->getUser()->getId();
        $cond = [
            'OR (friend.is_page = 0 AND friend.user_id = ' . $userId . $search . ')',
        ];

        if ($sPlugin = Phpfox_Plugin::get('mobile.service_friend_api_findfriendtomention')) {
            eval($sPlugin);
            if (isset($mReturnFromPlugin)) {
                return $mReturnFromPlugin;
            }
        }

        $hasPageGroup = false;
        if (version_compare('4.7.10', Phpfox::VERSION, '<')) {
            if (Phpfox::isModule('pages')) {
                $cond[] = 'OR (u.profile_page_id > 0 AND p.item_type = 0 ' . $search . ')';
                $hasPageGroup = true;
            }
            if (Phpfox::isModule('groups')) {
                $sExtraCond = 'p.item_type = 1 AND u.profile_page_id > 0';
                if (Phpfox::hasCallback(Phpfox::getService('groups.facade')->getItemType(), 'getExtraBrowseConditions')
                ) {
                    $sExtraCond .= Phpfox::callback(Phpfox::getService('groups.facade')->getItemType() . '.getExtraBrowseConditions',
                        'p');
                }
                $cond[] = 'OR (' . $sExtraCond . ' ' . $search . ')';
                $hasPageGroup = true;
            }
        }
        if ($hasPageGroup) {
            db()->leftJoin(Phpfox::getT('pages'), 'p', 'u.profile_page_id = p.page_id');
        }

        $sort = 'u.profile_page_id ASC, friend.friend_id DESC';
        $friends = [];

        $iCnt = db()->select('COUNT(DISTINCT u.user_id)')
            ->from(Phpfox::getT('user'), 'u')
            ->leftJoin(Phpfox::getT('friend'), 'friend', 'u.user_id = friend.friend_user_id AND u.status_id = 0')
            ->where($cond)
            ->execute('getSlaveField');

        if ($iCnt) {
            db()->select('uf.dob_setting, friend.friend_id, friend.friend_user_id, friend.is_top_friend, friend.time_stamp, ' . Phpfox::getUserField());
            if ($hasPageGroup) {
                db()->select(', p.item_type as page_type')
                    ->leftJoin(Phpfox::getT('pages'), 'p', 'u.profile_page_id = p.page_id');
            }
            $friends = db()->from(Phpfox::getT('user'), 'u')
                ->join(Phpfox::getT('user_field'), 'uf', 'u.user_id = uf.user_id')
                ->leftJoin(Phpfox::getT('friend'), 'friend', 'u.user_id = friend.friend_user_id AND u.status_id = 0')
                ->where($cond)
                ->limit($params['page'], (int)$params['limit'], $iCnt)
                ->order($sort)
                ->group('u.user_id')
                ->execute('getSlaveRows');
        }
        $results = [];
        if (count($friends)) {
            foreach ($friends as $key => $friend) {
                $itemType = 'user';
                $result = [];
                if (!$friend['profile_page_id']) {
                    $result = FriendResource::populate($friend)->displayShortFields()->toArray(['id', 'resource_name', 'module_name', 'user', 'friendship', 'friend_user_id']);
                } else if ($hasPageGroup) {
                    if ($friend['page_type'] == 0) {
                        $page = (new PageApi())->loadResourceById($friend['profile_page_id']);
                        if (empty($page)) continue;
                        $friend = array_merge($friend, $page);
                        $result = PageResource::populate($friend)->displayShortFields()->toArray(['id', 'resource_name', 'module_name', 'image', 'title', 'user']);
                        $itemType = 'page';
                    } else {
                        $page = (new GroupApi())->loadResourceById($friend['profile_page_id']);
                        if (empty($page)) continue;
                        $friend = array_merge($friend, $page);
                        $result = GroupResource::populate($friend)->displayShortFields()->toArray(['id', 'resource_name', 'module_name', 'image', 'title', 'user']);
                        $itemType = 'group';
                    }
                }
                $results[] = FriendMentionResource::populate([
                    'item_type'   => $itemType,
                    'item_detail' => $result
                ])->toArray(['resource_name', 'module_name', 'item_type', 'item_detail', 'id']);
            }
        }
        $aUser = Phpfox::getUserBy();
        if (is_array($aUser) && !empty($aUser)) {
            $aUser['friend_user_id'] = $aUser['user_id'];
            $aUser['id'] = $aUser['user_id'];
            $result = FriendResource::populate($aUser)->displayShortFields()->toArray(['id', 'resource_name', 'module_name', 'user', 'friendship', 'friend_user_id']);
            $result['is_you'] = true;
            $results[] = FriendMentionResource::populate([
                'item_type'   => 'user',
                'item_detail' => $result
            ])->toArray(['resource_name', 'module_name', 'item_type', 'item_detail', 'id']);
        }
        return $this->success($results);
    }
}