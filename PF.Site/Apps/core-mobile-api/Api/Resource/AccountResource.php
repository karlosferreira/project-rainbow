<?php

namespace Apps\Core_MobileApi\Api\Resource;

use Apps\Core_MobileApi\Adapter\Utility\UrlUtility;
use Phpfox;

class AccountResource extends ResourceBase
{
    const RESOURCE_NAME = "account";

    public $resource_name = self::RESOURCE_NAME;

    protected $idFieldName = "user_id";

    public $user_name;

    public $full_name;

    public $last_name;

    public $first_name;

    public $email;

    public $language_id;

    public $time_zone;

    public $default_currency;

    public $all_languages;

    public $all_time_zones;

    public $all_currencies;

    public $phone_number;

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
        if (empty($this->user_name)) {
            return null;
        }
        return \Phpfox::getLib('url')->makeUrl($this->user_name);
    }

    public function getDefaultCurrency()
    {
        return isset($this->rawData['default_currency']) ? $this->rawData['default_currency'] : null;
    }

    public function getTimeZone()
    {
        $time_zone = null;
        if (!empty($this->rawData['time_zone'])) {
            $time_zone = $this->rawData['time_zone'];
        } else {
            $time_zone = Phpfox::getParam('core.default_time_zone_offset');
        }
        return $time_zone;
    }

    public function getAllLanguages()
    {
        if (empty($this->all_languages)) {
            $languages = Phpfox::getService('language')->get(['l.user_select = 1']);
            if ($languages) {
                return array_map(function ($lang) {
                    return [
                        'language_id'   => $lang['language_id'],
                        'title'         => $lang['title'],
                        'language_code' => $lang['language_code'],
                        'flag_id'       => $lang['flag_id'],
                        'direction'     => $lang['direction'],
                        'is_default'    => $lang['is_default']
                    ];
                }, $languages);
            }
        }
        return null;
    }

    public function getAllTimeZones()
    {
        return null;
    }

    public function getAllCurrencies()
    {
        if (empty($this->all_currencies)) {
            $currencies = Phpfox::getService('core.currency')->get();
            if ($currencies) {
                $results = [];
                foreach ($currencies as $key => $currency) {
                    $results[] = [
                        'currency_id' => $key,
                        'symbol'      => $currency['symbol'],
                        'name'        => $this->getLocalization()->translate($currency['name']),
                        'format'      => $currency['format'],
                        'is_default'  => $currency['is_default']
                    ];
                }
                return $results;
            }
        }
        return null;
    }

    public function getMobileSettings($params = [])
    {
        $l = $this->getLocalization();
        return self::createSettingForResource([
            'forms' => [
                'contactUs' => [
                    'apiUrl' => UrlUtility::makeApiUrl('account/contact-us'),
                    'headerTitle' => $l->translate('contact_us'),
                ],
            ]
        ]);
    }

    public function getLanguageId()
    {
        if (empty($this->language_id)) {
            return Phpfox::getService('language')->getDefaultLanguage();
        }
        return $this->language_id;
    }
}