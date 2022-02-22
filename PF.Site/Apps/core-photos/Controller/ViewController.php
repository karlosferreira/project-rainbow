<?php

namespace Apps\Core_Photos\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class ViewController extends Phpfox_Component
{
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_view__1')) ? eval($sPlugin) : false);
        Phpfox::getUserParam('photo.can_view_photos', true);
        define('PHPFOX_SHOW_TAGS', true);
        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_view__2')) ? eval($sPlugin) : false);

        $aCallback = $this->getParam('aCallback');
        $sId = $this->request()->get('req2');
        $sAction = $this->request()->get('action');
        $this->setParam('sTagType', 'photo');
        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_view_process_start')) ? eval($sPlugin) : false);

        // Get the photo
        $aPhoto = Phpfox::getService('photo')->getPhoto($sId);

        $moduleId = $aPhoto['module_id'];
        if (!empty($moduleId) && $moduleId != 'photo') {
            if ($aCallback = Phpfox::callback($moduleId . '.getPhotoDetails', $aPhoto)) {
                $this->template()->setBreadCrumb($aCallback['breadcrumb_title'], $aCallback['breadcrumb_home'])
                    ->assign(['aCallback' => $aCallback]);
                $this->template()->setBreadCrumb($aCallback['title'], $aCallback['url_home']);
                if (Phpfox::isModule($moduleId) && Phpfox::hasCallback($moduleId, 'checkPermission')) {
                    $forcePermission = ($moduleId == 'groups' && Phpfox::getParam('photo.display_photo_album_created_in_group')) || ($moduleId == 'pages' && Phpfox::getParam('photo.display_photo_album_created_in_page'));
                    if (!$forcePermission && !Phpfox::callback($moduleId . '.checkPermission', $aCallback['item_id'], 'photo.view_browse_photos')) {
                        return Phpfox_Error::display(_p('unable_to_view_this_item_due_to_privacy_settings'));
                    }
                }
            }
        }

