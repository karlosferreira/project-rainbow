<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_7\Service;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Apps\Core_MobileApi\Api\Resource\AdResource;
use Apps\Core_MobileApi\Version1_7\Api\Form\Ad\SponsorItemForm;
use Phpfox;

class AdApi extends \Apps\Core_MobileApi\Service\AdApi
{

    protected $adSponsorService;

    public function __construct()
    {
        parent::__construct();
        if (Phpfox::isAppActive('Core_BetterAds')) {
            $this->adSponsorService = Phpfox::getService('ad.sponsor');
        }
    }

    function form($params = [])
    {
        $params = $this->resolver
            ->setDefined(['is_sponsor_feed'])
            ->setDefault([
                'is_sponsor_feed' => false
            ])
            ->setRequired(['section', 'id'])
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        /** @var $form SponsorItemForm $form */
        $form = $this->createForm(SponsorItemForm::class, [
            'title'  => $params['is_sponsor_feed'] ? 'sponsor_in_feed' : 'sponsor_item',
            'method' => 'post',
            'action' => UrlUtility::makeApiUrl('ad')
        ]);
        $form->setItemId($params['id']);
        $form->setSection($params['section']);

        $this->validateSponsorshipItem($params, $form);

        return $this->success($form->getFormStructure());
    }

    function create($params)
    {
        $params = $this->resolver
            ->setDefined(['is_sponsor_feed'])
            ->setDefault([
                'is_sponsor_feed' => false
            ])
            ->setRequired(['section', 'id'])
            ->getParameters();

        /** @var $form SponsorItemForm $form */
        $form = $this->createForm(SponsorItemForm::class);
        if (!$form->isValid()) {
            return $this->validationParamsError($form->getInvalidFields());
        }

        $this->validateSponsorshipItem($params, $form, $item);
        $values = $form->getValues();
        $id = $this->processCreate($values, $item);
        if ($id === true) {
            return $this->success([], [], $this->getLocalization()->translate('better_ads_finished'));
        } else if (is_numeric($id) && $id > 0) {
            $adId = db()->select('ads_id')
                ->from(':better_ads_invoice')
                ->where('invoice_id = ' . $id . ' AND is_sponsor = 1')
                ->executeField();
            $ad = Phpfox::getService('ad.get')->getSponsor($adId, Phpfox::getUserId());
            if (!$ad) {
                return $this->error();
            }
            $fTotalCost = $ad['price'];
            return $this->success([
                'pending_purchase' => [
                    'title'           => $this->getParse()->cleanOutput($ad['campaign_name']),
                    'item_number'     => 'ad|' . $id . '-sponsor',
                    'currency_id'     => $ad['currency_id'],
                    'resource_name'   => AdResource::populate([])->getResourceName(),
                    'module_name'     => AdResource::populate([])->getModuleName(),
                    'user_id'         => $this->getUser()->getId(),
                    'price'           => $fTotalCost,
                    'sub_description' => $this->getLocalization()->translate($values['is_sponsor_feed'] ? 'sponsor_in_feed' : 'sponsor_item'),
                    'price_text'      => html_entity_decode($this->getLocalization()->getCurrency($fTotalCost, $ad['currency_id'])),
                ]
            ]);
        }
        return $this->error($this->getErrorMessage());
    }

