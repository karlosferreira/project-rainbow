<?php

namespace Apps\PHPfox_Groups\Service;

use Phpfox;
use Phpfox_Pages_Category;

/**
 * Class Category
 *
 * @package Apps\PHPfox_Groups\Service
 */
class Category extends Phpfox_Pages_Category
{
    public function getFacade()
    {
        return Phpfox::getService('groups.facade');
    }

    /**
     * @param $iCategory
     *
     * @return int
     */
    public function getTypeIdByCategoryId($iCategory)
    {
        $pageType = db()->select('type_id')
            ->from($this->_sTable)
            ->where('category_id = ' . (int)$iCategory)
            ->execute('getSlaveField');
        return $pageType;
    }

    public function getForBrowse($bIncludePages = false, $userId = null, $iPagesLimit = null, $sView = '')
    {
        $where = 'pt.item_type = 1';
        if (!in_array($sView, ['my', 'joined'])) {
            $where .= ' AND pt.is_active = 1';
        }
        $aTypes = $this->database()->select('pt.*')
            ->from(Phpfox::getT('pages_type'), 'pt')
            ->where($where)
            ->order('pt.ordering ASC')
            ->execute('getSlaveRows');

        foreach ($aTypes as $iKey => $aType) {
            if ($bIncludePages) {
                $aTypes[$iKey]['pages'] = $this->getLatestPages($aType['type_id'], $userId, $iPagesLimit, $sView);

                foreach ($aTypes[$iKey]['pages'] as $iSubKey => &$aPage) {
                    $aPage['link'] = Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);

                    // check permission for each action
                    Phpfox::getService('groups')->getActionsPermission($aPage, $sView);

                    // check is member
                    $aPage['is_liked'] = Phpfox::getService('groups')->isMember($aPage['page_id']);

                    // pending request to be member
                    $aPage['joinRequested'] = Phpfox::getService('groups')->joinGroupRequested($aPage['page_id']);
                }

                // get total pages for each category
                $aTypes[$iKey]['total_pages'] = Phpfox::getService('groups')->getItemsByCategory($aType['type_id'], false, 1, $userId, true, $sView);
            }

            if ($sView) {
                $aTypes[$iKey]['link'] = Phpfox::permalink(['groups.category', 'view' => $sView], $aType['type_id'], $aType['name']);
            } else {
                $aTypes[$iKey]['link'] = Phpfox::permalink('groups.category', $aType['type_id'], $aType['name']);
            }

            // get sub categories
            $aTypes[$iKey]['image_path'] = sprintf($aTypes[$iKey]['image_path'], '_200');
        }

        return $aTypes;
    }

    /**
     * Get lastest pages
     *
     * @param        $iId
     * @param null   $userId
     * @param int    $iPagesLimit , number of page want to limit, 0 for unlimited
     * @param string $sView
     *
     * @return array|int|string
     */
    public function getLatestPages($iId, $userId = null, $iPagesLimit = 8, $sView = '')
    {
        $extra_conditions = 'pages.type_id = ' . (int)$iId;
        if (!empty($userId)) {
            $extra_conditions .= ' AND pages.app_id = 0 AND pages.view_id IN(0,1) AND pages.user_id = ' . (int)$userId . ' ';
        } else if ($sView != 'my') {
            if ($sView == 'pending') {
                $extra_conditions .= ' AND pages.app_id = 0 AND pages.view_id = 1 ';
            } else {
                $extra_conditions .= ' AND pages.app_id = 0 AND pages.view_id = 0 ';
            }
            if($sView == 'joined') {
                $aGroupIds = Phpfox::getService('groups')->getAllGroupIdsOfMember();
                if (count($aGroupIds)) {
                    $extra_conditions .= " AND pages.page_id IN (" . implode(',', $aGroupIds) . ")";
                }
                else {
                    $extra_conditions .= " AND pages.page_id IN (0)";
                }
            }
            else if ($sView == 'friend' || Phpfox::getParam('core.friends_only_community')) {
                $sFriendsList = '0';
                if (Phpfox::getUserId()) {
                    $aFriends = (Phpfox::isModule('friend') ? Phpfox::getService('friend')->getFromCache() : []);
                    $aFriendIds = array_column($aFriends, 'user_id');
                    if ($sView != 'friend') {
                        $aFriendIds[] = Phpfox::getUserId();
                    }
                    if (!empty($aFriendIds)) {
                        $sFriendsList = implode(',', $aFriendIds);
                    }
                }
                $extra_conditions .= ' AND (pages.user_id IN (' . $sFriendsList . ') ';
                if (Phpfox::getParam('core.friends_only_community')) {
                    $aGroupIds = Phpfox::getService('groups')->getAllGroupIdsOfMember();
                    if (count($aGroupIds)) {
                        $extra_conditions .= " OR pages.page_id IN (" . implode(',', $aGroupIds) . ")";
                    }
                }
                $extra_conditions .= ') ';
            }
        }

        if ($sView != 'pending' && ($userId != Phpfox::getUserId() || $userId === null) && Phpfox::hasCallback('groups', 'getExtraBrowseConditions')) {
            $extra_conditions .= Phpfox::callback('groups.getExtraBrowseConditions', 'pages');
        }

        $sOrder = 'pages.time_stamp DESC';
        $this->database()->select('pages.*')
            ->from(Phpfox::getT('pages'), 'pages')
            ->where($extra_conditions)
            ->order($sOrder)
            ->limit($iPagesLimit)
            ->union()
            ->unionFrom('pages');

        $this->database()->select('pages.*, pt.text, pt.text_parsed, pu.vanity_url, ' . Phpfox::getUserField('u2', 'profile_'))
            ->join(Phpfox::getT('user'), 'u2', 'u2.profile_page_id = pages.page_id')
            ->leftJoin(Phpfox::getT('pages_url'), 'pu', 'pu.page_id = pages.page_id')
            ->leftJoin(':pages_text', 'pt', 'pt.page_id = pages.page_id');

        return $this->database()
            ->order($sOrder)
            ->limit($iPagesLimit)
            ->execute('getSlaveRows');
    }

    /**
     * Move sub categories to another type
     *
     * @param $iOldTypeId
     * @param $iNewTypeId
     */
    public function moveSubCategoriesToAnotherType($iOldTypeId, $iNewTypeId)
    {
        // update type id of sub-categories
        db()->update(':pages_category', [
            'type_id' => $iNewTypeId
        ], [
            'type_id' => $iOldTypeId
        ]);
    }

    /**
     * Get category name
     *
     * @param $iCategoryId
     *
     * @return string
     */
    public function getCategoryName($iCategoryId)
    {
        return _p(db()->select('name')->from(':pages_category')->where(['category_id' => $iCategoryId])->executeField());
    }
}
