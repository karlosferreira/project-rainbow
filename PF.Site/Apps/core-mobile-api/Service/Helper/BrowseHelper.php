<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service\Helper;

use Phpfox;
use Phpfox_Database;
use Phpfox_Plugin;
use Phpfox_Search;

class BrowseHelper extends \Phpfox_Service
{
    /**
     * Item count.
     *
     * @var int
     */
    private $_iCnt = 0;

    /**
     * ARRAY of items
     *
     * @var array
     */
    private $_aRows = [];

    /**
     * ARRAY of params we are going to work with.
     *
     * @var array
     */
    private $_aParams = [];

    /**
     * Service object for the specific module we are working with
     *
     * @var object
     */
    private $_oBrowse = null;

    /**
     * Short access to the "view" request.
     *
     * @var string
     */
    private $_sView = '';

    private $_aConditions;

    /**
     * @return $this
     * @codeCoverageIgnore - not use anywhere.
     */
    public static function instance()
    {
        return Phpfox::getService('mobile.helper.browse');
    }

    /**
     * Set the params for the browse routine.
     *
     * @param array $aParams ARRAY of params.
     *
     * @return $this
     */
    public function params($aParams)
    {
        $this->_aParams = $aParams;

        $this->_oBrowse = Phpfox::getService($this->_aParams['service']);

        $this->_sView = $this->_request()->get('view');

        if ($this->_sView == 'friend') {
            Phpfox::isUser(true);
        }

        return $this;
    }

    /**
     *
     * Execute the browse routine. Runs the SQL query.
     *
     * @param $callback
     */
    public function execute(\Closure $callback = null)
    {
        $aActualConditions = (array)$this->_search()->getConditions();

        $this->_aConditions = [];
        $sExtraPrivacy = Phpfox::isUser() ? ',6' : '';
        foreach ($aActualConditions as $sCond) {
            switch ($this->_sView) {
                case 'friend':
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0,1,2' . $sExtraPrivacy, $sCond);
                    break;
                case 'my':
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0,1,2,3,4' . $sExtraPrivacy, $sCond);
                    break;
                case 'pages_member':
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0,1' . $sExtraPrivacy, $sCond);
                    break;
                case 'pages_admin':
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0,1,2' . $sExtraPrivacy, $sCond);
                    break;
                default:
                    $this->_aConditions[] = str_replace('%PRIVACY%', '0' . $sExtraPrivacy, $sCond);
                    break;
            }
        }

        $sUserJoinCond = 'u.user_id = ' . $this->_aParams['alias'] . '.user_id';
        if (Phpfox::isUser() && $this->search()->isBIsIgnoredBlocked()) {
            $aBlockedUserIds = Phpfox::getService('user.block')->get(null, true);
            if (!empty($aBlockedUserIds)) {
                $sUserJoinCond .= ' AND u.user_id NOT IN (' . implode(',', $aBlockedUserIds) . ')';
            }
        }
        $bIsMultipleUnion = isset($this->_aParams['multiple_union']) && method_exists($this->_oBrowse, 'getMultipleUnions');
        $aMultipleUnionParams = [
            'alias' => $this->_aParams['alias'],
            'table' => $this->_aParams['table'],
            'search_fields' => isset($this->_aParams['search_fields']) ? $this->_aParams['search_fields'] : [],
            'conditions' => $this->_aConditions,
        ];

        if (Phpfox::isModule('privacy') && Phpfox::getParam('core.section_privacy_item_browsing')
            && (isset($this->_aParams['hide_view']) && !in_array($this->_sView, $this->_aParams['hide_view']))) {
            $this->buildPrivacy($this->_aParams);
            if ($bIsMultipleUnion) {
                $aMultipleUnionParams = array_merge($aMultipleUnionParams, [
                    'sub_query' => $this->database()->execute(null),
                ]);
                $this->_oBrowse->getMultipleUnions($aMultipleUnionParams);
            }
            if (empty($this->_aParams['no_union_from'])) {
                $this->database()->unionFrom($this->_aParams['alias']);
            }
        } else {
            if ($bIsMultipleUnion) {
                $this->_oBrowse->getMultipleUnions($aMultipleUnionParams);
                if (empty($this->_aParams['no_union_from'])) {
                    $this->database()->unionFrom($this->_aParams['alias']);
                }
            } else {
                $this->_oBrowse->getQueryJoins();
                $this->database()->from($this->_aParams['table'], $this->_aParams['alias'])->where($this->_aConditions);
            }
        }

        if ($callback && $callback instanceof \Closure) {
            call_user_func($callback, $this);
        }

        if (!empty($this->_aParams['alias_select'])) {
            $aliasSelect = $this->_aParams['alias_select'];
        } else {
            $aliasSelect = $this->_aParams['alias'] . '.*';
        }

        $this->_oBrowse->query();
        $this->_aRows = $this->database()->select(Phpfox::getUserField() . "," . $aliasSelect . (isset($this->_aParams['select']) ? ',' . $this->_aParams['select'] : ''))
            ->join(Phpfox::getT('user'), 'u', $sUserJoinCond)
            ->order($this->_search()->getSort())
            ->limit($this->_search()->getPage(), $this->_search()->getDisplay(), $this->_iCnt, false, false)
            ->execute('getSlaveRows');


        if (method_exists($this->_oBrowse, 'processRows')) {
            $this->_oBrowse->processRows($this->_aRows);
        }
    }

