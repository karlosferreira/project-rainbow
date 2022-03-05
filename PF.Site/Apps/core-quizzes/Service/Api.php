<?php

namespace Apps\Core_Quizzes\Service;

use Core\Api\ApiServiceBase;
use Phpfox;
use Phpfox_Database;
use Phpfox_Error;
use Phpfox_Validator;

class Api extends ApiServiceBase
{
    public function __construct()
    {
        $this->setPublicFields([
            'quiz_id',
            'module_id',
            'item_id',
            'user_id',
            'view_id',
            'title',
            'description',
            'description_parsed',
            'questions',
            'privacy',
            'image_path',
            'time_stamp',
            'total_comment',
            'total_attachment',
            'total_view',
            'total_like',
            'total_play',
            'server_id',
            'is_featured',
            'is_sponsor',
            'link',
            'taken_by'
        ]);
    }

    public function gets()
    {
        if (!Phpfox::getUserParam('quiz.can_access_quiz')) {
            return $this->error(_p('You don\'t have permission to browse {{ items }}.', ['items' => _p('quizzes')]));
        }

        $userId = $this->request()->get('user_id');
        $legacy = $this->request()->get('legacy');
        $moduleId = $this->request()->get('module_id');
        $itemId = $this->request()->get('item_id');
        $view = $this->request()->get('view');

        if (!empty($userId) && !empty($legacy)) {
            Phpfox::getService('core')->getLegacyItem([
                    'field' => ['quiz_id', 'title'],
                    'table' => 'quiz',
                    'redirect' => 'quiz',
                    'title' => $legacy
                ]
            );
        }

        $aUser = !empty($userId) ? Phpfox::getService('user')->get($userId) : null;
        $bIsProfile = !empty($aUser['user_id']);
        $iCurrentUserId = Phpfox::getUserId();
        $bIsParentModule = !empty($moduleId) && !empty($itemId);

        $this->initSearchParams();

        $this->search()->set([
                'type' => 'quiz',
                'field' => 'q.quiz_id',
                'ignore_blocked' => true,
                'search_tool' => [
                    'table_alias' => 'q',
                    'search' => [
                        'default_value' => _p('search_quizzes'),
                        'name' => 'search',
                        'field' => 'q.title'
                    ],
                    'sort' => [
                        'latest' => ['q.time_stamp', _p('latest')],
                        'most-viewed' => ['q.total_view', _p('most_viewed')],
                        'most-liked' => ['q.total_like', _p('most_liked')],
                        'most-talked' => ['q.total_comment', _p('most_discussed')]
                    ],
                    'show' => [$this->getSearchParam('limit')]
                ]
            ]
        );

        $aBrowseParams = [
            'module_id' => 'quiz',
            'alias' => 'q',
            'field' => 'quiz_id',
            'table' => Phpfox::getT('quiz'),
            'hide_view' => ['pending', 'my']
        ];

        switch ($view) {
            case 'my':
                $this->search()->setCondition('AND q.user_id = ' . (int)$iCurrentUserId);
                break;
            case 'pending':
                $this->search()->setCondition('AND q.view_id = 1');
                break;
            default:
                if ($bIsProfile === true) {
                    $this->search()->setCondition('AND q.item_id = 0 AND q.user_id = ' . (int)$aUser['user_id'] . ' AND q.view_id IN(' . ($aUser['user_id'] == $iCurrentUserId ? '0,1' : '0') . ') AND q.privacy IN(' . (Phpfox::getParam('core.section_privacy_item_browsing') ? '%PRIVACY%' : Phpfox::getService('core')->getForBrowse($aUser)) . ')');
                } elseif ($bIsParentModule) {
                    $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND q.module_id = \'' . Phpfox_Database::instance()->escape($moduleId) . '\' AND q.item_id = ' . (int)$itemId . '');
                } else {
                    if ((Phpfox::getParam('quiz.display_quizzes_created_in_page') || Phpfox::getParam('quiz.display_quizzes_created_in_group'))) {
                        $aModules = [];
                        if (Phpfox::getParam('quiz.display_quizzes_created_in_group') && Phpfox::isAppActive('PHPfox_Groups')) {
                            $aModules[] = 'groups';
                        }
                        if (Phpfox::getParam('quiz.display_quizzes_created_in_page') && Phpfox::isAppActive('Core_Pages')) {
                            $aModules[] = 'pages';
                        }
                        if (count($aModules)) {
                            $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND (q.module_id IN ("' . implode('","', $aModules) . '") OR q.module_id = \'quiz\')');
                        } else {
                            $this->search()->setCondition('AND q.view_id = 0 AND q.privacy IN(%PRIVACY%) AND q.module_id = \'quiz\'');
                        }
                    } else {
                        $this->search()->setCondition('AND q.view_id = 0 AND q.item_id = 0 AND q.privacy IN(%PRIVACY%)');
                    }
                }
                break;
        }

