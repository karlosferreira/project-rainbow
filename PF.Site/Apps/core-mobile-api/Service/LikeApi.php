<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Resource\LikeResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\User\UserAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;


class LikeApi extends AbstractResourceApi implements MobileAppSettingInterface
{
    /**
     * @var \Like_Service_Process
     */
    private $processService;

    public function __construct()
    {
        parent::__construct();
        $this->processService = Phpfox::getService("like.process");
    }

    /**
     * Unlike by item id or like id
     *
     * @param $params
     *
     * @return array
     */
    public function delete($params)
    {
        $params = $this->resolver
            ->setDefined(['id', 'item_type', 'item_id'])
            ->setAllowedTypes('id', 'int')
            ->setAllowedTypes('item_id', 'int')
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        if (empty($params['id']) && (empty($params['item_type']) || empty($params['item_id']))) {
            $this->validationParamsError(['id', 'item_type', 'item_id']);
        }
        (($sPlugin = \Phpfox_Plugin::get('mobile.service_like_api_delete_start')) ? eval($sPlugin) : false);

        // Validate like
        if (empty($params['item_type']) || empty($params['item_id'])) {
            $like = $this->loadResourceById($params['id'], true);
        } else {
            $like = $this->loadResourceByType($params['item_type'], $params['item_id'], $this->getUser()->getId());
        }
        if (!$like) {
            return $this->notFoundError();
        }
        if ($like->user->getId() != Phpfox::getUserId() && !Phpfox::isAdmin()) {
            return $this->permissionError();
        }

        $this->processService->delete($like->getItemType(), $like->getItemId());

        if ($this->isPassed()) {
            $like = $this->getLikes($params['item_type'], $params['item_id']);
            $response = [
                'total_like'  => $like['total'],
                'like_phrase' => html_entity_decode($like['phrase'], ENT_QUOTES),
                'is_liked'    => false,
                'feed_id'     => (int)$this->getFeedId($params['item_type'], $params['item_id'])
            ];

            (($sPlugin = \Phpfox_Plugin::get('mobile.service_like_api_delete_end')) ? eval($sPlugin) : false);

            return $this->success($response);
        } else {
            return $this->error($this->getErrorMessage());
        }

    }

    public function processRow($item)
    {
        /** @var LikeResource $like */
        $like = $this->populateResource(LikeResource::class, $item);
        if(Phpfox::isModule('friend')) {
            list($iTotal, $aMutual) = Phpfox::getService('friend')->getMutualFriends($item['user_id'], 3, false);
            $like->setMutualFriends(['total' => $iTotal, 'friends' => $aMutual]);
        }
        $like->setSelf([
            'delete' => $this->createHyperMediaLink(null, $like, HyperLink::DELETE, 'like', [
                'item_type' => $like->getItemType(),
                'item_id'   => $like->getItemId()
            ])
        ]);
        return $like->toArray();
    }


    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    public function findAll($params = [])
    {
        $params = $this->resolver
            ->setDefined(['table_prefix', 'page', 'limit'])
            ->setRequired(['item_type', 'item_id'])
            ->setDefault([
                'limit'   => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'    => 1,
                'last_id' => 0
            ])
            ->setAllowedTypes('item_type', 'string')
            ->setAllowedTypes('item_id', 'int', ['min' => 1])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('last_id', 'int', ['min' => 0])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        if ($params['item_type'] == 'comment') {
            $params['item_type'] = 'feed_mini';
        }

        //check permission to view parent item
        if (!empty($params['item_type']) && !empty($params['item_id'])) {
            if (Phpfox::hasCallback($params['item_type'], 'canViewItem')) {
                if (!Phpfox::callback($params['item_type'] . '.canViewItem', $params['item_id'])) {
                    return $this->permissionError();
                }
            } else {
                $item = NameResource::instance()->getPermissionByResourceName(str_replace('_', '-', $params['item_type']), $params['item_id']);
                if ($item !== null && !$item) {
                    return $this->permissionError();
                }
            }
        }

        $cnt = $this->getAllLikes($params['item_type'], $params['item_id'], $params['table_prefix'], true);

        if ($cnt) {
            $likes = $this->getAllLikes($params['item_type'], $params['item_id'], $params['table_prefix'], false, $params['page'], $params['limit']);

            $this->processRows($likes);
        } else {
            $likes = [];
        }

        return $this->success($likes, [
            'pagination' => Pagination::strategy(Pagination::STRATEGY_LATEST)
                ->setParam((count($likes) > 0 ? $likes[count($likes) - 1]['id'] : 0))
                ->getPagination()
        ]);
    }

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function findOne($params)
    {
        throw new UnknownErrorException("API Not Found");
    }

    /**
     * Like
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function create($params)
    {
        $params = $this->resolver->setRequired([
            'item_type', 'item_id'
        ])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        if ($params['item_type'] === 'comment') {
            $params['item_type'] = 'feed_mini';
        }

        $result = null;

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_like_api_create_start')) ? eval($sPlugin) : false);

        if ($result === null) {
            $result = $this->processService()->add($params['item_type'], $params['item_id'], null) && $this->isPassed();
        }

        if ($result) {
            $like = $this->getLikes($params['item_type'], $params['item_id']);

            $response = [
                'total_like'  => $like['total'],
                'like_phrase' => html_entity_decode($like['phrase'], ENT_QUOTES),
                'is_liked'    => true,
                'feed_id'     => $this->getFeedId($params['item_type'], $params['item_id'])
            ];

            (($sPlugin = \Phpfox_Plugin::get('mobile.service_like_api_create_end')) ? eval($sPlugin) : false);

            return $this->success($response);
        }

        return $this->error($this->getErrorMessage());
    }

    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function update($params)
    {
        throw new UnknownErrorException("API Not Found");
    }

    /**
     * Update multiple document base on document query
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function patchUpdate($params)
    {
        throw new UnknownErrorException("API Not Found");
    }

    /**
     * Get Create/Update document form
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    public function form($params = [])
    {
        throw new UnknownErrorException("API Not Found");
    }

    /**
     * @param int  $id
     * @param bool $returnResource
     *
     * @return array|LikeResource|object
     */
    public function loadResourceById($id, $returnResource = false)
    {
        $like = $this->database()->select("*")
            ->from(":like")
            ->where("like_id = " . (int)$id)
            ->execute("getRow");
        if ($returnResource && $like) {
            return $this->populateResource(LikeResource::class, $like);
        }
        return $like;
    }

