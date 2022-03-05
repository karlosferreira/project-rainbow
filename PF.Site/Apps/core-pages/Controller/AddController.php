<?php

namespace Apps\Core_Pages\Controller;

use Phpfox;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');
define('PHPFOX_IS_PAGES_ADD', true);

class AddController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::isUser(true);
        if (!($iEditId = $this->request()->getInt('id', false))) {
            Phpfox::getService('pages')->canUserCreateNewPage();
        }

        $pageService = Phpfox::getService('pages');
        $pageService->setIsInPage();
        $aDetailErrorsMessages = [_p('page_name_is_empty')];
        $bIsEdit = false;
        $bIsNewPage = false;
        $sStep = $this->request()->get('req3');
        $iEditId && $aPage = $pageService->getForEdit($iEditId);
        // cannot edit page
        if (!\Phpfox_Error::isPassed()) {
            return false;
        }

        if ($iEditId && !empty($aPage)) {
            $bIsEdit = true;

            $aMenus = [
                'detail' => _p('details'),
                'info'   => _p('info')
            ];

            $aMenus['photo'] = _p('photo');
            $aMenus['permissions'] = _p('permissions');
            if (Phpfox::isModule('friend') && Phpfox::getUserBy('profile_page_id') == 0) {
                $aMenus['invite'] = _p('invite');
            }
            if (!$bIsNewPage) {
                $aMenus['url'] = _p('url');
                $aMenus['admins'] = _p('admins');
                $aMenus['widget'] = _p('widgets');
                $aMenus['menu'] = _p('menus');
            }

            if (Phpfox::getParam('core.google_api_key')) {
                $aMenus['location'] = _p('location');
            }

            if ($bIsNewPage) {
                $iCnt = 0;
                foreach ($aMenus as $sMenuName => $sMenuValue) {
                    $iCnt++;
                    $aMenus[$sMenuName] = _p('step_count', ['count' => $iCnt]) . ': ' . $sMenuValue;
                }
            }

            $pageUrl = $pageService->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url']);
            $this->template()->buildPageMenu('js_pages_block',
                $aMenus,
                [
                    'link'   => $pageUrl,
                    'phrase' => ($bIsNewPage ? _p('skip_view_this_page') : _p('view_this_page'))
                ]
            );

            if (($subTab = $this->request()->get('sub_tab'))
                && in_array($subTab, array_keys($aMenus))
                && !empty($pageActiveTab = $this->template()->getVar('sActiveTab'))
                && in_array($pageActiveTab, array_keys($aMenus))) {
                $this->template()->assign('sActiveTab', $subTab);
            }

            if (($aVals = $this->request()->getArray('val'))) {
                if (Phpfox::getService('pages.process')->update($aPage['page_id'], $aVals, $aPage)) {
                    if ($bIsNewPage && $this->request()->getInt('action') == '1') {
                        switch ($sStep) {
                            case 'invite':
                                if (Phpfox::isModule('friend')) {
                                    $this->url()->send('pages.add.url', ['id' => $aPage['page_id'], 'new' => '1']);
                                }
                                break;
                            case 'permissions':
                                $this->url()->send('pages.add.invite', ['id' => $aPage['page_id'], 'new' => '1']);
                                break;
                            case 'photo':
                                $this->url()->send('pages.add.permissions',
                                    ['id' => $aPage['page_id'], 'new' => '1']);
                                break;
                            case 'info':
                                $this->url()->send('pages.add.photo', ['id' => $aPage['page_id'], 'new' => '1']);
                                break;
                            default:
                                $this->url()->send('pages.add.info', ['id' => $aPage['page_id'], 'new' => '1']);
                                break;
                        }
                    }

                    // updated old page
                    $this->url()->send('pages.add', ['id' => $aPage['page_id'], 'tab' => empty($aVals['current_tab']) ? '' : $aVals['current_tab']], _p(defined('PHPFOX_UPLOAD_PAGES_PHOTO_IS_PENDING') ? 'page_successfully_updated_the_profile_photo_will_be_updated_after_approval_process_is_done' : 'page_successfully_updated'));
                } else {
                    \Phpfox_Error::setDisplay(false);
                    foreach (\Phpfox_Error::get() as $sError) {
                        if (in_array($sError, $aDetailErrorsMessages)) {
                            $aDetailErrors[] = $sError;
                        } else {
                            $aPhotoErrors[] = $sError;
                        }
                    }

                    if (isset($aPhotoErrors)) {
                        $this->template()->assign([
                            'sActivePageTab' => 'photo',
                            'aPhotoErrors'   => $aPhotoErrors
                        ]);
                    }
                    if (isset($aDetailErrors)) {
                        $this->template()->assign([
                            'sActivePageTab' => 'detail',
                            'aDetailErrors'  => $aDetailErrors
                        ]);
                    }
                }
            }

            if (Phpfox::isAdmin() && Phpfox::getUserId() != $aPage['user_id']) {
                $aViewer = Phpfox::getService('user')->getUser(Phpfox::getUserId());
                $this->template()->assign([
                    'aViewer' => json_encode([
                        'user_id'    => Phpfox::getUserId(),
                        'full_name'  => $aViewer['full_name'],
                        'user_image' => Phpfox::getLib('image.helper')->display([
                            'user'       => $aViewer,
                            'suffix'     => '_50_square',
                            'max_height' => 32,
                            'max_width'  => 32,
                            'return_url' => true
                        ])
                    ])
                ]);
            }

            if (Phpfox::getParam('core.google_api_key') != '') {
                if (isset($aPage['location_name']) && ((int)$aPage['location_latitude'] != 0 || (int)$aPage['location_longitude'] != 0)) {
                    $aPage['location'] = $aPage['location_name'];
                    $aPage['location_lat'] = $aPage['location_latitude'];
                    $aPage['location_lng'] = $aPage['location_longitude'];
                }
            }

            // build widgets
            $this->template()->assign([
                'aBlockWidgets' => $pageService->getWidgetsOrdering($iEditId),
                'aForms'        => $aPage,
                'aPageMenus'    => $pageService->_getMenu($aPage, $pageUrl, true)
            ]);
        }

        $this->template()->setTitle((($bIsEdit && !empty($aPage)) ? '' . _p('editing_page') . ': ' . $aPage['title'] : _p('add_new_page')))
            ->setBreadCrumb(_p('pages'), $this->url()->makeUrl('pages'))
            ->setBreadCrumb((($bIsEdit && !empty($aPage)) ? '' . _p('editing_page') . ': ' . $aPage['title'] : _p('add_new_page')),
                $this->url()->makeUrl('pages.add', ['id' => $iEditId]), true)
            ->setPhrase([
                'select_a_file_to_upload',
                'add_new_page'
            ])->setHeader([
                'jquery.cropit.js'                 => 'module_user',
                'progress.js'                      => 'static_script',
                'jquery/plugin/jquery.tablednd.js' => 'static_script',
                'drag.js'                          => 'app_core-pages',
            ])
            ->setHeader(['<script type="text/javascript">$Behavior.pagesProgressBarSettings = function(){ if ($Core.exists(\'#js_pages_block_customize_holder\')) { oProgressBar = {holder: \'#js_pages_block_customize_holder\', progress_id: \'#js_progress_bar\', uploader: \'#js_progress_uploader\', add_more: false, max_upload: 1, total: 1, frame_id: \'js_upload_frame\', file_id: \'image\'}; $Core.progressBarInit(); } }</script>'])
            ->setHeader('cache', [
                'invite.js' => 'app_core-pages'
            ])
            ->assign([
                    'aPermissions' => (isset($aPage) && isset($aPage['page_id']) ? $pageService->getPerms($aPage['page_id']) : []),
                    'aTypes'       => Phpfox::getService('pages.type')->get(0, true),
                    'bIsEdit'      => $bIsEdit,
                    'aWidgetEdits' => $pageService->getWidgetsForEdit(),
                    'bIsNewPage'   => $bIsNewPage,
                    'sStep'        => $sStep
                ]
            )->setMeta([
                'keywords'    => _p('seo_pages_meta_keywords'),
                'description' => _p('seo_pages_meta_description')
            ]);
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('pages.component_controller_add_clean')) ? eval($sPlugin) : false);
    }
}
