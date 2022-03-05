<?php

namespace Apps\Core_Pages\Service;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class Category extends \Phpfox_Pages_Category
{
    /**
     * @return Facade
     */
    public function getFacade()
    {
        return \Phpfox::getService('pages.facade');
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
            if ($sView == 'liked') {
                $aPageIds = Phpfox::getService('pages')->getAllPageIdsOfMember();
                if (count($aPageIds)) {
                    $extra_conditions .= " AND pages.page_id IN (" . implode(',', $aPageIds) . ")";
                }
                else {
                    $extra_conditions .= " AND pages.page_id IN (0)";
                }
            }
            else if ($sView == 'friend' || Phpfox::getParam('core.friends_only_community')) {
                $sFriendsList = '0';
                if (Phpfox::getUserId() && Phpfox::isModule('friend')) {
                    $aFriends = Phpfox::getService('friend')->getFromCache();
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
                    $aPageIds = Phpfox::getService('pages')->getAllPageIdsOfMember();
                    if (count($aPageIds)) {
                        $extra_conditions .= " OR pages.page_id IN (" . implode(',', $aPageIds) . ")";
                    }
                }
                $extra_conditions .= ') ';
            }
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

    public function getForBrowse($bIncludePages = false, $userId = null, $iPagesLimit = null, $sView = '')
    {
        $where = 'pt.item_type = 0';
        if (!in_array($sView, ['my', 'liked'])) {
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
                foreach ($aTypes[$iKey]['pages'] as $iSubKey => $aRow) {
                    $aTypes[$iKey]['pages'][$iSubKey]['link'] = Phpfox::getService('pages')->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

                    // check manage/delete for each page
                    $bCanModerate = Phpfox::getUserParam('pages.can_approve_pages') || Phpfox::getUserParam('pages.can_edit_all_pages') || Phpfox::getUserParam('pages.can_delete_all_pages');

                    if (Phpfox::isAdmin() || $bCanModerate || Phpfox::getUserId() == $aTypes[$iKey]['pages'][$iSubKey]['user_id']) {
                        $aTypes[$iKey]['pages'][$iSubKey]['manage'] = true;
                    } else {
                        $aTypes[$iKey]['pages'][$iSubKey]['manage'] = false;
                    }

                    // check is member
                    $aTypes[$iKey]['pages'][$iSubKey]['is_liked'] = Phpfox::getService('pages')->isMember($aRow['page_id']);

                    // check manage/delete for each page
                    Phpfox::getService('pages')->getActionsPermission($aTypes[$iKey]['pages'][$iSubKey], $sView);
                }

                // get total pages for each category
                $aTypes[$iKey]['total_pages'] = Phpfox::getService('pages')->getItemsByCategory($aType['type_id'], false, 0, $userId, true, $sView);
            }

            if ($sView) {
                $aTypes[$iKey]['link'] = Phpfox::permalink(['pages.category', 'view' => $sView], $aType['type_id'],
                    $aType['name']);
            } else {
                $aTypes[$iKey]['link'] = Phpfox::permalink('pages.category', $aType['type_id'], $aType['name']);
            }

            $aTypes[$iKey]['image_path'] = sprintf($aTypes[$iKey]['image_path'], '_200');
        }

        return $aTypes;
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
}