        // No photo founds lets get out of here
        if (!isset($aPhoto['photo_id']) || ($aPhoto['view_id'] && !Phpfox::getUserParam('photo.can_approve_photos') && $aPhoto['user_id'] != Phpfox::getUserId())) {
            return Phpfox_Error::display(_p('sorry_the_photo_you_are_looking_for_no_longer_exists',
                ['link' => $this->url()->makeUrl('photo')]));
        }

        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $aPhoto['user_id'])) {
            return \Phpfox_Module::instance()->setController('error.invalid');
        }

        if (Phpfox::isModule('privacy')) {
            Phpfox::getService('privacy')->check('photo', $aPhoto['photo_id'], $aPhoto['user_id'], $aPhoto['privacy'],
                $aPhoto['is_friend']);
        }

        if ($aPhoto['mature'] != 0) {
            if (Phpfox::getUserId()) {
                if ($aPhoto['user_id'] != Phpfox::getUserId()) {
                    if ($aPhoto['mature'] == 2 && Phpfox::getUserParam([
                            'photo.photo_mature_age_limit' => [
                                '>',
                                (int)Phpfox::getUserBy('age')
                            ]
                        ])
                    ) {
                        return Phpfox_Error::display(_p('sorry_this_photo_can_only_be_viewed_by_those_older_than_the_age_of_limit',
                            ['limit' => Phpfox::getUserParam('photo.photo_mature_age_limit')]));
                    }
                }
            } else {
                Phpfox::isUser(true);
            }
        }

        $this->setParam('bIsValidImage', true);

        /*
            Don't like that this is here, but if added in the service class it would require an extra JOIN to the user table and its such a waste of a query when we could
            just get the users details vis the cached user array.
        */
        $aPhoto['bookmark_url'] = $this->url()->permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null);

        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_view_process_photo')) ? eval($sPlugin) : false);

        // Assign the photo array so other blocks can use this information
        $this->setParam('aPhoto', $aPhoto);
        define('TAG_ITEM_ID', $aPhoto['photo_id']); // to be used with the cloud block

        // Check if we should set another controller
        if (!empty($sAction)) {
            switch ($sAction) {
                case 'download':
                    return Phpfox::getLib('module')->setController('photo.download');
                    break;
                default:
                    (($sPlugin = Phpfox_Plugin::get('photo.component_controller_view_process_controller')) ? eval($sPlugin) : false);
                    break;
            }
        }
        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$aPhoto['is_viewed']) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('photo', $aPhoto['photo_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('photo', $aPhoto['photo_id']);
                } else {
                    Phpfox::getService('track.process')->update('photo', $aPhoto['photo_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }
        if ($bUpdateCounter) {
            Phpfox::getService('photo.process')->updateCounter($aPhoto['photo_id'], 'total_view');
        }


        // Add photo tags to meta keywords
        if (!empty($aPhoto['tag_list']) && $aPhoto['tag_list'] && Phpfox::isModule('tag')) {
            $this->template()->setMeta('keywords', Phpfox::getService('tag')->getKeywords($aPhoto['tag_list']));
        }

        $sPhotoTitle = Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : _p('photo_photo_number', ['number' => $aPhoto['photo_id']]);

        $this->template()->setTitle($sPhotoTitle);

        $aParamFeed = [
            'comment_type_id' => 'photo',
            'privacy'         => $aPhoto['privacy'],
            'comment_privacy' => Phpfox::getUserParam('photo.can_post_on_photos') ? 0 : 3,
            'like_type_id'    => 'photo',
            'feed_is_liked'   => $aPhoto['is_liked'],
            'feed_is_friend'  => $aPhoto['is_friend'],
            'item_id'         => $aPhoto['photo_id'],
            'user_id'         => $aPhoto['user_id'],
            'total_comment'   => $aPhoto['total_comment'],
            'total_like'      => $aPhoto['total_like'],
            'feed_link'       => $this->url()->permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null),
            'feed_title'      => $sPhotoTitle,
            'feed_display'    => 'view',
            'feed_total_like' => $aPhoto['total_like'],
            'report_module'   => 'photo',
            'report_phrase'   => _p('report_this_photo')
        ];

        if ($aPhoto['view_id'] != 0) {
            $aParamFeed['no_share'] = true;
        }

        //Disable like and comment if non-friend view profile|cover album
        if ($aPhoto['is_profile_photo']) {
            if (!Phpfox::getService('user.privacy')->hasAccess($aPhoto['user_id'], 'feed.share_on_wall')) {
                unset($aParamFeed['comment_type_id']);
                $aParamFeed['disable_like_function'] = true;
            }
        }
        $this->setParam('aFeed', $aParamFeed);

        if ($aPhoto['server_id'] > 0) {
            $sImageUrl = Phpfox::getLib('image.helper')->display([
                    'server_id'  => $aPhoto['server_id'],
                    'path'       => 'photo.url_photo',
                    'file'       => $aPhoto['destination'],
                    'suffix'     => '_1024',
                    'return_url' => true
                ]
            );
            $iCdnMax = (($aPhoto['height'] < $aPhoto['width']) ? 800 : 500);
            list($iNewImageHeight, $iNewImageWidth) = Phpfox::getLib('image.helper')->getNewSize([
                $sImageUrl,
                $aPhoto['width'],
                $aPhoto['height']
            ], $iCdnMax, $iCdnMax);
            $this->template()->assign([
                    'iNewImageHeight' => $iNewImageHeight,
                    'iNewImageWidth'  => $iNewImageWidth
                ]
            );
        }
        //get categories
        $sCategories = '';
        if (isset($aPhoto['categories']) && is_array($aPhoto['categories'])) {
            $sCategories = implode(', ', array_map(function ($aCategory) {
                return strtr('<a href=":link">:text</a>', [
                    ':text' => $aCategory[0],
                    ':link' => $aCategory[1]
                ]);
            }, $aPhoto['categories']));
        }
        $aPhoto['sCategories'] = $sCategories;
        $this->template()->setHeader('cache', [
                'jquery/plugin/imgnotes/jquery.tag.js'           => 'static_script',
                'jquery/plugin/imgnotes/jquery.imgareaselect.js' => 'static_script',
                'jquery/plugin/imgnotes/jquery.imgnotes.js'      => 'static_script',
                'places.js'                                      => 'module_feed'
            ]
        );

        $bLoadCheckin = false;
        if (Phpfox::isModule('feed') && Phpfox::getParam('feed.enable_check_in') && Phpfox::getParam('core.google_api_key')) {
            $this->template()->setHeader('cache', [
                    'places.js' => 'module_feed'
                ]
            );
            $bLoadCheckin = true;
        }

        $iAvatarId = ((Phpfox::isUser()) ? storage()->get('user/avatar/' . Phpfox::getUserId()) : null);
        if ($iAvatarId) {
            $iAvatarId = $iAvatarId->value;
        }
        $iCover = storage()->get('user/cover/' . Phpfox::getUserId());
        if ($iCover) {
            $iCover = $iCover->value;
        }

        $sImageForMeta = Phpfox::getLib('image.helper')->display([
            'server_id'  => $aPhoto['server_id'],
            'path'       => 'photo.url_photo',
            'file'       => $aPhoto['destination'],
            'suffix'     => '_1024',
            'return_url' => true
        ]);

        $aMetaFBTags = [
            'og:title'        => $aPhoto['full_name'] . ' ' . _p('on_site_name', ['site_name' => Phpfox::getParam('core.site_title')]),
            'og:site_name'    => Phpfox::getParam('core.site_title'),
            'og:type'         => 'image.gallery',
            'og:image:type'   => 'image/jpeg',
            'og:image:width'  => $aPhoto['width'],
            'og:image:height' => $aPhoto['height'],
            'og:description'  => $aPhoto['description'],
            'og:image'        => $sImageForMeta
        ];

        $aMetaTwitterTags = [
            'twitter:title'       => $aPhoto['full_name'] . ' ' . _p('on_site_name', ['site_name' => Phpfox::getParam('core.site_title')]),
            'twitter:card'        => 'summary_large_image',
            'twitter:url'         => $this->url()->permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null),
            'twitter:description' => $aPhoto['description'],
            'twitter:image'       => $sImageForMeta
        ];

        $aTitleLabel = [
            'type_id' => 'photo'
        ];

        if ($aPhoto['is_featured']) {
            $aTitleLabel['label']['featured'] = [
                'title'       => '',
                'title_class' => 'flag-style-arrow',
                'label_class' => 'flag_style',
                'icon_class'  => 'diamond'

            ];
        }
        if ($aPhoto['is_sponsor']) {
            $aTitleLabel['label']['sponsored'] = [
                'title'       => '',
                'title_class' => 'flag-style-arrow',
                'label_class' => 'flag_style',
                'icon_class'  => 'sponsor'

            ];
        }
        $aTitleLabel['total_label'] = isset($aTitleLabel['label']) ? count($aTitleLabel['label']) : 0;
        if ($aPhoto['view_id'] == 1) {
            $aTitleLabel['label']['pending'] = [
                'title'       => '',
                'title_class' => 'flag-style-arrow',
                'icon_class'  => 'clock-o'

            ];
            $aPendingItem = [
                'message' => _p('photo_is_pending_approval'),
                'actions' => []
            ];
            if ($aPhoto['canApprove']) {
                $aPendingItem['actions']['approve'] = [
                    'is_ajax' => true,
                    'label'   => _p('approve'),
                    'action'  => '$.ajaxCall(\'photo.approve\', \'id=' . $aPhoto['photo_id'] . '\')'
                ];
            }
            if ($aPhoto['canEdit']) {
                $aPendingItem['actions']['edit'] = [
                    'is_ajax' => true,
                    'label'   => _p('edit'),
                    'action'  => '$Core.box(\'photo.editPhoto\', 700, \'photo_id=' . $aPhoto['photo_id'] . '\'); $(\'#js_tag_photo\').hide()',
                ];
            }
            if ($aPhoto['canDelete']) {
                $sDeleteMessage = _p('are_you_sure_you_want_to_delete_this_photo_permanently');
                if ($iAvatarId == $aPhoto['photo_id']) {
                    $sDeleteMessage = _p('are_you_sure_you_want_to_delete_this_photo_permanently_this_will_delete_your_current_profile_picture_also');
                } else if ($iCover == $aPhoto['photo_id']) {
                    $sDeleteMessage = _p('are_you_sure_you_want_to_delete_this_photo_permanently_this_will_delete_your_current_cover_photo_also');
                }
                $aPendingItem['actions']['delete'] = [
                    'is_ajax' => true,
                    'label'   => _p('delete'),
                    'action'  => '$Core.jsConfirm({message: \'' . $sDeleteMessage . '\'}, function () {$.ajaxCall(\'photo.deletePhoto\', \'id=' . $aPhoto['photo_id'] . '&is_detail=1\');}, function(){})'
                ];
            }

            $this->template()->assign([
                'aPendingItem' => $aPendingItem
            ]);
        }

        $aPhotos = [];
        if (($iFeedId = $this->request()->getInt('feed')) && Phpfox::isModule('feed')) {
            $sFeedTablePrefix = ($aCallback && !empty($aCallback['feed_table_prefix'])) ? $aCallback['feed_table_prefix'] : '';
            $aPhotos = Phpfox::getService('photo')->getFeedPhotos($iFeedId, null, $sFeedTablePrefix);
        }

        if ($iAlbumId = $this->request()->getInt('album')) {
            $aPhotos = Phpfox::getService('photo')->getPhotos($iAlbumId);
        }

        Phpfox::getService('photo')->buildMenu();
        $this->setParam('aForms', $aPhoto);
        $this->setParam('aCallback', $aCallback);

        $this->template()
            ->setBreadCrumb(_p('photos'),
                ($aCallback === null ? $this->url()->makeUrl('photo') : $this->url()->makeUrl($aCallback['url_home_photo'])),
                false)
            ->setBreadCrumb($sPhotoTitle, $this->url()->permalink('photo', $aPhoto['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $aPhoto['title'] : null),
                true)
            ->setMeta('description', _p('full_name_s_photo_from_time_stamp', [
                    'full_name'  => $aPhoto['full_name'],
                    'time_stamp' => Phpfox::getTime(Phpfox::getParam('core.global_update_time'),
                        $aPhoto['time_stamp'])
                ]) . ': ' . (empty($aPhoto['description']) ? $sPhotoTitle : $sPhotoTitle . '.' . $aPhoto['description']))
            ->setMeta('description', Phpfox::getParam('photo.photo_meta_description'))
            ->setMeta('keywords', $this->template()->getKeywords($sPhotoTitle))
            ->setMeta('keywords', Phpfox::getParam('photo.photo_meta_keywords'))
            ->setPhrase([
                    'none_of_your_files_were_uploaded_please_make_sure_you_upload_either_a_jpg_gif_or_png_file',
                    'updating_photo',
                    'save',
                    'cancel',
                    'click_here_to_tag_as_yourself',
                    'done_tagging'
                ]
            )
            ->keepBody(true)
            ->setEditor([
                    'load' => 'simple'
                ]
            )->assign([
                    'aForms'              => $aPhoto,
                    'aCallback'           => $aCallback,
                    'sPhotoJsContent'     => Phpfox::getService('photo.tag')->getJs($aPhoto['photo_id']),
                    'sPhotos'             => json_encode($aPhotos),
                    'iAvatarId'           => $iAvatarId,
                    'iCover'              => $iCover,
                    'sView'               => 'view',
                    'bIsDetail'           => true,
                    'sAddThisShareButton' => '',
                    'sShareDescription'   => str_replace(["\n", "\r", "\r\n"], '', $aPhoto['description']),
                    'bLoadCheckin'        => $bLoadCheckin,
                    'aTitleLabel'         => $aTitleLabel,
                ]
            );

        if (!empty($aPhoto['album_title'])) {
            $sAlbumTitleConvert = Phpfox::getLib('locale')->convert($aPhoto['album_title']);

            if (strpos($sAlbumTitleConvert, $aPhoto['full_name']) === false) {
                $aMetaTwitterTags['twitter:title'] = $aMetaFBTags['og:title'] = _p('album_title_for_meta', ['full_name' => $aPhoto['full_name'], 'album_title' => $aPhoto['album_title']])
                    . ' ' . _p('on_site_name', ['site_name' => Phpfox::getParam('core.site_title')]);
            } else {
                $aMetaTwitterTags['twitter:title'] = $aMetaFBTags['og:title'] = $sAlbumTitleConvert . ' ' . _p('on_site_name', ['site_name' => Phpfox::getParam('core.site_title')]);
            }

            $this->template()->setTitle($sAlbumTitleConvert);
            $this->template()->setMeta('description',
                '' . _p('part_of_the_photo_album') . ': ' . $aPhoto['album_title']);
        }

        $this->template()->clearMeta(['og:title', 'og:site_name'])->setMeta($aMetaFBTags)->setMeta($aMetaTwitterTags);

        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_view_process_end')) ? eval($sPlugin) : false);
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('photo.component_controller_view_clean')) ? eval($sPlugin) : false);
    }
}