<?php
group('/im', function () {
    // No host
    route('/no-hosting', function () {
        auth()->isAdmin(true);
        storage()->del('im_no_host');
        storage()->set('im_no_host', 1);
        return url()->send('admincp.app', ['id' => 'PHPfox_IM']);
    });

    // Hosted
    route('/hosted', function () {
        auth()->isAdmin(true);
        storage()->del('im_no_host');
        return url()->send('admincp.app', ['id' => 'PHPfox_IM']);
    });

    // AdminCP
    route('/admincp', function () {
        auth()->isAdmin(true);

        $url = url()->make('/admincp/app', ['id' => 'PHPfox_IM', 'im-reset-cache' => '1']);

        return view('admincp.html', [
            'callback'   => Core\Home::store() . 'pay/im_hosting?auth=' . PHPFOX_LICENSE_ID . ':' . PHPFOX_LICENSE_KEY . '&return_url=' . urlencode($url),
            'package_id' => (defined('PF_IM_PACKAGE_ID') ? PF_IM_PACKAGE_ID : 0),
            'no_hosting' => storage()->get('im_no_host'),
            'status'     => ($status = storage()->get('im_host_status')) ? $status->value : '',
            'expired'    => ($expired = storage()->get('im_host_expired')) ? !!($expired->value) : false
        ]);
    });

    route('/link', function () {
        $link = db()->select('*')->from(':link')->where(['link' => request()->get('url')])->executeRow();
        if (!$link) {
            $link = Phpfox::getService('link')->getLink(request()->get('url'));
            define('PHPFOX_SKIP_FEED', true);
            $link_id = Phpfox::getService('link.process')->add(
                [
                    'link' => [
                        'image'       => $link['default_image'],
                        'url'         => $link['link'],
                        'title'       => $link['title'],
                        'description' => $link['description'],
                        'embed_code'  => $link['embed_code']
                    ]
                ], true);
            $link = db()->select('*')->from(':link')->where(['link_id' => $link_id])->executeRow();
        }

        return [
            'link'       => $link,
            'time_stamp' => request()->get('time_stamp')
        ];
    });

    // IM in popup mode
    // route('/popup', function () {
    //     Core\View::$template = 'blank';

    //     $image = Phpfox_Image_Helper::instance()->display([
    //         'user'   => Phpfox::getUserBy(),
    //         'suffix' => '_120_square'
    //     ]);

    //     $imageUrl = Phpfox_Image_Helper::instance()->display([
    //         'user'       => Phpfox::getUserBy(),
    //         'suffix'     => '_50_square',
    //         'return_url' => true
    //     ]);

    //     $image = htmlspecialchars($image);
    //     $image = str_replace(['<', '>'], ['&lt;', '&gt;'], $image);

    //     $sticky_bar = '<div id="auth-user" data-image-url="' . str_replace("\"", '\'', $imageUrl) . '" data-user-name="' . Phpfox::getUserBy('user_name') . '" data-id="' . Phpfox::getUserId() . '" data-name="' . Phpfox::getUserBy('full_name') . '" data-image="' . $image . '"></div>';

    //     return render('popup.html', [
    //         'sticky_bar' => $sticky_bar
    //     ]);
    // });

    route('/popup', 'im.messages');

    route('/failed', function () {
        h1('Messenger', '#');

        return render('failed.html');
    });

    // Load friends
    route('/friends', function () {
        $iUserIds = array_unique(explode(',', request()->get('threads')));
        $sCond = '';
        foreach ($iUserIds as $iUserId) {
            $iUserId = (int)$iUserId;
            if ($iUserId && Phpfox::getUserId() !== $iUserId) {
                $sCond .= " AND friend.friend_user_id != $iUserId";
            }
        }
        $friends = Phpfox::getService('friend')->get("friend.user_id=" . Phpfox::getUserId() . $sCond,
            'u.full_name ASC', '', request()->get('limit'), false);
        $str = '';
        foreach ($friends as $friend) {
            $imageLink = \Phpfox_Image_Helper::instance()->display([
                'user'    => $friend,
                'suffix'  => '_120_square',
                'no_link' => true
            ]);
            $thread_id = (Phpfox::getUserId() < $friend['friend_user_id']) ? Phpfox::getUserId() . ':' . $friend['friend_user_id'] : $friend['friend_user_id'] . ':' . Phpfox::getUserId();
            $users = (Phpfox::getUserId() < $friend['friend_user_id']) ? Phpfox::getUserId() . ',' . $friend['friend_user_id'] : $friend['friend_user_id'] . ',' . Phpfox::getUserId();

            $str .= '<div class="pf-im-panel" data-thread-id="' . $thread_id . '"><div class="item-outer">'
                . '<div class="pf-im-panel-image"><a class="no_ajax_link" href="' . Phpfox_Url::instance()->makeUrl($friend['user_name']) . '" target="_blank">' . $imageLink . '</a></div><div class="pf-im-panel-content"><span class="__thread-name" data-users="' . $users . '">' . $friend['full_name']
                . '</span><div class="pf-im-panel-preview twa_built"></div></div></div></div>';
        }
        echo $str;
    });

    // Load friends firebase
    route('/friends-firebase', function () {
        $iUserIds = array_unique(explode(',', request()->get('threads')));
        $sCond = '';
        foreach ($iUserIds as $iUserId) {
            $iUserId = (int)$iUserId;
            if ($iUserId && Phpfox::getUserId() !== $iUserId) {
                $sCond .= " AND friend.friend_user_id != $iUserId";
            }
        }
        $friends = Phpfox::getService('friend')->get("friend.user_id=" . Phpfox::getUserId() . $sCond,
            'u.full_name ASC', '', request()->get('limit'), false);
        $str = '';
        foreach ($friends as $friend) {
            $userBanner = Phpfox::getService('ban')->isUserBanned(['user_id' => $friend['friend_user_id']]);
            $imageLink = \Phpfox_Image_Helper::instance()->display([
                'user'    => $friend,
                'suffix'  => '_120_square',
                'no_link' => true
            ]);
            $thread_id = (Phpfox::getUserId() < $friend['friend_user_id']) ? base64_encode(Phpfox::getUserId()) . base64_encode($friend['friend_user_id']) : base64_encode($friend['friend_user_id']) . base64_encode(Phpfox::getUserId());
            $users = (Phpfox::getUserId() < $friend['friend_user_id']) ? Phpfox::getUserId() . ',' . $friend['friend_user_id'] : $friend['friend_user_id'] . ',' . Phpfox::getUserId();

            $str .= '<div class="pf-im-panel" data-thread-id="' . $thread_id . '" data-friend-id="' . $friend['friend_user_id'] . '"' . ($userBanner ? ' data-user-banned="' . $userBanner['is_banned'] . '"' : '') . '><div class="item-outer">'
                . '<div class="pf-im-panel-image"><a class="no_ajax_link" href="' . Phpfox_Url::instance()->makeUrl($friend['user_name']) . '" target="_blank">' . $imageLink . '</a></div><div class="pf-im-panel-content"><span class="__thread-name" data-users="' . $users . '">' . $friend['full_name']
                . '</span><div class="pf-im-panel-preview twa_built"></div></div></div></div>';
        }
        echo $str;
    });

    route('/panel', function () {
        $cache = [];
        $users = request()->get('users');
        foreach (explode(',', $users) as $user) {
            if (empty($user)) {
                continue;
            }
            $cache[$user] = true;
        }

        $threads = [];
        foreach ($cache as $id => $value) {
            $u = (new \Api\User())->get($id);

            // check banned user
            if (!empty($u)) {
                $userBanner = Phpfox::getService('ban')->isUserBanned(['user_id' => $id]);
                if ($userBanner) {
                    $u->is_banned = $userBanner['is_banned'];
                }
                $u->is_friend = Phpfox::getService('friend')->isFriend($id, Phpfox::getUserId()) || setting('pf_im_allow_non_friends');
            }
            $threads[$id] = $u;
        }

        return $threads;
    });

    route('/conversation', function () {
        $user = null;
        $listing = null;

        if (!request()->get('listing_id') && !setting('pf_im_allow_non_friends') && Phpfox::isModule('friend') && !Phpfox::getService('friend')->isFriend(user()->id, request()->get('user_id'))) {
            return [
                'error' => 'not_friends'
            ];
        }

        if (request()->get('listing_id')) {
            $listing = Phpfox::getService('marketplace')->getListing(request()->get('listing_id'));
        }

        return [
            'user'    => (new \Api\User())->get(request()->get('user_id')),
            'listing' => $listing
        ];
    });

    route('/search-friends', function () {
        $friends = Phpfox::getService('friend')->get("u.full_name like '%" . request()->get('search') . "%' AND friend.user_id=" . Phpfox::getUserId(),
            'u.full_name ASC', '', setting('pf_total_conversations', 20), false);
        $str = '';
        foreach ($friends as $friend) {
            $imageLink = \Phpfox_Image_Helper::instance()->display([
                'user'    => $friend,
                'suffix'  => '_120_square',
                'no_link' => true
            ]);

            $str .= '<div class="pf-im-panel" onclick="$Core.composeMessage({user_id: ' . $friend['user_id'] . '});" data-friend-id="' . $friend['user_id'] . '"><div class="item-outer">'
                . '<div class="pf-im-panel-image"><a class="no_ajax_link" href="' . Phpfox_Url::instance()->makeUrl($friend['user_name']) . '" target="_blank">' . $imageLink . '</a></div><div class="pf-im-panel-content"><span class="__thread-name" data-users="">' . $friend['full_name'] . '</span>'
                . '<div class="pf-im-panel-preview"></div></div></div></div>';
        }
        echo $str;
    });

    route('/attachment', function () {
        if ($_FILES['file']['error'] !== UPLOAD_ERR_OK) {
            return;
        }
        $aValid = Phpfox::getService('attachment.type')->getTypes();

        $iMaxSize = null;
        if (Phpfox::getUserParam('attachment.item_max_upload_size') !== 0) {
            $iMaxSize = (Phpfox::getUserParam('attachment.item_max_upload_size') / 1024);
        }

        $oFile = Phpfox_File::instance();
        $aImage = $oFile->load('file', $aValid, $iMaxSize);
        if ($aImage === false) {
            return;
        }

        $bIsImage = in_array($aImage['ext'], Phpfox::getParam('attachment.attachment_valid_images'));

        define('PHPFOX_SKIP_FEED', true);
        $oAttachment = Phpfox::getService('attachment.process');
        $iId = $oAttachment->add([
                'category'  => '',
                'file_name' => $_FILES['file']['name'],
                'extension' => $aImage['ext'],
                'is_image'  => $bIsImage
            ]
        );
        $sFileName = $oFile->upload('file', Phpfox::getParam('core.dir_attachment'), $iId);
        $sFileSize = filesize(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''));

        $oAttachment->update([
            'file_size'   => $sFileSize,
            'destination' => $sFileName,
            'server_id'   => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
        ], $iId);

        if ($bIsImage) {
            $oImage = Phpfox_Image::instance();
            $sThumbnail = Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, '_thumb');
            $sViewImage = Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, '_view');

            $oImage->createThumbnail(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''), $sThumbnail, Phpfox::getParam('attachment.attachment_max_thumbnail'), Phpfox::getParam('attachment.attachment_max_thumbnail'));
            $oImage->createThumbnail(Phpfox::getParam('core.dir_attachment') . sprintf($sFileName, ''), $sViewImage, Phpfox::getParam('attachment.attachment_max_medium'), Phpfox::getParam('attachment.attachment_max_medium'));

            Phpfox::getService('user.space')->update(Phpfox::getUserId(), 'attachment', $sFileSize);
            $sPath = Phpfox::getLib('image.helper')->display(['server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID'), 'path' => 'core.url_attachment', 'file' => $sFileName, 'suffix' => '_view', 'max_width' => 'attachment.attachment_max_medium', 'max_height' => 'attachment.attachment_max_medium', 'return_url' => true]);
        } else {
            $sPath = Phpfox_Url::instance()->makeUrl('im/download', ['url' => sprintf($sFileName, '')]);
        }

        return [
            'id'      => $iId,
            'isImage' => $bIsImage,
            'path'    => $sPath
        ];
    });

    route('/get-attachment', function () {
        $iId = request()->get('id');
        list($iCnt, $aAttachment) = Phpfox::getService('attachment')->get(['attachment_id' => $iId]);
        if (!$iCnt) {
            return false;
        }
        $aAttachment = $aAttachment[0];
        if ($aAttachment['is_image']) {
            $bIsImage = true;
            $sPath = Phpfox::getLib('image.helper')->display(['server_id' => $aAttachment['server_id'], 'path' => 'core.url_attachment', 'file' => $aAttachment['destination'], 'suffix' => '', 'max_width' => 'attachment.attachment_max_medium', 'max_height' => 'attachment.attachment_max_medium', 'return_url' => true]);
            $sThumb = Phpfox::getLib('image.helper')->display(['server_id' => $aAttachment['server_id'], 'path' => 'core.url_attachment', 'file' => $aAttachment['destination'], 'suffix' => '_thumb', 'max_width' => 'attachment.attachment_max_medium', 'max_height' => 'attachment.attachment_max_medium', 'return_url' => true]);
        } else {
            $bIsImage = false;
            $sPath = Phpfox_Url::instance()->makeUrl('im/download', ['url' => $aAttachment['url']]);
            $sThumb = '';
        }
        return [
            'id'        => $iId,
            'is_image'  => $bIsImage,
            'path'      => $sPath,
            'thumb'     => $sThumb,
            'file_name' => $aAttachment['file_name']
        ];
    });

    route('/ban-user', function () {
        $banId = request()->get('ban_id');
        if (!$banId) {
            Phpfox::getService('ban.process')->banUser(Phpfox::getUserId(), 0, 2);
        } else {
            $banItem = db()->select('*')->from(':ban')->where(['ban_id' => $banId])->executeRow();
            if (empty($banItem)) {
                return ['success' => false];
            }
            $userGroupsAffected = unserialize($banItem['user_groups_affected']);
            if (is_array($userGroupsAffected) && !empty($userGroupsAffected) && !in_array(Phpfox::getUserBy('user_group_id'), $userGroupsAffected)) {
                return ['success' => false];
            }
            Phpfox::getService('ban.process')->banUser(Phpfox::getUserId(), $banItem['days_banned'], $banItem['return_user_group'], $banItem['reason'], $banId);
        }
        return ['success' => true];
    });

    route('/get-token', function () {
        $timestamp = \Phpfox_Request::instance()->get('timestamp');
        if (empty($timestamp)) {
            exit;
        }
        // return token
        echo md5($timestamp . setting('pf_im_node_server_key'));
        exit;
    });

    route('/login-admin', function () {
        $email = request()->get('email');
        $password = request()->get('password');
        if (Phpfox::getService('user.auth')->loginAdmin($email, $password)) {
            return [
                'success' => true,
                'message' => _p('all_old_messages_removed_successfully'),
                'label'   => _p('notice')
            ];
        }
        return [
            'success' => false,
            'message' => Phpfox_Error::get()
        ];
    });

    route('/import-chat-plus', function () {
        Phpfox::isAdmin();
        $thread = request()->get('thread');
        if (!empty($thread) && Phpfox::isAppActive('P_ChatPlus')) {
            // import conversation to Chat Plus Job
            $thread = json_decode($thread, true);
            foreach ($thread['messages'] as $key => $message) {
                if (!empty($message['attachment_id'])) {
                    //Get attachment
                    list($cnt, $attachments) = Phpfox::getService('attachment')->get(['attachment_id' => $message['attachment_id']]);
                    if ($cnt && !empty($attachments)) {
                        $attachment = $attachments[0];
                        $thread['messages'][$key]['attachments'] = [
                            [
                                'attachment_id' => $attachment['attachment_id'],
                                'time_stamp'    => $attachment['time_stamp'],
                                'file_name'     => $attachment['file_name'],
                                'file_size'     => $attachment['file_size'],
                                'extension'     => $attachment['extension'],
                                'is_image'      => $attachment['is_image'],
                                'download_url'  => Phpfox::getLib('url')->makeUrl('attachment.download', ['url' => $attachment['url']])
                            ]
                        ];
                    }
                }
            }
            Phpfox::getService('chatplus.job')->addJob('onImportConversation', $thread);
            if (!empty($thread['last_thread'])) {
                Phpfox::addMessage(_p('done_messages_will_be_exported_soon'));
            }
            return [
                'success' => true
            ];
        }
        return [
            'success' => false,
            'message' => _p('opps_something_went_wrong')
        ];
    });


    // download
    route('/download', function () {
        \Phpfox_Module::instance()->dispatch('im.download');
        return 'controller';
    });
});

