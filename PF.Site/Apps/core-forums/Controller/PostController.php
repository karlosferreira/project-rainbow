<?php
/**
 * [PHPFOX_HEADER]
 */
namespace Apps\Core_Forums\Controller;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');


class PostController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $aCallback = false;
        if ($this->request()->get('module')) {
            $this->template()->assign(array('bIsGroup' => '1'));
        }

        $sModule = $this->request()->get('module');
        $iItemId = $this->request()->getInt('item');

        if ($sModule
            && Phpfox::isModule($sModule)
            && $iItemId
            && Phpfox::hasCallback($sModule, 'addForum')
        ) {
            $aCallback = Phpfox::callback($sModule . '.addForum', $iItemId);
            if ($aCallback === false) {
                return Phpfox_Error::display(_p('Cannot find the parent item.'));
            }

            $this->template()->setBreadCrumb($aCallback['breadcrumb_title'], $aCallback['breadcrumb_home']);
            $this->template()->setBreadCrumb($aCallback['title'], $aCallback['url_home']);
            $this->template()->setBreadCrumb(_p('discussions'), $aCallback['url_home'] . 'forum/');
        } else {
            if ($sModule && $iItemId && $aCallback === false) {
                return Phpfox_Error::display(_p('Cannot find the parent item.'));
            }
            $this->template()->setBreadCrumb(_p('forum'), $this->url()->makeUrl('forum'));
        }

        $iId = $this->request()->getInt('id');
        $aAccess = Phpfox::getService('forum')->getUserGroupAccess($iId, Phpfox::getUserBy('user_group_id'));
        if ($aAccess['can_view_thread_content']['value'] != true) {
            return Phpfox_Error::display(_p('unable_to_view_this_item_due_to_privacy_settings'));
        }

        if (Phpfox::isModule('poll')) {
            $this->template()->setHeader('cache', array(
                    '<script type="text/javascript">$Behavior.loadSortableAnswers = function() {$(".sortable").sortable({placeholder: "placeholder", axis: "y"});}</script>'
                )
            );
        }

        $this->template()->setEditor()
            ->setTitle(_p('forum'))
            ->setHeader('cache', array(
                    'switch_legend.js' => 'static_script',
                    'switch_menu.js' => 'static_script'
                )
            );

        $bIsEdit = false;


        if ($this->request()->get('req3') == 'thread') {
            if ($iEditId = $this->request()->getInt('edit')) {
                $aThread = Phpfox::getService('forum.thread')->getForEdit($iEditId);

                if (!isset($aThread['thread_id'])) {
                    return Phpfox_Error::display(_p('not_a_valid_thread'));
                }

                if ((Phpfox::getUserParam('forum.can_edit_own_post') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_edit_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'],
                        'edit_post') || Phpfox::getService('forum.thread')->isAdminOfParentItem($aThread['thread_id'])
                ) {
                    $bIsEdit = true;
                    $iId = $aThread['forum_id'];

                    if (Phpfox::isModule('tag')) {
                        $aThread['tag_list'] = Phpfox::getService('tag')->getForEdit('forum', $aThread['thread_id']);
                    }

                    $this->template()->assign(array(
                            'aForms' => $aThread,
                            'iEditId' => $aThread['thread_id']
                        )
                    );
                } else {
                    return Phpfox_Error::display(_p('insufficient_permission_to_edit_this_thread'));
                }
            }

            if ($aCallback === false) {
                $aForum = Phpfox::getService('forum')
                    ->id($iId)
                    ->getForum();

                if (!isset($aForum['forum_id'])) {
                    return Phpfox_Error::display(_p('not_a_valid_forum'));
                }

                if ($aForum['is_closed']) {
                    return Phpfox_Error::display(_p('forum_is_closed'));
                }
            } else {
                $aForum = [];
            }

            if (!$bIsEdit) {
                if ($sModule && $iItemId && Phpfox::hasCallback($sModule,
                        'checkPermission') && !Phpfox::callback($sModule . '.checkPermission', $iItemId,
                        'forum.share_forum')
                ) {
                    return Phpfox_Error::display(_p('unable_to_view_this_item_due_to_privacy_settings'));
                }
                $bPass = false;
                if (Phpfox::getUserParam('forum.can_add_new_thread') || (isset($aForum['forum_id']) && Phpfox::getService('forum.moderate')->hasAccess($aForum['forum_id'],
                            'add_thread'))
                ) {
                    $bPass = true;
                }

                if ($bPass === false) {
                    return Phpfox_Error::display(_p('insufficient_permission_to_reply_to_this_thread'));
                }
            }

            if (!Phpfox::getService('forum')->hasAccess($iId, 'can_start_thread')) {
                return Phpfox_Error::display(_p('you_are_unable_to_create_a_new_post_in_this_forum_dot'));
            }

            $aValidation = array(
                'title' => _p('provide_a_title_for_your_thread'),
                'text' => _p('provide_some_text')
            );

            if (Phpfox::isModule('captcha') && Phpfox::getUserParam('forum.enable_captcha_on_posting')) {
                $aValidation['image_verification'] = _p('complete_captcha_challenge');
            }

            $oValid = Phpfox_Validator::instance()->set(array(
                    'sFormName' => 'js_form',
                    'aParams' => $aValidation
                )
            );

            $bPosted = false;
            if ($aVals = $this->request()->getArray('val')) {
                if (isset($aVals['type_id']) && $aVals['type_id'] == 'announcement') {
                    $bPosted = true;
                }

                if ($oValid->isValid($aVals)) {
                    if ($bIsEdit) {
                        $aVals['post_id'] = $aThread['start_id'];
                        $aVals['was_announcement'] = $aThread['is_announcement'];
                        $aVals['forum_id'] = $aThread['forum_id'];

                        if (Phpfox::getService('forum.thread.process')->update($aThread['thread_id'],
                            $aThread['user_id'], $aVals)
                        ) {
                            $this->url()->permalink('forum.thread', $aThread['thread_id'],
                                Phpfox::getLib('parse.input')->clean($aVals['title'], 255), true,
                                _p('thread_successfully_updated'));
                        }
                    } else {
                        if (($iFlood = Phpfox::getUserParam('forum.forum_thread_flood_control')) !== 0) {
                            $aFlood = array(
                                'action' => 'last_post', // The SPAM action
                                'params' => array(
                                    'field' => 'time_stamp', // The time stamp field
                                    'table' => Phpfox::getT('forum_thread'), // Database table we plan to check
                                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                                    'time_stamp' => $iFlood * 60 // Seconds);
                                )
                            );

                            // actually check if flooding
                            if (Phpfox::getLib('spam')->check($aFlood)) {
                                Phpfox_Error::set(_p('posting_a_new_thread_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                            }
                        }

                        if (Phpfox_Error::isPassed() && ($iId = Phpfox::getService('forum.thread.process')->add($aVals,
                                $aCallback))
                        ) {
                            $this->url()->permalink('forum.thread', $iId,
                                Phpfox::getLib('parse.input')->clean($aVals['title'], 255), true);
                        }
                    }
                }
            }

            if ($aCallback === false) {
                $this->template()->setBreadCrumb($aForum['breadcrumb'])
                    ->setBreadCrumb(_p($aForum['name']),
                        $this->url()->permalink('forum', $aForum['forum_id'], $aForum['name']))
                    ->setBreadCrumb(($bIsEdit ? _p('editing_thread') . ': ' . $aThread['title'] : _p('post_new_thread')),
                        (($bIsEdit) ? $this->url()->makeUrl('forum.post.thread',
                            array('edit' => $iEditId)) : $this->url()->makeUrl('forum.post.thread',
                            array('id' => $iId))), true);
            } else {
                $this->template()
                    ->setBreadCrumb(($bIsEdit ? _p('editing_thread') . ': ' . $aThread['title'] : _p('post_new_thread')),
                        (($bIsEdit) ? $this->url()->makeUrl('forum.post.thread',
                            array('edit' => $iEditId)) : $this->url()->makeUrl('forum.post.thread',
                            array('id' => $iId))), true);
            }

            $sForumParents = '';
            if ($aCallback === false) {
                if (Phpfox::getUserParam('forum.can_post_announcement')) {
                    $sForumParents = Phpfox::getService('forum')->active($aForum['forum_id'])->getJumpTool($aForum['forum_id'], true);
                } elseif (Phpfox::getService('forum.moderate')->hasAccess($aForum['forum_id'], 'post_announcement')) {
                    $sForumParents = Phpfox::getService('forum')->active($aForum['forum_id'])->getModifiedJumpTool($aForum['forum_id'], true);
                }
            }

            $this->template()
                ->setPhrase(array(
                        'you_have_reached_your_limit',
                        'answer',
                        'you_must_have_a_minimum_of_total_answers',
                        'are_you_sure',
                        'notice'
                    )
                )
                ->assign(array(
                        'iForumId' => $iId,
                        'iActualForumId' => $iId,
                        'sFormLink' => ($aCallback == false ? $this->url()->makeUrl('forum.post.thread',
                            array('id' => $iId)) : $this->url()->makeUrl('forum.post.thread',
                            array('module' => $sModule, 'item' => $iItemId))),
                        'sCreateJs' => $oValid->createJS(),
                        'sGetJsForm' => $oValid->getJsForm(),
                        'sForumParents' => $sForumParents,
                        'bPosted' => $bPosted,
                        'sReturnLink' => ($bIsEdit ? ($aCallback === false ? $this->url()->makeUrl('forum', array(
                            $aForum['name_url'] . '-' . $aForum['forum_id'],
                            $aThread['title_url']
                        )) : $this->url()->makeUrl($aCallback['url_home'] . '.forum', $aThread['title_url'])) : ''),
                        'bIsEdit' => $bIsEdit,
                        'aCallback' => $aCallback
                    )
                );

            if (Phpfox::getUserParam('forum.can_add_forum_attachments')) {
                $this->setParam('attachment_share', array(
                        'type' => 'forum',
                        'id' => 'js_forum_form',
                        'edit_id' => isset($aThread) && isset($aThread['start_id']) ? $aThread['start_id'] : 0
                    )
                );
            }
        } else {
            if ($iEditId = $this->request()->getInt('edit')) {
                $aPost = Phpfox::getService('forum.post')->getForEdit($iEditId);
                $aPost['count'] = Phpfox::getService('forum.post')->getPostCount($aPost['thread_id'], $aPost['post_id']) - 1;
                if (!isset($aPost['post_id'])) {
                    return Phpfox_Error::display(_p('not_a_valid_post'));
                }

                $bCanEditPost = (Phpfox::getUserParam('forum.can_edit_own_post') && $aPost['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_edit_other_posts') || Phpfox::getService('forum.moderate')->hasAccess($aPost['forum_id'],
                        'edit_post');
                if ($bCanEditPost) {
                    $bIsEdit = true;
                    $iId = $aPost['thread_id'];

                    $this->template()->assign(array(
                            'aForms' => $aPost,
                            'iEditId' => $aPost['post_id']
                        )
                    );

                    if (PHPFOX_IS_AJAX) {
                        Phpfox_Ajax::instance()->setTitle(_p('editing_post') . ': ' . (empty($aPost['title']) ? '#' . $aPost['count'] : Phpfox::getLib('parse.output')->shorten($aPost['title'],
                                80, '...')));
                    }
                } else {
                    return Phpfox_Error::display(_p('insufficient_permission_to_edit_this_thread'));
                }
            }

            if (($iQuoteId = $this->request()->getInt('quote')) && ($aQuotePost = Phpfox::getService('forum.post')->getForEdit($iQuoteId))) {
                Phpfox_Ajax::instance()->setTitle(_p('replying_to_a_post_by_full_name', array(
                    'full_name' => Phpfox::getLib('parse.output')->shorten($aQuotePost['full_name'], 80, '...')
                )));
            }

            $aThread = Phpfox::getService('forum.thread')->getActualThread($iId, $aCallback);

            if (!isset($aThread['thread_id'])) {
                return Phpfox_Error::display(_p('not_a_valid_thread'));
            }

            if ($aThread['is_closed'] && ((isset($bCanEditPost) && !$bCanEditPost) || !isset($bCanEditPost))) {
                return Phpfox_Error::display(_p('thread_is_closed'));
            }

            if ($aCallback === false && $aThread['forum_is_closed']) {
                return Phpfox_Error::display(_p('forum_is_closed'));
            }


            if (!$iEditId && $aThread['is_announcement']) {
                return Phpfox_Error::display(_p('thread_is_an_announcement_not_allowed_to_leave_a_reply'));
            }

            if (!$bIsEdit) {
                $bPass = false;
                if ((Phpfox::getUserParam('forum.can_reply_to_own_thread') && $aThread['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('forum.can_reply_on_other_threads') || Phpfox::getService('forum.moderate')->hasAccess($aThread['forum_id'],
                        'can_reply')
                ) {
                    $bPass = true;
                }

                if ($bPass === false) {
                    return Phpfox_Error::display(_p('insufficient_permission_to_reply_to_this_thread'));
                }
            }

            $sExtraText = '';

            if ($sSavedText = $this->request()->get('save_text')) {
                $sExtraText .= Phpfox::getLib('parse.output')->clean($sSavedText);
            }

            if ((($iQuote = $this->request()->getInt('quote')) || (($sCookie = Phpfox::getCookie('forum_quote')) && !empty($sCookie)))) {
                $sCookie = Phpfox::getCookie('forum_quote');
                if (!empty($sCookie)) {
                    $iQuote = $sCookie . $iQuote;
                }

                $sExtraText .= Phpfox::getService('forum.post')->getQuotes($aThread['thread_id'], $iQuote);
            }

            $aSubForms = array();
            if (isset($aThread['is_subscribed'])) {
                $aSubForms['is_subscribed'] = $aThread['is_subscribed'];
            }

            if (!empty($sExtraText)) {
                $aSubForms['text'] = $sExtraText;
            }

            if (isset($bCanEditPost) && $bCanEditPost) {
                $aSubForms = array_merge($aSubForms, $aPost);
            }

            $this->template()->assign('aForms', $aSubForms);

            $aValidation = array(
                'text' => _p('provide_some_text')
            );

            if (Phpfox::isModule('captcha') && Phpfox::getUserParam('forum.enable_captcha_on_posting')) {
                $aValidation['image_verification'] = _p('complete_captcha_challenge');
            }

            $oValid = Phpfox_Validator::instance()->set(array(
                    'sFormName' => 'js_form',
                    'aParams' => $aValidation
                )
            );

            $aForum = Phpfox::getService('forum')
                ->id($aThread['forum_id'])
                ->getForum();


            if ($aVals = $this->request()->getArray('val')) {
                $aVals['forum_id'] = $aThread['forum_id'];

                if ($oValid->isValid($aVals)) {

                    Phpfox::setCookie('forum_quote', '', -1);

                    if ($bIsEdit) {
                        if (Phpfox::getService('forum.post.process')->update($aPost['post_id'], $aPost['user_id'],
                            $aVals)
                        ) {
                            $this->url()->permalink('forum', $aThread['thread_id'], $aThread['title'], true, null,
                                array('post' => $aPost['post_id']));
                        }
                    } else {
                        if (($iFlood = Phpfox::getUserParam('forum.forum_post_flood_control')) !== 0) {
                            $aFlood = array(
                                'action' => 'last_post', // The SPAM action
                                'params' => array(
                                    'field' => 'time_stamp', // The time stamp field
                                    'table' => Phpfox::getT('forum_post'), // Database table we plan to check
                                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                                    'time_stamp' => $iFlood * 60 // Seconds);
                                )
                            );

                            // actually check if flooding
                            if (Phpfox::getLib('spam')->check($aFlood)) {
                                Phpfox_Error::set(_p('posting_a_reply_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                            }
                        }

                        if (Phpfox_Error::isPassed()) {
                            if (($iId = Phpfox::getService('forum.post.process')->add($aVals, $aCallback))) {
                                $this->url()->permalink('forum.thread', $aThread['thread_id'], $aThread['title'], true,
                                    null, array('post' => $iId));
                            } else {

                                if (Phpfox::getUserParam('forum.approve_forum_post')) {
                                    $this->url()->permalink('forum', $aThread['thread_id'], $aThread['title'], true,
                                        _p('your_post_has_successfully_been_added_however_it_is_pending_an_admins_approval_before_it_can_be_displayed_publicly'),
                                        array('post' => $iId));
                                }
                            }
                        }
                    }
                }
            }

            if ($aCallback === false) {
                $this->template()->setBreadCrumb($aForum['breadcrumb'])
                    ->setBreadCrumb(_p($aForum['name']),
                        $this->url()->makeUrl('forum', $aForum['name_url'] . '-' . $aForum['forum_id']));
            }

            $this->template()
                ->setBreadCrumb($aThread['title'], ($aCallback === false ? $this->url()->makeUrl('forum', array(
                    $aForum['name_url'] . '-' . $aForum['forum_id'],
                    $aThread['title_url']
                )) : $this->url()->makeUrl($aCallback['url_home'] . '.forum', $aThread['title_url'])))
                ->setBreadCrumb(($bIsEdit ? _p('editing_post') . ': ' . (empty($aPost['title']) ? '#' . $aPost['post_id'] : $aPost['title']) : _p('post_new_reply')),
                    ($bIsEdit ? ($aCallback === false ? $this->url()->makeUrl('forum', array(
                        $aThread['forum_url'] . '-' . $aThread['forum_id'],
                        $aThread['title_url'],
                        'post_' . $aPost['post_id']
                    )) : $this->url()->makeUrl($aCallback['url_home'] . '.forum',
                        array($aThread['title_url'], 'post' => $aPost['post_id']))) : null), true)
                ->assign(array(
                        'iThreadId' => $iId,
                        'iActualForumId' => $aForum['forum_id'],
                        'sFormLink' => ($aCallback === false ? $this->url()->makeUrl('forum.post.reply',
                            array('id' => $iId)) : $this->url()->makeUrl('forum.post.reply',
                            array('id' => $iId, 'module' => $sModule, 'item' => $iItemId))),
                        'sCreateJs' => $oValid->createJS(),
                        'sGetJsForm' => $oValid->getJsForm((PHPFOX_IS_AJAX ? false : true)),
                        'sReturnLink' => ($bIsEdit ? ($aCallback === false ? $this->url()->makeUrl('forum', array(
                            $aThread['forum_url'] . '-' . $aThread['forum_id'],
                            $aThread['title_url'],
                            'post_' . $aPost['post_id']
                        )) : $this->url()->makeUrl($aCallback['url_home'] . '.forum', $aThread['title_url'])) : ''),
                        'sThreadReturnLink' => ($aCallback === false ? $this->url()->makeUrl('forum', array(
                            $aThread['forum_url'] . '-' . $aThread['forum_id'],
                            $aThread['title_url']
                        )) : $this->url()->makeUrl($aCallback['url_home'], array('forum', $aThread['title_url']))),
                        'iTotalPosts' => $aThread['total_post'],
                        'bIsEdit' => $bIsEdit,
                        'aCallback' => $aCallback
                    )
                );

            if (Phpfox::getUserParam('forum.can_add_forum_attachments')) {
                $this->setParam('attachment_share', array(
                        'type' => 'forum',
                        'inline' => PHPFOX_IS_AJAX,
                        'id' => 'js_forum_form',
                        'edit_id' => ($bIsEdit ? $aPost['post_id'] : '')
                    )
                );
            }
        }

        if (!empty($aThread)) {
            $this->template()->buildPageMenu('js_forums_post_block', [], [
                'link' => Phpfox::permalink('forum.thread', $aThread['thread_id'], $aThread['title']),
                'phrase' => _p('view_thread')
            ]);
        }

        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('forum.component_controller_post_clean')) ? eval($sPlugin) : false);
    }
}
