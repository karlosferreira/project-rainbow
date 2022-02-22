<?php
namespace Apps\Core_Forums\Service\Moderate;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');


class Process extends Phpfox_Service
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('forum_moderator');
    }

    /**
     * @param array $aVals
     *
     * @return bool
     */
    public function add($aVals)
    {
        $aUsers = [];
        $aModerators = $this->database()->select('user_id, moderator_id')
            ->from($this->_sTable)
            ->where('forum_id = ' . $aVals['forum'])
            ->execute('getSlaveRows');
        foreach ($aModerators as $aModerator) {
            $aUsers[$aModerator['user_id']] = $aModerator['moderator_id'];
        }

        $deletedModeratorIds = [];
        if (empty($aVals['user_id'])) {
            if (!empty($aVals['users'])) {
                $currentUserIds = array_unique(array_keys($aUsers));
                $insertedUserIds = array_diff($aVals['users'], $currentUserIds);
                $updatedUserIds = array_intersect($aVals['users'], $currentUserIds);
                $deletedUserIds = array_diff($currentUserIds, $aVals['users']);

                if (!empty($deletedUserIds)) {
                    foreach ($deletedUserIds as $deletedUserId) {
                        if (isset($aUsers[$deletedUserId])) {
                            $deletedModeratorIds[] = $aUsers[$deletedUserId];
                        }
                    }
                }

                if (!empty($insertedUserIds) || !empty($updatedUserIds)) {
                    $allUserIds = array_unique(array_merge(!empty($insertedUserIds) ? $insertedUserIds : [], !empty($updatedUserIds) ? $updatedUserIds : []));
                    foreach ($allUserIds as $iUserId) {
                        if (isset($aUsers[$iUserId])) {
                            // update
                            $this->_update($aUsers[$iUserId], $aVals['param']);
                        } else {
                            // insert
                            $this->_insert($aVals['forum'], $iUserId, $aVals['param']);
                        }
                    }
                }
            } elseif (!empty($aUsers) ) {
                $deletedModeratorIds = array_values($aUsers);
            }
        }

        if (!empty($deletedModeratorIds) && db()->delete($this->_sTable, ['moderator_id' => ['in' => implode(',', $deletedModeratorIds)]])) {
            db()->delete(':forum_moderator_access', ['moderator_id' => ['in' => implode(',', $deletedModeratorIds)]]);
        }

        if (!empty($aVals['user_id']) && is_numeric($aVals['user_id'])) {
            if (isset($aUsers[$aVals['user_id']])) {
                // update
                $this->_update($aUsers[$aVals['user_id']], $aVals['param']);
            } else {
                $this->_insert($aVals['forum'], $aVals['user_id'], $aVals['param']);
            }
        }

        $this->cache()->remove('forum_permission');

        return true;
    }

    /**
     * @param int $iModeratorId
     * @param array $aDatas
     *
     * @return void
     */
    private function _update($iModeratorId, $aDatas)
    {
        $this->database()->delete(Phpfox::getT('forum_moderator_access'), 'moderator_id = ' . $iModeratorId);

        foreach ($aDatas as $sVar => $mValue) {
            if ($mValue) {
                $this->database()->insert(Phpfox::getT('forum_moderator_access'),
                    array('moderator_id' => $iModeratorId, 'var_name' => $sVar));
            }
        }
    }

    /**
     * @param int $iForumId
     * @param int $iUserId
     * @param array $aDatas
     *
     * @return void
     */
    private function _insert($iForumId, $iUserId, $aDatas)
    {
        $iId = $this->database()->insert($this->_sTable, array('forum_id' => $iForumId, 'user_id' => $iUserId));

        foreach ($aDatas as $sVar => $mValue) {
            if ($mValue) {
                $this->database()->insert(Phpfox::getT('forum_moderator_access'), [
                    'moderator_id' => $iId,
                    'var_name' => $sVar
                ]);
            }
        }
    }

    /**
     * @param int $iId
     *
     * @return void
     */
    public function delete($iId)
    {
        $this->database()->delete(Phpfox::getT('forum_moderator'), 'moderator_id = ' . (int)$iId);
        $this->database()->delete(Phpfox::getT('forum_moderator_access'), 'moderator_id = ' . (int)$iId);

        $this->cache()->remove('forum_permission');
    }

    /**
     * If a call is made to an unknown method attempt to connect
     * it to a specific plug-in with the same name thus allowing
     * plug-in developers the ability to extend classes.
     *
     * @param string $sMethod is the name of the method
     * @param array $aArguments is the array of arguments of being passed
     *
     * @return null
     */
    public function __call($sMethod, $aArguments)
    {
        /**
         * Check if such a plug-in exists and if it does call it.
         */
        if ($sPlugin = Phpfox_Plugin::get('forum.service_moderate_process__call')) {
            eval($sPlugin);
            return null;
        }

        /**
         * No method or plug-in found we must throw a error.
         */
        Phpfox_Error::trigger('Call to undefined method ' . __CLASS__ . '::' . $sMethod . '()', E_USER_ERROR);
    }
}