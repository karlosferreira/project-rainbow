<?php
/**
 * [PHPFOX_HEADER]
 */
namespace Apps\Core_Marketplace\Service;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');


class Browse extends Phpfox_Service
{

    private $_sCategory = null;

    private $_bIsSeen = false;

    private $_isSearch = false;

    private $_isApi = false;

    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('marketplace');
    }

    public function seen()
    {
        $this->_bIsSeen = true;

        return $this;
    }

    public function isApi($value)
    {
        $this->_isApi = (boolean)$value;
    }

    public function category($sCategory)
    {
        $this->_sCategory = $sCategory;

        return $this;
    }

    public function search() {
        $this->_isSearch = true;
        return $this;
    }

    public function processRows(&$aRows)
    {
        foreach ($aRows as $iKey => $aListing) {
            Phpfox::getService('marketplace')->getPermissions($aRows[$iKey]);
            if (!isset($aListing['categories'])) {
                $aRows[$iKey]['categories'] = Phpfox::getService('marketplace.category')->getCategoriesById($aListing['listing_id']);
            }
            // Mark expired items here so its easier to display them in the template
            if ((Phpfox::getParam('marketplace.days_to_expire_listing') > 0) && ($aListing['time_stamp'] < (PHPFOX_TIME - (Phpfox::getParam('marketplace.days_to_expire_listing') * 86400)))) {
                $aRows[$iKey]['is_expired'] = true;
            }
            $aRows[$iKey]['url'] = Phpfox_Url::instance()->permalink('marketplace', $aListing['listing_id'], $aListing['title']);
        }
    }

    public function query()
    {
    }

    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Phpfox::getService('friend')->queryJoin($bNoQueryFriend)) {
            $this->database()->join(Phpfox::getT('friend'), 'friends',
                'friends.user_id = l.user_id AND friends.friend_user_id = ' . Phpfox::getUserId());
        }
        if ($this->_sCategory !== null) {
            $this->database()->select('mc.name AS category_name, mc.category_id, ')
                ->innerJoin(Phpfox::getT('marketplace_category_data'), 'mcd', 'mcd.listing_id = l.listing_id')
                ->join(Phpfox::getT('marketplace_category'), 'mc', 'mc.category_id = mcd.category_id');

            if (!$bIsCount) {
                $this->database()->group('l.listing_id');
            }
        }

        if ($this->_isApi) {
            db()->select('mtext.description_parsed AS description, ')
                ->leftJoin(':marketplace_text', 'mtext', 'mtext.listing_id = l.listing_id');
        }

        if ($this->_bIsSeen !== false) {
            $this->database()->join(Phpfox::getT('marketplace_invite'), 'mi',
                'mi.listing_id = l.listing_id AND mi.visited_id = 0 AND mi.invited_user_id = ' . Phpfox::getUserId());
        }
        if($this->_isSearch) {
            $this->database()->join(Phpfox::getT('marketplace_text'), 'mt', 'mt.listing_id = l.listing_id');
        }
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     * @return mixed
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('marketplace.service_browse__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}
