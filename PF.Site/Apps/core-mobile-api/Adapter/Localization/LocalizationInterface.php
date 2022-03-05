<?php

namespace Apps\Core_MobileApi\Adapter\Localization;


interface LocalizationInterface
{
    /**
     * Translate phrase into user language
     *
     * @param      $phrase
     * @param null $params
     * @param null $languageId
     *
     * @return mixed
     */
    function translate($phrase, $params = [], $languageId = null);

    /**
     * Get all countries in site
     * @return mixed
     */
    function getAllCountry();

    /**
     * Get all states in site
     *
     * @param $countryIso
     *
     * @return mixed
     */
    function getAllState($countryIso);

    /**
     * Get all countries and their children
     * @return mixed
     */
    function getCountryAndState();

    /**
     * Get all current languages on site
     *
     * @param $defaultFirst boolean show default language first
     *
     * @return mixed
     */
    function getAllLanguage($defaultFirst = true);

    /**
     * Get all currencies on site
     * @return mixed
     */
    function getAllCurrencies();

    /**
     * Get default currency
     * @return mixed
     */
    function getDefaultCurrency();

    /**
     * Get price display by currency
     *
     * @param      $price
     * @param null $currencyId
     * @param null $precision
     *
     * @return mixed
     */
    function getCurrency($price, $currencyId = null, $precision = null);
}