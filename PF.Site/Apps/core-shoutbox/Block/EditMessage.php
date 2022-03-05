<?php
namespace Apps\phpFox_Shoutbox\Block;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Apps\phpFox_Shoutbox\Service\Shoutbox as sb;

/**
 * Class EditMessage
 * @package Apps\phpFox_Shoutbox\Block
 */
class EditMessage extends Phpfox_Component
{
    public function process()
    {
        $iShoutboxId = $this->getParam('shoutbox_id');
        if(empty($iShoutboxId))
        {
            return \Phpfox_Error::display(_p('shoutbox_cannot_edit_invalid_message'));
        }
        $aShoutbox = sb::get()->getShoutbox($iShoutboxId, true, true);
        if(empty($aShoutbox))
        {
            return \Phpfox_Error::display(_p('shoutbox_cannot_edit_invalid_message'));
        }
        if(!$aShoutbox['canEdit'])
        {
            return \Phpfox_Error::display(_p('shoutbox_you_are_not_allowed_to_edit_message'));
        }
        $this->template()->assign([
            'aShoutbox' => $aShoutbox,
            'textEncrypt' => base64_encode(Phpfox::getLib('parse.output')->clean(html_entity_decode($aShoutbox['text'], ENT_QUOTES, 'UTF-8'), false))
        ]);
    }
}