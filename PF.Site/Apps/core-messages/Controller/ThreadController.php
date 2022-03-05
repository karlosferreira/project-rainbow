<?php

namespace Apps\Core_Messages\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Template;
use Phpfox_Error;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');

class ThreadController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::isUser(true);
        $iThreadId = !empty($this->request()->getInt('id')) ? $this->request()->getInt('id') : ($this->getParam('id'));
        list($aThread, $aMessages) = Phpfox::getService('mail')->getThreadedMail($iThreadId);
        if ($aThread === false) {
            return Phpfox_Error::display(_p('unable_to_find_a_conversation_history_with_this_user'));
        }
        $aMessages = Phpfox::getService('mail')->listDateForMessages($aMessages);
        $aValidation = [
            'message' => _p('add_reply')
        ];

        $oValid = Phpfox_Validator::instance()->set([
                'sFormName' => 'js_form',
                'aParams' => $aValidation
            ]
        );

        if ($aThread['user_is_archive']) {
            $this->request()->set('view', 'trash');
        }

        Phpfox::getService('mail.process')->threadIsRead($aThread['thread_id']);

        if ($aThread['is_group']) {
            $sGroupTitle = Phpfox::getService('mail')->getGroupTitle($aThread['thread_id']);
        }

        $bCanViewThread = false;
        $bCanReplyThread = false;

        foreach ($aThread['users'] as $aUser) {
            if ($aUser['user_id'] == Phpfox::getUserId()) {
                $bCanViewThread = true;
            }
            if (Phpfox::getService('mail')->canMessageUser($aUser['user_id'])) {
                $bCanReplyThread = true;
            }
        }

        if (!$bCanViewThread) {
            return Phpfox_Error::display('Unable to view this thread.');
        }

        $this->template()->setHeader('cache', [
                'jquery/plugin/jquery.scrollTo.js' => 'static_script'
            ]
        )
            ->setEditor()
            ->assign([
                    'sCreateJs' => $oValid->createJS(),
                    'sGetJsForm' => $oValid->getJsForm(false),
                    'aMessages' => $aMessages,
                    'aThread' => $aThread,
                    'sCurrentPageCnt' => ($this->request()->getInt('page', 0) + 1),
                    'bCanReplyThread' => $bCanReplyThread,
                    'bCanLoadMore' => count($aMessages) < 10 ? 0 : 1
                ]
            );

        $this->setParam('global_moderation', [
                'name' => 'mail',
                'custom_fields' => '<div><input type="hidden" name="forward_thread_id" value="' . $aThread['thread_id'] . '" id="js_forward_thread_id" /></div>',
                'menu' => [
                    [
                        'phrase' => _p('forward'),
                        'action' => 'forward'
                    ]
                ]
            ]
        );

        $this->setParam('attachment_share', [
                'type' => 'mail',
                'inline' => true,
                'id' => 'js_compose_new_message'
            ]
        );
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('mail.component_controller_thread_clean')) ? eval($sPlugin) : false);
    }
}