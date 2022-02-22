<?php


namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Mapping\ResourceMetadata;
use Apps\Core_MobileApi\Api\Resource\Object\Image;
use Phpfox;

class SubscriptionResource extends ResourceBase
{
    const RESOURCE_NAME = "subscription";
    public $resource_name = self::RESOURCE_NAME;
    public $module_name = 'subscribe';
    const MANUAL_RENEW = 2;
    const AUTO_RENEW = 1;

    /**
     * Custom ID Field Name
     */
    protected $idFieldName = "purchase_id";

    /**
     * @var UserResource
     */

    public $user_id;

    public $title;
    public $description;
    public $status;
    public $status_text;
    public $expiry_date;
    public $membership;
    public $price;
    public $price_text;
    public $transaction_id;
    public $user_group_id;
    public $fail_user_group;

    public $recurring_cost;
    public $recurring_period;
    public $sub_description;
    public $frequency;
    public $frequency_interval;

    public $package_id;
    public $renew_type;
    public $payment_method;
    public $can_cancel;
    public $can_renew;
    public $can_update_renew_method;
    public $image;
    public $currency_id;
    public $item_number;

    /**
     * Get detail url
     *
     * @return string
     */
    public function getLink()
    {
        return null;
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'resource_name' => $this->getResourceName(),
            'search_input'  => [
                'can_search' => false,
            ],
            'forms'         => [
                'cancelSubscription' => [
                    'apiUrl'         => UrlUtility::makeApiUrl('subscription/cancel'),
                    'headerTitle'    => $l->translate('cancel_subscription'),
                    'succeedAction'  => '@@restart_app',
                    'confirmTitle'   => $l->translate('confirm'),
                    'confirmMessage' => $l->translate('are_you_absolutely_sure_this_operation_cannot_be_undone'),
                ],
                'changePackage'      => [
                    'apiUrl'        => UrlUtility::makeApiUrl('subscription/change-package'),
                    'headerTitle'   => $l->translate('membership'),
                    'succeedAction' => '@@go_back'
                ]
            ]
        ]);
    }

    public function getMembership()
    {
        if (!$this->membership) {
            $this->membership = $this->getLocalization()->translate(isset($this->rawData['s_title']) ? $this->rawData['s_title'] : '');
        }
        return $this->membership;
    }

    public function getExpiryDate()
    {
        if ($this->recurring_period == 0) {
            $this->expiry_date = $this->getLocalization()->translate('no_expiration_date');
        } else if ($this->rawData['expiry_date']) {
            $this->expiry_date = $this->convertDatetime($this->rawData['expiry_date']);
        }
        return $this->expiry_date;
    }

    public function getCanCancel()
    {
        return $this->status == 'completed';
    }

    public function getStatusText()
    {
        $l = $this->getLocalization();
        switch ($this->status) {
            case 'completed':
                $text = $l->translate('sub_active');
                break;
            case 'cancel':
                $text = $l->translate('canceled');
                break;
            case 'pending':
                $text = $l->translate('pending_payment');
                break;
            case 'expire':
                $text = $l->translate('expired');
                break;
            default:
                $text = $l->translate('pending_action');
                break;
        }
        return $text;
    }

    public function getTitle()
    {
        $this->title = $this->getLocalization()->translate($this->title);
        return $this->parse->cleanOutput($this->title);
    }

    public function getDescription()
    {
        $this->description = $this->getLocalization()->translate($this->description);
        return $this->parse->cleanOutput($this->description);
    }

    public function getImage()
    {
        if ($this->rawData['image_path']) {
            $this->image = Image::createFrom([
                'file'      => $this->rawData['image_path'],
                'server_id' => $this->rawData['server_id'],
                'path'      => 'subscribe.url_image',
                'suffix'    => '_120'
            ], [], false);
        }
        return $this->image ? $this->image : \Phpfox::getParam('subscribe.app_url') . \Phpfox::getParam('subscribe.default_photo_package');
    }

    public function getRecurringCost()
    {
        $this->recurring_cost = isset($this->rawData['default_recurring_cost']) ? (float)$this->rawData['default_recurring_cost'] : 0;
        return $this->recurring_cost;
    }

    public function getSubDescription()
    {
        if ($this->recurring_period) {
            $this->sub_description = html_entity_decode(\Phpfox::getService('subscribe')->getPeriodPhrase($this->recurring_period, $this->getRecurringCost(), $this->price, $this->currency_id), ENT_QUOTES);
            $this->sub_description = preg_replace('/\((.+)?\)/', '$1', $this->sub_description);
        } else {
            $this->sub_description = $this->getLocalization()->translate('one_time');
        }
        return ucfirst($this->sub_description);
    }

    protected function loadMetadataSchema(ResourceMetadata $metadata = null)
    {
        parent::loadMetadataSchema($metadata);
        $this->metadata
            ->mapField('user_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('package_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('price', ['type' => ResourceMetadata::FLOAT])
            ->mapField('recurring_cost', ['type' => ResourceMetadata::FLOAT])
            ->mapField('user_group_id', ['type' => ResourceMetadata::INTEGER])
            ->mapField('fail_user_group', ['type' => ResourceMetadata::INTEGER])
            ->mapField('renew_type', ['type' => ResourceMetadata::INTEGER])
            ->mapField('can_renew', ['type' => ResourceMetadata::BOOL])
            ->mapField('recurring_period', ['type' => ResourceMetadata::INTEGER]);
    }

    public function getPrice()
    {
        if (empty($this->rawData['is_detail']) && $this->status == 'completed' && $this->recurring_period > 0 && $this->renew_type == self::MANUAL_RENEW) {
            //User is renewing recurring purchase
            $this->price = $this->rawData['default_recurring_cost'];
        }
        return $this->price;
    }

    public function getPriceText()
    {
        if ($this->price > 0) {
            $this->price_text = $this->getLocalization()->getCurrency($this->getPrice(), $this->currency_id);
        } else {
            $this->price_text = $this->getLocalization()->translate('free');
        }
        return $this->parse->cleanOutput($this->price_text);
    }

    public function getItemNumber()
    {
        if ($this->getCanRenew()) {
            $this->item_number = 'subscribe|' . $this->getId() . '-renew|' . PHPFOX_TIME;
        } else {
            $this->item_number = 'subscribe|' . $this->getId();
        }
        return $this->item_number;
    }

    public function getFrequency()
    {
        switch ($this->recurring_period) {
            case '1':
            case '2':
            case '3':
                $this->frequency = 'MONTH';
                break;
            case '4':
                $this->frequency = 'YEAR';
                break;
            default:
                break;
        }
        return $this->frequency;
    }

    public function getFrequencyInterval()
    {
        switch ($this->recurring_period) {
            case '1':
            case '4':
                $this->frequency_interval = 1;
                break;
            case '2':
                $this->frequency_interval = 3;
                break;
            case '3':
                $this->frequency_interval = 6;
                break;
            default:
                break;
        }
        return $this->frequency_interval;
    }

    public function getCanRenew()
    {
        if ($this->can_renew === null) {
            if (isset($this->rawData['expiry_date']) && isset($this->rawData['number_day_notify_before_expiration'])) {
                $iNotifyDays = (int)$this->rawData['number_day_notify_before_expiration'];
                $iNotifyBeforeDate = (int)$this->rawData['expiry_date'] - $iNotifyDays * 24 * 3600;
                $canRenewDate = (int)$this->rawData['expiry_date'] + 3 * 24 * 3600; // add 3 day after expired
                $this->can_renew = $this->renew_type == self::MANUAL_RENEW && $this->status == 'completed'
                    && (PHPFOX_TIME >= $iNotifyBeforeDate && PHPFOX_TIME <= $canRenewDate) && $this->user_id == Phpfox::getUserId();
            } else {
                $this->can_renew = false;
            }
        }
        return $this->can_renew;
    }

    public function getCanUpdateRenewMethod()
    {
        if ($this->can_update_renew_method === null) {
            $this->can_update_renew_method = !in_array($this->renew_type, [self::AUTO_RENEW, self::MANUAL_RENEW])
                && $this->status != 'completed' && $this->recurring_period > 0;
        }
        return $this->can_update_renew_method;
    }

    public function getRecurringPeriod()
    {
        if ($this->recurring_period && $this->renew_type == self::MANUAL_RENEW) {
            return 0;
        }
        return $this->recurring_period;
    }
}