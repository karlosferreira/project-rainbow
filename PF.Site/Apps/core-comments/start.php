<?php

Phpfox::getLib('module')
    ->addAliasNames('comment', 'Core_Comments')
    ->addServiceNames([
        'comment'                  => Apps\Core_Comments\Service\Comment::class,
        'comment.api'              => Apps\Core_Comments\Service\Api::class,
        'comment.process'          => Apps\Core_Comments\Service\Process::class,
        'comment.callback'         => Apps\Core_Comments\Service\Callback::class,
        'comment.stickers'         => Apps\Core_Comments\Service\Stickers\Stickers::class,
        'comment.tracking'         => Apps\Core_Comments\Service\Tracking::class,
        'comment.stickers.process' => Apps\Core_Comments\Service\Stickers\Process::class,
        'comment.emoticon'         => Apps\Core_Comments\Service\Emoticon::class,
        'comment.history'          => Apps\Core_Comments\Service\History::class
    ])
    ->addTemplateDirs([
        'comment' => PHPFOX_DIR_SITE_APPS . 'core-comments' . PHPFOX_DS . 'views'
    ])
    ->addComponentNames('controller', [
        'comment.admincp.spam-comments'    => Apps\Core_Comments\Controller\Admin\SpamCommentsController::class,
        'comment.admincp.manage-stickers'  => Apps\Core_Comments\Controller\Admin\ManageStickersController::class,
        'comment.admincp.pending-comments' => Apps\Core_Comments\Controller\Admin\PendingCommentsController::class,
        'comment.admincp.add-sticker-set'  => Apps\Core_Comments\Controller\Admin\AddStickerSetController::class,
        'comment.admincp.frame-upload'     => Apps\Core_Comments\Controller\Admin\FrameUploadController::class,
        'comment.comments'                 => Apps\Core_Comments\Controller\CommentsController::class,
        'comment.replies'                  => Apps\Core_Comments\Controller\RepliesController::class,
        'comment.rss'                      => Apps\Core_Comments\Controller\RssController::class,
        'comment.view'                     => Apps\Core_Comments\Controller\ViewController::class,
    ])
    ->addComponentNames('ajax', [
        'comment.ajax' => Apps\Core_Comments\Ajax\Ajax::class
    ])
    ->addComponentNames('block', [
        'comment.mini'               => Apps\Core_Comments\Block\Mini::class,
        'comment.comment'            => Apps\Core_Comments\Block\CommentBlock::class,
        'comment.attach-sticker'     => Apps\Core_Comments\Block\AttachStickerBlock::class,
        'comment.emoticon'           => Apps\Core_Comments\Block\EmoticonBlock::class,
        'comment.sticker-collection' => Apps\Core_Comments\Block\StickerCollectionBlock::class,
        'comment.edit-history'       => Apps\Core_Comments\Block\EditHistory::class,
        'comment.more-replies'       => Apps\Core_Comments\Block\MoreReplies::class
    ]);

group('/comment', function () {
    route('/comments', 'comment.comments');
    route('/replies', 'comment.replies');
    route('/rss', 'comment.rss');
    route('/view/*', 'comment.view');

    // BackEnd routes
    route('/admincp', function () {
        auth()->isAdmin(true);
        Phpfox::getLib('module')->dispatch('comment.admincp.manage-stickers');
        return 'controller';
    });

    route('/admincp/stickers-set/order', function () {
        auth()->isAdmin(true);
        $ids = request()->get('ids');
        $ids = trim($ids, ',');
        $ids = explode(',', $ids);
        $values = [];
        foreach ($ids as $key => $id) {
            $values[$id] = $key + 1;
        }
        Phpfox::getService('core.process')->updateOrdering([
                'table'  => 'comment_sticker_set',
                'key'    => 'set_id',
                'values' => $values,
            ]
        );
        Phpfox::getLib('cache')->remove();
        return true;
    });
});

Phpfox::getLib('setting')->setParam('comment.thumbnail_sizes', [150, 200]);
Phpfox::getLib('setting')->setParam('comment.attach_sizes', [150, 200, 500, 1024]);