        if (in_array($moduleId, ['pages', 'groups'])) {
            $sService = $moduleId;
            if (Phpfox::hasCallback($sService, 'checkPermission') && !Phpfox::callback($sService . '.checkPermission', $itemId, 'quiz.view_browse_quizzes')) {
                return $this->error(_p('Cannot display this section due to privacy.'));
            }
        }

        $this->search()->setContinueSearch(true);
        $this->search()->browse()->params($aBrowseParams)
            ->execute();

        $aQuizzes = $this->search()->browse()->getRows();
        if (Phpfox_Error::isPassed()) {
            $aItems = [];
            foreach($aQuizzes as $iKey => $aQuiz) {
                $aQuiz = Phpfox::getService('quiz')->getQuizByUrl($aQuiz['quiz_id']);
                Phpfox::getService('quiz')->addExtraInformation($aQuiz);
                $aItems[] = $this->getItem($aQuiz);
            }
            return $this->success($aItems);
        }
        return $this->error();
    }

    public function get($params, $messages = [])
    {
        $quiz = Phpfox::getService('quiz')->getQuizByUrl($params['id']);

        if (!Phpfox::getUserParam('quiz.can_access_quiz') || empty($quiz)) {
            return $this->error(_p('that_quiz_does_not_exist_or_its_awaiting_moderation'));
        }

        if (!empty($quiz['module_id']) && !empty($quiz['item_id'])) {
            if (!Phpfox::isModule($quiz['module_id'])) {
                return $this->error(_p('Cannot find the parent item.'));
            } elseif (Phpfox::hasCallback($quiz['module_id'], 'checkPermission')
                && !Phpfox::callback($quiz['module_id'] . '.checkPermission', $quiz['item_id'], 'quiz.view_browse_quizzes')) {
                return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
            }
        }

        if ((Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $quiz['user_id'])) ||
            (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('quiz', $quiz['quiz_id'], $quiz['user_id'], $quiz['privacy'], $quiz['is_friend'], true))) {
            return $this->error(_p('Cannot {{ action }} this {{ item }}.', ['action' => _p('view__l'), 'item' => _p('quiz')]));
        }

        Phpfox::getService('quiz')->addExtraInformation($quiz);
        return $this->success($this->getItem($quiz), $messages);
    }

    public function post()
    {
        $this->isUser();

        if (!Phpfox::getUserParam('quiz.can_create_quiz')) {
            return $this->error(_p('You don\'t have permission to add new {{ item }}.', ['item' => _p('quiz')]));
        } elseif (!Phpfox::getService('quiz')->checkLimitation()) {
            return $this->error(_p('quiz_you_have_reached_your_limit_to_create_new_quiz'));
        }

        $aVals = $this->request()->getArray('val');
        $sModule = isset($aVals['module_id']) ? $aVals['module_id'] : null;
        $iItem = isset($aVals['item_id']) ? $aVals['item_id'] : null;

        $aValidation = [
            'title' => [
                'def' => 'required',
                'title' => _p('you_need_to_write_a_title'),
            ],
            'description' => [
                'def' => 'required',
                'title' => _p('you_need_to_write_a_description'),
            ]
        ];

        $oValid = Phpfox_Validator::instance()->set([
                'sFormName' => 'js_add_quiz_form',
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
                $bCheckParentPrivacy = Phpfox::callback($sModule . '.checkPermission', $iItem, 'quiz.share_quizzes');
            }

            if (!$bCheckParentPrivacy) {
                return $this->error(_p('unable_to_view_this_item_due_to_privacy_settings'));
            }
        } else {
            if (!empty($sModule) && !empty($iItem) && $aCallback === null) {
                return $this->error(_p('Cannot find the parent item.'));
            }
        }

        if (($iFlood = Phpfox::getUserParam('quiz.flood_control_quiz')) !== 0) {
            $aFlood = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field' => 'time_stamp', // The time stamp field
                    'table' => Phpfox::getT('quiz'), // Database table we plan to check
                    'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                    'time_stamp' => $iFlood * 60 // Seconds);
                ]
            ];

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($aFlood)) {
                return $this->error(_p('you_are_creating_a_quiz_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
            }
        }

        if (Phpfox::getUserParam('quiz.is_picture_upload_required') && (!isset($aVals['temp_file']) || $aVals['temp_file'] == '')) {
            return $this->error(_p('please_select_quiz_banner'));
        }

        $aQuestions = (isset($aVals['q'])) ? $aVals['q'] : [];
        if (empty($aQuestions)) {
            $this->error(_p('you_need_at_least_one_question_to_create_quiz'));
        } else {
            list($mValid,) = Phpfox::getService('quiz')->checkStructure($aQuestions);
            if ($mValid !== true && is_array($mValid)) {
                return $this->error(array_shift($mValid));
            }
        }

        if ($oValid->isValid($aVals)) {
            if ($iId = Phpfox::getService('quiz.process')->add($aVals, Phpfox::getUserId())) {
                return $this->get(['id' => $iId], [_p('{{ item }} successfully added.', ['item' => _p('quiz')])]);
            }
        }

        return $this->error();
    }

    public function put($params)
    {
        $this->isUser();

        $aVals = $this->request()->getArray('val');

        $aValidation = [
            'title' => [
                'def' => 'required',
                'title' => _p('you_need_to_write_a_title'),
            ],
            'description' => [
                'def' => 'required',
                'title' => _p('you_need_to_write_a_description'),
            ]
        ];

        $oValid = Phpfox_Validator::instance()->set([
                'sFormName' => 'js_add_quiz_form',
                'aParams' => $aValidation,
            ]
        );

        $aQuiz = Phpfox::getService('quiz')->getQuizToEdit((int)$params['id']);

        if (empty($aQuiz)) {
            return $this->error(_p('that_quiz_does_not_exist_or_its_awaiting_moderation'));
        }

        $iQuizOwner = (int)$aQuiz['user_id'];
        if ($iQuizOwner == Phpfox::getUserId()) {
            $bShowTitle = $bShowQuestions = Phpfox::getUserParam('quiz.can_edit_own_questions') || Phpfox::getUserParam('quiz.can_edit_others_questions');

        } else {
            $bShowTitle = $bShowQuestions = Phpfox::getUserParam('quiz.can_edit_others_questions');
        }

        if ($bShowQuestions == false && $bShowTitle == false) {
            return $this->error(_p('you_are_not_allowed_to_edit_this_quiz'));
        }

        $aQuestions = isset($aVals['q']) ? $aVals['q'] : false;
        $mValid = true;
        if ($aQuestions !== false) {
            list($mValid, $bNull) = Phpfox::getService('quiz')->checkStructure($aQuestions);
        }

        if ($mValid === true && $oValid->isValid($aVals)) {
            $aVals['quiz_id'] = $aQuiz['quiz_id'];
            list($mEdit,) = Phpfox::getService('quiz.process')->update($aVals, Phpfox::getUserId());
            if ($mEdit === true) {
                return $this->get(['id' => $aQuiz['quiz_id']], [_p('{{ item }} successfully updated.', ['item' => _p('quiz')])]);
            }
        } else {
            $aQuiz['questions'] = (isset($bNull) && is_array($bNull)) ? $bNull : ((isset($aVals['q'])) ? $aVals['q'] : []);
            if (is_array($mValid)) {
                return $this->error(array_shift($mValid));
            }
        }

        return $this->error();
    }

    public function delete($params)
    {
        $this->isUser();

        if (!Phpfox::getService('user.auth')->hasAccess('quiz', 'quiz_id', $params['id'],
            'quiz.can_delete_own_quiz',
            'quiz.can_delete_others_quizzes') || !Phpfox::getService('quiz.process')->deleteQuiz((int)$params['id'], Phpfox::getUserId())
        ) {
            return $this->error(_p('Cannot {{ action }} this {{ item }}.',
                ['action' => _p('delete__l'), 'item' => _p('quiz')]), true);
        }

        return $this->success([], [_p('{{ item }} successfully deleted.', ['item' => _p('quiz')])]);
    }
}