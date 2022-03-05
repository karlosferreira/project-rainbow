<?php

namespace Apps\Core_Pages\Controller;

use Core\Event;
use Phpfox;
use Phpfox_Error;
use Phpfox_Module;
use Phpfox_Plugin;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');
define('PHPFOX_IS_PAGES_VIEW', true);
define('PHPFOX_PAGES_ITEM_TYPE', 'pages');

class ViewController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::getUserParam('pages.can_view_browse_pages', true);

        $mId = $this->request()->getInt('req2');
        defined('PHPFOX_PAGES_ITEM_ID') or define('PHPFOX_PAGES_ITEM_ID', $mId);

        if (request()->segment(2) == 'add') {
            if (!defined('IS_PAGES_MODULE')) {
                define('IS_PAGES_MODULE', true);
            }

            return Phpfox_Module::instance()->setController('pages.add');
        }

        if (!($aPage = Phpfox::getService('pages')->getForView($mId))) {
            return Phpfox_Error::display(_p('the_page_you_are_looking_for_cannot_be_found'));
        }

        $sCurrentModule = Phpfox_Url::instance()->reverseRewrite($this->request()->get((($this->request()->get('req1') == 'pages') ? 'req3' : 'req2')));
        if ($sCurrentModule != '') {
            $this->template()->assign([
                'bRefreshPhoto' => true,
            ]);
        }

        if ($aPage['view_id'] != '0' && !(Phpfox::getUserParam('pages.can_approve_pages') || $aPage['is_admin'])) {
            return Phpfox_Error::display(_p('the_page_you_are_looking_for_cannot_be_found'));
        }

        if ($aPage['view_id'] == '2') {
            return Phpfox_Error::display(_p('the_page_you_are_looking_for_cannot_be_found'));
        }

        if (!Phpfox::getService('pages')->isMember($aPage['page_id']) && Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy')) {
            Phpfox::getService('privacy')->check('pages', $aPage['page_id'], $aPage['user_id'], $aPage['privacy'],
                (isset($aPage['is_friend']) ? $aPage['is_friend'] : 0), null, true);
        }

        $bCanViewPage = true;

        Phpfox::getService('pages')->buildWidgets($aPage['page_id']);

        (($sPlugin = Phpfox_Plugin::get('pages.component_controller_view_build')) ? eval($sPlugin) : false);

        $this->setParam([
            'aParentModule' => array(
                'module_id' => 'pages',
                'item_id' => $aPage['page_id'],
                'url' => Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url'])
            ),
            'allowTagFriends' => false
        ]);

        if (isset($aPage['is_admin']) && $aPage['is_admin']) {
            define('PHPFOX_IS_PAGE_ADMIN', true);
        }

        $sModule = $sCurrentModule;

        if (empty($sModule) && !empty($aPage['landing_page'])) {
            $sModule = $sCurrentModule = ($aPage['landing_page'] == 'video' ? 'v' : $aPage['landing_page']);
        }

        Phpfox::getService('pages')->getActionsPermission($aPage);
        (($sPlugin = Phpfox_Plugin::get('pages.component_controller_view_assign')) ? eval($sPlugin) : false);

        $this->setParam([
            'aPage' => $aPage,
            'aCallback' => array_merge($aPage, [
                'module_id' => 'pages',
                'item_id' => $aPage['page_id'],
                'url_home' => Phpfox::getService('pages')->getUrl($aPage['page_id'], $aPage['title'], $aPage['vanity_url'])
            ])
        ]);


        // Get Image
        if (!empty($aPage['pages_image_path'])) {
            $photoUrl = Phpfox::getLib('image.helper')->display([
                'server_id' => $aPage['image_server_id'],
                'path' => 'pages.url_image',
                'file' => $aPage['pages_image_path'],
                'suffix' => '',
                'return_url' => true
            ]);
            $this->template()
                ->setMeta('og:image', $photoUrl)
                ->setMeta('og:image:width', 1000)
                ->setMeta('og:image:height', 600);
        }
        else {
            $this->template()->setMeta('og:image', '');
        }

        $total_likes = $aPage['total_like'];
        if ($total_likes != 1) {
            $total_likes = _p('pages_total_likes', ['total' => $total_likes]);
        }
        else {
            $total_likes = _p('pages_total_like', ['total' => 1]);
        }

        if (isset($aPage['text']) && !empty($aPage['text'])) {
            $this->template()->setMeta('description', $aPage['title'] . '. ' . $total_likes . '. ' . Phpfox::getLib('parse.output')->feedStrip($aPage['text']));
        }
        else {
            $this->template()->setMeta('description', $aPage['title'] . '. ' . $total_likes . '. ' . _p('seo_pages_meta_description'));
        }

        $this->template()
            ->setHeader('cache', [
                'jquery.cropit.js'                 => 'module_user',
            ])
            ->setPhrase(['close_without_save_your_changes'])
            ->assign(array(
                    'aPage' => $aPage,
                    'sCurrentModule' => $sCurrentModule,
                    'bCanViewPage' => $bCanViewPage,
                    'iViewCommentId' => $this->request()->getInt('comment-id'),
                    'bHasPermToViewPageFeed' => Phpfox::getService('pages')->hasPerm($aPage['page_id'],
                        'pages.view_browse_updates'),
                    'bIsMessageActive' => Phpfox::isModule('mail')
                )
            )->setMeta([
                'keywords' => _p('seo_pages_meta_keywords')
            ]);

        if (setting('core.google_api_key') != '') {
            $this->template()->setHeader(array(
                '<script type="text/javascript">oParams["core.google_api_key"] = "' . setting('core.google_api_key') . '";</script>'
            ));

            if (((int)$aPage['location_latitude'] != 0 || (int)$aPage['location_longitude'] != 0)) {
                $this->template()->assign([
                    'sLat' => $aPage['location_latitude'],
                    'sLng' => $aPage['location_longitude'],
                    'sLocationName' => $aPage['location_name']
                ]);
            }
        }

        if ($bCanViewPage && $sCurrentModule == 'members') {
            Phpfox::getComponent('pages.members', ['bNoTemplate' => true], 'controller');
            Phpfox_Module::instance()->resetBlocks('pages.members');
            $this->template()->setTitle(_p('members') . ' &raquo; ' . $aPage['title'], true);
        } elseif ($bCanViewPage && $sModule && Phpfox::isModule($sModule)
            && Phpfox::hasCallback($sModule, 'getPageSubMenu') && !$this->request()->getInt('comment-id')) {
            if ((Phpfox::hasCallback($sModule, 'canViewPageSection') && !Phpfox::callback($sModule . '.canViewPageSection', $aPage['page_id']))
                || !Phpfox::getService('pages')->isActiveIntegration($sModule)) {
                return Phpfox_Error::display(_p('unable_to_view_this_section_due_to_privacy_settings'));
            }

            $this->template()->assign('bIsPagesViewSection', true);
            $this->setParam('bIsPagesViewSection', true);
            $this->setParam('sCurrentPageModule', $sModule);

            Phpfox::getComponent($sModule . '.index', array('bNoTemplate' => true), 'controller');

            Phpfox_Module::instance()->resetBlocks($sModule . '.index');
        } elseif ($bCanViewPage
            && !Phpfox::getService('pages')->isWidget($sModule)
            && !$this->request()->getInt('comment-id')
            && $sModule
            && Phpfox::isAppAlias($sModule)
        ) {

            if (Phpfox::hasCallback($sModule,
                    'canViewPageSection') && !Phpfox::callback($sModule . '.canViewPageSection', $aPage['page_id'])
            ) {
                return Phpfox_Error::display(_p('unable_to_view_this_section_due_to_privacy_settings'));
            }
            $app_content = Event::trigger('pages_view_' . $sModule);

            Phpfox_Module::instance()->resetBlocks();

            event('lib_module_page_id', function ($obj) use ($sModule) {
                $obj->id = 'pages_' . $sModule;
            });

            $this->template()->assign([
                'app_content' => $app_content
            ]);

        } elseif ($bCanViewPage && $sModule && Phpfox::getService('pages')->isWidget($sModule) && !$this->request()->getInt('comment-id')) {
            define('PHPFOX_IS_PAGES_WIDGET', true);
            $aWidget = Phpfox::getService('pages')->getWidget($sModule);
            $aWidget['title'] = Phpfox::getLib('parse.output')->clean($aWidget['title']);
            $this->template()->setTitle($aWidget['title'] . ' &raquo; ' . $aPage['title'])
                ->setBreadCrumb($aWidget['title'])
                ->assign(array(
                    'aWidget' => Phpfox::getService('pages')->getWidget($sModule)
                )
            );
        } else {
            $bCanPostComment = true;
            if ($sCurrentModule == 'pending') {
                $this->setParam('global_moderation', array(
                        'name' => 'pages',
                        'ajax' => 'pages.moderation',
                        'menu' => array(
                            array(
                                'phrase' => _p('delete'),
                                'action' => 'delete'
                            ),
                            array(
                                'phrase' => _p('approve'),
                                'action' => 'approve'
                            )
                        )
                    )
                );
            }

            if (Phpfox::getService('pages')->isAdmin($aPage)) {
                define('PHPFOX_FEED_CAN_DELETE', true);
            }

            if (Phpfox::getUserId()) {
                $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aPage['user_id'], Phpfox::getUserId());
                if ($bIsBlocked) {
                    $bCanPostComment = false;
                }
            }

            if ($sCurrentModule != 'info') {
                define('PHPFOX_IS_PAGES_IS_INDEX', true);
            }

            $this->setParam('aFeedCallback', array(
                    'module' => 'pages',
                    'table_prefix' => 'pages_',
                    'ajax_request' => 'pages.addFeedComment',
                    'item_id' => $aPage['page_id'],
                    'disable_share' => ($bCanPostComment ? false : true),
                    'feed_comment' => 'pages_comment'
                )
            );
            if (isset($aPage['text']) && !empty($aPage['text'])) {
                $this->template()->setMeta('description', $aPage['text']);
            }
            $this->template()->setTitle($aPage['title'])
                ->setEditor()
                ->setHeader('cache', array(
                        'jquery/plugin/jquery.highlightFade.js' => 'static_script',
                        'jquery/plugin/jquery.scrollTo.js' => 'static_script'
                    )
                );

            if ($sModule == 'info') {
                $this->template()->setTitle(_p('info') . ' &raquo; ' . $aPage['title'], true);
            }

            if (in_array($sModule, ['', 'wall', 'home'])) {
                Phpfox_Module::instance()->appendPageClass('_is_pages_feed');
            }
        }

        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('pages.component_controller_view_clean')) ? eval($sPlugin) : false);
    }
}
