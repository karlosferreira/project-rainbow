<?php

namespace Apps\Core_Comments\Ajax;

use Phpfox;
use Phpfox_Ajax;
use Phpfox_Error;
use Phpfox_Pager;
use Phpfox_Plugin;
use Phpfox_Request;
use Phpfox_Template;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Comment_Component_Ajax_Ajax
 */
class Ajax extends Phpfox_Ajax
{
    public function add()
    {
        $aVals = $this->get('val');
        $bPassCaptcha = true;
        if ($aVals['type'] != 'app' && Phpfox::hasCallback($aVals['type'], 'getAjaxCommentVar')) {
            $sVar = Phpfox::callback($aVals['type'] . '.getAjaxCommentVar');
            if ($sVar !== null) {
                Phpfox::getUserParam($sVar, true);
            }
        }

        if (!Phpfox::getUserParam('comment.can_post_comments')) {
            $this->html('#js_comment_process', '');
            $this->call("$('#js_comment_submit').removeAttr('disabled');");
            $this->hide('.js_feed_comment_process_form');
            $this->alert(_p('your_user_group_is_not_allowed_to_add_comments'));

            return false;
        }

        (($sPlugin = Phpfox_Plugin::get('comment.component_ajax_ajax_add_start')) ? eval($sPlugin) : false);

        if ((isset($bNoCaptcha) && isset($bCaptchaFailed)) && $bCaptchaFailed === true) {
            $this->html('#js_comment_process', '');
            $this->call("$('#js_comment_submit').removeAttr('disabled');");
            $this->alert(_p('captcha_failed_please_try_again'));

            return false;
        }

        if ($aVals['type'] == 'profile' && !Phpfox::getService('user.privacy')->hasAccess($aVals['item_id'], 'comment.add_comment')) {
            $this->html('#js_comment_process', '');
            return false;
        }

        if (!Phpfox::getUserParam('comment.can_comment_on_own_profile') && $aVals['type'] == 'profile' && $aVals['item_id'] == Phpfox::getUserId() && empty($aVals['parent_id'])) {
            $this->html('#js_comment_process', '');
            $this->call("$('#js_comment_submit').removeAttr('disabled');");
            $this->alert(_p('you_cannot_write_a_comment_on_your_own_profile'));

            return false;
        }

        if ($aVals['type'] && ($iFlood = Phpfox::getUserParam('comment.comment_post_flood_control')) !== 0) {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp',
                    // The time stamp field
                    'table'      => Phpfox::getT('comment'),
                    // Database table we plan to check
                    'condition'  => 'type_id = \'' . db()->escape($aVals['type']) . '\' AND user_id = ' . Phpfox::getUserId(),
                    // Database WHERE query
                    'time_stamp' => $iFlood * 60
                    // Seconds);
                ]
            ];

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                if (isset($aVals['is_via_feed'])) {
                    $this->call('$(\'#js_feed_comment_form_' . $aVals['is_via_feed'] . '\').find(\'.js_feed_add_comment_button:first\').show();');
                    $this->call('$(\'#js_feed_comment_form_' . $aVals['is_via_feed'] . '\').find(\'.js_feed_comment_process_form:first\').hide();');
                } else {
                    $this->html('#js_comment_process', '');
                    $this->call("$('#js_comment_submit').removeAttr('disabled');");
                }

                $this->alert(_p('posting_a_comment_a_little_too_soon_total_time', ['total_time' => Phpfox::getLib('spam')->getWaitTime()]));

