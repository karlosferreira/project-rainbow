<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service\Helper;

use Phpfox;
use Phpfox_Database;


class SearchHelper extends \Phpfox_Service
{
    /**
     * SQL conditions.
     *
     * @var array
     */
    private $_aConds = [];

    /**
     * Total amount of items this search returned
     *
     * @var int
     */
    private $_iTotalCount = 0;

    /**
     * Custom search date
     *
     * @var bool
     */
    private $_bIsCustomSearchDate = false;

    /**
     * Check to see if the form is being reset
     *
     * @var bool
     */
    private $_bIsReset = false;

    /**
     * SQL order by.
     *
     * @var array
     */
    private $_sSort = '';

    /**
     * Limit number of Item
     * @var int
     */
    private $_iLimit = 10;

    /**
     * Pagination param
     * @var int
     */
    private $_iPage = 1;

    /**
     * Check if a search has been ignored items belong to blocked users.
     *
     * @var bool
     */
    private $_bIsIgnoredBlocked = false;

    /**
     * search_tool when
     *
     * @var array
     */
    private $_aSearchTool = [];
    private $_aParams = [];
    private $_aSearchTools = [];
    private $_aConditions = [];

    /**
     * @var string
     */
    private $_sPagingCond = '';
    private $_aBounds;

    /**
     * @return $this
     */
    public static function instance()
    {
        return Phpfox::getService('mobile.helper.search');
    }

    /**
     * Check if we submitted the search form.
     *
     * @return bool TRUE if form submitted, FALSE if not.
     * @codeCoverageIgnore
     */
    public function isSearch()
    {
        if ($this->_request()->get('search')) {
            return true;
        }
        return false;
    }

    /**
     * Set an SQL condition.
     *
     * @param string $sValue
     */
    public function setCondition($sValue)
    {
        $this->_aConds[] = $sValue;
    }

    public function clearConditions()
    {
        $this->_aConds = [];
    }

    /**
     * Get all SQL conditions.
     *
     * @return array
     */
    public function getConditions()
    {
        static $aConds = null;

        if ($this->_bIsReset) {
            $aConds = null;
            $this->_bIsReset = false;
        }

        if ($aConds !== null) {
            return $aConds;
        }

        if (!empty($this->_aSearchTool) && ($this->_request()->get('when') || $this->_bIsCustomSearchDate)) {
            $iTimeDisplay = Phpfox::getLib('date')->mktime(0, 0, 0, Phpfox::getTime('m'), Phpfox::getTime('d'), Phpfox::getTime('Y'));

            $sWhenField = (isset($this->_aSearchTool['when_field']) ? $this->_aSearchTool['when_field'] : 'time_stamp');
            $sWhenEndField = (isset($this->_aSearchTool['when_end_field']) ? $this->_aSearchTool['when_end_field'] : 'time_stamp');
            $sSwitch = ($this->_request()->get('when') ? $this->_request()->get('when') : $this->_bIsCustomSearchDate);

            switch ($sSwitch) {
                case 'today':
                    $iEndDay = Phpfox::getLib('date')->mktime(23, 59, 59, Phpfox::getTime('m'), Phpfox::getTime('d'), Phpfox::getTime('Y'));
                    $this->_aConds[] = ' AND (' . $this->_aSearchTool['table_alias'] . '.' . $sWhenField . ' >= \'' . Phpfox::getLib('date')->convertToGmt($iTimeDisplay) . '\' AND ' . $this->_aSearchTool['table_alias'] . '.' . $sWhenField . ' < \'' . Phpfox::getLib('date')->convertToGmt($iEndDay) . '\')';
                    break;
                case 'this-week':
                    $weekStartEnd = Phpfox::getLib('date')->getWeekStartEnd();
                    $this->_aConds[] = ' AND ' . $this->_aSearchTool['table_alias'] . '.' . $sWhenField . ' >= ' . (int)Phpfox::getLib('date')->convertToGmt($weekStartEnd['start']);
                    $this->_aConds[] = ' AND ' . $this->_aSearchTool['table_alias'] . '.' . $sWhenField . ' <= ' . (int)Phpfox::getLib('date')->convertToGmt($weekStartEnd['end']);
                    break;
                case 'this-month':
                    $this->_aConds[] = ' AND ' . $this->_aSearchTool['table_alias'] . '.' . $sWhenField . ' >= \'' . Phpfox::getLib('date')->convertToGmt(Phpfox::getLib('date')->getThisMonth()) . '\'';
                    $iLastDayMonth = Phpfox::getLib('date')->mktime(0, 0, 0, date('n') + 1, 1, Phpfox::getTime('Y')) - 1;
                    $this->_aConds[] = ' AND ' . $this->_aSearchTool['table_alias'] . '.' . $sWhenField . ' <= \'' . Phpfox::getLib('date')->convertToGmt($iLastDayMonth) . '\'';
                    break;
                case 'upcoming':
                    $this->_aConds[] = ' AND ' . $this->_aSearchTool['table_alias'] . '.' . $sWhenField . ' >= \'' . PHPFOX_TIME . '\'';
                    break;
                case 'ongoing':
                    $this->_aConds[] = ' AND ' . $this->_aSearchTool['table_alias'] . '.' . $sWhenField . ' <= \'' . PHPFOX_TIME . '\'';
                    $this->_aConds[] = ' AND ' . $this->_aSearchTool['table_alias'] . '.' . $sWhenEndField . ' > \'' . PHPFOX_TIME . '\'';
                    break;
                default:

                    break;
            }
        }

        if ($this->getABounds() && isset($this->_aSearchTool['location_field'])) {
            $sLatField = isset($this->_aSearchTool['location_field']['latitude_field']) ? $this->_aSearchTool['location_field']['latitude_field'] : 'location_lat';
            $sLngField = isset($this->_aSearchTool['location_field']['longitude_field']) ? $this->_aSearchTool['location_field']['longitude_field'] : 'location_lng';
            $sTableAlias = $this->_aSearchTool['table_alias'];
            if (isset($this->_aBounds['south']) && isset($this->_aBounds['north']) && is_numeric($this->_aBounds['south']) && is_numeric($this->_aBounds['north'])) {
                $this->_aConds[] = ' AND ' . $sTableAlias . '.' . $sLatField . ' >= ' . $this->_aBounds['south'] . ' AND ' . $sTableAlias . '.' . $sLatField . ' != 0';
                $this->_aConds[] = ' AND ' . $sTableAlias . '.' . $sLatField . ' <= ' . $this->_aBounds['north'];
            }
            if (isset($this->_aBounds['west']) && isset($this->_aBounds['east']) && is_numeric($this->_aBounds['west']) && is_numeric($this->_aBounds['east'])) {
                if ($this->_aBounds['west'] > $this->_aBounds['east']) {
                    $this->_aConds[] = ' AND ' . $sTableAlias . '.' . $sLngField . ' <= ' . $this->_aBounds['west'] . ' AND ' . $sTableAlias . '.' . $sLngField . ' != 0';
                    $this->_aConds[] = ' AND ' . $sTableAlias . '.' . $sLngField . ' >= ' . $this->_aBounds['east'];
                } else {
                    $this->_aConds[] = ' AND ' . $sTableAlias . '.' . $sLngField . ' >= ' . $this->_aBounds['west'] . ' AND ' . $sTableAlias . '.' . $sLngField . ' != 0';
                    $this->_aConds[] = ' AND ' . $sTableAlias . '.' . $sLngField . ' <= ' . $this->_aBounds['east'];
                }
            }
        }

        if (!count($this->_aConds) && empty($this->_sPagingCond)) {
            return [];
        }

        $oDb = Phpfox_Database::instance();
        $aConds = [];
        foreach ($this->_aConds as $mKey => $mValue) {
            if (defined('PHPFOX_SEARCH_MODE_CONVERT') && PHPFOX_SEARCH_MODE_CONVERT) {
                $aConds[] = (is_numeric($mKey) ? $mValue : str_replace('[VALUE]', Phpfox::getLib('parse.input')->convert($oDb->escape($mValue)), $mKey));
            } else {
                $aConds[] = (is_numeric($mKey) ? $mValue : str_replace('[VALUE]', Phpfox::getLib('parse.input')->clean($oDb->escape($mValue)), $mKey));
            }
        }

        if ($this->_sPagingCond) {
            $aConds[] = $this->_sPagingCond;
        }

        return $aConds;
    }

