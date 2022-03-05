<?php

namespace Apps\Core_MobileApi\Adapter\Localization;

use Core\Phrase;
use Phpfox;
use Phpfox_Locale;

class PhpfoxLocalization implements LocalizationInterface
{
    /**
     * @var Phrase
     */
    private static $_oTranslator;
    private static $_sDefaultLanguageId = 'en';

    /**
     * @var \Core_Service_Country_Country
     */
    protected $country;

    /**
     * @var \Language_Service_Language
     */
    protected $language;

    /**
     * @var \Core_Service_Currency_Currency
     */
    protected $currency;

    public function __construct()
    {
        $sDefaultLanguageId = Phpfox_Locale::instance()->getLangId();
        if (self::$_oTranslator === null || self::$_sDefaultLanguageId !== $sDefaultLanguageId) {
            self::$_oTranslator = new Phrase();
            self::$_sDefaultLanguageId = $sDefaultLanguageId;
        }
        $this->country = Phpfox::getService('core.country');
        $this->language = Phpfox::getService('language');
        $this->currency = Phpfox::getService('core.currency');
    }

    /**
     * Translate phrase into user language
     *
     * @param       $phrase
     * @param array $params
     * @param null  $languageId
     *
     * @return mixed
     */
    function translate($phrase, $params = [], $languageId = null)
    {
        return html_entity_decode(self::$_oTranslator->get($phrase, $params, $languageId), ENT_QUOTES);
    }

    function getAllCountry()
    {
        return $this->country->get();
    }

    function getAllState($countryIso)
    {
        return $this->country->getChildren($countryIso);
    }

    function getCountryAndState()
    {
        return $this->country->getCountriesAndChildren();
    }

    function getAllLanguage($defaultFirst = true)
    {
        return $this->language->getAll($defaultFirst);
    }

    function getAllCurrencies()
    {
        return $this->currency->get();
    }

    function getDefaultCurrency()
    {
        return $this->currency->getDefault();
    }

    function getCurrency($price, $currencyId = null, $precision = null)
    {
        return $this->currency->getCurrency($price, $currencyId, $precision);
    }

}