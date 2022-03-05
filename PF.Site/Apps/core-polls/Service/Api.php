<?php

namespace Apps\Core_Polls\Service;

use Core\Api\ApiServiceBase;
use Phpfox;
use Phpfox_Database;
use Phpfox_Validator;

class Api extends ApiServiceBase
{
    public function __construct()
    {
        $this->setPublicFields([
            'poll_id',
            'module_id',
            'item_id',
            'user_id',
            'view_id',
            'question',
            'description',
            'description_parsed',
            'privacy',
            'image_path',
            'time_stamp',
            'total_comment',
            'total_attachment',
            'total_view',
            'total_like',
            'randomize',
            'hide_vote',
            'is_featured',
            'is_sponsor',
            'is_multiple',
            'close_time',
            'answer',
        ]);
    }

    /**
     * @description: update a poll
     * @param $params
     *
     * @return array|bool
     */
    public function put($params)
    {
        $this->isUser();

        $aVals = $this->request()->get('val');

        $aValidation = [
            'question' => [
                'def' => 'required',
                'title' => _p('provide_a_question_for_your_poll'),
            ],
        ];

        $oValid = Phpfox_Validator::instance()->set([
                'sFormName' => 'js_poll_form',
                'aParams' => $aValidation,
            ]
        );

        if (!empty($aPoll = !empty($params['id']) ? Phpfox::getService('poll')->getPollById((int)$params['id']) : [])) {
            $bCanEditPoll = (($aPoll['user_id'] == Phpfox::getUserId() && Phpfox::getUserParam('poll.poll_can_edit_own_polls'))
                || Phpfox::getUserParam('poll.poll_can_edit_others_polls'));
            $sModule = $aPoll['module_id'];
            $iItem = $aPoll['item_id'];
            if (!$bCanEditPoll) {
                return $this->error(_p('You don\'t have permission to edit {{ item }}.', ['item' => _p('poll')]));
            }
        } else {
            return $this->error(_p('that_poll_does_not_exist'));
        }

        $aCallback = null;
        if (!empty($sModule) && Phpfox::hasCallback($sModule, 'getItem')) {
            $aCallback = Phpfox::callback($sModule . '.getItem', $iItem);
            if ($aCallback === false) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        } else {
            if (!empty($sModule) && !empty($iItem) && $aCallback === null) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        $mErrors = Phpfox::getService('poll')->checkStructure($aVals);
        if (is_array($mErrors)) {
            return $this->error(array_shift($mErrors));
        }

        if (empty($aVals['temp_file']) && Phpfox::getParam('poll.is_image_required')) {
            if (empty($aPoll['image_path']) || !empty($aVals['remove_photo'])) {
                return $this->error(_p('each_poll_requires_an_image'));
            }
        }

        if ($oValid->isValid($aVals)) {
            $aVals['poll_id'] = $aPoll['poll_id'];
            if (Phpfox::getService('poll.process')->add(Phpfox::getUserId(), $aVals, true)) {
                return $this->get(['id' => $aVals['poll_id']],
                    [_p('{{ item }} successfully updated.', ['item' => _p('poll')])]);
            }
        }

        return $this->error();
    }

    /**
     * @description: get info of a poll
     * @param array $params
     * @param array $messages
     *
     * @return array|bool
     */
    public function get($params, $messages = [])
    {
        $errorMessage = null;

        if (Phpfox::getUserParam('poll.can_access_polls')
            && !empty($poll = Phpfox::getService('poll')->getPollByUrl($params['id'], false, false, true, true))) {
            if ($poll === false) {
                $errorMessage = _p('not_a_valid_poll');
            } elseif (!empty($poll['module_id']) && !empty($poll['item_id'])) {
                if (!Phpfox::isModule($poll['module_id'])) {
                    $errorMessage = _p('Cannot find the parent item.');
                } elseif (Phpfox::hasCallback($poll['module_id'], 'checkPermission')
                    && !Phpfox::callback($poll['module_id'] . '.checkPermission', $poll['item_id'], 'poll.view_browse_polls')) {
                    $errorMessage = _p('unable_to_view_this_item_due_to_privacy_settings');
                }
            } elseif ((Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $poll['user_id']))
                || (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('poll', $poll['poll_id'], $poll['user_id'], $poll['privacy'], $poll['is_friend'], true))) {
                $errorMessage = _p('You don\'t have permission to {{ action }} this {{ item }}.',
                    ['action' => _p('view__l'), 'item' => _p('poll')]);
            }
        }

        if ($errorMessage) {
            return $this->error($errorMessage, true);
        }

        if (!empty($poll['image_path']) && !preg_match('/^https?:\/\//', $poll['image_path'])) {
            $poll['image_path'] = Phpfox::getLib('image.helper')->display([
                'server_id' => $poll['server_id'],
                'path' => 'poll.url_image',
                'file' => $poll['image_path'],
                'suffix' => '_500',
                'return_url' => true
            ]);
        }

        return $this->success($this->getItem($poll), $messages);
    }

