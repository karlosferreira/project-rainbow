<?php
namespace Apps\Core_Messages\Block;

use Phpfox;
use Phpfox_Component;

/**
 * Class GroupMember
 * @package Apps\Core_Messages\Block
 */
class GroupMember extends Phpfox_Component
{
    public function process()
    {
        $iThreadId = $this->getParam('thread_id');

        if(empty($iThreadId))
        {
            return \Phpfox_Error::display(_p('mail_invalid_conversation'));
        }

        $aThread = Phpfox::getService('mail')->getThreadedMail($iThreadId);

        if(empty($aThread[0]) || (!empty($aThread[0]) && !$aThread[0]['is_group']))
        {
            return \Phpfox_Error::display(_p('mail_invalid_conversation'));
        }

        $aUsers = Phpfox::getService('mail')->getGroupConversationMembers($iThreadId);

        $this->template()->assign([
            'aUsers' => $aUsers
        ]);

        return 'block';
    }
}