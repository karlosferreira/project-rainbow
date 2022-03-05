<?php

namespace Apps\Core_Messages\Service\CustomList;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;
use Phpfox_Ajax;
use Phpfox_Search;

defined('PHPFOX') or exit('NO DICE!');

class CustomList extends Phpfox_Service
{
    public function getAddCustomlistContent()
    {
        Phpfox::getComponent('mail.customlist.add', [], 'controller');
        $sContent = Phpfox_Ajax::instance()->getContent(false);
        $sTitleContentDefault = '<div class="fw-bold create-custom"><span id="back-to-list-js" class="back-to-list hidden"><i class="ico ico-arrow-left-circle-o"></i></span>' . _p('mail_create_custom_list') . '</div>';
        return [$sTitleContentDefault, $sContent];
    }

    /**
     * get Number of members of a customlist
     * @param $iFolderId
     * @return int
     */
    public function getCustomListMemberCount($iFolderId)
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_folder'), 'tf')
            ->join(Phpfox::getT('mail_thread_custom_list'), 'tcl', 'tf.folder_id = tcl.folder_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = tcl.user_id')
            ->where('tf.folder_id = ' . $iFolderId)
            ->execute('getSlaveField');
        return $iCnt;

    }

    /**
     * get content of first customlist in mail.customlist.index or a customlist after creation
     * @param $iFolderId
     * @return string
     */
    public function getCustomListContentDefault($iFolderId)
    {
        Phpfox::getComponent('mail.customlist.add', [
            'id' => $iFolderId
        ], 'controller');
        $sContent = Phpfox_Ajax::instance()->getContent(false);
        return $sContent;
    }

    /**
     * get a Customlist by Id
     * @param $iCustomList
     * @return array
     */
    public function getCustomList($iCustomList)
    {
        if (empty($iCustomList)) {
            return false;
        }
        $aRow = db()->select('*')
            ->from(Phpfox::getT('mail_thread_folder'))
            ->where('folder_id = ' . (int)$iCustomList)
            ->execute('getSlaveRow');
        if (!empty($aRow)) {
            $aUsers = db()->select(Phpfox::getUserField())
                ->from(Phpfox::getT('mail_thread_custom_list'), 'tcl')
                ->join(Phpfox::getT('user'), 'u', 'u.user_id = tcl.user_id')
                ->where('folder_id = ' . $iCustomList)
                ->execute('getSlaveRows');
            $aRow['users'] = $aUsers;
        }
        return $aRow;
    }

    /**
     * get number of customlist that user created
     * @param $iUserId
     * @return int
     */
    public function getUserFolderCount($iUserId)
    {
        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_folder'))
            ->where('user_id = ' . $iUserId)
            ->execute('getSlaveField');
        return (int)$iCnt;
    }

    /**
     * filter customlist by conditions
     * @param array $aSearch
     * @param int $iPage
     * @param int $iSize
     * @return array
     */
    public function getSearch($aSearch, $iPage = 1, $iSize = 10)
    {
        $oSearch = Phpfox_Search::instance();
        $aRows = [];
        if (!empty($aSearch['name'])) {
            $oSearch->setCondition('AND (f.name LIKE "%' . Phpfox::getLib('parse.input')->clean($aSearch['name']) . '%")');
        }
        $oSearch->setCondition('AND (f.user_id = ' . Phpfox::getUserId() . ')');

        $aConds = $oSearch->getConditions();

        $iCnt = db()->select('COUNT(*)')
            ->from(Phpfox::getT('mail_thread_folder'), 'f')
            ->where($aConds)
            ->execute('getSlaveField');

        if ($iCnt) {
            $aRows = db()->select('f.*, COUNT(l.folder_id) AS total_contacts')
                ->from(Phpfox::getT('mail_thread_folder'), 'f')
                ->join(Phpfox::getT('mail_thread_custom_list'), 'l', 'l.folder_id = f.folder_id')
                ->where($aConds)
                ->order('time_stamp DESC')
                ->group('f.folder_id')
                ->limit($iPage, $iSize)
                ->execute('getSlaveRows');
        }
        return [$iCnt, $aRows];
    }

    /**
     * get number of customlist have quitely same value with $sName
     * @param $sName
     * @return array
     */
    public function getListByName($sName)
    {
        $aRows = db()->select('*')
            ->from(Phpfox::getT('mail_thread_folder'))
            ->where('name LIKE "%' . $sName . '%"')
            ->order('time_stamp DESC')
            ->execute('getSlaveRows');
        return $aRows;
    }

    /**
     * get all customlist of current user
     * @return array
     */
    public function get()
    {
        $aRows = db()->select('*')
            ->from(Phpfox::getT('mail_thread_folder'))
            ->where('user_id = ' . Phpfox::getUserId())
            ->execute('getSlaveRows');
        return $aRows;
    }
}