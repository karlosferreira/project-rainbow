<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_Marketplace\Service\Marketplace;
use Apps\Core_Marketplace\Service\Process;
use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\MarketplaceInvoiceResource;
use Apps\Core_MobileApi\Api\Resource\MarketplaceResource;
use Apps\Core_MobileApi\Api\Security\Marketplace\MarketplaceAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;

/**
 * Class EventInviteApi
 * @package Apps\Core_MobileApi\Service
 */
class MarketplaceInvoiceApi extends AbstractResourceApi
{
    /**
     * @var Marketplace
     */
    private $marketplaceService;

    /**
     * @var Process
     */
    private $processService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    /**
     * EventInviteApi constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->marketplaceService = Phpfox::getService('marketplace');
        $this->processService = Phpfox::getService('marketplace.process');
        $this->userService = Phpfox::getService('user');
    }

    public function __naming()
    {
        return [
            'marketplace-invoice/buy-now/:id' => [
                'get' => 'buyNowByPurchaseId'
            ]
        ];
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined(['limit', 'page'])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1,
            ])
            ->resolve($params)->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $this->denyAccessUnlessGranted(MarketplaceAccessControl::VIEW);
        $cond = [];
        $cond[] = 'AND mi.user_id = ' . $this->getUser()->getId();
        list(, $invoices) = $this->getInvoices($cond, $params['page'], $params['limit']);

        $this->processRows($invoices);
        return $this->success($invoices);
    }

    private function getInvoices($cond, $page = 1, $pageSize = 10, $groupUser = false)
    {
        if ($groupUser) {
            $this->database()->group('mi.user_id');
        }

        $count = $this->database()->select('COUNT(*)')
            ->from(Phpfox::getT('marketplace_invoice'), 'mi')
            ->where($cond)
            ->execute('getSlaveField');

        if ($groupUser) {
            $this->database()->group('mi.user_id');
        }

        $items = $this->database()->select('mi.*, m.title, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('marketplace_invoice'), 'mi')
            ->leftJoin(Phpfox::getT('marketplace'), 'm', 'm.listing_id = mi.listing_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = mi.user_id')
            ->where($cond)
            ->limit($page, $pageSize)
            ->order('mi.invoice_id desc')
            ->execute('getSlaveRows');

        foreach ($items as $iKey => $item) {
            if (empty($item['title'])) {
                $items[$iKey]['title'] = $this->getLocalization()->translate('mobile_deleted_listing');
            }
            switch ($item['status']) {
                case 'completed':
                    $items[$iKey]['status_phrase'] = _p('paid');
                    break;
                case 'cancel':
                    $items[$iKey]['status_phrase'] = _p('cancelled');
                    break;
                case 'pending':
                    $items[$iKey]['status_phrase'] = _p('pending_payment');
                    break;
                default:
                    $items[$iKey]['status_phrase'] = _p('pending_payment');
                    break;
            }
        }

        return [$count, $items];
    }

    /**
     * @param $params
     *
     * @return array|bool|mixed|void
     * @throws \Exception
     */
    function findOne($params)
    {
        $params = $this->resolver
            ->setRequired(['id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $invite = $this->loadResourceById($params['id']);
        if (empty($invite)) {
            return $this->notFoundError();
        }
        return $this->success(MarketplaceInvoiceResource::populate($invite)->lazyLoad(['user'])->toArray());
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function patchUpdate($params)
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        return null;
    }

    /**
     * @param $id
     *
     * @return mixed
     *
     * @param $returnResource
     */
    function loadResourceById($id, $returnResource = false)
    {
        $invite = $this->database()->select('mi.*')
            ->from(':marketplace_invoice', 'mi')
            ->join(':user', 'u', 'mi.user_id = u.user_id')
            ->where('invoice_id = ' . (int)$id)
            ->execute('getSlaveRow');
        return $invite;
    }

    public function processRow($item)
    {
        return MarketplaceInvoiceResource::populate($item)->lazyLoad(['user'])->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new MarketplaceAccessControl($this->getSetting(), $this->getUser());
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

    public function buyNowByPurchaseId($params)
    {
        $id = $this->resolver->resolveId($params);

        $invoice = $this->marketplaceService->getInvoice($id);
        if (!$invoice) {
            return $this->notFoundError();
        }
        /** @var MarketplaceResource $resource */
        $resource = (new MarketplaceApi())->loadResourceById($invoice['listing_id'], true);

        $this->denyAccessUnlessGranted(MarketplaceAccessControl::BUY_NOW, $resource);

        $image = $resource->getImage();
        return $this->success([
            'pending_purchase' => [
                'title'         => $resource->getTitle(),
                'description'   => $resource->getShortDescription(),
                'price_text'    => $resource->getPrice(),
                'seller_id'     => $resource->user->getId(),
                'image'         => isset($image->sizes['400']) ? $image->sizes['400'] : $image,
                'item_number'   => 'marketplace|' . $id,
                'currency_id'   => $invoice['currency_id'],
                'price'         => $invoice['price'],
                'allow_point'   => $resource->allow_point_payment,
                'allow_gateway' => $resource->is_sell
            ]
        ]);
    }
}