    /**
     * @description: delete a poll
     * @param $params
     *
     * @return array|bool
     */
    public function delete($params)
    {
        $this->isUser();

        if (!Phpfox::getService('user.auth')->hasAccess('poll', 'poll_id', $params['id'],
                'poll.poll_can_delete_own_polls',
                'poll.poll_can_delete_others_polls')
            || !Phpfox::getService('poll.process')->moderatePoll($params['id'], 2)) {
            return $this->error(_p('Cannot {{ action }} this {{ item }}.',
                ['action' => _p('delete__l'), 'item' => _p('poll')]), true);
        }

        return $this->success([], [_p('{{ item }} successfully deleted.', ['item' => _p('poll')])]);
    }

    /**
     * @description: add new poll
     * @return array|bool
     */
    public function post()
    {
        $this->isUser();

        if (!Phpfox::getUserParam('poll.can_create_poll')) {
            return $this->error(_p('You don\'t have permission to add new {{ item }}.', ['item' => _p('poll')]));
        } elseif (!Phpfox::getService('poll')->checkLimitation()) {
            return $this->error(_p('poll_you_have_reached_your_limit_to_create_new_poll'));
        }

        $aVals = $this->request()->get('val');
        $sModule = isset($aVals['module_id']) ? $aVals['module_id'] : null;
        $iItem = isset($aVals['item_id']) ? $aVals['item_id'] : null;

        $aValidation = [
            'question' => [
                'def' => 'required',
                'title' => _p('provide_a_question_for_your_poll'),
            ],
        ];

        $oValid = Phpfox_Validator::instance()->set([
                'sFormName' => 'js_poll_form',
                'aParams' => $aValidation,
            ]
        );

        $aCallback = null;
        if (!empty($sModule) && Phpfox::hasCallback($sModule, 'getItem')) {
            $aCallback = Phpfox::callback($sModule . '.getItem', $iItem);
            if ($aCallback === false) {
                return $this->error(_p('Cannot find the parent item.'));
            }

            $bCheckParentPrivacy = true;
            if (Phpfox::hasCallback($sModule, 'checkPermission')) {
                $bCheckParentPrivacy = Phpfox::callback($sModule . '.checkPermission', $iItem, 'poll.share_polls');
            }

            if (!$bCheckParentPrivacy) {
                return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
            }
        } else {
            if (!empty($sModule) && !empty($iItem) && $aCallback === null) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        // avoid a flood
        $iFlood = Phpfox::getUserParam('poll.poll_flood_control');
        if ($iFlood != '0') {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('poll'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ],
            ];
            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                // Set an error
                return $this->error(_p('poll_flood_control', ['x' => $iFlood]));
            }
        }

        $mErrors = Phpfox::getService('poll')->checkStructure($aVals);
        if (is_array($mErrors)) {
            return $this->error(array_shift($mErrors));
        }

        if (empty($aVals['temp_file']) && Phpfox::getParam('poll.is_image_required')) {
            return $this->error(_p('each_poll_requires_an_image'));
        }

        if ($oValid->isValid($aVals)) {
            if (list($iId,) = Phpfox::getService('poll.process')->add(Phpfox::getUserId(), $aVals)) {
                return $this->get(['id' => $iId], [_p('{{ item }} successfully added.', ['item' => _p('poll')])]);
            }
        }

