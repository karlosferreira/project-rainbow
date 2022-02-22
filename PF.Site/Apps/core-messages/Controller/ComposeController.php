<?php
namespace Apps\Core_Messages\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Error;
use Phpfox_Url;
use Phpfox_Database;
use Phpfox_Ajax;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');

class ComposeController extends Phpfox_Component
{
    private $_bReturn = null;

    public function process()
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('mail.can_compose_message', true);
        $bClaiming = ($this->getParam('page_id') != false);
        if (Phpfox::isSpammer())
        {
            return Phpfox_Error::display(_p('currently_your_account_is_marked_as_a_spammer'));
        }
        $aVals = $this->request()->getArray('val');
        $bIsAjaxPopup = $this->request()->get('is_ajax_popup');
        $bIsSendMessageProfile = $this->request()->get('no_remove_box');
        $bIsSending = isset($aVals['sending_message']);
        $iCustomlistId = (int)$this->getParam('customlist_id');
        $aValidation = [];
        if(!empty($iCustomlistId))
        {
            $aCustomlist = Phpfox::getService('mail.customlist')->getCustomList($iCustomlistId);
            if((int)$aCustomlist['user_id'] == Phpfox::getUserId())
            {
                $sSelectedCustomlist = '<span id="js_item_search_custom_list_16" class="item-search-custom-list-selected"><span class="item-search-custom-list-name">'.$aCustomlist['name'].'</span><input type="hidden" value="'.$aCustomlist['folder_id'].'" name="val[customlist][]"> </span>';
                $this->template()->assign([
                    'aCustomlist' => $aCustomlist,
                    'sSelectedCustomlist' => $sSelectedCustomlist
                ]);
            }
        }
        if (($iUserId = $this->request()->get('id')) || ($iUserId = $this->getParam('id')))
        {
            $aUser = Phpfox::getService('user')->getUser($iUserId, Phpfox::getUserField());
            if (isset($aUser['user_id']))
            {

                if ($bClaiming == false && $bIsSending != true && Phpfox::getService('mail')->canMessageUser($aUser['user_id']) == false)
                {
                    return Phpfox_Error::display(_p('unable_to_send_a_private_message_to_this_user_at_the_moment'));
                }

                $this->template()->assign('aUser', $aUser);
                if ($bClaiming)
                {
                    $aPage = Phpfox::getService('pages')->getPage($this->getParam('page_id'));
                    $this->template()->assign(array(
                        'iPageId' => $this->getParam('page_id'),
                        'aPage' => $aPage,
                        'sMessageClaim' => _p('page_claim_message', array(
                            'title' => $aPage['title'],
                            'url' => ($aPage['vanity_url'] ? Phpfox_Url::instance()->makeUrl($aPage['vanity_url']) : Phpfox::permalink('pages', $aPage['page_id'], $aPage['title']))
                        ))));
                }
            }

            (($sPlugin = Phpfox_Plugin::get('mail.component_controller_compose_controller_to')) ? eval($sPlugin) : false);
        }

        $bIsThreadForward = false;
        if (($iThreadId = $this->request()->getInt('forward_thread_id')))
        {
            $bIsThreadForward = true;
        }

        if (Phpfox::isAppActive('Core_Captcha') && Phpfox::getUserParam('mail.enable_captcha_on_mail'))
        {
            $aValidation['image_verification'] = _p('complete_captcha_challenge');
        }

        (($sPlugin = Phpfox_Plugin::get('mail.component_controller_compose_controller_validation')) ? eval($sPlugin) : false);

        $oValid = Phpfox_Validator::instance()->set(array(
                'sFormName' => 'js_form',
                'aParams' => $aValidation
            )
        );

