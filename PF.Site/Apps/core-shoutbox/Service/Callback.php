<?php
namespace Apps\phpFox_Shoutbox\Service;

use Phpfox_Service;
use Phpfox;
use Apps\phpFox_Shoutbox\Service\Get as GetService;
/**
 * Class Callback
 *
 * @package Apps\phpFox_Shoutbox\Service
 */
class Callback extends Phpfox_Service
{
    /**
     * @return array
     */
    public function getPagePerms()
    {
        $aPerms = [
            'shoutbox.share_shoutbox' => _p('who_can_share_messages'),
            'shoutbox.view_shoutbox'  => _p('who_can_view_shoutbox'),
        ];
        
        return $aPerms;
    }
    
    /**
     * @return array
     */
    public function getGroupPerms()
    {
        $aPerms = [
            'shoutbox.share_shoutbox' => _p('who_can_share_messages'),
            'shoutbox.view_shoutbox'  => _p('who_can_view_shoutbox'),
        ];
    
        return $aPerms;
    }

    public function getNotificationLike($aNotification)
    {
        $aRow = db()->select('s.*')
                ->from(Phpfox::getT('shoutbox'),'s')
                ->where('s.shoutbox_id = '. (int)$aNotification['item_id'])
                ->execute('getSlaveRow');

        if (!isset($aRow['shoutbox_id'])) {
            return false;
        }

        $sUser = Phpfox::getService('notification')->getUsers($aNotification);


        $sPhrase = _p('shoutbox_user_like_your_message',[
            'full_name' => $sUser,
            'message_text' => (new GetService())->getTextForNotification($aRow['text'])
        ]);

        return array(
            'link' => Phpfox::getLib('url')->makeUrl('shoutbox.view',['id' => (int)$aNotification['item_id']]),
            'custom_image' => $sPhrase,
            'icon' => Phpfox::getLib('template')->getStyle('image', 'activity.png', 'blog')
        );
    }
}