<?php

namespace Apps\PHPfox_IM\Controller;

use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class AdminExportDataController extends \Admincp_Component_Controller_App_Index
{
    public function process()
    {
        parent::process();
        // check Chat Plus enabled
        if (!Phpfox::isAppActive('P_ChatPlus')) {
            $this->template()->assign([
                'bNoChatPlus' => true
            ]);
            return;
        }
        $server = setting('pf_im_chat_server', 'nodejs');
        $this->template()->assign([
            'server' => $server,
        ]);

        if ($server == 'nodejs') {
            $this->template()->setHeader([
                'im-libraries.min.js' => 'app_core-im',
            ]);
        } else {
            $image = Phpfox::getLib('image.helper')->display([
                'user' => Phpfox::getUserBy(),
                'suffix' => '_120_square'
            ]);

            $imageUrl = Phpfox::getLib('image.helper')->display([
                'user' => Phpfox::getUserBy(),
                'suffix' => '_120_square',
                'return_url' => true
            ]);

            $image = htmlspecialchars($image);
            $image = str_replace(['<', '>'], ['&lt;', '&gt;'], $image);

            $sticky_bar = '<div id="auth-user" data-image-url="' . str_replace("\"", '\'', $imageUrl) . '" data-user-name="' . Phpfox::getUserBy('user_name') . '" data-id="' . Phpfox::getUserId() . '" data-name="' . Phpfox::getUserBy('full_name') . '" data-image="' . $image . '"></div>';
            $this->template()->assign([
                'sticky_bar' => $sticky_bar
            ]);
        }
        $error = '';
        if (db()->select('job_id')->from(Phpfox::getT('chatplus_job'))->where(['name' => 'onImportConversation'])->executeField()) {
            $error = _p('im_exported_data_to_chat_plus');
        }
        $this->template()->assign([
            'error' => $error
        ])->setPhrase([
            'opps_something_went_wrong',
            'done_messages_will_be_exported_soon'
        ]);
        return 'controller';
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