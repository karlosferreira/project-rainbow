<?php

namespace Apps\Core_Messages\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Ajax;
use Phpfox_Template;
use Phpfox_Error;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');

class ConversationPopupController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);

        $iThreadId = !empty($this->request()->getInt('id')) ? $this->request()->getInt('id') : ($this->getParam('id'));
        list($aThread, $aMessages) = Phpfox::getService('mail')->getThreadedMail($iThreadId);

        if ($aThread === false) {
            return Phpfox_Error::display(_p('unable_to_find_a_conversation_history_with_this_user'));
        }

        $aMessages = Phpfox::getService('mail')->listDateForMessages($aMessages);

        $aValidation = array(
            'message' => _p('add_reply')
        );

        $oValid = Phpfox_Validator::instance()->set(array(
                'sFormName' => 'js_form',
                'aParams' => $aValidation
            )
        );

        if ($aThread['user_is_archive']) {
            $this->request()->set('view', 'trash');
        }

        Phpfox::getService('mail.process')->threadIsRead($aThread['thread_id']);

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

        $this->template()->setTitle($aThread['thread_name'])
            ->setHeader('cache', array(
                    'jquery/plugin/jquery.scrollTo.js' => 'static_script'
                )
            )
            ->setEditor()
            ->assign(array(
                    'sCreateJs' => $oValid->createJS(),
                    'sGetJsForm' => $oValid->getJsForm(false),
                    'aMessages' => $aMessages,
                    'aThread' => $aThread,
                    'sCurrentPageCnt' => ($this->request()->getInt('page', 0) + 1),
                    'bCanReplyThread' => $bCanReplyThread,
                    'bCanComposeMessage' => Phpfox::getUserParam('mail.can_compose_message'),
                    'sForm' => 'js_ajax_mail_thread'
                )
            );

        $this->setParam('global_moderation', array(
                'name' => 'mail',
                'custom_fields' => '<div><input type="hidden" name="forward_thread_id" value="' . $aThread['thread_id'] . '" id="js_forward_thread_id" /></div>',
                'menu' => array(
                    array(
                        'phrase' => _p('forward'),
                        'action' => 'forward'
                    )
                )
            )
        );

        $this->setParam('attachment_share', array(
                'type' => 'mail',
                'inline' => true,
                'id' => 'js_compose_new_message'
            )
        );
        return null;
    }
}