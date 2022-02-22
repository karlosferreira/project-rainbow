<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Resource\FriendRequestResource;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;
use Phpfox_Error;

class FriendRequestApi extends AbstractResourceApi
{
    protected $friendDirection;

    const ONE_WAY = 'one_way_friendships';
    const TWO_WAY = 'two_way_friendships';

    public function __construct()
    {
        parent::__construct();
        $this->friendDirection = Phpfox::getParam('friend.friendship_direction', 'two_way_friendships');
    }

    public function __naming()
    {
        return [
            'friend/request'     => [
                'get'  => 'findAll',
                'post' => 'create',
                'put'  => 'update'
            ],
            "friend/request/:id" => [
                "maps"  => [
                    "delete" => 'delete',
                    "get"    => 'findOne'
                ],
                "where" => [
                    'id' => "(\d+)"
                ]
            ]
        ];
    }

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $params = $this->resolver
            ->setDefined(['limit', 'view', 'page'])
            ->setAllowedValues('view', ['all', 'pending', 'sent'])
            ->setDefault([
                'page'  => 1,
                'limit' => 10
            ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if ($params['view'] == "" || $params['view'] == "all") {
            list($cnt, $requests) = $this->getRequestService()
                ->get($params['page'], $params['limit']);
        } else if ($params['view'] == 'pending') {
            list($cnt, $requests) = $this->getRequestService()
                ->getPending($params['page'], $params['limit']);
        } else {
            throw new UnknownErrorException("Unknown requests");
        }


        $this->processRows($requests);

        return $this->success($requests, [
            'total' => $cnt
        ]);
    }

    /**
     * @param array $item
     *
     * @return FriendRequestResource
     */
    public function processRow($item)
    {
        $resource = FriendRequestResource::populate($item);
        if (empty($item['mutual_friends'])) {
            list($iTotal, $aMutual) = $this->getFriendService()->getMutualFriends($item['user_id'], 3, false);
            $resource->setMutualFriends(['total' => $iTotal, 'friends' => $aMutual]);
        }
        return $resource;
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
        $params = $this->resolver->setRequired(['id'])
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $request = $this->getRequestService()->getRequest($params['id']);

        if (empty($request)) {
            return $this->notFoundError();
        }

        return $this->success($this->processRow($request)->toArray());
    }

    /**
     * Set friend request to a user
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        $params = $this->resolver
            ->setDefined(['friend_user_id', 'ignore_error'])
            ->setRequired(['friend_user_id'])
            ->setAllowedTypes('friend_user_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();
        $friendId = $params['friend_user_id'];


        if ($friendId == $this->getUser()->getId()) {
            return $this->validationParamsError(['friend_user_id' => $this->getLocalization()->translate('invalid_friend_user')]);
        }
        $user = Phpfox::getService('user')->getUser($friendId);
        if (!$user) {
            return $this->notFoundError($this->getLocalization()->translate('user_not_found'));
        }
        $this->denyAccessUnlessGranted(UserAccessControl::ADD_FRIEND, UserResource::populate($user));

        if (Phpfox::getService('user.block')->isBlocked($friendId, $this->getUser()->getId())) {
            return $this->error(_p('unable_to_send_a_friend_request_to_this_user_at_this_moment'));
        }

        $bFriendRequestAwait = false;
        $iRequestId = $this->getRequestService()->isRequested($friendId, $this->getUser()->getId(), true);

        if ($iRequestId) {
            $aRequest = $this->getRequestService()->getRequest($iRequestId, true);
        }

        if (!empty($aRequest)) {
            if (empty($aRequest['is_ignore'])) {
                $bFriendRequestAwait = true;
            } else {
                $this->getProcessService()->delete($iRequestId, $friendId);
            }
        }

        if ($bFriendRequestAwait) {
            $success = $this->getFriendProcessService()->add($this->getUser()->getId(), $friendId);
        } else {
            $success = $this->processCreateFriendRequest($friendId);
        }

        if ($success && $this->isPassed()) {
            return $this->success([
                'friendship' => $this->friendDirection == self::ONE_WAY || $bFriendRequestAwait ? 1 : 3
            ], [], $this->getLocalization()->translate($this->friendDirection == self::ONE_WAY || $bFriendRequestAwait ? 'add_friend_successfully' : 'the_request_has_been_sent_successfully'));
        }

        if ($params['ignore_error']) {
            Phpfox_Error::reset();
            return $this->success([], [], $this->getLocalization()->translate('the_request_has_been_sent_successfully'));
        }
        return $this->error($this->getErrorMessage());
    }

    public function createAccessControl()
    {
        $this->accessControl = new UserAccessControl($this->getSetting(), $this->getUser());
    }

    /**
     * Response (approve/deny) Friend Request
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function update($params)
    {
        $params = $this->resolver->setRequired(['action', 'friend_user_id'])
            ->setDefined(['relation_data_id', 'ignore_error'])
            ->setAllowedValues('action', ['approve', 'deny'])
            ->setAllowedTypes('friend_user_id', 'int', ['min' => 1])
            ->setAllowedTypes('relation_data_id', 'int')
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        $userId = $this->getUser()->getId();
        if (!empty($params['relation_data_id'])) {
            $aRelationship = Phpfox::getService('custom.relation')->getDataById($params['relation_data_id']);
            if (!isset($aRelationship['with_user_id']) || $aRelationship['with_user_id'] != $userId) {
                return $this->permissionError();
            }
            if ($params['action'] == 'approve') {
                Phpfox::getService('custom.relation.process')->updateRelationship(0, $aRelationship['user_id'],
                    $aRelationship['with_user_id'], $params['relation_data_id']);
            } else {
                Phpfox::getService('custom.relation.process')->denyStatus($params['relation_data_id'],
                    $aRelationship['with_user_id']);
                if (Phpfox::isModule('friend')) {
                    $request = $this->loadFriendRequestToMe($params['friend_user_id']);
                    if (!empty($request)) {
                        Phpfox::getService('friend.request.process')->delete($request['request_id'],
                            $aRelationship['user_id']);
                    }
                }
            }
            return $this->success([], [], '');
        }

        if ($this->getFriendService()->isFriend($userId, $params['friend_user_id'])) {
            return $this->success([], [], $this->getLocalization()->translate('you_are_already_friend_of_this_user'));
        }

        if ($params['action'] == 'approve') {
            $result = $this->getFriendProcessService()->add($userId, $params['friend_user_id']);
            $message = $this->getLocalization()->translate("the_request_has_been_accepted_successfully");
        } else {
            $result = $this->getFriendProcessService()->deny($userId, $params['friend_user_id']);
            $message = $this->getLocalization()->translate("the_request_has_been_denied_successfully");
        }

        if ($result && $this->isPassed()) {
            return $this->success([], [], $message);
        }

        if ($params['ignore_error']) {
            Phpfox_Error::reset();
            return $this->success([], [], $message);
        }

        return $this->error($this->getErrorMessage());
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
     * Delete a document
     * DELETE: /resource-name/:id
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function delete($params)
    {
        $params = $this->resolver
            ->setDefined(['id', 'friend_user_id'])
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->setAllowedTypes('friend_user_id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();

        if (empty($params['id']) && empty($params['friend_user_id'])) {
            return $this->validationParamsError(['id', 'friend_user_id']);
        }
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!empty($params['id'])) {
            $request = $this->loadResourceById($params['id']);
        } else {
            $request = $this->loadMyRequestByFriendId($params['friend_user_id']);
        }

        if (empty($request) || $request['friend_user_id'] != $this->getUser()->getId()) {
            return $this->notFoundError();
        }
        $return = $this->getProcessService()->delete($request['request_id'], $request['friend_user_id']);

        if ($return) {
            return $this->success(['id' => $request['request_id']]);
        }
        return $this->error();
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

    function loadResourceById($id, $returnResource = false)
    {
        return $this->getRequestService()->getRequest($id, true);
    }

    protected function loadMyRequestByFriendId($friendId)
    {
        return $this->database()->select('*')
            ->from(':friend_request')
            ->where('user_id = ' . (int)$friendId . ' AND friend_user_id = ' . (int)$this->getUser()->getId())
            ->execute('getRow');
    }

    protected function loadFriendRequestToMe($friendId)
    {
        return $this->database()->select('*')
            ->from(':friend_request')
            ->where('friend_user_id = ' . (int)$friendId . ' AND user_id = ' . (int)$this->getUser()->getId())
            ->execute('getRow');
    }

    /**
     * @return \Friend_Service_Request_Request
     */
    protected function getRequestService()
    {
        return Phpfox::getService("friend.request");
    }

    /**
     * @return \Friend_Service_Friend
     */
    protected function getFriendService()
    {
        return Phpfox::getService('friend');
    }

    /**
     * @return \Friend_Service_Request_Process
     */
    protected function getProcessService()
    {
        return Phpfox::getService('friend.request.process');
    }

    protected function processCreateFriendRequest($friendId)
    {
        $userId = $this->getUser()->getId();

        if ($this->getFriendService()->isFriend($userId, $friendId)) {
            return $this->permissionError($this->getLocalization()->translate('you_are_already_friends_with_this_user'));
        }
        if ($this->getRequestService()->isRequested($userId, $friendId)) {
            return $this->permissionError($this->getLocalization()->translate('you_are_already_sent_request_to_this_user'));
        }
        if ($this->friendDirection == self::ONE_WAY) {
            return $this->getFriendProcessService()->add($userId, $friendId);
        } else {
            return $this->getProcessService()->add($userId, $friendId);
        }
    }

    /**
     * @return \Friend_Service_Process
     */
    protected function getFriendProcessService()
    {
        return Phpfox::getService('friend.process');
    }

    function approve($params)
    {
        return null;
    }

    function feature($params)
    {
        return null;
    }

    function sponsor($params)
    {
        return null;
    }

    public function getUnseenTotal($userId)
    {
        return $this->database()->select('COUNT(*)')
            ->from(':friend_request')
            ->where('user_id = ' . (int)$userId . ' AND is_seen = 0 AND is_ignore = 0')
            ->execute('getSlaveField');
    }
}