    /**
     * Gets the count.
     *
     * @return int Total items.
     *
     * @codeCoverageIgnore
     */
    public function getCount()
    {
        return (int)$this->_iCnt;
    }

    /**
     * Get items
     *
     * @return array ARRAY of items.
     */
    public function getRows()
    {
        return (array)$this->_aRows;
    }

    /**
     * Extends database class
     *
     * @see Phpfox_Database
     * @return object Returns database object
     */
    public function database()
    {
        return Phpfox_Database::instance();
    }

    /**
     * Extends search class
     *
     * @see \Apps\Core_MobileApi\Service\Helper\SearchHelper
     * @return \Apps\Core_MobileApi\Service\Helper\SearchHelper
     */
    public function search()
    {
        return Phpfox::getService('mobile.helper.search');
    }

    /**
     * Reset the search
     *
     */
    public function reset()
    {
        $this->_aRows = [];
        $this->_iCnt = 0;
        $this->_aConditions = [];
        $this->_aParams = [];

        Phpfox_Search::instance()->reset();
    }

    /**
     * @return PsrRequestHelper
     */
    private function _request()
    {
        return PsrRequestHelper::instance(defined('PHPFOX_UNIT_TEST') && PHPFOX_UNIT_TEST === true);
    }

    /**
     * @return SearchHelper
     */
    private function _search()
    {
        return SearchHelper::instance();
    }

