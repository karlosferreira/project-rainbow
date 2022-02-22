<?php

namespace Apps\Core_Comments\Service;

use Phpfox;


defined('PHPFOX') or exit('NO DICE!');

class Emoticon extends \Phpfox_Service
{
    protected $_sTable;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('comment_emoticon');
    }

    public function getAll()
    {
        return get_from_cache(['comment.emoticons'], function () {
            return db()->select('*')->from($this->_sTable)->execute('getSlaveRows');
        }, 1);
    }

    public function getRecentEmoticon($iUserId = null)
    {
        if (!$iUserId) {
            $iUserId = Phpfox::getUserId();
        }
        $iDay = PHPFOX_TIME - 86000 * 7;
        return Phpfox::getService('comment.tracking')->getTracking($iUserId, 'emoticon', $iDay);
    }
}