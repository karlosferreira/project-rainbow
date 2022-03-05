<?php

namespace Apps\PHPfox_Groups\Controller;

use Core\Event;
use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Module;
use Phpfox_Plugin;
use Phpfox_Url;

defined('PHPFOX') or exit('NO DICE!');
define('PHPFOX_IS_PAGES_VIEW', true);
define('PHPFOX_PAGES_ITEM_TYPE', 'groups');

class ViewController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::getUserParam('groups.pf_group_browse', true);
        (!Phpfox::getService('groups')->isGroupAvaiable() ? Phpfox::getService('groups')->isPage($this->request()->get('req1')) : '');
        $aPage = Phpfox::getService('groups')->getForView($this->request()->get('req2'));
        if (empty($aPage)) {
            return Phpfox_Error::display(_p('The group you are looking for cannot be found.'));
        }

        defined('PHPFOX_PAGES_ITEM_ID') or define('PHPFOX_PAGES_ITEM_ID', $aPage['page_id']);

        $sCurrentModule = Phpfox_Url::instance()->reverseRewrite($this->request()->get((($this->request()->get('req1') == 'groups') ? 'req3' : 'req2')));
        if ($sCurrentModule != '') {
            $this->template()->assign([
                'bRefreshPhoto' => true,
            ]);
        }

        if ($aPage['view_id'] != '0' && !Phpfox::getUserParam('groups.can_approve_groups') && (Phpfox::getUserId() != $aPage['user_id'])) {
            return Phpfox_Error::display(_p('The group you are looking for cannot be found.'));
        }

        if ($aPage['view_id'] == '2') {
            return Phpfox_Error::display(_p('The group you are looking for cannot be found.'));
        }

        $isMember = Phpfox::getService('groups')->isMember($aPage['page_id']);
        if (!$isMember && Phpfox::getUserBy('profile_page_id') <= 0 && Phpfox::isModule('privacy')) {
            Phpfox::getService('privacy')->check('groups', $aPage['page_id'], $aPage['user_id'],
                $aPage['privacy'], (isset($aPage['is_friend']) ? $aPage['is_friend'] : 0), null, true);
        }

        $decodedEmail = !empty($this->request()->get('code')) ? base64_decode($this->request()->get('code')) : '';
        //Check group privacy
        if ($aPage['reg_method'] == 2
            && !$isMember
            && $aPage['user_id'] != Phpfox::getUserId()
            && !Phpfox::isAdmin()
            && !Phpfox::getService('groups')->checkCurrentUserInvited($aPage['page_id'], $decodedEmail)) {
            Phpfox_Url::instance()->send('privacy.invalid');
        }
        $bCanViewPage = true;
        \Phpfox::getService('groups')->buildWidgets($aPage['page_id']);

        (($sPlugin = Phpfox_Plugin::get('groups.component_controller_view_build')) ? eval($sPlugin) : false);

        $this->setParam([
            'aParentModule' => [
                'module_id' => 'groups',
                'item_id' => $aPage['page_id'],
                'url' => \Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url']),
            ],
            'allowTagFriends' => false
        ]);

        if (isset($aPage['is_admin']) && $aPage['is_admin']) {
            defined('PHPFOX_IS_PAGE_ADMIN') or define('PHPFOX_IS_PAGE_ADMIN', true);
        }
        Phpfox::getService('groups')->getActionsPermission($aPage);
        $sModule = $sCurrentModule;

        (($sPlugin = Phpfox_Plugin::get('groups.component_controller_view_assign')) ? eval($sPlugin) : false);

        $this->setParam([
            'aPage' => $aPage,
            'aCallback' => array_merge($aPage, [
                'module_id' => 'groups',
                'item_id' => $aPage['page_id'],
                'url_home' => Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'],
                    $aPage['vanity_url'])
            ])
        ]);

        $this->template()
            ->setHeader('cache', [
                'jquery.cropit.js'                 => 'module_user',
            ])
            ->setPhrase(['close_without_save_your_changes'])
            ->assign([
                    'aPage' => $aPage,
                    'sCurrentModule' => $sCurrentModule,
                    'bCanViewPage' => $bCanViewPage,
                    'iViewCommentId' => $this->request()->getInt('comment-id'),
                    'bHasPermToViewPageFeed' => \Phpfox::getService('groups')->hasPerm($aPage['page_id'],
                        'groups.view_browse_updates'),
                ]
            );

        if ($bCanViewPage && $sCurrentModule == 'members') {
            Phpfox::getComponent('groups.members', ['bNoTemplate' => true], 'controller');
            Phpfox_Module::instance()->resetBlocks('groups.members');
            $this->template()->setTitle(_p('members') . ' &raquo; ' . $aPage['title'], true);
        } elseif ($bCanViewPage
            && $sModule
            && Phpfox::isModule($sModule)
            && Phpfox::hasCallback($sModule, 'getGroupSubMenu')
            && !$this->request()->getInt('comment-id')) {
            if ((Phpfox::hasCallback($sModule, 'canViewGroupSection') && !Phpfox::callback($sModule . '.canViewGroupSection', $aPage['page_id']))
                || !Phpfox::getService('groups')->isActiveIntegration($sModule)) {
                return Phpfox_Error::display(_p('Unable to view this section due to privacy settings.'));
            }

            $this->template()->assign('bIsPagesViewSection', true);
            $this->setParam('bIsPagesViewSection', true);
            $this->setParam('sCurrentPageModule', $sModule);

            Phpfox::getComponent($sModule . '.index', ['bNoTemplate' => true], 'controller');

            Phpfox_Module::instance()->resetBlocks($sModule . '.index');
        } elseif ($bCanViewPage
            && !\Phpfox::getService('groups')->isWidget($sModule)
            && !$this->request()->getInt('comment-id')
            && $sModule
            && Phpfox::isAppAlias($sModule)
        ) {
            if (Phpfox::hasCallback($sModule,
                    'canViewGroupSection') && !Phpfox::callback($sModule . '.canViewGroupSection', $aPage['page_id'])) {
                return Phpfox_Error::display(_p('Unable to view this section due to privacy settings.'));
            }

            $app_content = Event::trigger('groups_view_' . $sModule);

            Phpfox_Module::instance()->resetBlocks();

            event('lib_module_page_id', function ($obj) use ($sModule) {
                $obj->id = 'groups_' . $sModule;
            });

            $this->template()->assign([
                'app_content' => $app_content,
            ]);

        } elseif ($bCanViewPage && $sModule && \Phpfox::getService('groups')->isWidget($sModule) && !$this->request()->getInt('comment-id')) {
            define('PHPFOX_IS_PAGES_WIDGET', true);
            $aWidget = Phpfox::getService('groups')->getWidget($sModule);
            $aWidget['title'] = Phpfox::getLib('parse.output')->clean($aWidget['title']);
            $this->template()->setTitle($aWidget['title'] . ' &raquo; ' . $aPage['title'])
                ->setBreadCrumb($aWidget['title'])
                ->assign([
                        'aWidget' => \Phpfox::getService('groups')->getWidget($sModule),
                    ]
                );
        } else {
            $bCanPostComment = true;
            if ($sCurrentModule == 'pending') {
                $aPendingUsers = Phpfox::getService('groups')->getPendingUsers($aPage['page_id']);
                if (!count($aPendingUsers)) {
                    $this->url()->send(\Phpfox::getService('groups')->getUrl($aPage['page_id'], $aPage['title'],
                        $aPage['vanity_url']));
                }

                $this->template()->assign('aPendingUsers', $aPendingUsers);
                $this->setParam('global_moderation', [
                        'name' => 'groups',
                        'ajax' => 'PHPfox_Groups.moderation',
                        'menu' => [
                            [
                                'phrase' => _p('Approve'),
                                'action' => 'approve',
                            ],
                            [
                                'phrase' => _p('Delete'),
                                'action' => 'delete',
                            ],
                        ],
                    ]
                );
            }

            if (\Phpfox::getService('groups')->isAdmin($aPage)) {
                defined('PHPFOX_FEED_CAN_DELETE') or define('PHPFOX_FEED_CAN_DELETE', true);
            }

            if (Phpfox::getUserId()) {
                $bIsBlocked = Phpfox::getService('user.block')->isBlocked($aPage['user_id'], Phpfox::getUserId());
                if ($bIsBlocked) {
                    $bCanPostComment = false;
                }
            }

            if ($sCurrentModule != 'info') {
                defined('PHPFOX_IS_PAGES_IS_INDEX') or define('PHPFOX_IS_PAGES_IS_INDEX', true);
            }

            $this->setParam('aFeedCallback', [
                    'module' => 'groups',
                    'table_prefix' => 'pages_',
                    'ajax_request' => 'groups.addFeedComment',
                    'item_id' => $aPage['page_id'],
                    'disable_share' => ($bCanPostComment ? false : true),
                    'feed_comment' => 'groups_comment',
                ]
            );

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
            } else {
                $this->template()->setMeta('og:image', '');
            }

            $total_members = $aPage['total_like'];
            if ($total_members != 1) {
                $total_members = _p('groups_total_members', ['total' => $total_members]);
            } else {
                $total_members = _p('groups_total_member', ['total' => 1]);
            }
            if (isset($aPage['text']) && !empty($aPage['text'])) {
                $this->template()->setMeta('description', $aPage['title'] . '. ' . $total_members . '. ' . Phpfox::getLib('parse.output')->feedStrip($aPage['text']));
            } else {
                $this->template()->setMeta('description', $aPage['title'] . '. ' . $total_members . '. ' . _p('seo_groups_meta_description'));
            }

            $this->template()->setTitle($aPage['title'])
                ->setEditor()
                ->setHeader('cache', [
                        'jquery/plugin/jquery.highlightFade.js' => 'static_script',
                        'jquery/plugin/jquery.scrollTo.js' => 'static_script',
                    ]
                )
                ->setMeta([
                    'keywords' => _p('seo_groups_meta_keywords')
                ]);

            if ($sModule == 'info') {
                $this->template()->setTitle(_p('info') . ' &raquo; ' . $aPage['title'], true);
            }

            if (in_array($sModule, ['', 'wall', 'home'])) {
                Phpfox_Module::instance()->appendPageClass('_is_groups_feed');
            }
        }

        (($sPlugin = Phpfox_Plugin::get('groups.component_controller_view_breadcrumbs')) ? eval($sPlugin) : false);

        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('groups.component_controller_view_clean')) ? eval($sPlugin) : false);
    }
}
