<?php

namespace Apps\Core_Messages\Service\CustomList;

use Phpfox;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Service;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');

class Process extends Phpfox_Service
{

    /**
     * Send new message for member in customlist when using compose message
     * @param $aVals
     * @param bool $bIsPageClaim
     * @return bool
     * @throws \Exception
     */
    public function addMessageForCustomList($aVals, $bIsPageClaim = false)
    {
        $aCustom = Phpfox::getService('mail.customlist')->getCustomList($aVals['customlist'][0]);
        if (empty($aCustom)) {
            return Phpfox_Error::set(_p('mail_invalid_custom_list'));
        }

        $aUnable2SendUsers = [];
        $aUsers = array_column($aCustom['users'], 'user_id');
        foreach($aUsers as $key => $iUserId) {
            if (!isset($aVals['claim_page']) && $iUserId != Phpfox::getUserId() && !Phpfox::getService('mail')->canMessageUser($iUserId)) {
                $aUnable2SendUsers[] = $iUserId;
                unset($aUsers[$key]);
            }
        }

        $bHasAttachments = (Phpfox::getUserParam('mail.can_add_attachment_on_mail') && !empty($aVals['attachment']) && Phpfox::isModule('attachment'));
        $oFilter = Phpfox::getLib('parse.input');
        $bAddMoreAttachments = false;

        foreach ($aUsers as $iUserId) {
            if (Phpfox::isModule('friend') && !Phpfox::getService('friend')->isFriend(Phpfox::getUserId(), $iUserId)) {
                return Phpfox_Error::set(_p('you_can_only_message_your_friends'));
            }
            $aTemp = [$iUserId, Phpfox::getUserId()];
            sort($aTemp, SORT_NUMERIC);
            $sHashId = md5(implode('', $aTemp));
            $aPastThread = $this->database()->select('*')
                ->from(Phpfox::getT('mail_thread'))
                ->where('hash_id = \'' . $this->database()->escape($sHashId) . '\'')
                ->execute('getSlaveRow');
            if (!empty($aPastThread)) {
                $aThreadUsers = $this->database()->select(Phpfox::getUserField() . ', u.email, u.language_id, u.user_group_id')
                    ->from(Phpfox::getT('mail_thread_user'), 'mtu')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = mtu.user_id')
                    ->where('mtu.user_id IN(' . implode(', ', $aTemp) . ')')
                    ->group('u.user_id', true)
                    ->execute('getSlaveRows');

                foreach ($aThreadUsers as $aThreadUser) {
                    if (!isset($aVals['claim_page']) && $aThreadUser['user_id'] != Phpfox::getUserId() && !Phpfox::getService('mail')->canMessageUser($aThreadUser['user_id'])) {
                        return Phpfox_Error::set(_p('unable_to_send_a_private_message_to_full_name_as_they_have_disabled_this_option', ['full_name' => $aThreadUser['full_name']]));
                    }
                }
                $iId = $aPastThread['thread_id'];
                $this->database()->update(Phpfox::getT('mail_thread'), [
                    'time_stamp' => PHPFOX_TIME
                ], 'thread_id = ' . (int)$iId
                );
                $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_sent_update' => '0', 'is_read' => '0', 'is_archive' => '0'], 'thread_id = ' . (int)$iId);
                $this->database()->update(Phpfox::getT('mail_thread_user'), ['is_read' => '1'], 'thread_id = ' . (int)$iId . ' AND user_id = ' . Phpfox::getUserId());

                // Send the user an email
                $sLink = Phpfox_Url::instance()->makeUrl('mail');

                foreach ($aThreadUsers as $aThreadUser) {
                    if ($aThreadUser['user_id'] == Phpfox::getUserId()) {
                        continue;
                    }

                    (($sPlugin = Phpfox_Plugin::get('mail.service_process_add_2')) ? eval($sPlugin) : false);
                    if (isset($bPluginSkip) && $bPluginSkip === true) {
                        continue;
                    }

                    Phpfox::getLib('mail')->to($aThreadUser['user_id'])
                        ->subject(['mail.full_name_sent_you_a_message_on_site_title', ['full_name' => Phpfox::getUserBy('full_name'), 'site_title' => Phpfox::getParam('core.site_title')], false, null, $aThreadUser['language_id']])
                        ->message(['mail.full_name_sent_you_a_message_no_subject', [
                                'full_name' => Phpfox::getUserBy('full_name'),
                                'message' => $oFilter->clean(strip_tags(Phpfox::getLib('parse.bbcode')->cleanCode(str_replace(['&lt;', '&gt;'], ['<', '>'], $aVals['message'])))),
                                'link' => $sLink
                            ]
                            ]
                        )
                        ->notification('mail.new_message')
                        ->send();
                }

            } else {
                $iId = $this->database()->insert(Phpfox::getT('mail_thread'), [
                        'hash_id' => $sHashId,
                        'time_stamp' => PHPFOX_TIME,
                    ]
                );

                $sLink = Phpfox_Url::instance()->makeUrl('mail');

                foreach ($aTemp as $iUserId) {
                    $this->database()->insert(Phpfox::getT('mail_thread_user'), [
                            'thread_id' => $iId,
                            'is_read' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                            'is_sent' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                            'is_sent_update' => ($iUserId == Phpfox::getUserId() ? '1' : '0'),
                            'user_id' => (int)$iUserId
                        ]
                    );
                    db()->insert(Phpfox::getT('mail_thread_user_compare'), [
                        'thread_id' => $iId,
                        'user_id' => $iUserId
                    ]);


                    if ($iUserId == Phpfox::getUserId()) {
                        continue;
                    }

                    (($sPlugin = Phpfox_Plugin::get('mail.service_process_add_2')) ? eval($sPlugin) : false);
                    if (isset($bPluginSkip) && $bPluginSkip === true) {
                        continue;
                    }

                    $aUser = Phpfox::getService('user')->getUser($iUserId);
                    Phpfox::getLib('mail')->to($iUserId)
                        ->subject(['mail.full_name_sent_you_a_message_on_site_title', ['full_name' => Phpfox::getUserBy('full_name'), 'site_title' => Phpfox::getParam('core.site_title')], false, null, $aUser['language_id']])
                        ->message(['mail.full_name_sent_you_a_message_no_subject', [
                                'full_name' => Phpfox::getUserBy('full_name'),
                                'message' => $oFilter->clean(strip_tags(Phpfox::getLib('parse.bbcode')->cleanCode(str_replace(['&lt;', '&gt;'], ['<', '>'], $aVals['message'])))),
                                'link' => $sLink
                            ]
                            ]
                        )
                        ->notification('mail.new_message')
                        ->send();
                }
            }
            $iTextId = $this->database()->insert(Phpfox::getT('mail_thread_text'), [
                    'thread_id' => $iId,
                    'time_stamp' => PHPFOX_TIME,
                    'user_id' => Phpfox::getUserId(),
                    'text' => $oFilter->prepare($aVals['message']),
                    'is_mobile' => '0'
                ]
            );
            $this->database()->update(Phpfox::getT('mail_thread'), ['last_id' => (int)$iTextId], 'thread_id = ' . (int)$iId);
            $this->database()->update(Phpfox::getT('mail_thread'), ['last_id_for_admin' => (int)$iTextId], 'thread_id = ' . (int)$iId);
            // If we uploaded any attachments make sure we update the 'item_id'
            if ($bHasAttachments) {
                if ($bAddMoreAttachments) {
                    $aIds = explode(',', $aVals['attachment']);
                    $sIds = '';
                    foreach ($aIds as $iID) {
                        if (empty($iID) || !is_numeric($iID)) {
                            continue;
                        }
                        $aAttachment = Phpfox::getService('attachment')->getForDownload($iID);
                        $aInsert = [
                            'category' => $aAttachment['category_id'],
                            'link_id' => $aAttachment['link_id'],
                            'file_name' => $aAttachment['file_name'],
                            'extension' => $aAttachment['extension'],
                            'is_image' => $aAttachment['is_image']
                        ];

                        $iNewId = Phpfox::getService('attachment.process')->add($aInsert);
                        db()->update(Phpfox::getT('attachment'), ['destination' => $aAttachment['destination'], 'file_size' => $aAttachment['file_size']], 'attachment_id = ' . $iNewId);
                        $sIds .= $iNewId . ',';
                    }
                    $sIds = trim($sIds, ',');

                    Phpfox::getService('attachment.process')->updateItemId($sIds, Phpfox::getUserId(), $iTextId);
                    $this->database()->update(Phpfox::getT('mail_thread_text'), ['total_attachment' => Phpfox::getService('attachment')->getCountForItem($iTextId, 'mail')], 'message_id = ' . (int)$iTextId);
                } else {
                    Phpfox::getService('attachment.process')->updateItemId($aVals['attachment'], Phpfox::getUserId(), $iTextId);
                    $this->database()->update(Phpfox::getT('mail_thread_text'), ['total_attachment' => Phpfox::getService('attachment')->getCountForItem($iTextId, 'mail')], 'message_id = ' . (int)$iTextId);
                    $bAddMoreAttachments = true;
                }

            }
        }

        (($sPlugin = Phpfox_Plugin::get('mail.service_process_add')) ? eval($sPlugin) : false);
        if (!empty($aUnable2SendUsers)) {
            $aUserIds = array_slice($aUnable2SendUsers, 0, 2);

            $aUserFullNames = db()->select('full_name')->from(Phpfox::getT('user'))
                ->where('user_id in ('. implode(',', $aUserIds). ')')
                ->execute('getSlaveRows');

            $aFullNames = array_column($aUserFullNames, 'full_name');
            $sNames = implode(', ', $aFullNames);
            if (count($aUnable2SendUsers) > 2) {
                if (count($aUnable2SendUsers) == 3) {
                    return Phpfox_Error::set(_p('unable_to_send_a_private_message_to_full_name_and_1_other_as_they_have_disabled_this_option', [
                        'full_name' => $sNames
                    ]));
                } else {
                    return Phpfox_Error::set(_p('unable_to_send_a_private_message_to_full_name_and_total_others_as_they_have_disabled_this_option', [
                        'full_name' => $sNames,
                        'total' => count($aUnable2SendUsers) - 2
                    ]));
                }
            }
            return Phpfox_Error::set(_p('unable_to_send_a_private_message_to_full_name_as_they_have_disabled_this_option', ['full_name' => $sNames]));
        }
        return true;
    }

