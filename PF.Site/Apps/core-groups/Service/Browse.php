<?php

namespace Apps\PHPfox_Groups\Service;

use Phpfox;
use Phpfox_Pages_Browse;

/**
 * Class Browse
 *
 * @package Apps\PHPfox_Groups\Service
 */
class Browse extends Phpfox_Pages_Browse
{
    private $_aUserGroupIds = [];
    public function getFacade()
    {
        return Phpfox::getService('groups.facade');
    }

    /**
     * @param array $aUserGroupIds
     *
     * @return $this
     */
    public function groupIds($aUserGroupIds)
    {
        $this->_aUserGroupIds = $aUserGroupIds;
        return $this;
    }

    /**
     * @param bool $bIsCount
     * @param bool $bNoQueryFriend
     *
     * @return void
     */
    public function getQueryJoins($bIsCount = false, $bNoQueryFriend = false)
    {
        if (Phpfox::isModule('friend') && Phpfox::getService('friend')->queryJoin($bNoQueryFriend)) {
            $onConditions = '(friends.user_id = pages.user_id AND friends.friend_user_id = ' . Phpfox::getUserId();
            if (Phpfox::getParam('core.friends_only_community')) {
                if (count($this->_aUserGroupIds)) {
                    $onConditions .= ") OR pages.page_id IN (" . implode(',', $this->_aUserGroupIds) . ")";
                    if (!$bIsCount) {
                        $this->database()->group('pages.page_id');
                    }
                } else {
                    $onConditions .= ')';
                }
            } else {
                $onConditions .= ')';
            }
            $this->database()->join(Phpfox::getT('friend'), 'friends', $onConditions);
        }
    }

    public function processRows(&$aRows)
    {
        foreach ($aRows as $iKey => $aRow) {
            Phpfox::getService('groups')->getActionsPermission($aRows[$iKey], 'pending');

            if (!empty($aRow['category_id'])) {
                $aRows[$iKey]['category_link'] = Phpfox::permalink('groups.sub-category', $aRow['category_id'], $aRow['category_name']);
            }
            else {
                $aRows[$iKey]['type_link'] = Phpfox::permalink('groups.category', $aRow['type_id'], $aRow['type_name']);
            }
            $aRows[$iKey]['link'] = $this->getFacade()->getItems()->getUrl($aRow['page_id'], $aRow['title'], $aRow['vanity_url']);

            list($iCnt, $aMembers) = Phpfox::getService('groups')->getMembers($aRow['page_id'], 4);
            $aRows[$iKey]['members'] = $aMembers;
            $aRows[$iKey]['total_members'] = $iCnt;
            $aRows[$iKey]['remain_members'] = $iCnt - 3;
            $aRows[$iKey]['text_parsed'] = Phpfox::getService('groups')->getInfo($aRow['page_id'], true);
            $aRows[$iKey]['profile_user_id'] = Phpfox::getService('groups')->getUserId($aRow['page_id']);
        }
    }
}