        return $this->error();
    }

    /**
     * @description: get polls
     * @return array|bool
     */
    public function gets()
    {
        $userId = $this->request()->get('user_id');
        $legacy = $this->request()->get('legacy');
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $view = $this->request()->get('view');

        if (!Phpfox::getUserParam('poll.can_access_polls')
            || ($view == 'pending' && !Phpfox::getUserParam('poll.poll_can_moderate_polls'))
            || (in_array($view, ['my', 'pending']) && !Phpfox::isUser())) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('Polls')]));
        }

        if (!empty($userId) && !empty($legacy)) {
            Phpfox::getService('core')->getLegacyItem([
                    'field' => ['poll_id', 'question'],
                    'table' => 'poll',
                    'redirect' => 'poll',
                    'search' => 'question_url',
                    'title' => $legacy,
                ]
            );
        }

        Phpfox::getService('poll.browse')->isApi();

        $aUser = !empty($userId) ? Phpfox::getService('user')->get($userId) : null;
        $bIsProfile = !empty($aUser['user_id']);
        $iCurrentUserId = Phpfox::getUserId();
        $bIsParentModule = !empty($moduleId) && !empty($itemId);


        $this->initSearchParams();

        $this->search()->set([
                'type' => 'poll',
                'field' => 'poll.poll_id',
                'ignore_blocked' => true,
                'search_tool' => [
                    'table_alias' => 'poll',
                    'search' => [
                        'name' => 'search',
                        'field' => 'poll.question',
                    ],
                    'sort' => [
                        'latest' => ['poll.time_stamp', _p('latest')],
                        'most-viewed' => ['poll.total_view', _p('most_viewed')],
                        'most-liked' => ['poll.total_like', _p('most_liked')],
                        'most-talked' => ['poll.total_comment', _p('most_discussed')],
                    ],
                    'show' => [$this->getSearchParam('limit', 10)],
                ],
            ]
        );

        $aBrowseParams = [
            'module_id' => 'poll',
            'alias' => 'poll',
            'field' => 'poll_id',
            'table' => Phpfox::getT('poll'),
            'hide_view' => ['pending', 'my'],
        ];

        switch ($view) {
            case 'my':
                $this->search()->setCondition('AND poll.user_id = ' . (int)$iCurrentUserId);
                break;
            case 'pending':
                $this->search()->setCondition('AND poll.view_id = 1');
                break;
            default:
                if ($bIsProfile === true) {
                    $this->search()->setCondition('AND poll.item_id = 0 AND poll.user_id = ' . (int)$aUser['user_id'] . ' AND poll.view_id IN(' . ($aUser['user_id'] == $iCurrentUserId ? '0,1' : '0') . ') AND poll.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ')');
                } elseif ($bIsParentModule) {
                    $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND poll.module_id = \'' . Phpfox_Database::instance()->escape($moduleId) . '\' AND poll.item_id = ' . (int)$itemId . '');
                } else {
                    if ((Phpfox::getParam('poll.display_polls_created_in_page') || Phpfox::getParam('poll.display_polls_created_in_group'))) {
                        $aModules = [];
                        if (Phpfox::getParam('poll.display_polls_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                            $aModules[] = 'groups';
                        }
                        if (Phpfox::getParam('poll.display_polls_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                            $aModules[] = 'pages';
                        }
                        if (count($aModules)) {
                            $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND (poll.module_id IN ("' . implode('","', $aModules) . '") OR poll.module_id IS NULL)');
                        } else {
                            $this->search()->setCondition('AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%) AND poll.module_id IS NULL');
                        }
                    } else {
                        $this->search()->setCondition('AND poll.item_id = 0 AND poll.view_id = 0 AND poll.privacy IN(%PRIVACY%)');
                    }

                }
                $this->search()->setCondition('AND (poll.close_time = 0 OR poll.close_time > ' . PHPFOX_TIME . ')');
                break;
        }

        if (in_array($moduleId, ['pages', 'groups'])) {
            $sService = $moduleId;
            if (Phpfox::hasCallback($sService, 'checkPermission') && !Phpfox::callback($sService . '.checkPermission', $itemId, 'poll.view_browse_polls')) {
                return $this->error(_p('Cannot display this section due to privacy.'));
            }
        }

        $this->search()->setContinueSearch(true);
        $this->search()->browse()->params($aBrowseParams)
            ->execute();

        $aPolls = $this->search()->browse()->getRows();
        $aItems = [];

        foreach ($aPolls as $aPoll) {
            $aItems[] = $this->getItem($aPoll);
        }

        return $this->success($aItems);
    }
}