\Phpfox_Module::instance()->addComponentNames('controller', [
    'im.download'                      => '\Apps\PHPfox_IM\Controller\DownloadController',
    'im.messages'                      => '\Apps\PHPfox_IM\Controller\MessagesController',
    'im.admincp.manage-sound'          => '\Apps\PHPfox_IM\Controller\AdminManageSoundController',
    'im.admincp.import-data-v3'        => '\Apps\PHPfox_IM\Controller\AdminImportDataController',
    'im.admincp.export-data-chat-plus' => '\Apps\PHPfox_IM\Controller\AdminExportDataController',
    'im.admincp.delete-messages'       => '\Apps\PHPfox_IM\Controller\AdminDeleteMessagesController',
])->addTemplateDirs([
    'im' => PHPFOX_DIR_SITE_APPS . 'core-im' . PHPFOX_DS . 'views',
])->addComponentNames('ajax', [
    'im.ajax' => \Apps\PHPfox_IM\Ajax\Ajax::class
])->addAliasNames('im', 'PHPfox_IM');

\Phpfox_Template::instance()->setPhrase([
    "all_chats",
    "messenger",
    "conversations",
    "friends",
    "no_conversations",
    "no_friends_found",
    "send",
    "open_in_new_tab",
    "close_chat_box",
    "this_message_has_been_deleted",
    "messaged_you",
    "unable_to_load_im",
    "hide_thread",
    "search_thread",
    "noti_thread",
    "no_message",
    "search_message",
    "enter_search_text",
    "play",
    "close",
    "loading_conversation",
    "loading_messages",
    "error",
    "deleted_user",
    "invalid_user",
    "you_cannot_reply_this_conversation",
    "uploading",
    "im_failed",
    "add_attachment",
    "im_file",
    "just_now",
    "a_minute_ago",
    "minutes_ago",
    "a_hour_ago",
    "hours_ago",
    "im_load_more",
    "messages",
    "view_all_messages",
    "chat",
    "im_pick_a_contact_from_the_list_and_start_your_conversation",
    "are_you_sure_all_message_will_be_deleted",
    "done_messages_will_be_exported_soon"
]);