function comment_parse($sTxt)
{
    $aEmojis = Phpfox::getService('comment.emoticon')->getAll();
    $corePath = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-comments';
    $parsedOutput = Phpfox::getLib('parse.output');

    $sTxt = html_entity_decode($sTxt, ENT_QUOTES, 'UTF-8');
    $sTxt = comment_output_parse($sTxt, ['url' => true, 'user_tag' => true]);
    $sTxt = html_entity_decode($sTxt, ENT_QUOTES, 'UTF-8');
    $sTxt = Phpfox::getLib('parse.output')->parseUrls($sTxt, ['attr' => ['class' => 'comment_parsed_url']]);
    if (Phpfox::getParam('core.disable_all_external_urls') && !preg_match('/<a([^>]*) href="([^"]*)"([^>]*)>/i', $sTxt)) {
        $sTxt = preg_replace('/<\/a>$/i', '', $sTxt);
    }
    $sTxt = $parsedOutput->htmlspecialchars(html_entity_decode($sTxt, ENT_QUOTES, 'UTF-8'));
    $sTxt = preg_replace_callback('/&lt;a.*?class=&quot;.*?comment_parsed_url.*?&quot;.*?&gt;.*?&lt;\/a&gt;/', function($match) {
        return html_entity_decode($match[0], ENT_QUOTES, 'UTF-8');
    }, $sTxt);

    // Parse users/groups/pages mentions
    $sTxt = $parsedOutput->replaceUserTag($sTxt);
    $sTxt = preg_replace_callback('/\[group=(\d+)\].+?\[\/group\]/u', function ($matches) {
        return comment_parsePageTagged($matches[1], 1);
    }, $sTxt);
    $sTxt = preg_replace_callback('/\[page=(\d+)\].+?\[\/page\]/u', function ($matches) {
        return comment_parsePageTagged($matches[1], 0);
    }, $sTxt);

    $aEmojiChecks = [
        ';)' => [
            'value' => '&quot',
            'pattern' => '/(&quot)?(;\))/'
        ]
    ];

    //Parse emoticon
    foreach ($aEmojis as $aEmoji) {
        if (isset($aEmojiChecks[$aEmoji['code']])) {
            $checkingValue = $aEmojiChecks[$aEmoji['code']]['value'];
            $sTxt = preg_replace_callback($aEmojiChecks[$aEmoji['code']]['pattern'], function($match) use($sTxt, $checkingValue, $aEmoji, $corePath) {
                if (count($match) == 3 && $match[1] == $checkingValue) {
                    $returnText = $match[1] . $match[2];
                } else {
                    $returnText = '<span class="item-tag-emoji"><img class="comment_content_emoji" title="' . $aEmoji['title'] . '" src="' . $corePath . '/assets/images/emoticons/' . $aEmoji['image'] . '"  alt="' . $aEmoji['image'] . '"/></span>';
                }
                return $returnText;
            }, $sTxt);
        } else {
            $sTxt = str_replace($aEmoji['code'], '<span class="item-tag-emoji"><img class="comment_content_emoji" title="' . $aEmoji['title'] . '" src="' . $corePath . '/assets/images/emoticons/' . $aEmoji['image'] . '"  alt="' . $aEmoji['image'] . '"/></span>', $sTxt);
        }
    }

    return $sTxt;
}

function comment_parsePageTagged($iPageId, $iType)
{
    $sOut = '';
    if (($iType == 1 && !Phpfox::isAppActive('PHPfox_Groups')) || ($iType == 0 && !Phpfox::isAppActive('Core_Pages'))) {
        return $sOut;
    }

    $oService = $iType ? Phpfox::getService('groups') : Phpfox::getService('pages');
    $aPage = $oService->getPage($iPageId);
    $sUrl = $oService->getUrl($iPageId, $aPage['title'], $aPage['vanity_url']);
    $iUserId = $oService->getUserId($iPageId);
    if (isset($aPage['title'])) {
        if ($iType == 0
            || $aPage['reg_method'] != 2
            || Phpfox::isAdmin()
            || !empty($aGroup['is_liked'])
            || Phpfox::getService('groups')->getPageOwnerId($iPageId) == Phpfox::getUserId()
            || Phpfox::getService('groups')->checkCurrentUserInvited($iPageId)) {
            $sOut = '<span class="user_profile_link_span" id="js_user_name_link_' . $iUserId . '"><a href="' . $sUrl . '">' . $aPage['title'] . '</a></span>';
        } else {
            $sOut = $aPage['title'];
        }
    }
    return $sOut;
}