                return false;
            }
        }

        if ((Phpfox::getLib('parse.format')->isEmpty($aVals['text'])
                || (isset($aVals['default_feed_value']) && $aVals['default_feed_value'] == $aVals['text'])) && empty($aVals['photo_id']) && empty($aVals['sticker_id'])) {
            if (isset($aVals['is_via_feed'])) {
                $this->call('$(\'#js_feed_comment_form_' . $aVals['is_via_feed'] . '\').find(\'.js_feed_add_comment_button:first\').show();');
                $this->call('$(\'#js_feed_comment_form_' . $aVals['is_via_feed'] . '\').find(\'.js_feed_comment_process_form:first\').hide();');
            } else {
                $this->html('#js_comment_process', '');
                $this->call("$('#js_comment_submit').removeAttr('disabled');");
            }

            $this->alert(_p('add_some_text_to_your_comment'));
            $this->hide('.js_feed_comment_process_form');

            return false;
        }

        if (Phpfox::isModule('captcha') && !isset($bNoCaptcha) && Phpfox::getUserParam('captcha.captcha_on_comment') && isset($aVals['image_verification']) && !Phpfox::getService('captcha')->checkHash($aVals['image_verification'])) {
            $bPassCaptcha = false;
            $this->call("$('#js_captcha_image').ajaxCall('captcha.reload', 'sId=js_captcha_image&sInput=image_verification');");
            $this->alert(_p('captcha_failed_please_try_again'), _p('error'));
        }

        if ($bPassCaptcha) {
            if (($mId = Phpfox::getService('comment.process')->add($aVals)) === false) {
                $this->html('#js_comment_process', '');
                $this->call("$('#js_comment_submit').removeAttr('disabled');");
                $this->hide('.js_feed_comment_process_form');
                $this->val('.js_app_comment_feed_textarea', '');

                return false;
            }

            $this->hide('#js_captcha_load_for_check');

            // Comment requires moderation
            if (in_array($mId, ['failed_hash_check', 'pending_comment'])) {
                if (isset($aVals['parent_id']) && $aVals['parent_id'] > 0) {
                    $this->call('$("#js_comment_form_holder_' . $aVals['parent_id'] . '").closest(\'.comment-container-reply\').find(\'.js_comment_add_reply\').remove();');
                    $this->call('$("#js_comment_form_holder_' . $aVals['parent_id'] . '").empty().detach().insertBefore("#js_comment_mini_child_holder_' . $aVals['parent_id'] . '").removeClass("comment-item comment-item-reply comment-reply-new");');
                } else {
                    $this->call('$Core.Comment.resetCommentForm(' . $aVals['is_via_feed'] . ');');
                }
                if ($mId == 'pending_comment') {
                    $this->alert(_p('your_comment_has_been_added_successfully_it_is_waiting_for_an_admin_approval'),
                        null, 350);
                }
            } else if ($mId == 'pending_moderation') {
                $this->call("$('#js_comment_form')[0].reset();");

                $this->alert(_p('your_comment_was_successfully_added_moderated'));
            } else {
                $this->call('if (typeof(document.getElementById("js_no_comments")) != "undefined") { $("#js_no_comments").hide(); }');

                $aRow = Phpfox::getService('comment')->getComment($mId);

                $iNewTotalPoints = (int)Phpfox::getUserParam('comment.points_comment');
                $this->call('if ($Core.exists(\'#js_global_total_activity_points\')){ var iTotalActivityPoints = parseInt($(\'#js_global_total_activity_points\').html().replace(\'(\', \'\').replace(\')\', \'\')); $(\'#js_global_total_activity_points\').html(iTotalActivityPoints + ' . $iNewTotalPoints . '); }');

                if (isset($aVals['is_via_feed'])) {
                    $aRow['is_loaded_more'] = true;
                    $aRow['is_added_more'] = true;
                    Phpfox::getLib('parse.output')->setImageParser(['width' => 200, 'height' => 200]);
                    Phpfox_Template::instance()->assign([
                        'aComment'   => $aRow,
                        'bIsAjaxAdd' => 1,
                        'iParentId'  => isset($aVals['parent_id']) && $aVals['parent_id'] ? $aVals['parent_id'] : 0,
                    ])->getTemplate('comment.block.mini');
                    Phpfox::getLib('parse.output')->setImageParser(['clear' => true]);

                    if (isset($aVals['parent_id']) && $aVals['parent_id'] > 0) {
                        $this->html('#js_comment_form_holder_' . $aVals['parent_id'], '');
                        $this->html('#js_comment_form_holder_' . $aVals['parent_id'], '');
                        $this->append('#js_comment_children_holder_' . $aVals['parent_id'], $this->getContent(false));
                        $this->call('$("#js_comment_form_holder_' . $aVals['parent_id'] . '").closest(\'.comment-container-reply\').find(\'.js_comment_add_reply\').remove();');
                        $this->call('$("#js_comment_form_holder_' . $aVals['parent_id'] . '").detach().insertBefore("#js_comment_mini_child_holder_' . $aVals['parent_id'] . '").removeClass("comment-item comment-item-reply");');
                        if (empty($aVals['is_single'])) {
                            $this->call('$("#js_comment_children_holder_' . $aVals['parent_id'] . '").closest(".js_mini_feed_comment").addClass("has-replies");');
                        }
                        $this->call('if ($("#js_app_comment_feed_form_' . $aVals['is_via_feed'] . '_' . $aVals['parent_id'] . ' .js_preview_link_attachment_custom_form").length) { $("#js_app_comment_feed_form_' . $aVals['is_via_feed'] . '_' . $aVals['parent_id'] . ' .js_preview_link_attachment_custom_form").remove(); }');
                    } else {
                        if (isset($aVals['is_in_view'])) {
                            $this->call('Editor.setContent(\'\');');
                        } else {
                            $this->call('$Core.Comment.resetCommentForm(' . $aVals['is_via_feed'] . ');');
                        }
                        $this->call('$(\'#js_feed_comment_form_textarea_' . $aVals['is_via_feed'] . '\').parent().find(\'.js_feed_comment_process_form:first\').hide();');
                        $this->append('#js_feed_comment_view_more_' . $aVals['is_via_feed'], $this->getContent(false));
                        $this->call('if ($("#js_app_comment_feed_form_' . $aVals['is_via_feed'] . ' .js_preview_link_attachment_custom_form").length) { $("#js_app_comment_feed_form_' . $aVals['is_via_feed'] . ' .js_preview_link_attachment_custom_form").remove(); }');
                    }
                } else {
                    Phpfox::getLib('parse.output')->setImageParser(['width' => 500, 'height' => 500]);
                    Phpfox_Template::instance()->assign([
                        'aRow'           => $aRow,
                        'bCanPostOnItem' => false
                    ])->getTemplate('comment.block.entry');
                    Phpfox::getLib('parse.output')->setImageParser(['clear' => true]);

                    if (isset($aVals['parent_id']) && $aVals['parent_id'] > 0) {
                        $this->call("$('#js_comment_form_{$aVals['parent_id']}').slideUp(); $('#js_comment_form_form_{$aVals['parent_id']}').html(''); $('#js_comment_parent{$aVals['parent_id']}').html('<div style=\"margin-left:30px;\">" . $this->getContent() . "</div>' + $('#js_comment_parent{$aVals['parent_id']}').html()).slideDown(); $('#js_comment_form')[0].reset();");
                    } else {
                        $this->call("$('#js_new_comment').html('" . $this->getContent() . "' + $('#js_new_comment').html()).slideDown(); $.scrollTo('#js_new_comment', 800); $('#js_comment_form')[0].reset();");
                    }

                    $this->call('$(\'#js_comment' . $aRow['comment_id'] . '\').find(\'.valid_message:first\').show().fadeOut(5000);');
                }
                $this->call('$(\'.js_comment_emoticon_container\').remove();$(\'.js_comment_sticker_container\').remove();');
                //Update recent sticker to global container
                $this->call('$Core.Comment.updateRecentStickers('. Phpfox::getUserId() .');');
                //Update recent emoticon
                Phpfox::getBlock('comment.emoticon');
                $this->call('$Core.Comment.loadedEmoticon("' . $this->getContent() . '");');
            }

            if (!isset($aVals['is_via_feed']) && Phpfox::isModule('captcha') && Phpfox::getUserParam('captcha.captcha_on_comment') && !isset($bNoCaptcha)) {
                $this->call("$('#js_captcha_image').ajaxCall('captcha.reload', 'sId=js_captcha_image&sInput=image_verification');");
            }
            (($sPlugin = Phpfox_Plugin::get('comment.component_ajax_ajax_add_passed')) ? eval($sPlugin) : false);
        }

        if (!isset($aVals['is_via_feed'])) {
            $this->html('#js_comment_process', '');
            $this->call("$('#js_comment_submit').removeAttr('disabled'); $('#js_reply_comment').val('0'); $('#js_reply_comment_info').html('');");
        }

        if (Phpfox::isModule('captcha') && !isset($bNoCaptcha) && Phpfox::getUserParam('captcha.captcha_on_comment')) {
            $this->call("$('#js_captcha_image').ajaxCall('captcha.reload', 'sId=js_captcha_image&sInput=image_verification');");
        }

        if ($aVals['type'] == 'photo') {
            $this->call("if (\$Core.exists('.js_feed_comment_view_more_holder')) { $('.js_feed_comment_view_more_holder')[0].scrollTop = $('.js_feed_comment_view_more_holder')[0].scrollHeight; }");
        }

        // get the onclick atrribute
        $sCall = "sOnClick = $('#js_feed_comment_view_more_link_" . $aVals['is_via_feed'] . " .comment_mini_link .no_ajax_link').attr('onclick');";
        // if there is "view all comments" link
        $sCall .= "if (typeof sOnClick != 'undefined') {";
        // regex to get the params for the ajax call in this
        $sCall .= "sPattern = new RegExp('(comment_)?type_id=([a-z]+_?[a-z]*)&(amp;)?item_id=[0-9]+&(amp;)?feed_id=[0-9]+', 'i');";
        // save the current ajax params
        $sCall .= "sOnClickParam = sPattern.exec(sOnClick);";
        // replace the params, adding the new "added" variable
        $sCall .= "sNewOnClick = sOnClick.replace(sOnClickParam[0], sOnClickParam[0]+'&added=1');";
        // replace the onclick attribute
        $sCall .= "$('#js_feed_comment_view_more_link_" . $aVals['is_via_feed'] . " .comment_mini_link .no_ajax_link').attr('onclick', sNewOnClick);";
        // if there is "view all comments" link
        $sCall .= "}";
        // call this JS code
        $this->call($sCall);

        $this->call('$Core.loadInit();');
    }

    public function browse()
    {
        Phpfox::getBlock('comment.view', [
            'iTotal'  => $this->get('iTotal'),
            'sType'   => $this->get('sType'),
            'iItemId' => $this->get('iItemId'),
            'iPage'   => $this->get('page')
        ]);

        (($sPlugin = Phpfox_Plugin::get('comment.component_ajax_browse')) ? eval($sPlugin) : false);

        $this->html('#js_comment_listing', $this->getContent(false));
        $this->call('$Core.loadInit(); $.scrollTo("#js_comment_listing", 340);');
    }

    public function getQuote()
    {
        $aRow = Phpfox::getService('comment')->getQuote($this->get('id'));
        if (isset($aRow['user_id'])) {
            $sText = Phpfox::getLib('parse.output')->ajax(str_replace("'", "\'", $aRow['text']));

            (($sPlugin = Phpfox_Plugin::get('comment.component_ajax_get_quote')) ? eval($sPlugin) : false);

            if (!isset($bHasPluginCall)) {
                $this->call("$('#text').val($('#text').val() + \"\\n\" + '[quote=" . $aRow['user_id'] . "]" . $sText . "[/quote]' + \"\\n\\n\"); $.scrollTo('#add-comment', 340); $('#text').focus();");
            }
        }
    }

    public function updateText()
    {
        $aVals = $this->get('val');
        if (!$aVals['comment_id']) {
            return false;
        }
        $aExtra = Phpfox::getService('comment')->getExtraByComment($aVals['comment_id']);
        if (Phpfox::getLib('parse.format')->isEmpty($aVals['text']) && ((empty($aVals['attach_changed']) && !$aExtra) || (empty($aVals['photo_id']) && empty($aVals['sticker_id']) && $aExtra && !empty($aVals['attach_changed'])))) {
            $this->alert(_p('add_some_text_to_your_comment'));
            $this->call("$('#js_quick_edit_processingjs_comment_text_" . $this->get('comment_id') . "').hide();");
            return false;
        }

        if ($bRes = Phpfox::getService('comment.process')->updateText($aVals['comment_id'], $aVals)) {
            $aComment = Phpfox::getService('comment')->getComment($aVals['comment_id']);
            $this->template()->assign([
                'aComment' => $aComment
            ])->getTemplate('comment.block.mini-extra');
            $this->call('$(\'.js_comment_quick_edit_holder_' . $aComment['comment_id'] . '\').remove();');
            $this->call('$(\'#js_comment_' . $aComment['comment_id'] . '\').removeClass(\'comment-item-edit\');');
            $this->call('$(\'#js_comment_action_' . $aComment['comment_id'] . '\').show();');
            $this->call('$(\'#js_comment_options_' . $aComment['comment_id'] . '\').removeClass(\'hide\');');
            $this->call('$(\'.js_comment_text_inner_' . $aComment['comment_id'] . '\').find(\'.js_comment_text_holder\').html(\'' . $this->getContent() . '\').show();');
            if ($bRes == 1) {
                if (isset($aComment['extra_data']['extra_type']) && $aComment['extra_data']['extra_type'] == 'preview' && !Phpfox::getParam('core.disable_all_external_urls')) {
                    $this->call('$Core.Comment.appendActionAfterEdit(' . $aComment['comment_id'] . ');');
                } else {
                    $this->call('$Core.Comment.appendActionAfterEdit(' . $aComment['comment_id'] . ',true);');
                }
            }
            $this->call('$Core.loadInit();');
        }
    }

    public function getText()
    {
        $iCommentId = $this->get('comment_id');
        $aRow = Phpfox::getService('comment')->getCommentForEdit($iCommentId);

        (($sPlugin = Phpfox_Plugin::get('comment.component_ajax_get_text')) ? eval($sPlugin) : false);
        if (!$aRow) {
            return false;
        }
        $aExtra = [];
        $sType = '';
        if (count($aRow['extra_data'])) {
            $aExtra = $aRow['extra_data'];
            if ($aExtra['extra_type'] == 'sticker') {
                $aExtra['sticker_id'] = $aExtra['item_id'];
            } else if ($aExtra['extra_type'] == 'photo') {
                $aExtra['path'] = $aExtra['image_path'];
                $aExtra['file_id'] = 1; //Set virtual val
            }
            $sType = $aExtra['extra_type'];
        }
        $generatedValue = $aRow['text'];
        preg_match_all('/(?<match>\[(?<type>[\w]+)=(?<id>[\d]+)\](?<name>[\p{L}\p{P}\p{S}\p{N}\s]+)\[\/([\w]+)\])/Umu', $generatedValue, $matches);
        if(isset($matches['match'])) {
            foreach($matches['match'] as $key=>$match) {
                if(isset($matches['type'][$key]) && isset($matches['id'][$key]) && isset($matches['name'][$key])) {
                    $generatedValue = str_replace($match, sprintf('<span id="generated" class="generatedMentionTag" contenteditable="false" data-type="%s" data-id="%d">%s</span>', $matches['type'][$key], $matches['id'][$key], $matches['name'][$key]), $generatedValue);
                }
            }
        }
        $generatedValue = preg_replace('/\R/', '<br>', $generatedValue);
        if (!isset($bHasPluginCall)) {
            $this->template()->assign([
                'aComment' => $aRow,
                'generatedValue' => $generatedValue,
                'aForms'   => $aExtra,
                'sType'    => $sType,
                'bIsEdit'  => true,
            ])->getTemplate('comment.block.edit-comment');
            $this->call('$(\'#js_comment_' . $iCommentId . '\').addClass(\'comment-item-edit\');');
            $this->call('$(\'#js_comment_action_' . $iCommentId . '\').hide();');
            $this->call('$(\'#js_comment_options_' . $iCommentId . '\').addClass(\'hide\');');
            $this->call('$(\'#js_edit_comment_loading_' . $iCommentId . '\').remove();');
            $this->call('$(\'#js_comment_text_' . $iCommentId . '\').show();');
            $this->call('$(\'.js_comment_text_inner_' . $iCommentId . '\').find(\'.js_comment_text_holder\').hide();');
            $this->call('$(\'.js_comment_text_inner_' . $iCommentId . '\').append(\'' . $this->getContent() . '\');');
            $this->call('var editCommentEle = $(\'.js_comment_quick_edit_holder_' . $iCommentId . '\').find(\'div.js_comment_textarea_edit:first\') ;editCommentEle.focus();var offset = $(\'.js_comment_quick_edit_holder_' . $iCommentId . '\').find(\'.js_comment_textarea_edit:first\').val().length*2; $(\'.js_comment_quick_edit_holder_' . $iCommentId . '\').find(\'.js_comment_textarea_edit:first\')[0].setSelectionRange(offset,offset);');
        }
        $this->call('$Core.loadInit();');
    }

    public function inlineDelete()
    {
        $sTypeId = $this->get('type_id');
        $aRow = Phpfox::getService('comment')->getComment($this->get('comment_id'));
        if (Phpfox::getService('comment.process')->deleteInline($this->get('comment_id'), $sTypeId)) {
            $this->slideUp('#js_comment_' . $this->get('comment_id'));
            $this->call('$Core.Comment.updateReplyCounter(' . $aRow['parent_id'] . ', \'-\');');
            if ($sTypeId && $iItemId = $this->get('item_id')) {
                $this->call('$Core.Comment.updateCommentCounter(\'' . $sTypeId . '\', ' . $iItemId . ', \'-\');');
            }
        }
    }

    public function moderateSpam()
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('comment.can_moderate_comments', true);
        $sAction = $this->get('action');
        $iId = $this->get('id');
        if (Phpfox::getService('comment.process')->moderate($iId, $sAction, true)) {
            if ($sAction == 'deny') {
                Phpfox::addMessage(_p('comment_denied_successfully'));
            } else {
                Phpfox::addMessage(_p('comment_approved_successfully'));
            }
            $this->call('window.location.reload();');

            $this->call('if ($(\'#js_request_comment_count_total\').length > 0) { var iTotalCommentRequest = parseInt($(\'#js_request_comment_count_total\').html()); $(\'#js_request_comment_count_total\').html(\'\' + parseInt((iTotalCommentRequest - 1)) + \'\'); if ((iTotalCommentRequest - 1) == 0) { $(\'#js_request_comment_holder\').remove(); } requestCheckData(); }');

        }
    }

    public function viewAllComments()
    {
        $aComments = Phpfox::getService('comment')->getCommentsForFeed($this->get('comment_type_id'),
            $this->get('item_id'), 500, null, $this->get('comment_id'));

        foreach ($aComments as $aComment) {
            if (isset($aComment['children'])) {
                foreach ($aComment['children']['comments'] as $aMini) {
                    $this->template()->assign([
                        'aComment' => $aMini,
                        'aFeed'    => ['feed_id' => $this->get('item_id')]
                    ])->getTemplate('comment.block.mini');
                }
            }
        }

        $this->html('#js_comment_children_holder_' . $this->get('comment_id'), $this->getContent(false));
        $this->call('$("#comment_mini_child_view_holder_' . $this->get('comment_id') . '").parent().removeClass("comment_mini_child_holder_padding");');
        $this->remove('#comment_mini_child_view_holder_' . $this->get('comment_id'));
        $this->call('$Core.loadInit();');
    }

    public function viewMoreFeed()
    {
        $aComments = Phpfox::getService('comment')->getCommentsForFeed($this->get('comment_type_id'),
            $this->get('item_id'), Phpfox::getParam('comment.comment_page_limit'),
            ($this->get('total') ? (int)$this->get('total') : null), null, $this->get('feed_table_prefix', ''));

        if (!count($aComments)) {
            Phpfox_Error::set(_p('no_comments_found_dot'));

            return false;
        }

        // if the added parameter is 1
        if ($this->get('added') == 1) {
            // remove the last object, or it will be displayed as duplicate
            array_pop($aComments);
        }

        foreach ($aComments as $aComment) {
            $this->template()->assign([
                'aComment' => $aComment,
                'aFeed'    => ['feed_id' => $this->get('item_id')]
            ])->getTemplate('comment.block.mini');
        }

        if ($this->get('append')) {
            $this->prepend('#js_feed_comment_view_more_' . ($this->get('feed_id') ? $this->get('feed_id') : $this->get('item_id')),
                $this->getContent(false));

            Phpfox_Pager::instance()->set([
                    'ajax'    => 'comment.viewMoreFeed',
                    'page'    => Phpfox_Request::instance()->getInt('page'),
                    'size'    => $this->get('pagelimit'),
                    'count'   => $this->get('total'),
                    'phrase'  => _p('view_previous_comments'),
                    'icon'    => 'misc/comment.png',
                    'aParams' => [
                        'comment_type_id'   => $this->get('comment_type_id'),
                        'item_id'           => $this->get('item_id'),
                        'append'            => true,
                        'pagelimit'         => $this->get('pagelimit'),
                        'total'             => $this->get('total'),
                        'feed_table_prefix' => $this->get('feed_table_prefix', '')
                    ]
                ]
            );

            $this->template()->getLayout('pager');

            $this->html('#js_feed_comment_pager_' . ($this->get('comment_type_id') . '_' . $this->get('item_id')),
                $this->getContent(false));
        } else {
            $this->hide('#js_feed_comment_view_more_link_' . ($this->get('feed_id') ? $this->get('feed_id') : $this->get('item_id')));
            $this->html('#js_feed_comment_view_more_' . ($this->get('feed_id') ? $this->get('feed_id') : $this->get('item_id')),
                $this->getContent(false));
        }

        $this->call('$Core.loadInit();');
    }

    public function getChildren()
    {
        $this->template()->assign([
                'bCanPostOnItem' => Phpfox::getUserParam(Phpfox::callback($this->get('type') . '.getAjaxCommentVar'))
            ]
        );
        $this->_getChildren($this->get('comment_id'));

        $this->html('#js_comment_parent_view_' . $this->get('comment_id'),
            '<div style="margin-left:30px;">' . $this->getContent(false) . '</div>');
    }

    private function _getChildren($iId)
    {
        static $iCacheCnt = 0;

        $iCacheCnt++;

        list(, $aComments) = Phpfox::getService('comment')->get('cmt.*', ['cmt.parent_id = ' . $iId . '']);
        foreach ($aComments as $iKey => $aComment) {
            // Assign template vars for this comment.
            $this->template()->assign([
                    'aRow'           => $aComment,
                    'bCanPostOnItem' => ($iCacheCnt >= Phpfox::getParam('comment.total_child_comments') ? false : true)
                ]
            );

            // Display the comment
            $this->template()->getTemplate('comment.block.entry');

            if ($aComment['child_total'] > 0) {
                echo '<div style="margin-left:30px;">' . "\n";
                $this->_getChildren($aComment['comment_id']);
                echo '</div>' . "\n";
            }
        }
    }

    /** Tickers */
    public function addStickerSet()
    {
        $iId = $this->get('id', 0);
        $sTitle = $this->get('title');
        $bIsEdit = false;
        if ($iId) {
            $bIsEdit = true;
        }
        if (empty($sTitle)) {
            echo json_encode([
                'error' => _p('sticker_set_title_is_required'),
            ]);
            exit;
        }
        $aVals = [
            'id'    => $iId,
            'title' => $sTitle
        ];
        $iSetId = Phpfox::getService('comment.stickers.process')->addStickerSet($aVals, $bIsEdit);
        echo json_encode([
            'id' => $iSetId,
        ]);
        exit;
    }

    public function updateStickersOrdering()
    {
        $aVals = $this->get('val');
        $iSetId = $this->get('set_id');
        Phpfox::getService('comment.stickers.process')->updateStickersOrdering(['values' => $aVals['ordering']], $iSetId);
    }

    public function deleteSticker()
    {
        $iId = $this->get('id');
        if (!$iId) {
            return false;
        }
        if (Phpfox::getService('comment.stickers.process')->deleteSticker($iId)) {
            Phpfox::addMessage(_p('sticker_deleted_successfully'));
            $this->call('window.location.reload();');
        }
    }

    public function toggleActiveStickerSet()
    {
        $iSetId = $this->get('id');
        $iActive = $this->get('active');
        Phpfox::getService('comment.stickers.process')->toggleActiveStickerSet($iSetId, $iActive);
    }

    public function appendPreviewPhoto()
    {
        $iFeedId = $this->get('feed_id');
        $iParentId = $this->get('parent_id');
        $iEditId = $this->get('edit_id');
        $iFileId = $this->get('id');

        $aFile = Phpfox::getService('core.temp-file')->get($iFileId);
        if (!$aFile) {
            return false;
        }
        $this->template()->assign([
            'aForms' => $aFile,
            'sType'  => 'photo',
        ])->getTemplate('comment.block.preview-attach');
        if ($iParentId) {
            $this->call('$(\'#js_comment_form_holder_' . $iParentId . '\').find(\'.item-edit-content\').append(\'' . $this->getContent() . '\');');
            $this->call('$(\'#js_comment_form_holder_' . $iParentId . '\').find(\'.js_app_comment_feed_textarea\').focus();');
        } else if ($iFeedId) {
            $this->call('$(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_core_comment_form .item-edit-content\').append(\'' . $this->getContent() . '\');');
            $this->call('$(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_core_comment_form .js_app_comment_feed_textarea\').focus();');
            $this->call('if ($Core.Comment.bIsMobile) { $(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_comment_form_holder .mobile-sent-btn\').addClass(\'has-attach\'); $(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_comment_form_holder .mobile-sent-btn:not(.has-text)\').addClass(\'active\');}');
        } else {
            $this->call('$(\'.js_comment_quick_edit_holder_' . $iEditId . '\').find(\'.item-edit-content\').append(\'' . $this->getContent() . '\');');
            $this->call('$(\'.js_comment_quick_edit_holder_' . $iEditId . '\').find(\'.js_comment_textarea_edit\').focus();');
        }
        $this->call('clickAttachPhoto = false; $Core.Comment.selectingSticker = false;');
        $this->call('$Core.loadInit();');
    }

    public function deleteTempFile()
    {
        $iId = $this->get('id');
        if (!$iId) {
            return false;
        }
        Phpfox::getService('core.temp-file')->delete($iId, true);
        return true;
    }

    public function loadAttachEmoticon()
    {
        $iFeedId = $this->get('feed_id');
        $iParentId = $this->get('parent_id');
        $iEditId = $this->get('edit_id');
        if (!$iFeedId && !$iParentId && !$iEditId) {
            return false;
        }
        Phpfox::getBlock('comment.emoticon');
        $this->call('$Core.Comment.loadedEmoticon("' . $this->getContent() . '", ' . $iFeedId . ',' . $iParentId . ', ' . $iEditId . ');');
        $this->call('$Core.loadInit();');
        return true;
    }

    public function hideComment()
    {
        $iId = $this->get('id');
        $iUserId = Phpfox::getUserId();
        $bIsUnHide = $this->get('un_hide', false);
        if (!$iId || !$iUserId) {
            return false;
        }
        $aOwner = Phpfox::getService('user')->getUser($this->get('owner_id'));
        if (Phpfox::getService('comment.process')->hideComment($iId, $iUserId, $bIsUnHide)) {
            if (!$bIsUnHide) {
                $this->template()->assign([
                    'iCommentId' => $iId,
                    'sFirstName' => Phpfox::getService('user')->getFirstName($aOwner['full_name']),
                    'iParentId'  => $this->get('parent_id'),
                    'aOwner'     => $aOwner
                ])->getTemplate('comment.block.hide-comment');
                $this->call('$(\'#js_comment_' . $iId . '\').after(\'' . $this->getContent() . '\');');
                $this->call('$(\'#js_comment_' . $iId . '\').hide();');
                $this->call('$Core.loadInit();');
            } else {
                $this->call('$(\'#js_comment_' . $iId . '\').removeClass(\'view-hidden\').show();');
                $this->call('$(\'#js_hide_comment_' . $iId . '\').remove();');
                $this->call('$(\'#js_comment_action_' . $iId . '\').removeClass(\'hide\');');
                $this->call('$(\'#js_comment_options_' . $iId . '\').removeClass(\'hide\');');
            }
            return true;
        }
        return false;
    }

    public function removePreview()
    {
        $iId = $this->get('id');
        if (!$iId) {
            return false;
        }
        if (Phpfox::getService('comment.process')->removeExtraComment($iId, 'preview')) {
            $this->call('$(\'#js_link_preview_' . $iId . '\').remove();');
            $this->call('$(\'#js_remove_preview_action_' . $iId . '\').remove();');
        }
    }

    public function loadAttachSticker()
    {
        $iFeedId = $this->get('feed_id');
        $iParentId = $this->get('parent_id');
        $iEditId = $this->get('edit_id');
        if (!$iFeedId && !$iParentId && !$iEditId) {
            return false;
        }
        Phpfox::getBlock('comment.attach-sticker', ['feed_id' => $iFeedId, 'parent_id' => $iParentId, 'edit_id' => $iEditId]);
        $this->call('$Core.Comment.loadedStickerSet("' . $this->getContent() . '",' . $iFeedId . ','. $iParentId .',' . $iEditId . ');');
        return true;
    }

    public function appendPreviewSticker()
    {
        $iFeedId = $this->get('feed_id');
        $iParentId = $this->get('parent_id');
        $iEditId = $this->get('edit_id');
        $iStickerId = $this->get('sticker_id');
        $aSticker = Phpfox::getService('comment.stickers')->getStickerById($iStickerId);
        if (!$aSticker) {
            return false;
        }
        $this->template()->assign([
            'aForms'  => $aSticker,
            'sType'   => 'sticker',
            'bIsEdit' => $iEditId ? true : false,
        ])->getTemplate('comment.block.preview-attach');
        if ($iParentId) {
            $this->call('$(\'#js_comment_form_holder_' . $iParentId . '\').find(\'.item-edit-content\').append(\'' . $this->getContent() . '\').parents(\'.comment-box-reply\').addClass(\'has-photo-sticker\');');
            $this->call('$(\'#js_comment_form_holder_' . $iParentId . '\').find(\'.js_feed_comment_sticker_id\').val(\'' . $iStickerId . '\');');
            $this->call('$(\'#js_comment_form_holder_' . $iParentId . '\').find(\'.js_app_comment_feed_textarea\').focus();');
        } else if ($iFeedId) {
            $this->call('$(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_core_comment_form .item-edit-content\').append(\'' . $this->getContent() . '\').parents(\'.comment-box\').addClass(\'has-photo-sticker\');');
            $this->call('$(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_core_comment_form .js_feed_comment_sticker_id\').val(\'' . $iStickerId . '\');');
            $this->call('$(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_core_comment_form .js_app_comment_feed_textarea\').focus();');
            $this->call('if ($Core.Comment.bIsMobile) { $(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_comment_form_holder .mobile-sent-btn\').addClass(\'has-attach\'); $(\'#js_item_feed_' . $iFeedId . '\').find(\'.js_feed_comment_form_holder .mobile-sent-btn:not(.has-text)\').addClass(\'active\');}');
        } else {
            $this->call('$(\'.js_comment_quick_edit_holder_' . $iEditId . '\').find(\'.item-edit-content\').append(\'' . $this->getContent() . '\').parents(\'.comment-box-edit\').addClass(\'has-photo-sticker\');');
            $this->call('$(\'.js_comment_quick_edit_holder_' . $iEditId . '\').find(\'.js_feed_comment_sticker_id\').val(\'' . $iStickerId . '\');');
            $this->call('$(\'.js_comment_quick_edit_holder_' . $iEditId . '\').find(\'.js_feed_comment_attach_change\').val(1);');
            $this->call('$(\'.js_comment_quick_edit_holder_' . $iEditId . '\').find(\'.js_comment_textarea_edit\').focus();');
        }
        $this->call('$Core.loadInit();');
    }

    public function loadStickerCollection()
    {
        Phpfox::getBlock('comment.sticker-collection', [
            'iFeedId'   => $this->get('feed_id'),
            'iParentId' => $this->get('parent_id'),
            'iEditId'   => $this->get('edit_id')
        ]);
    }

    public function updateMyStickerSet()
    {
        $iSetId = $this->get('id');
        $bIsAdd = $this->get('is_add');
        if (!$iSetId) {
            return false;
        }
        if (Phpfox::getService('comment.stickers.process')->updateMyStickerSet($iSetId, Phpfox::getUserId(), $bIsAdd)) {
            if ($bIsAdd) {
                $aSet = Phpfox::getService('comment.stickers')->getStickerSetById($iSetId, 8);
                $aSet['is_added'] = true;
                $aSet['is_my'] = true;

                $this->template()->assign([
                    'aSet' => $aSet,
                ])->getTemplate('comment.block.sticker-set');
                $this->call('$(\'#core_comment_sticker_my\').find(\'.item-container\').append(\'' . $this->getContent() . '\');');
            }
            $this->call('$Core.Comment.updateLayoutMyStickerSets(' . $iSetId . ',' . $bIsAdd . ');');

            Phpfox::getBlock('comment.attach-sticker', [
                'bUpdateOpened' => true,
                'is_global'     => false,
                'feed_id'       => $this->get('feed_id'),
                'parent_id'     => $this->get('parent_id'),
                'edit_id'       => $this->get('edit_id')
            ]);
            $this->call('$(\'.js_comment_group_icon.open\').find(\'.js_comment_sticker_container\').html(\'' . $this->getContent() . '\');');
            $this->call('$(\'.js_comment_group_icon:not(.open)\').find(\'.js_comment_sticker_container\').remove();');
            $this->call('setTimeout(function() { $Core.Comment.initStickerAttachBar($(\'.js_comment_group_icon.open\').find(\'.js_comment_attach_sticker\')); }, 200);');

            Phpfox::getBlock('comment.attach-sticker');
            $this->call('$Core.Comment.loadedStickerSet("' . $this->getContent() . '");');
            $this->call('$Core.loadInit();');
        }
        return true;
    }

    public function previewStickerSet()
    {
        $iSetId = $this->get('id');
        if (!$iSetId) {
            return false;
        }
        $aSet = Phpfox::getService('comment.stickers')->getStickerSetById($iSetId);
        if ($aSet) {
            $aSet['is_added'] = Phpfox::getService('comment.stickers')->checkIsAddedSet($iSetId, Phpfox::getUserId());
            $this->template()->assign([
                'aSet'             => $aSet,
                'iStickerFeedId'   => $this->get('feed_id', 0),
                'iStickerParentId' => $this->get('parent_id', 0),
                'iStickerEditId'   => $this->get('edit_id', 0)
            ])->getTemplate('comment.block.preview-sticker-set');
            $this->call('$(\'.js_comment_preview_sticker_set_holder\').html(\'' . $this->getContent() . '\').removeClass(\'hide\');');
            $this->call('$(\'.js_comment_sticker_sets_holder\').addClass(\'hide\');');
            $this->call('$(\'.js_box_title_content:first\').prepend(\'<span class="pr-1" onclick="return $Core.Comment.removePreviewStickerSet(this);"><i class="ico ico-arrow-left"></i></span>\');');
        }
        $this->call('$Core.loadInit();');
        return true;
    }

    public function showEditHistory()
    {
        Phpfox::getBlock('comment.edit-history', ['comment_id' => $this->get('id')]);
    }

    public function refreshStickers()
    {
        $iSetId = $this->get('id');
        if (!$iSetId) {
            return false;
        }
        $aStickers = Phpfox::getService('comment.stickers')->getStickersBySet($iSetId);
        $this->template()->assign([
            'aStickers' => $aStickers,
        ])->getTemplate('comment.block.admin.list-stickers');
        $this->call('$(\'#js_list_stickers\').html(\'' . $this->getContent() . '\');');
        $this->call('$Core.loadInit();');
        return true;
    }
    public function updateGlobalRecentStickers()
    {
        Phpfox_Template::instance()->assign([
            'aRecentStickers' => Phpfox::getService('comment.stickers')->getRecentSticker(Phpfox::getUserId()),
            'bIsGlobal' => true
        ])->getTemplate('comment.block.recent-stickers');
        $this->call('$("#comment_sticker_set_holder").find(".js_recent_stickers_list").html("' . $this->getContent() . '");');
        $this->call('$Core.Comment.initCanvasForSticker(\'.core_comment_gif:not(.comment_built)\');');
        return true;
    }
}