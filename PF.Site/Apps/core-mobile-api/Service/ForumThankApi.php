<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Resource\ForumThankResource;
use Apps\Core_MobileApi\Api\Security\Forum\ForumPostAccessControl;
use Apps\Core_MobileApi\Api\Security\Forum\ForumThankAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

class ForumThankApi extends AbstractResourceApi implements ActivityFeedInterface
{
    /**
     * @var \Like_Service_Process
     */
    private $processService;

    private $postService;

    public function __construct()
    {
        parent::__construct();
        $this->processService = Phpfox::getService('forum.post.process');
        $this->postService = Phpfox::getService('forum.post');
    }

    function findAll($params = [])
    {
        $params = $this->resolver
            ->setDefined(['limit', 'page', 'post_id'])
            ->setRequired(['post_id'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->setAllowedTypes('post_id', 'int')
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int', ['min' => 1])
            ->resolve($params)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        if (!$this->getSetting()->getAppSetting('forum.enable_thanks_on_posts')) {
            return $this->permissionError();
        }

        $thanks = $this->postService->getThanksForPost($params['post_id'], $params['page'], $params['limit'], $iCount);

        $this->processRows($thanks);

        return $this->success($thanks, [
            'pagination' => Pagination::strategy(Pagination::STRATEGY_LATEST)
                ->setParam((count($thanks) > 0 ? $thanks[count($thanks) - 1]['id'] : 0))
                ->getPagination()
        ]);

    }

    public function processRow($item)
    {
        /** @var ForumThankResource $like */
        $thank = $this->populateResource(ForumThankResource::class, $item);
        return $thank->setExtra($this->getAccessControl()->getPermissions($thank))->toArray();
    }

    function findOne($params)
    {
        // TODO: Implement findOne() method.
    }

    function create($params)
    {
        $params = $this->resolver->setRequired([
            'post_id'
        ])->resolve($params)->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $postApi = (new ForumPostApi());
        $post = $postApi->loadResourceById($params['post_id'], true);
        if (!$post) {
            return $this->notFoundError();
        }
        $postApi->denyAccessUnlessGranted(ForumPostAccessControl::THANK, $post);

        if (($id = $this->processService->thank($params['post_id'])) && $this->isPassed()) {
            $thankCount = $this->postService->getThanksCount($params['post_id']);
            return $this->success([
                'total_thank' => $thankCount,
                'thank_data'  => $this->processRow($this->loadResourceById($id)),
                'thank_id'    => $id,
            ]);
        }

        return $this->error($this->getErrorMessage());
    }

    function update($params)
    {
        // TODO: Implement update() method.
    }

    function patchUpdate($params)
    {
        // TODO: Implement patchUpdate() method.
    }

    function delete($params)
    {
        $id = $this->resolver->setRequired(['id'])->resolveId($params);

        $thank = $this->loadResourceById($id, true);
        $this->denyAccessUnlessGranted(ForumThankAccessControl::DELETE, $thank);
        if ($this->processService->deleteThanks($id)) {
            $thankCount = $this->postService->getThanksCount($thank->post_id);
            return $this->success([
                'total_thank' => $thankCount,
                'thank_id'    => null,
            ]);
        }
        return $this->error($this->getErrorMessage());
    }

    function form($params = [])
    {
        // TODO: Implement form() method.
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

    function loadResourceById($id, $returnResource = false)
    {
        $thank = $this->database()->select('ft.*, ' . Phpfox::getUserField())
            ->from(':forum_thank', 'ft')
            ->join(':user', 'u', 'u.user_id = ft.user_id')
            ->join(':forum_post', 'fp', 'fp.post_id = ft.post_id')
            ->where('ft.thank_id = ' . (int)$id)
            ->execute('getSlaveRow');
        if (empty($thank['thank_id'])) {
            return null;
        }
        if ($returnResource) {
            return ForumThankResource::populate($thank);
        }
        return $thank;
    }

    public function getFeedDisplay($param, $item)
    {
        // TODO: Implement getFeedDisplay() method.
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new ForumThankAccessControl($this->getSetting(), $this->getUser());
    }
}