    /**
     * Change customlist title
     * @param $aVals
     * @return bool|mixed
     * @throws \Exception
     */
    public function changeCustomListTitle($aVals)
    {
        $aVals['title'] = trim(strip_tags($aVals['title']));
        $aVals['title'] = db()->escape($aVals['title']);
        if (empty($aVals['title']) || !is_string($aVals['title'])) {
            return Phpfox_Error::set(_p('mail_invalid_title'));
        }
        $aRow = db()->select('*')
            ->from(Phpfox::getT('mail_thread_folder'))
            ->where('folder_id = ' . (int)$aVals['folder_id'])
            ->execute('getSlaveRow');
        if (empty($aRow)) {
            return Phpfox_Error::set(_p('mail_custom_list_not_exist'));
        }

        db()->update(Phpfox::getT('mail_thread_folder'), ['name' => $aVals['title']], 'folder_id = ' . $aVals['folder_id']);

        return $aVals['title'];
    }

    /**
     * Create new customlist
     * @param $aVals
     * @param bool $bIsEdit
     * @return bool|int
     */
    public function add($aVals, $bIsEdit = false)
    {
        if (!$bIsEdit) {
            $aFolderInsert = [
                'name' => $aVals['name'],
                'time_stamp' => PHPFOX_TIME
            ];
            $aFolderInsert['user_id'] = Phpfox::getUserId();
            $iId = db()->insert(Phpfox::getT('mail_thread_folder'), $aFolderInsert);

            foreach ($aVals['invite'] as $iUserId) {
                db()->insert(Phpfox::getT('mail_thread_custom_list'), ['folder_id' => $iId, 'user_id' => $iUserId]);
            }
            return $iId;
        } else {
            db()->delete(Phpfox::getT('mail_thread_custom_list'), 'folder_id = ' . $aVals['id']);
            foreach ($aVals['invite'] as $iUserId) {
                db()->insert(Phpfox::getT('mail_thread_custom_list'), ['folder_id' => $aVals['id'], 'user_id' => $iUserId]);
            }
            return true;
        }
    }

    /**
     * Delete customlist
     * @param $iFolderId
     * @return bool
     */
    public function delete($iFolderId)
    {
        if (is_numeric($iFolderId)) {
            db()->delete(Phpfox::getT('mail_thread_custom_list'), 'folder_id = ' . (int)$iFolderId . ' AND user_id = ' . Phpfox::getUserId());
            db()->delete(Phpfox::getT('mail_thread_folder'), 'folder_id = ' . (int)$iFolderId . ' AND user_id = ' . Phpfox::getUserId());
        } elseif (is_string($iFolderId)) {
            db()->delete(Phpfox::getT('mail_thread_custom_list'), 'folder_id IN (' . $iFolderId . ') AND user_id = ' . Phpfox::getUserId());
            db()->delete(Phpfox::getT('mail_thread_folder'), 'folder_id IN (' . $iFolderId . ') AND user_id = ' . Phpfox::getUserId());
        }
        return true;
    }
}