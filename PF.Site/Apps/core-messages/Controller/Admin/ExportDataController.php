<?php

namespace Apps\Core_Messages\Controller\Admin;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;

defined('PHPFOX') or exit('NO DICE!');

class ExportDataController extends Phpfox_Component
{
    public function process()
    {
        $bImported = false;
        $isExport = $this->request()->get('export');
        if (!Phpfox::isAppActive('P_ChatPlus')) {
            $this->template()->assign([
                'bNoChatPlus' => true
            ]);
        } elseif (db()->select('job_id')->from(Phpfox::getT('chatplus_job'))->where(['name' => 'onImportConversation'])->executeField()) {
            $bImported = true;
            if ($isExport) {
                return Phpfox_Error::display(_p('exported_data_to_chat_plus'));
            }
        }

        if ($isExport) {
            if (function_exists('set_time_limit')) {
                set_time_limit(600);
            }
            list(, $conversations) = Phpfox::getService('mail')->getConversationForAdmin([], null, null);
            foreach ($conversations as $key => $conversation) {
                $conversation_id = $conversation['thread_id'];
                $conversationImport = [
                    'conversation_id'   => $conversation_id,
                    'time_stamp'        => $conversation['time_stamp'],
                    'is_group'          => $conversation['is_group'],
                    'conversation_name' => $conversation['thread_name']
                ];
                $users = db()->select('u.user_id')
                    ->from(Phpfox::getT('mail_thread_user'), 'th')
                    ->join(Phpfox::getT('user'), 'u', 'u.user_id = th.user_id')
                    ->where('th.thread_id = ' . (int)$conversation_id)
                    ->execute('getSlaveRows');
                $conversationImport['users'] = array_column($users, 'user_id');

                $conditions = 'mtt.is_deleted = 0 AND mtt.thread_id = ' . $conversation_id;
                list(, $messages) = Phpfox::getService('mail')->getMessagesForAdmin($conditions, null, null);
                foreach ($messages as $mesKey => $message) {
                    unset($messages[$mesKey]['thread_id']);
                    unset($messages[$mesKey]['is_mobile']);
                    unset($messages[$mesKey]['has_forward']);
                    unset($messages[$mesKey]['full_name']);
                    unset($messages[$mesKey]['forwards']);
                    if (isset($message['attachments'])) {
                        $attachmentsImport = [];
                        foreach ($message['attachments'] as $attachment) {
                            $attachmentsImport[] = [
                                'attachment_id' => $attachment['attachment_id'],
                                'time_stamp'    => $attachment['time_stamp'],
                                'file_name'     => $attachment['file_name'],
                                'file_size'     => $attachment['file_size'],
                                'extension'     => $attachment['extension'],
                                'is_image'      => $attachment['is_image'],
                                'download_url'  => $this->url()->makeUrl('mail.download-export', ['url' => $attachment['url']])
                            ];
                        }
                        $messages[$mesKey]['attachments'] = $attachmentsImport;
                    }
                }
                $conversationImport['messages'] = $messages;

                // import conversation to Chat Plus Job
                Phpfox::getService('chatplus.job')->addJob('onImportConversation', $conversationImport);
            }
            $this->url()->send('admincp.mail.export-data-chat-plus', [], _p('export_data_from_messages_successfully'));
        }
        $this->template()->setTitle(_p('export_data_to_chat_plus'))
            ->setBreadCrumb(_p('apps'), $this->url()->makeUrl('admincp.apps'))
            ->setBreadCrumb(_p('mail_app_title'), $this->url()->makeUrl('admincp.mail'))
            ->setBreadCrumb(_p('export_data_to_chat_plus'))
            ->assign([
                'bImported' => $bImported
            ]);
    }
}