    public function buildPrivacy($aCond = [], $sOrder = null, $iPage = null, $sDisplay = null, $extra_conditions = null, $bUnionLimit = false)
    {
        $bIsCount = isset($aCond['count']);

        $oObject = Phpfox::getService($aCond['service']);

        if ($sPlugin = Phpfox_Plugin::get('privacy.service_privacy_buildprivacy')) {
            eval($sPlugin);
        }

        if (isset($callback) && is_callable($callback)) {
            return call_user_func($callback, $this);
        }

        $conditions = $this->_search()->getConditions();

        if (!empty($extra_conditions)) {
            $conditions[] = $extra_conditions;
        }


        if (Phpfox::getUserParam('core.can_view_private_items')) {
            $oObject->getQueryJoins($bIsCount, true);
            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }
            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->where(str_replace('%PRIVACY%', '0,1,2,3,4,6', $conditions));

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }
            return null;
        }

        $aUserCond = [];
        $aFriendCond = [];
        $aFriendOfFriends = [];
        $aCustomCond = [];
        $aPublicCond = [];
        $aCommunityCond = [];
        foreach ($conditions as $sCond) {
            $aFriendCond[] = str_replace('%PRIVACY%', '1,2,6', $sCond);
            $aFriendOfFriends[] = str_replace('%PRIVACY%', '2', $sCond);
            $aUserCond[] = str_replace('%PRIVACY%', '1,2,3,4,6', $sCond);
            $aCustomCond[] = str_replace('%PRIVACY%', '4', $sCond);
            $aPublicCond[] = str_replace('%PRIVACY%', '0', $sCond);
            $aCommunityCond[] = str_replace('%PRIVACY%', '6', $sCond);
        }

        // Users items
        if (Phpfox::isUser()) {
            $oObject->getQueryJoins($bIsCount, true);

            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->where(array_merge(['AND ' . $aCond['alias'] . '.user_id = ' . Phpfox::getUserId()], $aUserCond));

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }
        }

        // Items based on custom lists
        if (Phpfox::isUser()) {
            $oObject->getQueryJoins($bIsCount);

            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->join(Phpfox::getT('privacy'), 'p', 'p.module_id = \'' . str_replace('.', '_', $aCond['module_id']) . '\' AND p.item_id = ' . $aCond['alias'] . '.' . $aCond['field'])
                ->join(Phpfox::getT('friend_list_data'), 'fld', 'fld.list_id = p.friend_list_id AND fld.friend_user_id = ' . Phpfox::getUserId() . '')
                ->where($aCustomCond);

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }
        }

        // Friend of friends items
        if (!Phpfox::getParam('core.friends_only_community') && Phpfox::isUser()) {
            $oObject->getQueryJoins($bIsCount);

            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }

            $whereInFriendList = strtr('f1.friend_user_id IN (SELECT friend_user_id from :friend WHERE is_page=0 AND user_id=:user_id) AND ', [
                ':friend'  => Phpfox::getT('friend'),
                ':user_id' => intval(Phpfox::getUserId()),
            ]);

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->join(Phpfox::getT('friend'), 'f1', 'f1.is_page = 0 AND f1.user_id = ' . $aCond['alias'] . '.user_id')
//				->join(Phpfox::getT('friend'), 'f2', 'f2.is_page = 0 AND f2.user_id = ' . Phpfox::getUserId() . ' AND f2.friend_user_id = f1.friend_user_id')
                ->where(array_merge([$whereInFriendList, $aCond['alias'] . '.user_id = f1.user_id AND ' . $aCond['alias'] . '.user_id != ' . Phpfox::getUserId() . ''], $aFriendOfFriends));

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }
        }

        // Friends items
        if (Phpfox::isUser()) {
            $oObject->getQueryJoins($bIsCount, true);

            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->join(Phpfox::getT('friend'), 'f', 'f.is_page = 0 AND f.user_id = ' . $aCond['alias'] . '.user_id AND f.friend_user_id = ' . Phpfox::getUserId())
                ->where($aFriendCond);

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }
        }

        $forcePublic = false;

        (($sPlugin = Phpfox_Plugin::get('mobile.service_helper_browse_helper_build_privacy')) ? eval($sPlugin) : false);

        if (Phpfox::getParam('core.friends_only_community') && !$forcePublic) {
            // Public items
            $oObject->getQueryJoins($bIsCount);

            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->where(array_merge(['AND ' . $aCond['alias'] . '.user_id != ' . Phpfox::getUserId()], $aPublicCond));

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }

            // Public items for the specific user
            $oObject->getQueryJoins($bIsCount, true);

            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->where(array_merge(['AND ' . $aCond['alias'] . '.user_id = ' . Phpfox::getUserId()], $aPublicCond));

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }
        } else {
            // Public items
            $oObject->getQueryJoins($bIsCount);

            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->where($aPublicCond);

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }
        }

        if(Phpfox::isUser() && (!Phpfox::getParam('core.friends_only_community') || $forcePublic)) {
            // Community items
            $oObject->getQueryJoins($bIsCount);

            if (!$bIsCount && isset($aCond['join']) && !empty($aCond['join'])) {
                $this->database()->leftJoin(
                    $aCond['join']['table'],
                    $aCond['join']['alias'],
                    $aCond['join']['alias'] . "." . $aCond['join']['field'] . ' = ' . $aCond['alias'] . "." . $aCond['field']
                );
            }

            $this->database()->select(($bIsCount ? (isset($aCond['distinct']) ? 'COUNT(DISTINCT ' . $aCond['distinct'] . ')' : 'COUNT(*)') : $aCond['alias'] . '.*'))
                ->from($aCond['table'], $aCond['alias'])
                ->where($aCommunityCond);

            if ($bUnionLimit) {
                $this->database()->order($sOrder)->limit($iPage, $sDisplay)->union();
            } else {
                $this->database()->union();
            }
        }

        return null;
    }

    public function changeParentView($moduleId, $itemId)
    {
        if (!empty($moduleId) && !empty($itemId) && in_array($moduleId, ['pages', 'groups'])) {
            if (Phpfox::getService($moduleId)->isAdmin($itemId)) {
                $this->request()->set('view', 'pages_admin');
            } else if (Phpfox::getService($moduleId)->isMember($itemId)) {
                $this->request()->set('view', 'pages_member');
            }
        }
        (($sPlugin = Phpfox_Plugin::get('mobile.browse_helper_change_parent_view')) ? eval($sPlugin) : null);

        return $this;
    }
}