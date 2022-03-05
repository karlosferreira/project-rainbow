<?php

namespace Apps\Core_Messages\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_File;
use Phpfox_Error;
use Phpfox_Pager;
use Phpfox_Module;
use Phpfox_Template;

defined('PHPFOX') or exit('NO DICE!');

class IndexController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::isUser(true);

        if (($aItemModerate = $this->request()->get('conversation_action'))) {
            $sFile = Phpfox::getService('mail')->getThreadsForExport($aItemModerate);
            Phpfox_File::instance()->forceDownload($sFile, 'mail.xml');
        }

        $iThreadId = $this->request()->get('thread_id');
        if (($sAction = $this->request()->get('action')) && $iThreadId) {
            if (Phpfox::getService('mail.process')->applyConversationAction($sAction, $iThreadId)) {
                if($sAction == 'spam') {
                    $this->url()->send('mail', [],  _p('mail_marked_spam_successfully'));
                } else if ($sAction == 'delete') {
                    $this->url()->send('mail', [],  _p('mail_delete_successfully'));
                } else if ($sAction == 'leave') {
                    $this->url()->send('mail', [],  _p('mail_leave_successfully'));
                }
            }
        }
        $aDefaultFolders = Phpfox::getService('mail.helper')->getDefaultFolder();
        $aSearch = $this->request()->get('search');
        $sTitleFolderDefault = !empty($aSearch['view']) ? $aDefaultFolders[$aSearch['view']]['title'] : (!empty($aSearch['custom']) ? Phpfox::getService('mail.customlist')->getCustomList($aSearch['custom'])['name'] : _p('mail_all_mails'));
        $sView = !empty($aSearch['view']) ? $aSearch['view'] : '';

        $iPageSize = 8;
        $aFolders = Phpfox::getService('mail.customlist')->get();

        list($iCnt, $aRows) = Phpfox::getService('mail')->getSearch($aSearch, $this->search()->getPage(), $iPageSize, $iThreadId);
        $bCanLoadMore = ($iPageSize < $iCnt) ? true : false;
        $sChatContentDefault = $sContentTitle = '';
        $aThread = [];
        if($iThreadId) {
            $aThread = Phpfox::getService('mail')->getThread($iThreadId);
            if (!$aThread['is_read']) {
                $aThread['viewer_is_new'] = 0;
            }
        }
        if (!$aThread && !empty($aRows[0])) {
            if (!$aRows[0]['is_read']) {
                $aRows[0]['viewer_is_new'] = 0;
            }
            $aThread = $aRows[0];
        }

        if($aThread) {
            $sChatContentDefault = Phpfox::getService('mail')->getChatContentDefault($aThread['thread_id']);
            $sContentTitle = Phpfox::getService('mail.helper')->createConversationTitle($aThread, $sView);
            Phpfox_Module::instance()->dispatch('mail.index');
        }

        $iCustomlistId = $this->request()->getInt('customlist_id');
        $bIsRealCustomlistMessage = false;
        if (!empty($iCustomlistId)) {
            $sChatContentDefault = '';
            $bIsRealCustomlistMessage = true;
        }

        if (empty($sChatContentDefault)) {
            $sContentTitle = Phpfox::getService('mail')->getMailComposeContent($bIsRealCustomlistMessage, $iCustomlistId);
            Phpfox_Module::instance()->dispatch('mail.index');
            $this->setParam('attachment_share', [
                    'type' => 'mail',
                    'inline' => true,
                    'id' => 'js_compose_new_message'
                ]
            );
        }

        $aMassActions = [];

        if ($sView == 'trash') {
            $aMassActions['un-archive'] = '<i class="ico ico-inbox-o mr-1"></i>' . _p('un_archive');
        } elseif ($sView == 'spam') {
            $aMassActions['un-spam'] = '<i class="ico ico-flag-triangle-o mr-1"></i>' . _p('mail_unspam');
        } else {
            $aMassActions['archive'] = '<i class="ico ico-inbox-o mr-1"></i>' . _p('archive');
            $aMassActions['spam'] = '<i class="ico ico-inbox-o mr-1"></i>' . _p('mail_spam');
            $aMassActions['delete'] = '<i class="ico ico-trash-o mr-1"></i>' . _p('Delete');
            $aMassActions['mark_as_read'] = '<i class="ico ico-check-circle-alt mr-1"></i>' . _p('mail_mark_as_read');
        }
        $aMassActions['export'] = '<i class="ico ico-external-link mr-1"></i>' . _p('export');

        $this->template()
            ->setPhrase([
                'add_new_folder',
                'adding_new_folder',
                'view_folders',
                'edit_folders',
                'you_will_delete_every_message_in_this_folder',
                'updating'
            ])
            ->setHeader('cache', [
                'jquery/plugin/jquery.highlightFade.js' => 'static_script',
                'selector.js' => 'static_script',
            ])
            ->assign([
                'aMails' => $aRows,
                'aFolders' => $aFolders,
                'iTotalMessages' => $iCnt,
                'sSiteName' => Phpfox::getParam('core.site_title'),
                'sChatContentDefault' => $sChatContentDefault,
                'sTitleContentDefault' => $sContentTitle,
                'sView' => !empty($aSearch['view']) ? $aSearch['view'] : '',
                'aDefaultFolders' => $aDefaultFolders,
                'aForms' => $aSearch,
                'sCustomList' => !empty($aSearch['custom']) ? $aSearch['custom'] : '',
                'bCanLoadMore' => $bCanLoadMore,
                'sDefaultFolderTitle' => $sTitleFolderDefault,
                'aMassActions' => $aMassActions,
                'sForm' => !empty($sChatContentDefault) ? 'js_ajax_mail_thread' : 'js_ajax_compose_message',
                'bIsComposeForCustomlist' => $bIsRealCustomlistMessage ? 1 : 0,
                'bCanComposeMessage' => Phpfox::getUserParam('mail.can_compose_message')
            ]
        );
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('mail.component_controller_index_clean')) ? eval($sPlugin) : false);
    }
}