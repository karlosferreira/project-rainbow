<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 25/5/18
 * Time: 9:58 AM
 */

namespace Apps\Core_MobileApi\Adapter\Localization;


class NoTranslate implements LocalizationInterface
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
    function translate($phrase, $params = [], $languageId = null)
    {
        return html_entity_decode($phrase, ENT_QUOTES);
    }

    /**
     * Get all countries in site
     * @return mixed
     */
    function getAllCountry()
    {
        return [];
    }

    /**
     * Get all states in site
     *
     * @param $countryIso
     *
     * @return mixed
     */
    function getAllState($countryIso)
    {
        return [];
    }

    /**
     * Get all countries and their children
     * @return mixed
     */
    function getCountryAndState()
    {
        return [];
    }

    /**
     * Get all current languages on site
     *
     * @param $defaultFirst boolean show default language first
     *
     * @return mixed
     */
    function getAllLanguage($defaultFirst = true)
    {
        return [];
    }

    /**
     * Get all currencies on site
     * @return mixed
     */
    function getAllCurrencies()
    {
        return [];
    }

    /**
     * Get default currency on site
     * @return mixed
     */
    function getDefaultCurrency()
    {
        return '';
    }

    /**
     *  Get currency
     *
     * @param      $price
     * @param null $currencyId
     * @param null $precision
     *
     * @return mixed|string
     */
    function getCurrency($price, $currencyId = null, $precision = null)
    {
        return '';
    }

}