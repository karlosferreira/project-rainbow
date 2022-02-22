<?php

namespace Apps\P_Reaction\Service\Api;

use Apps\Core_MobileApi\Adapter\MobileApp\MobileApp;
use Apps\Core_MobileApi\Adapter\MobileApp\MobileAppSettingInterface;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_MobileApi\Service\LikeApi;
use Apps\Core_MobileApi\Service\NameResource;
use Apps\P_Reaction\Api\Resource\PReactionResource;
use Phpfox;

class PReactionApi extends AbstractResourceApi implements MobileAppSettingInterface
{

    private $reactionService;

    private $processService;

    public function __construct()
    {

        parent::__construct();
        $this->reactionService = Phpfox::getService('preaction');
        $this->processService = Phpfox::getService('preaction.process');
    }

    public function __naming()
    {
        return [
            'preaction/reaction-tabs' => [
                'get' => 'getReactionTabs'
            ],
            'preaction/reacted-lists' => [
                'get' => 'getReacted'
            ]
        ];
    }

    function findAll($params = [])
    {
        $getArray = $this->resolver->resolveSingle($params, 'is_get_array', 'bool', [], false);

        $items = $this->reactionService->getReactions();

        $this->processRows($items);

        if ($getArray) {
            return $items;
        }
        return $this->success($items);
    }

    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $item = $this->loadResourceById($id, true);
        if (!$item) {
            return $this->notFoundError();
        }

        return $this->success($item->toArray());
    }

    function create($params)
    {
        return null;
    }

    function update($params)
    {
        return null;
    }

    function patchUpdate($params)
    {
        return null;
    }

    function delete($params)
    {
        return null;
    }

    function form($params = [])
    {
        return null;
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

    function loadResourceById($id, $returnResource = false)
    {
        $item = $this->reactionService->getReactionById($id, true);
        if (!isset($item['id'])) {
            return null;
        }
        if ($returnResource) {
            return PReactionResource::populate($item);
        }
        return $item;
    }

    public function getAppSetting($param)
    {
        $l = $this->getLocalization();
        $app = new MobileApp('preaction', [
            'title' => $l->translate('reaction'),
            'home_view' => 'menu',
            'main_resource' => new PReactionResource([])
        ]);
        return $app;
    }

    public function getUserReacted($itemId, $typeId, $userId = null)
    {
        if (!$userId) {
            $userId = $this->getUser()->getId();
        }
        $reacted = $this->reactionService->getReactedDetail($itemId, $typeId, $userId);
        return $this->processRow($reacted);
    }

    public function processRow($item)
    {
        return PReactionResource::populate($item)->displayShortFields()->toArray();
    }

    public function getReactionTabs($params)
    {
        $params = $this->resolver->setRequired(['item_id', 'item_type'])
            ->setDefined(['table_prefix'])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        list($totalReacts, $items) = $this->reactionService->getMostReaction($params['item_type'], $params['item_id'], $params['table_prefix']);
        $response = [];
        if ($totalReacts) {
            if(count($items) > 1) {
                $response[] = [
                    'title' => $this->getLocalization()->translate('all'),
                    'total_reacted' => (int)Phpfox::getService('core.helper')->shortNumber($totalReacts),
                    'id' => 0,
                    'icon' => null,
                    'color' => null
                ];
            }
            foreach ($items as $item) {
                $reaction = PReactionResource::populate($item)->toArray(['title', 'id', 'icon', 'color']);
                $response[] = array_merge($reaction, [
                    'total_reacted' => (int)Phpfox::getService('core.helper')->shortNumber($item['total_reacted'])
                ]);
            }
        }
        return $this->success($response);
    }

    public function getReacted($params)
    {
        $params = $this->resolver->setRequired(['item_id', 'item_type'])
            ->setDefined(['id', 'page', 'limit', 'table_prefix'])//reaction id
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page' => 1
            ])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getInvalidParameters());
        }
        //check permission to view parent item
        if (!empty($params['item_type']) && !empty($params['item_id'])) {
            if (Phpfox::hasCallback($params['item_type'], 'canViewItem')) {
                if (!Phpfox::callback($params['item_type'] . '.canViewItem', $params['item_id'])) {
                    return $this->permissionError();
                }
            } else {
                $item = NameResource::instance()->getPermissionByResourceName(str_replace('_','-',$params['item_type']),$params['item_id']);
                if ($item !== null && !$item) {
                    return $this->permissionError();
                }
            }
        }
        $items = $this->reactionService->getListUserReact($params['item_type'], $params['item_id'], (int)$params['id'], $params['table_prefix'], $params['limit'], $params['page'], $count);
        $response = [];
        if (count($items)) {
            foreach ($items as $item) {
                $resourceItem = (new LikeApi())->processRow($item);
                $response[] = array_merge($resourceItem, PReactionResource::populate($item)->toArray(['title', 'icon']));
            }
        }
        return $this->success($response);
    }
}