<?php

namespace Apps\PHPfox_IM\Controller;

use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class AdminDeleteMessagesController extends \Admincp_Component_Controller_App_Index
{
    public function process()
    {
        parent::process();

        $server = setting('pf_im_chat_server', 'nodejs');
        $this->template()->assign([
            'server'        => $server,
            'firebaseImage' => Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-im/assets/images/firebase-remove-step.png',
            'algoliaImage'  => Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-im/assets/images/algolia-remove.png',
            'message'       => _p('the_administrator_can_remove_all_old_messages_from_server_server', [
                'server' => $server == 'firebase' ? 'Firebase' : 'Node JS'
            ])
        ]);
        if ($server == 'nodejs') {
            $this->template()->setHeader([
                'im-libraries.min.js' => 'app_core-im',
            ])->setPhrase([
                'all_old_messages_removed_successfully',
                'notice'
            ]);
        }
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('im.component_controller_admincp_manage_sound_clean')) ? eval($sPlugin) : false);
    }
}