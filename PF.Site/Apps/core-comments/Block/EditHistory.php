<?php

namespace Apps\Core_Comments\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;

class EditHistory extends Phpfox_Component
{
    public function process()
    {
        $iCommentId = $this->getParam('comment_id');
        if (!$iCommentId) {
            return false;
        }
        $aEditHistory = Phpfox::getService('comment.history')->getEditHistory($iCommentId);
        if (!$aEditHistory) {
            return false;
        }
        $this->template()->assign([
            'aEditHistory' => $aEditHistory
        ]);
        return 'block';
    }
}