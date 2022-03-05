<?php

namespace Apps\Core_Messages\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox_Module;

defined('PHPFOX') or exit('NO DICE!');

class CustomListController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        $aSearch = $this->request()->get('search');
        $iSize = 8;
        if ($iDeleteId = $this->request()->get('delete')) {
            if (Phpfox::getService('mail.customlist.process')->delete($iDeleteId)) {
                $this->url()->send('mail.customlist', [], _p('mail_delete_custom_list_successfully'));
            }
        }

        list($iCnt, $aCustomList) = Phpfox::getService('mail.customlist')->getSearch($aSearch, $this->search()->getPage(), $iSize);

        $bCanLoadMore = ($iSize < $iCnt) ? true : false;
        $sTitleContentDefault = $sCustomListDefaultContent = '';
        if (!empty($aCustomList[0])) {
            $sCustomListDefaultContent = Phpfox::getService('mail.customlist')->getCustomListContentDefault($aCustomList[0]['folder_id']);
            Phpfox_Module::instance()->dispatch('mail.customlist.index');
            $sTitleContentDefault = '
                <div class="js_custom_list_title custon-list-input" data-id="' . $aCustomList[0]['folder_id'] . '"><span id="back-to-list-js" class="back-to-list hidden"><i class="ico ico-arrow-left-circle-o" aria-hidden="true"></i></span><span class="js_customlist_name_' . $aCustomList[0]['folder_id'] . ' fw-bold">' . $aCustomList[0]['name'] . '</span><input type="text" class="js_custom_list_title_change" value="' . $aCustomList[0]['name'] . '" style="display:none;"></div>
                <div class="dropdown remove-list-group"><span class="btn fz-16" data-toggle="dropdown"><i class="ico ico-gear-o" aria-hidden="true"></i></span><ul class="dropdown-menu dropdown-menu-right"><li><a href="' . $this->url()->makeUrl('mail', ['customlist_id' => $aCustomList[0]['folder_id']]) . '"><i class="fa fa-pencil-square-o" aria-hidden="true"></i>' . _p('mail_send_messages') . '</a></li><li class="item_delete"><a href="' . \Phpfox_Url::instance()->makeUrl('mail.customlist', ['delete' => $aCustomList[0]['folder_id']]) . '" class="sJsConfirm" data-message="' . _p('are_you_sure') . '"><i class="fa fa-trash-o" aria-hidden="true"></i>' . _p('Delete') . '</a></li></ul></div>';
            $aDefaultCustom = Phpfox::getService('mail.customlist')->getCustomList($aCustomList[0]['folder_id']);
            if (!empty($aDefaultCustom['users'])) {
                $users = [];
                foreach ($aDefaultCustom['users'] as $user) {
                    $users[] = [
                        'user_id' => $user['user_id'],
                        'full_name' => $user['full_name'],
                        'user_image' => base64_encode(Phpfox::getLib('image.helper')->display([
                            'user' => $user,
                            'suffix' => '_120_square',
                            'max_width' => 32,
                            'max_height' => 32,
                            'no_link' => true,
                            'style' => "vertical-align:middle;",
                        ]))
                    ];
                }
            }
        } elseif(Phpfox::getUserParam('mail.can_compose_message')) {
            list($sTitleContentDefault, $sCustomListDefaultContent) = Phpfox::getService('mail.customlist')->getAddCustomlistContent();
            Phpfox_Module::instance()->dispatch('mail.customlist.index');
        }
        else {
            $sTitleContentDefault = '<div class="fw-bold create-custom"><span id="back-to-list-js" class="back-to-list hidden"><i class="ico ico-arrow-left-circle-o"></i></span>'._p('mail_you_do_not_have_permission_to_add_new_custom_list').'</div>';
        }

        $this->template()->setPhrase(['mail_invalid_name', 'mail_friend_list_cannot_be_null', 'mail_limitation_custom_list_members', 'mail_cannot_create_more_custom_list_because_of_limitation'])
            ->assign([
                'aCustomList' => $aCustomList,
                'aForms' => $aSearch,
                'sContentDefault' => $sCustomListDefaultContent,
                'sTitleDefault' => $sTitleContentDefault,
                'iTotal' => count($aCustomList),
                'bCanLoadMore' => $bCanLoadMore,
                'aCustomListMembers' => json_encode($users),
                'bCanComposeMessage' => Phpfox::getUserParam('mail.can_compose_message')
            ])
            ->setHeader('cache', array(
                'imagesloaded.min.js' => 'static_script',
            ));
    }
}