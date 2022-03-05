<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Comments\Service\Process;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Exception\UnknownErrorException;
use Apps\Core_MobileApi\Api\Resource\CommentResource;
use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Comment\CommentAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_MobileApi\Version1_6\Api\Resource\CommentStickerResource;
use Apps\Core_MobileApi\Version1_6\Api\Resource\CommentStickerSetResource;
use Phpfox;
use Phpfox_Plugin;

class CommentApi extends AbstractResourceApi implements MobileAppSettingInterface
{

    protected $bIsNewComment;

    public function __construct()
    {
        parent::__construct();
        $this->bIsNewComment = class_exists('Apps\Core_Comments\Service\Stickers\Stickers');
    }

    public function __naming()
    {
        return [
            'comment/reply/:id' => [
                'get'   => 'getReply',
                'where' => [
                    'id' => '(\d+)'
                ]
            ],
            'comment/edit/:id'  => [
                "get"   => "getCommentForEdit",
                'where' => [
                    'id' => '(\d+)',
                ]
            ],
            'comment/remove-preview/:id' => [
                'post'   => 'removePreview',
                'where' => [
                    'id' => '(\d+)',
                ]
            ],
            'comment/my-sticker/:id' => [
                'put' => 'updateMySticker',
                'where' => [
                    'id' => '(\d+)',
                ]
            ],
            'comment/hide/:id' => [
                'put' => 'hideComment',
                'where' => [
                    'id' => '(\d+)',
                ]
            ]
        ];
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed
     * @throws UnknownErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    public function delete($params)
    {
        $id = $this->resolver->resolveId($params);

        /** @var CommentResource $comment */
        $comment = $this->loadResourceById($id, true);
        if (!$comment) {
            return $this->notFoundError();
        }
        $parentId = ($comment instanceof ResourceBase) ? $comment->parent_id : $comment['parent_id'];
        $itemType = !empty($params['item_type']) ? $params['item_type'] : $comment->getItemType();
        $itemId = !empty($params['item_id']) ? $params['item_id'] : $comment->getItemId();
        $this->denyAccessUnlessGranted(CommentAccessControl::DELETE, $comment);
        $this->processService()->deleteInline($comment->getId(), $itemType);
        if ($this->isPassed()) {
            $data = [
                'id'      => (int)$params['id'],
                'feed_id' => $this->getFeedId($itemType, $itemId),
            ];

            if ($parentId) {
                $data['parent_id'] = intval($parentId);
                $data['child_total'] = intval($this->getTotalChild($parentId));
            } else {
                $data['total_comment'] = intval($this->getTotalComment($itemType, $itemId));
            }
            return $this->success($data);
        }

        return $this->error($this->getErrorMessage());
    }

    public function processRow($commentData)
    {
        $comment = $this->populateResource(CommentResource::class, $commentData);
        $comment->setExtra($this->getAccessControl()->getPermissions($comment));
        $comment->setSelf([
            CommentAccessControl::VIEW   => $this->createHyperMediaLink(CommentAccessControl::VIEW, $comment,
                HyperLink::GET, 'comment/:id', ['id' => $comment->getId()]),
            CommentAccessControl::DELETE => $this->createHyperMediaLink(CommentAccessControl::DELETE, $comment,
                HyperLink::DELETE, 'comment/:id', ['id' => $comment->getId()])
        ]);

        return $comment;
    }

    /**
     * @param $params
     *
     * @return array|bool
     * @throws \Apps\Core_MobileApi\Api\Exception\NotFoundErrorException
     * @throws \Apps\Core_MobileApi\Api\Exception\ValidationErrorException
     */
    public function getReply($params)
    {
        $params = $this->resolver->setDefined(['id', 'limit', 'page'])
            ->setRequired(['id'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $comment = $this->loadResourceById($params['id']);
        if (!$comment) {
            return $this->notFoundError();
        }

        $this->denyAccessUnlessGranted(CommentAccessControl::VIEW, null, [
            'item_type' => $comment['type_id'],
            'item_id'   => $comment['item_id']
        ]);

        $reply = $this->database()
            ->select('c.*, ct.*, l.like_id AS is_liked, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->leftJoin(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->leftJoin(Phpfox::getT('like'), 'l',
                'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId())
            ->where("c.parent_id = " . $params['id'])
            ->order("c.comment_id ASC")
            ->limit($params['page'], $params['limit'], $comment['child_total'])
            ->execute('getRows');
        if ($this->bIsNewComment) {
            foreach ($reply as $key => $comment) {
                $reply[$key]['extra_data'] = Phpfox::getService('comment')->getExtraByComment($comment['comment_id']);
                $reply[$key]['is_hidden'] = Phpfox::getService('comment')->checkHiddenComment($comment['comment_id'], $this->getUser()->getId());
                $reply[$key]['total_hidden'] = 1;
                $reply[$key]['hide_ids'] = $comment['comment_id'];
                $reply[$key]['hide_this'] = $reply[$key]['is_hidden'];
                if ($key && $reply[$key - 1]['is_hidden'] && $reply[$key]['is_hidden']) {
                    $reply[$key - 1]['hide_this'] = false;
                    $reply[$key]['hide_ids'] = $reply[$key - 1]['hide_ids'] . ',' . $comment['comment_id'];
                    $reply[$key]['total_hidden'] = $reply[$key - 1]['total_hidden'] + 1;
                }
            }
        }
        $this->processRows($reply);

        return $this->success($reply);
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
        $params = $this->resolver->setDefined(['item_type', 'item_id', 'parent_id', 'last_id', 'limit', 'first_child', 'api_version_name', 'ignore_error'])
            ->setRequired(['item_type', 'item_id'])
            ->setDefault([
                'limit'   => Pagination::DEFAULT_ITEM_PER_PAGE,
                'last_id' => 0,
                'first_child' => 0,
                'ignore_error' => 0
            ])
            ->setAllowedTypes('parent_id', 'int', ['min' => 1])
            ->setAllowedTypes('item_id', 'int', ['min' => 1])
            ->setAllowedTypes('last_id', 'int', ['min' => 0])
            ->setAllowedValues('first_child', [0, 1])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }

        //Predict parent item id, because item_id might be an ID of Feed Comment, not Parent Item
        $parentItem = $this->predictParentItemId($params);
        $accessControl = $this->getAccessControl();
        $accessControl->setParameters([
            'item_type' => $params['item_type'],
            'item_id'   => $parentItem ? $parentItem : $params['item_id'],
            'api_version_name' => isset($params['api_version_name']) ? $params['api_version_name'] : 'mobile'
        ]);
        if (!$accessControl->isGranted(CommentAccessControl::VIEW)) {
            if (empty($params['ignore_error'])) {
                return $this->permissionError();
            } else {
                return $this->success([]);
            }
        }

        $comments = $this->getComments($params);

        return $this->success($comments, [
            'pagination' => Pagination::strategy(Pagination::STRATEGY_LATEST)
                ->setParam((count($comments) > 0 ? $comments[0]['id'] : 0))
                ->getPagination()
        ]);
    }

    protected function predictParentItemId($params)
    {
        $parentItem = 0;
        if (!empty($params['item_type']) && !empty($params['item_id']) && Phpfox::hasCallback($params['item_type'], 'getFeedDetails')) {
            $callback = Phpfox::callback($params['item_type'] . '.getFeedDetails', $params['item_id']);
            //Get parent item id, only support parent have specific feed table (event, pages, groups...)
            if (isset($callback['table_prefix'])) {
                $parentItem = $this->database()->select('parent_user_id')
                    ->from(Phpfox::getT($callback['table_prefix'] . 'feed'))
                    ->where([
                        'type_id' => [
                            'like' => $params['item_type'] . '%'
                        ],
                        'item_id' => $params['item_id']
                    ])->executeField();
            }
        }
        return $parentItem;
    }

    protected function getComments($params, $recursive = true)
    {
        $conds = [];
        if (!empty($params['parent_id'])) {
            $conds[] = "AND c.parent_id = " . $params['parent_id'];
            if (!empty($params['first_child'])) {
                $order = 'c.comment_id DESC';
            } else {
                $order = 'c.comment_id ASC';
            }
            if ($params['last_id'] > 0) {
                $conds[] = ' AND c.comment_id ' . (!empty($params['first_child']) ? '< ' : '> ') . $params['last_id'];
            }
        } else {
            $conds = [
                'c.type_id = \'' . $this->database()->escape($params['item_type']) . '\'  ',
                'AND c.item_id = ' . (int)$params['item_id'],
                "AND c.parent_id = 0"
            ];
            if ($params['last_id'] > 0) {
                $conds[] = ' AND c.comment_id < ' . $params['last_id'];
            }
            $order = 'c.comment_id DESC';
        }

        //Don't get pending comment
        $conds[] = 'AND c.view_id = 0';

        if ($this->getUser()->getId()) {
            $userIds = Phpfox::getService('user.block')->get($this->getUser()->getId(), true);
            if ($userIds) {
                $conds[] = 'AND c.user_id NOT IN (' . implode(',', $userIds) . ')';
            }
        }
        $comments = $this->database()
            ->select('c.*, ct.*, l.like_id AS is_liked, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->leftJoin(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->leftJoin(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->leftJoin(Phpfox::getT('like'), 'l',
                'l.type_id = \'feed_mini\' AND l.item_id = c.comment_id AND l.user_id = ' . Phpfox::getUserId())
            ->where($conds)
            ->order($order)
            ->limit($params['limit'])
            ->execute('getRows');

        if ((!empty($params['first_child']) && count($comments) > 0) || empty($params['parent_id'])) {
            $comments = array_reverse($comments);
        }
        if ($recursive) {
            foreach ($comments as $key => $comment) {
                if ($this->bIsNewComment) {
                    $comments[$key]['extra_data'] = Phpfox::getService('comment')->getExtraByComment($comment['comment_id']);
                    $comments[$key]['is_hidden'] = Phpfox::getService('comment')->checkHiddenComment($comment['comment_id'], $this->getUser()->getId());
                    $comments[$key]['total_hidden'] = 1;
                    $comments[$key]['hide_ids'] = $comment['comment_id'];
                    $comments[$key]['hide_this'] = $comments[$key]['is_hidden'];
                    if ($key && $comments[$key - 1]['is_hidden'] && $comments[$key]['is_hidden']) {
                        $comments[$key - 1]['hide_this'] = false;
                        $comments[$key]['hide_ids'] = $comments[$key - 1]['hide_ids'] . ',' . $comment['comment_id'];
                        $comments[$key]['total_hidden'] = $comments[$key - 1]['total_hidden'] + 1;
                    }
                }
                if ($comment['child_total'] > 0) {
                    $comments[$key]['child_total'] = $this->database()->select('COUNT(*)')->from(':comment')->where([
                        'parent_id' => $comment['comment_id'],
                        'view_id'   => 0
                    ])->executeField();
                    //Get 2 first comment only
                    $comments[$key]['children'] = $this->getComments([
                        'parent_id'   => $comment['comment_id'],
                        'last_id'     => 0,
                        'first_child' => true,
                        'limit'       => $this->getSetting()->getAppSetting('comment.thread_comment_total_display', 3),
                    ], false);
                }
            }
        } elseif ($this->bIsNewComment) {
            foreach ($comments as $key => $comment) {
                $comments[$key]['extra_data'] = Phpfox::getService('comment')->getExtraByComment($comment['comment_id']);
                $comments[$key]['is_hidden'] = Phpfox::getService('comment')->checkHiddenComment($comment['comment_id'], $this->getUser()->getId());
                $comments[$key]['total_hidden'] = 1;
                $comments[$key]['hide_ids'] = $comment['comment_id'];
                $comments[$key]['hide_this'] = $comments[$key]['is_hidden'];
                if ($key && $comments[$key - 1]['is_hidden'] && $comments[$key]['is_hidden']) {
                    $comments[$key - 1]['hide_this'] = false;
                    $comments[$key]['hide_ids'] = $comments[$key - 1]['hide_ids'] . ',' . $comment['comment_id'];
                    $comments[$key]['total_hidden'] = $comments[$key - 1]['total_hidden'] + 1;
                }
            }
        }

        $this->processRows($comments);
        return $comments;
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
        $id = $this->resolver->resolveId($params);

        $comment = $this->loadResourceById($id);
        if (empty($comment)) {
            return $this->notFoundError();
        }
        $comment = $this->processRow($comment);
        $this->denyAccessUnlessGranted(CommentAccessControl::VIEW, $comment);

        return $this->success($comment->toArray());
    }

    /**
     * Post comment
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        $params = $this->resolver
            ->setDefined(['table_prefix', 'parent_id', 'is_via_feed'])
            ->setRequired(['item_type', 'item_id', 'text'])
            ->setAllowedTypes('item_id', 'int', ['min' => 1])
            ->setAllowedTypes('parent_id', 'int', ['min' => 0])
            ->setDefault([
                'parent_id' => 0
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $parentItem = $this->predictParentItemId($params);

        $this->denyAccessUnlessGranted(CommentAccessControl::ADD, null, [
            'item_type' => $params['item_type'],
            'item_id'   => $parentItem ? $parentItem : $params['item_id']
        ]);

        $id = $this->processCreate($params);

        if ($this->isPassed()) {
            if ($id == 'pending_comment') {
                return $this->success([
                    'feed_id' => $this->getFeedId($params['item_type'], $params['item_id']),
                    'pending_comment' => true,
                    'item' => []
                ],[], $this->getLocalization()->translate('your_comment_has_been_added_successfully_it_is_waiting_for_an_admin_approval'));
            }
            $comment = $this->loadResourceById($id);
            if (empty($comment)) {
                return $this->privacyError($this->getLocalization()->translate('unable_to_post_a_comment_on_this_item_due_to_privacy_settings'));
            }
            $comment = $this->processRow($comment);

            $data = [
                'item'    => $comment->toArray(),
                'feed_id' => $this->getFeedId($params['item_type'], $params['item_id']),
            ];

            if ($params['parent_id']) {
                $data['parent_id'] = intval($params['parent_id']);
                $data['child_total'] = $this->getTotalChild($params['parent_id']);
            } else {
                $data['total_comment'] = $this->getTotalComment($params['item_type'], $params['item_id']);
            }

            (($sPlugin = Phpfox_Plugin::get('mobile.service_comment_api_create_success')) ? eval($sPlugin) : false);

            return $this->success($data);
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
    function update($params)
    {
        $params = $this->resolver
            ->setRequired(['id', 'text'])
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $comment = $this->loadResourceById($params['id'], true);
        if (!$comment) {
            return $this->notFoundError();
        }
        // Permission checking
        $this->denyAccessUnlessGranted(CommentAccessControl::EDIT, $comment);

        $success = $this->processUpdate($params['id'], $params['text']);
        if ($success && $this->isPassed()) {
            $text = preg_replace('/([^>\n]?)(\n)/', '&#10;', $params['text']);
            $text = preg_replace('/([^>\r]?)(\r)/', '&#13;', $text);
            $text = stripslashes($text);
            $comment->text = $text;

            return $this->success([
                'id'   => (int)$params['id'],
                'item' => $comment->toArray(),
            ]);
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
    function form($params = [])
    {
        throw new UnknownErrorException("API Not Found");
    }

    /**
     * @param      $id
     * @param bool $resource
     *
     * @return array|CommentResource
     */
    function loadResourceById($id, $resource = false)
    {
        $comment = $this->database()
            ->select('c.*, ct.*, COUNT(cr.comment_id) as child_total, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->leftJoin(Phpfox::getT('comment'), 'cr', 'cr.parent_id= c.comment_id AND cr.view_id = 0')
            ->leftJoin(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->where('c.comment_id = ' . (int)$id)
            ->execute("getRow");
        if (empty($comment['comment_id'])) {
            return null;
        }
        if ($this->bIsNewComment) {
            $comment['extra_data'] = Phpfox::getService('comment')->getExtraByComment($comment['comment_id']);
            $comment['is_hidden'] = Phpfox::getService('comment')->checkHiddenComment($comment['comment_id'], $this->getUser()->getId());
        }
        if ($resource) {
            return $this->populateResource(CommentResource::class, $comment);
        }
        return $comment;
    }

    /**
     * @deprecated will be removed from v4.5.4
     * @param $type
     * @param $itemId
     * @codeCoverageIgnore
     *
     * @return CommentResource
     */
    public function loadResourceByType($type, $itemId)
    {
        return null;
    }

    /**
     * @return Process|object
     */
    protected function processService()
    {
        return Phpfox::getService("comment.process");
    }

    /**
     * @param $params
     *
     * @return array|bool|int
     * @throws UnknownErrorException
     */
    private function processCreate($params)
    {
        $validText = preg_replace('/([^>\s]?)(\s)/', '', $params['text']);
        if (Phpfox::getLib('parse.format')->isEmpty($validText)
            || strlen(preg_replace('/([^>\n]?)(\n)/', '', $validText)) === 0
            || strlen(preg_replace('/([^>\r]?)(\r)/', '', $validText)) === 0) {
            return $this->error($this->getLocalization()->translate('add_some_text_to_your_comment'));
        }
        $params['type'] = $params['item_type'];
        $params['is_api'] = true;
        $params['text'] = preg_replace('/([^>\n]?)(\n)/', '&#10;', $params['text']);
        $params['text'] = preg_replace('/([^>\r]?)(\r)/', '&#13;', $params['text']);
        $params['text'] = stripslashes($params['text']);
        return $this->processService()->add($params);

    }

    /**
     * @param $id
     * @param $text
     *
     * @return bool
     * @throws \Exception
     */
    private function processUpdate($id, $text)
    {
        $validText = preg_replace('/([^>\s]?)(\s)/', '', $text);
        if (Phpfox::getLib('parse.format')->isEmpty($validText)
            || strlen(preg_replace('/([^>\n]?)(\n)/', '', $validText)) === 0
            || strlen(preg_replace('/([^>\r]?)(\r)/', '', $validText)) === 0) {
            return $this->error($this->getLocalization()->translate('add_some_text_to_your_comment'));
        }
        $text = preg_replace('/([^>\n]?)(\n)/', '&#10;', $text);
        $text = preg_replace('/([^>\r]?)(\r)/', '&#13;', $text);
        $text = stripslashes($text);

        return $this->processService()->updateText($id, !$this->bIsNewComment ? $text : ['text' => $text]);
    }

    /**
     * Create Comment Access Control to control permission
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new CommentAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get("item_id");

        if ($moduleId) {
            $context = AppContextFactory::create($moduleId, $itemId);
            if ($context === null) {
                return $this->notFoundError();
            }
            $this->accessControl->setAppContext($context);
        }
        return true;
    }

    /**
     * @param string $itemType
     * @param int $itemId
     *
     * @return array|int|string
     * @internal param $params
     */
    protected function getTotalComment($itemType, $itemId)
    {
        $cnt = $this->database()
            ->select('COUNT(*)')
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->where('c.parent_id = 0 AND c.type_id = \'' . $this->database()->escape($itemType) . '\' AND c.item_id = ' . (int)$itemId)
            ->execute('getSlaveField');
        return (int)$cnt;
    }

    protected function getTotalChild($commentId)
    {
        $cnt = $this->database()->select('COUNT(*)')
            ->from(':comment')
            ->where('parent_id =' . (int)$commentId)
            ->execute('getField');
        return (int)$cnt;
    }

    /**
     * @param $itemType
     * @param $itemId
     *
     * @return array|int|string
     */
    protected function getFeedId($itemType, $itemId)
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

    /**
     * @param $param
     *
     * @return MobileApp
     * @throws \Apps\Core_MobileApi\Api\Exception\UndefinedResourceName
     */
    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        return new MobileApp('comment', [
            'title'         => $l->translate('comments'),
            'main_resource' => new CommentResource([]),
            'other_resources'   => [
                new CommentStickerResource([]),
                new CommentStickerSetResource([])
            ],
        ]);
    }

    public function getCommentForEdit($params)
    {
        $id = $this->resolver->resolveId($params);
        $comment = Phpfox::getService('comment')->getCommentForEdit($id);

        if (!isset($comment['comment_id'])) {
            return $this->notFoundError();
        }
        $resource = $this->populateResource(CommentResource::class, $comment);
        $this->denyAccessUnlessGranted(CommentAccessControl::EDIT, $resource);
        $text = html_entity_decode($comment['text'], ENT_QUOTES);
        return $this->success([
            'id'   => (int)$id,
            'text' => $text
        ]);
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

    public function getScreenSetting($param) {}

    public function getRelatedComment($itemType, $itemId)
    {
        $userId = $this->getUser()->getId();
        $conds = [
            'c.view_id' => 0,
            'c.type_id' => $itemType,
            'c.item_id' => $itemId
        ];
        $reply = $this->database()
            ->select('c.*, ct.*, f.friend_id as is_friend, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('comment'), 'c')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
            ->join(Phpfox::getT('friend'), 'f', 'f.friend_user_id = c.user_id AND f.user_id = ' . $userId)
            ->leftJoin(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
            ->where($conds)
            ->order('c.time_stamp DESC')
            ->execute('getRow');
        $results = [];
        if (!empty($reply)) {
            if (!empty($reply['parent_id'])) {
                $parentComment = $this->database()
                    ->select('c.*, ct.*, ' . Phpfox::getUserField())
                    ->from(Phpfox::getT('comment'), 'c')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = c.user_id')
                    ->leftJoin(Phpfox::getT('comment_text'), 'ct', 'ct.comment_id = c.comment_id')
                    ->where([
                        'c.view_id'    => 0,
                        'c.comment_id' => $reply['parent_id']
                    ])
                    ->execute('getRow');
                if (!empty($parentComment['comment_id'])) {
                    if ($this->bIsNewComment) {
                        $reply['extra_data'] = Phpfox::getService('comment')->getExtraByComment($reply['comment_id']);
                        $reply['is_hidden'] = Phpfox::getService('comment')->checkHiddenComment($reply['comment_id'], $this->getUser()->getId());
                    }
                    $parentComment['children'][] = $this->processRow($reply)->toArray();
                    $results[] = $parentComment;
                }
            } else {
                $reply['ignore_child'] = !empty($reply['child_total']) && $reply['child_total'] > 0;
                $results[] = $reply;
            }
        }
        if ($this->bIsNewComment && count($results)) {
            foreach ($results as $key => $result) {
                $results[$key]['extra_data'] = Phpfox::getService('comment')->getExtraByComment($result['comment_id']);
                $results[$key]['is_hidden'] = Phpfox::getService('comment')->checkHiddenComment($result['comment_id'], $this->getUser()->getId());
            }
        }
        $this->processRows($results);
        return $results;
    }

    public function removePreview($params)
    {
        $id = $this->resolver->resolveId($params);
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        if (!$id) {
            return $this->notFoundError();
        }
        if (Phpfox::getService('comment.process')->removeExtraComment($id, 'preview')) {
            return $this->success();
        }
        return $this->error();
    }

    public function updateMySticker($params)
    {
        $params = $this->resolver->setRequired(['id'])
            ->setDefined(['is_remove'])
            ->setDefault([
                'is_remove' => 0
            ])->resolve($params)->getParameters();
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        if (empty($params['id'])) {
            return $this->notFoundError();
        }
        if (Phpfox::getService('comment.stickers.process')->updateMyStickerSet($params['id'], $this->getUser()->getId(), !$params['is_remove'])) {
            return $this->success([],[],$this->getLocalization()->translate($params['is_remove'] ? 'remove_stickers_set_successfully' : 'add_new_stickers_set_successfully'));
        }
        return $this->error();
    }

    public function hideComment($params)
    {
        $params = $this->resolver->setRequired(['id'])
            ->setDefined(['is_unhide'])
            ->setDefault([
                'is_unhide' => 0
            ])->resolve($params)->getParameters();
        $comment = $this->loadResourceById($params['id'], true);
        if (empty($comment)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(CommentAccessControl::HIDE, $comment);
        if (Phpfox::getService('comment.process')->hideComment($params['id'], $this->getUser()->getId(), $params['is_unhide'])) {
            return $this->success();
        }
        return $this->error();
    }
}