    /**
     * Set the total number of items this search returned.
     *
     * @param int $iTotalCount
     *
     * @codeCoverageIgnore
     */
    public function setCount($iTotalCount)
    {
        $this->_iTotalCount = $iTotalCount;
    }

    /**
     * Get the total of items this search returned.
     *
     * @return int
     * @see self::setCount()
     *
     * @codeCoverageIgnore
     */
    public function getCount()
    {
        return $this->_iTotalCount;
    }

    /**
     * Reset the search
     *
     */
    public function reset()
    {
        $this->_aConditions = [];
        $this->_aParams = [];
        $this->_aSearchTools = [];
        $this->_aConds = [];
        $this->_sSort = '';
        $this->_bIsReset = true;
        $this->_sPagingCond = '';
        $this->_aBounds = [];
        $this->_bIsIgnoredBlocked = false;
        $this->_iPage = 1;
        $this->_iLimit = 10;
        $this->_iTotalCount = 0;
        $this->_bIsCustomSearchDate = false;
    }

    public function getLimit()
    {
        return $this->_iLimit;
    }

    public function setLimit($limit)
    {
        $this->_iLimit = $limit;
        return $this;
    }

    public function setPage($page)
    {
        $this->_iPage = $page;
        return $this;
    }

    public function setSearchTool($aTool = [])
    {
        $this->_aSearchTool = $aTool;
        return $this;
    }

    public function setSort($sSort)
    {
        $this->_sSort = $sSort;
        return $this;
    }

    public function getSort()
    {
        return $this->_sSort;
    }

    public function getPage()
    {
        if ($this->_iPage !== 1) {
            return $this->_iPage;
        }
        return $this->_request()->getInt('page', 1);
    }

    public function getDisplay()
    {
        return $this->getLimit();
    }

    /**
     * @return BrowseHelper
     * @codeCoverageIgnore
     */
    public function browse()
    {
        return \Phpfox::getService('mobile.helper.search.browse');
    }

    /**
     * @return RequestHelper
     */
    private function _request()
    {
        return \Phpfox::getService('mobile.helper.request');
    }

    /**
     * @return bool
     */
    public function isBIsIgnoredBlocked()
    {
        return $this->_bIsIgnoredBlocked;
    }

    /**
     * @param bool $bIsIgnoredBlocked
     */
    public function setBIsIgnoredBlocked($bIsIgnoredBlocked)
    {
        $this->_bIsIgnoredBlocked = $bIsIgnoredBlocked;
    }

    /**
     * @param string $sVar
     *
     * @codeCoverageIgnore
     */
    public function setPagingCondition($sVar)
    {
        $this->_sPagingCond = $sVar;
    }

    protected function getABounds()
    {
        return $this->_aBounds;
    }

    /**
     * @param mixed $aBounds
     */
    public function setABounds($aBounds)
    {
        $this->_aBounds = $aBounds;
    }

}