        if (($aVals = $this->request()->getArray('val')))
        {
            // Lets make sure they are actually trying to send someone a message.
            if (((!isset($aVals['to'])) || (isset($aVals['to']) && !count($aVals['to']))) && (!isset($aVals['copy_to_self']) || $aVals['copy_to_self'] != 1) && (empty($aVals['customlist'])))
            {
                Phpfox_Error::set(_p('mail_select_members_or_custom_list_to_send_message'));
            }
            if((!empty($aVals['message']) && empty(strip_tags($aVals['message'],'<img>'))) || (empty($aVals['message']) && empty($aVals['attachment'])))
            {
                Phpfox_Error::set(_p('provide_message'));
            }
            else
            {
                $aVals['message'] = strip_tags($aVals['message'],'<img>');
            }
            if(!empty($aVals['to']) && ((int)count($aVals['to']) > (int)setting('mail.chat_group_member_maximum')))
            {
                Phpfox_Error::set(_p('mail_number_of_members_over_limitation',['number' => setting('mail.chat_group_member_maximum')]));
            }
            if ($oValid->isValid($aVals))
            {
                if (Phpfox::getLib('spam')->check(array(
                        'action' => 'isSpam',
                        'params' => array(
                            'module' => 'comment',
                            'content' => Phpfox::getLib('parse.input')->prepare($aVals['message'])
                        )
                    )
                )
                ) {
                    Phpfox_Error::set(_p('this_message_feels_like_spam_try_again'));
                }

                if (Phpfox_Error::isPassed()) {
                    if ($bClaiming) {
                        $aVals['claim_page'] = true;
                    }
                    if (($aIds = (!empty($aVals['to'])) ? Phpfox::getService('mail.process')->add($aVals, $bClaiming) : Phpfox::getService('mail.customlist.process')->addMessageForCustomList($aVals, $bClaiming))) {
                        if (isset($aVals['page_id']) && !empty($aVals['page_id'])) {
                            Phpfox_Database::instance()->insert(Phpfox::getT('pages_claim'), array('status_id' => '1', 'page_id' => ((int)$aVals['page_id']), 'user_id' => Phpfox::getUserId(), 'time_stamp' => PHPFOX_TIME));
                        }

                        if (PHPFOX_IS_AJAX) {
                            $this->_bReturn = true;
                            return true;
                        }

                        $this->url()->send('mail');
                    } else {
                        if (PHPFOX_IS_AJAX) {
                            $this->_bReturn = false;
                            return false;
                        }
                    }
                } else {
                    if (PHPFOX_IS_AJAX) {
                        $this->_bReturn = false;
                        return false;
                    }
                }
            } else {
                if (PHPFOX_IS_AJAX) {
                    $this->_bReturn = false;
                    return false;
                }
            }
        }

        if (Phpfox::isModule('friend'))
        {
            $this->template()->setPhrase(array('loading'));
        }
        $this->template()->setTitle(_p('compose_new_message'))
            ->setPhrase(array(
                'add_new_folder',
                'adding_new_folder',
                'view_folders',
                'edit_folders',
                'you_will_delete_every_message_in_this_folder',
            ))
            ->setEditor()
            ->setHeader('cache', array(
                'switch_legend.js' => 'static_script',
                'switch_menu.js' => 'static_script',
                'jquery/plugin/jquery.highlightFade.js' => 'static_script',
                'jquery/plugin/jquery.scrollTo.js' => 'static_script',
            ))
            ->assign(array(
                    'sCreateJs' => $oValid->createJS(),
                    'sGetJsForm' => $oValid->getJsForm(),
                    'bIsThreadForward' => $bIsThreadForward,
                    'sThreadsToForward' => $this->request()->get('forwards'),
                    'sForwardThreadId' => $iThreadId,
                    'iGroupMemberMaximum' => setting('mail.chat_group_member_maximum'),
                    'sForm' => !empty($bIsAjaxPopup) || !empty($bIsSendMessageProfile) || !empty($bClaiming)? 'js_ajax_compose_message' : '',
                    'bIsAjaxPopup' => $bIsAjaxPopup,
                    'sFriendPhrase' => _p('mail_friend_title'),
                    'sCustomlistPhrase' => _p('mail_custom_list_title'),
                    'numberOfMembersOverLimitation' => _p('mail_number_of_members_over_limitation')
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

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('mail.component_controller_compose_clean')) ? eval($sPlugin) : false);
    }

    public function getReturn()
    {
        if (!$this->_bReturn)
        {
            Phpfox_Ajax::instance()->call('$Core.processForm(\'#js_mail_compose_submit\', true);');
        }

        return $this->_bReturn;
    }
}