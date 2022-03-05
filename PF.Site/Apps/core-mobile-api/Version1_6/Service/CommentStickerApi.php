<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_6\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_MobileApi\Version1_6\Api\Resource\CommentStickerSetResource;
use Phpfox;

class CommentStickerApi extends AbstractResourceApi
{

    protected $stickerService;

    protected $processService;

    protected $bIsNewComment;

    public function __construct()
    {
        parent::__construct();
        $this->bIsNewComment = class_exists('Apps\Core_Comments\Service\Stickers\Stickers');

        if ($this->bIsNewComment) {
            $this->stickerService = Phpfox::getService('comment.stickers');
            $this->processService = Phpfox::getService('comment.stickers.process');
        }
    }

    function findAll($params = [])
    {
        if (!$this->bIsNewComment) {
            return null;
        }
        $params = $this->resolver
            ->setDefined(['user_id', 'page', 'limit'])
            ->setAllowedTypes('user_id', 'int')
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int', ['min' => 1])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page' => 1
            ])
            ->resolve($params)->getParameters();
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        if (!empty($params['user_id'])) {
            //Get sticker set of user
            $recentStickers = Phpfox::getService('comment.stickers')->getRecentSticker($params['user_id']);
            $recentStickerSet = [];
            if (is_array($recentStickers) && count($recentStickers)) {
                $recentStickerSet = [
                    'set_id' => -1,
                    'title' => $this->getLocalization()->translate('recent'),
                    'is_recent_set' => true,
                    'total_sticker' => count($recentStickers),
                    'stickers' => $recentStickers,
                ];
            }
            $stickers = $this->stickerService->getAllStickerSetByUser($params['user_id']);
            if (!empty($recentStickerSet)) {
                array_unshift($stickers, $recentStickerSet);
            }
        } else {
            $stickers = $this->stickerService->getAllSticker($this->getUser()->getId(), $params['limit']);
        }
        $this->processRows($stickers);
        return $this->success($stickers);
    }

    function findOne($params)
    {
        if (!$this->bIsNewComment) {
            return null;
        }
        $params = $this->resolver->setRequired(['id'])
            ->setDefined(['limit'])
            ->setAllowedTypes('limit', 'int')
            ->setAllowedTypes('id', 'int', ['min' => 1])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        $id = $params['id'];
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);
        $stickerSet = $this->stickerService->getStickerSetById($id, !empty($params['limit']) ? $params['limit'] : null);
        if (!$stickerSet) {
            return $this->notFoundError();
        }
        $item = $this->processRow($stickerSet);
        return $this->success($item->toArray());
    }

    function create($params)
    {
        // TODO: Implement create() method.
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
        // TODO: Implement delete() method.
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
        // TODO: Implement loadResourceById() method.
    }

    public function processRow($item)
    {
        $itemResource = $this->populateResource(CommentStickerSetResource::class, $item);

        return $itemResource;
    }
}