    /**
     * @param $params
     * @param $form SponsorItemForm
     * @param $item
     *
     * @return array|bool|void
     */
    protected function validateSponsorshipItem($params, &$form, &$item = [])
    {
        $section = $module = $params['section'];
        $itemData = $params['is_sponsor_feed'] ? [
            'iItemId' => $params['id'],
            'sModule' => $params['section']
        ] : $params['id'];
        $function = 'getToSponsorInfo';
        $sections = [];
        if (strpos($section, '_') !== false) {
            $sections = explode('_', $section);
            $module = reset($sections);
        }
        $sectionItem = count($sections) == 2 ? $sections[1] : '';

        if ($sectionItem) {
            $function = $function . ucfirst($sectionItem);
        }

        if ($params['is_sponsor_feed']) {
            $function = 'getSponsorPostInfo';
            $module = 'feed';
            $sectionItem = '';
        }

        if (Phpfox::hasCallback($module, $function)) {
            $item = Phpfox::callback($module . '.' . $function, $itemData);
        }

        $localization = $this->getLocalization();
        if (empty($item)) {
            return $this->error($localization->translate('module_is_not_a_valid_module', ['module' => $params['section']]));
        }
        if (!empty($item['error'])) {
            return $this->error($item['error']);
        }

        $currentUserId = $this->getUser()->getId();
        $setting = $this->getSetting();

        // check that the user viewing is either the owner of the item or an admin
        if ((!$params['is_sponsor_feed'] && $item['user_id'] != $currentUserId) || ($params['is_sponsor_feed'] && ($item['user_id'] != $currentUserId) && !$setting->getUserSetting('feed.can_sponsor_feed'))) {
            return $this->permissionError($localization->translate('sponsor_error_owner'));
        }
        if ($sectionItem) {
            $prices = Phpfox::getUserParam($module . '.' . $module . '_' . $sectionItem . '_sponsor_price');
            $withoutPaying = $setting->getUserSetting($module . '.can_sponsor_' . $sectionItem);
        } else {
            $prices = Phpfox::getUserParam($module . '.' . $module . '_sponsor_price');
            $withoutPaying = $setting->getUserSetting($module . '.can_sponsor_' . $module);
        }
        if (is_array($prices)) {
            if (!isset($prices[$localization->getDefaultCurrency()])) {
                return $this->error($localization->translate('the_default_currency_has_no_price'));
            }
            $item['ad_cost'] = $prices[$localization->getDefaultCurrency()];
        } else if (is_numeric($prices) && $prices >= 0) {
            $item['ad_cost'] = $prices;
        } else {
            return $this->error($localization->translate('the_currency_for_your_membership_has_no_price'));
        }
        $form->setCostInfo($localization->getCurrency($item['ad_cost'], $localization->getDefaultCurrency()));
        $form->setWithoutPaying($withoutPaying);
        $form->setSponsorItem($item);
        $form->setSponsorFeed($params['is_sponsor_feed']);

        return true;
    }

    protected function processCreate($values, $item)
    {
        $this->convertForm($values);
        // if price is 0
        if (empty($item['ad_cost'])) {
            // Payment completed: no payment required
            // add the sponsor
            $values['is_active'] = true;
            if ($this->processService->addSponsor($values)) {
                return true;
            }
        } else {
            if (!isset($values['total_view']) || ($values['total_view'] != 0 && $values['total_view'] < 1000)) {
                return $this->error($this->getLocalization()->translate('better_ads_impressions_cant_be_less_than_a_thousand'));
            }
            if (!isset($values['name']) || empty($values['name'])) {
                return $this->error($this->getLocalization()->translate('better_ads_provide_a_campaign_name'));
            }
            if ($invoiceId = Phpfox::getService('ad.process')->addSponsor($values)) {
                return $invoiceId;
            }
        }
        return false;
    }

    protected function convertForm(&$values)
    {
        $sections = [];
        if (!empty($values['is_sponsor_feed'])) {
            if (!$newItemId = Phpfox::getService('feed')->getForItem($values['section'], $values['id'])) {
                return $this->notFoundError();
            }
            // correct "feed" item_id
            $values['item_id'] = $newItemId['feed_id'];
            $values['module'] = 'feed'; // assign feed as module instead of original
            $values['section'] = '';
        } else {
            if (strpos($values['section'], '_') !== false) {
                $sections = explode('_', $values['section']);
                $values['module'] = reset($sections);
            } else {
                $values['module'] = $values['section'];
            }
            $values['section'] = count($sections) == 2 ? $sections[1] : '';
            $values['item_id'] = $values['id'];
        }
        $startTime = (new \DateTime($values['start_time']));
        if (empty($startTime)) {
            return $this->validationParamsError(['start_time']);
        }
        $values['start_month'] = $startTime->format('m');
        $values['start_day'] = $startTime->format('d');
        $values['start_year'] = $startTime->format('Y');
        $values['start_hour'] = $startTime->format('H');
        $values['start_minute'] = $startTime->format('i');

        if (!empty($values['end_option'])) {
            $endTime = isset($values['end_time']) ? (new \DateTime($values['end_time'])) : null;
            if (empty($endTime)) {
                return $this->validationParamsError(['end_time']);
            }

            if ($startTime->getTimestamp() > $endTime->getTimestamp()) {
                return $this->error($this->getLocalization()->translate('end_time_cannot_less_than_start_time'));
            }
            $values['end_month'] = $endTime->format('m');
            $values['end_day'] = $endTime->format('d');
            $values['end_year'] = $endTime->format('Y');
            $values['end_hour'] = $endTime->format('H');
            $values['end_minute'] = $endTime->format('i');
        }
        return true;
    }

    public function getSponsor($id)
    {
        return $this->adGetService->getSponsor($id);
    }

    public function getSponsorLink($module, $params)
    {
        return $this->adSponsorService->getLink($module, $params);
    }
}