    /**
     * @param string $itemType
     * @param int    $itemId
     * @param int    $userId
     *
     * @return \Apps\Core_MobileApi\Api\Resource\ResourceBase
     */
    public function loadResourceByType($itemType, $itemId, $userId)
    {
        $like = $this->database()->select("*")
            ->from(":like")
            ->where("user_id = " . (int)$userId . " AND type_id = '{$itemType}' AND item_id = {$itemId}")
            ->execute("getRow");
        if (!$like) {
            return null;
        }
        return $this->populateResource(LikeResource::class, $like);
    }

    /**
     * @return \Like_Service_Process
     */
    private function processService()
    {
        return $this->processService;
    }

//    /**
//     * @return \Like_Service_Like
//     */
//    private function likeService()
//    {
//        return Phpfox::getService("like");
//    }
//
//    /**
//     * @param string $itemType
//     * @param int    $itemId
//     * @param int    $lastId
//     *
//     * @return array|int|string
//     * @internal param $params
//     */
//    private function getTotalLike($itemType, $itemId, $lastId = 0)
//    {
//        $cnt = $this->database()
//            ->select('COUNT(*)')
//            ->from(Phpfox::getT('like'), 'l')
//            ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
//            ->where('l.type_id = \'' . $this->database()->escape($itemType) . '\' AND l.item_id = ' . (int)$itemId . ($lastId > 0 ? ' AND l.like_id > ' . (int)$lastId : ''))
//            ->execute('getSlaveField');
//        return (int)$cnt;
//    }

    /**
     * @param $itemType
     * @param $itemId
     *
     * @return array
     * @throws \Exception
     */
    private function getLikes($itemType, $itemId)
    {
        $like = Phpfox::getService('like')->getAll($itemType, $itemId);

        (($sPlugin = \Phpfox_Plugin::get('mobile.service_like_api_get_likes')) ? eval($sPlugin) : false);

        return isset($like['likes']) ? $like['likes'] : ['total' => 0, 'phrase' => ''];
    }

    /**
     * @param $itemType
     * @param $itemId
     *
     * @return array|int|string
     */
    private function getFeedId($itemType, $itemId)
    {
        $itemTypes = [$this->database()->escape($itemType)];
        if ($itemType == 'photo') {
            $itemTypes = array_merge($itemTypes, ['user_photo', 'user_cover', 'groups_photo', 'groups_cover_photo', 'pages_photo', 'pages_cover_photo']);
        }
        $itemTypes = implode("','", $itemTypes);
        return $this->database()->select('feed_id')
            ->from(Phpfox::getT('feed'))
            ->where('item_id = ' . (int)$itemId . ' AND type_id IN (\'' . $itemTypes . '\')')
            ->execute('getSlaveField');
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        return new MobileApp('like', [
            'title'          => $l->translate('likes'),
            'main_resource'  => new LikeResource([]),
            'other_resource' => [],
        ]);
    }


    public function getAllLikes($sType, $iItemId, $sPrefix = '', $bGetCount = false, $iPage = 0, $iTotal = null)
    {
        $sPrefix = $sPrefix . 'feed';
        if ($sType == 'feed') {
            $this->database()->where('(l.type_id = "feed" OR l.type_id = "feed_comment") AND l.item_id = ' . (int)$iItemId);
        } else {
            $this->database()->where('l.type_id = \'' . $this->database()->escape($sType) . '\' AND l.item_id = ' . (int)$iItemId . ($sType == 'app' ? " AND feed_table = '{$sPrefix}'" : ''));
        }
        $this->database()
            ->from(Phpfox::getT('like'), 'l')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = l.user_id')
            ->leftJoin(Phpfox::getT('friend'), 'f', 'f.friend_user_id = l.user_id AND f.user_id =' . Phpfox::getUserId());

        if ($bGetCount) {
            return $this->database()->select('count(*)')->executeField();
        } else {
            if ($iPage) {
                $this->database()->limit($iPage, $iTotal);
            }

            $aLikes = $this->database()->select(Phpfox::getUserField() . ', f.friend_id AS is_friend, l.*')
                ->group('u.user_id')
                ->order('is_friend DESC, u.full_name ASC')
                ->execute('getSlaveRows');

            return $aLikes;
        }
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

    public function createAccessControl()
    {
        $this->accessControl = new UserAccessControl($this->getSetting(), $this->getUser());
    }

    public function getActions()
    {
        $l = $this->getLocalization();
        return [
            'like/add_friend_request' => [
                'method'          => 'post',
                'url'             => 'mobile/friend/request',
                'data'            => 'friend_user_id=:user, ignore_error=1',
                'new_state'       => 'friendship=3',
                'confirm_title'   => $l->translate('confirm'),
                'confirm_message' => $l->translate('user_will_have_to_confirm_that_you_are_friends'),
            ],
        ];
    }

    public function getScreenSetting($param)
    {
        // TODO: Implement getScreenSetting() method.
    }
}