function comment_parse_emojis_inline($sTxt)
{
    return comment_parse_emojis($sTxt, true);
}
function comment_parse_emojis($sTxt, $bInlineCss = false)
{
    $aEmojis = Phpfox::getService('comment.emoticon')->getAll();
    $corePath = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-comments';

    /*parse emojis*/
    foreach ($aEmojis as $aEmoji) {
        $sTxt = str_replace($aEmoji['code'], '<span class="item-tag-emoji comment-item-tag-emoji"' . ($bInlineCss ? 'style="width: 16px; height: 16px; display: inline-block; margin: 0 2px;"' : '') . '><img data-code="' . $aEmoji['code'] . '" class="comment_content_emoji" title="' . $aEmoji['title'] . '" src="' . $corePath . '/assets/images/emoticons/' . $aEmoji['image'] . '"  alt="' . $aEmoji['image'] . '"' . ($bInlineCss ? 'style="width: 100%; height: 100%"' : '') . '/></span>', $sTxt);
    }
    return $sTxt;
}

function divcontenteditable_comment_parse_emojis($sTxt)
{
    $aEmojis = Phpfox::getService('comment.emoticon')->getAll();
    $corePath = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/core-comments';

    /*parse emojis*/
    foreach ($aEmojis as $aEmoji) {
        $sTxt = str_replace($aEmoji['code'], '<img data-code="' . $aEmoji['code'] . '" class="comment_content_emoji s-2" style="pointer-events:none; margin: 0px 1px; vertical-align:middle" title="' . $aEmoji['title'] . '" src="' . $corePath . '/assets/images/emoticons/' . $aEmoji['image'] . '"  alt="' . $aEmoji['image'] . '"/>', $sTxt);
    }
    return $sTxt;
}

/**
 * Use this function instead of parse output lib.
 * Because the lib function always strip \n chars although $bParseNewLine is true
 *
 * @param $sTxt
 *
 * @return mixed|string
 */
function comment_output_parse($sTxt, $ignoredParsedParams = [])
{
    if (empty($sTxt)) {
        return $sTxt;
    }

    $sTxt = ' ' . $sTxt;

    $oParseLib = Phpfox::getLib('parse.output');
    (($sPlugin = Phpfox_Plugin::get('parse_output_parse')) ? eval($sPlugin) : null);

    if (isset($override) && is_callable($override)) {
        $sTxt = call_user_func($override, $sTxt);
    } else if (!Phpfox::getParam('core.allow_html')) {
        $sTxt = $oParseLib->htmlspecialchars($sTxt);
    } else {
        $sTxt = $oParseLib->cleanScriptTag($sTxt);
        $sTxt = $oParseLib->cleanStyleTag($sTxt);
        $sTxt = $oParseLib->cleanRedundantClosingTags($sTxt);
    }

    $sTxt = Phpfox::getService('ban.word')->clean($sTxt);
    $sTxt = Phpfox::getLib('parse.bbcode')->parse($sTxt);

    $sTxt = str_replace("\n\r\n\r", "", $sTxt);
    $sTxt = str_replace("\n\r", "", $sTxt);

    if (empty($ignoredParsedParams['url'])) {
        $sTxt = $oParseLib->parseUrls($sTxt);
    }

    $sTxt = preg_replace_callback('/\[PHPFOX_PHRASE\](.*?)\[\/PHPFOX_PHRASE\]/i', function ($aMatches) {
        return (isset($aMatches[1]) ? _p($aMatches[1]) : $aMatches[0]);
    }, $sTxt);

    $sTxt = ' ' . $sTxt;

    //support responsive table
    $sTxt = preg_replace("/<table([^\>]*)>/uim", "<div class=\"table-wrapper table-responsive\"><table $1>", $sTxt);
    $sTxt = preg_replace("/<\/table>/uim", "</table></div>", $sTxt);

    if (empty($ignoredParsedParams['user_tag'])) {
        $sTxt = $oParseLib->replaceUserTag($sTxt);
    }

    $sTxt = trim($sTxt);

    return $sTxt;
}