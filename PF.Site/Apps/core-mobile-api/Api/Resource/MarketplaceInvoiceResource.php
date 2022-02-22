<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;

class MarketplaceInvoiceResource extends ResourceBase
{
    const RESOURCE_NAME = "marketplace-invoice";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = "marketplace";

    protected $idFieldName = "invoice_id";

    public $title;
    public $listing_id;
    public $currency_id;
    public $price;
    public $price_text;
    public $status;
    public $status_phrase;

    //Define params for detail
    public $detail_params;

    /**
     * @var UserResource
     */
    public $user;

    public function __construct($data)
    {
        parent::__construct($data);
    }

    /**
     * Get detail url
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata->mapField('listing_id', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getPriceText()
    {
        if ($this->price > 0) {
            $this->price_text = $this->getLocalization()->getCurrency($this->price, $this->currency_id);
        } else {
            $this->price_text = $this->getLocalization()->translate('free');
        }
        return $this->parse->cleanOutput($this->price_text);
    }

    public function getDetailParams()
    {
        $this->detail_params = [
            'id'            => $this->listing_id,
            'module_name'   => 'marketplace',
            'resource_name' => 'marketplace'
        ];
        return $this->detail_params;
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name'    => $this->getResourceName(),
            'urls.base'        => 'mobile/marketplace-invoice',
            'search_input'     => [
                'can_search' => false,
            ],
            'list_view.tablet' => [
                'numColumns' => 3,
            ],
            'list_view'        => [
                'item_view'       => 'marketplace_invoice',
                'noItemMessage'   => [
                    'image' => $this->getAppImage(),
                    'label' => $l->translate('you_do_not_have_any_invoices'),
                ],
                'noResultMessage' => [
                    'image'     => $this->getAppImage('no-result'),
                    'label'     => $l->translate('no_results'),
                    'sub_label' => $l->translate('try_another_search'),
                ],
                'alignment'       => 'left'
            ],
            'fab_buttons'      => false,
            'can_add'          => false,
            'can_search'       => false,
            'app_menu'         => [
                ['label' => $l->translate('invoices')]
            ],
            'payment'          => [
                'buy_now' => [
                    'apiUrl' => 'mobile/marketplace-invoice/buy-now/:id',
                    'method' => 'get'
                ]
            ]
        ]);
    }
}