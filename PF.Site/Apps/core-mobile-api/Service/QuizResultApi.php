<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\QuizResource;
use Apps\Core_MobileApi\Api\Resource\QuizResultResource;
use Apps\Core_MobileApi\Api\Security\Quiz\QuizAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Quizzes\Service\Process;
use Apps\Core_Quizzes\Service\Quiz;
use Phpfox;

class QuizResultApi extends AbstractResourceApi
{
    /**
     * @var Quiz
     */
    private $quizService;

    /**
     * @var Process
     */
    private $processService;

    /**
     * @var \User_Service_User
     */
    private $userService;

    public function __construct()
    {
        parent::__construct();
        $this->quizService = Phpfox::getService('quiz');
        $this->processService = Phpfox::getService('quiz.process');
        $this->userService = Phpfox::getService('user');
    }

    public function getRouteMap()
    {
        $module = 'quiz';
        return [
            [
                'path'      => 'quiz-result/:id/:user_id',
                'routeName' => ROUTE_MODULE_DETAIL,
                'defaults'  => [
                    'moduleName'   => $module,
                    'resourceName' => 'quiz_result',
                ]
            ]
        ];
    }

    public function getMemberResults($quiz_id, $page = 0, $limit = 10)
    {
        $item = NameResource::instance()->getApiServiceByResourceName(QuizResource::RESOURCE_NAME)->loadResourceById($quiz_id);
        if (!$item) {
            return [];
        }
        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $item['user_id'])) {
            return [];
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('quiz', $item['quiz_id'], $item['user_id'], $item['privacy'],
                $item['is_friend'], true)) {
            return [];
        }
        $takenQuiz = $this->quizService->hasTakenQuiz(Phpfox::getUserId(), $quiz_id);
        if (!Phpfox::getUserParam('quiz.can_view_results_before_answering') && !$takenQuiz && ($item['user_id'] != Phpfox::getUserId())) {
            return [];
        }
        $userResults = $this->getByQuiz($item, $page, $limit);
        $items = !empty($userResults) ? array_values($userResults) : [];
        $this->processRows($items);
        return $items;
    }


    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'quiz_id', 'limit', 'page'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('quiz_id', 'int')
            ->setRequired(['quiz_id'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $item = NameResource::instance()->getApiServiceByResourceName(QuizResource::RESOURCE_NAME)->loadResourceById($params['quiz_id']);
        if (!$item) {
            return $this->notFoundError();
        }
        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $item['user_id'])) {
            return $this->permissionError();
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('quiz', $item['quiz_id'], $item['user_id'], $item['privacy'],
                $item['is_friend'], true)) {
            return $this->permissionError();
        }
        $userResults = $this->getByQuiz($item, $params['page'], $params['limit']);
        $items = !empty($userResults) ? array_values($userResults) : [];
        $this->processRows($items);
        return $this->success($items);
    }

    function findOne($params)
    {
        $params = $this->resolver
            ->setDefined(['user_id'])
            ->setRequired(['id', 'user_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        $item = NameResource::instance()->getApiServiceByResourceName(QuizResource::RESOURCE_NAME)->loadResourceById($params['id']);
        if (!$item) {
            return $this->notFoundError();
        }
        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $item['user_id'])) {
            return $this->permissionError();
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('quiz', $item['quiz_id'], $item['user_id'], $item['privacy'],
                $item['is_friend'], true)) {
            return $this->permissionError();
        }
        $takenQuiz = $this->quizService->hasTakenQuiz(Phpfox::getUserId(), $item['quiz_id']);
        if (!Phpfox::getUserParam('quiz.can_view_results_before_answering') && !$takenQuiz && ($item['user_id'] != Phpfox::getUserId())) {
            return $this->permissionError();
        }
        $user = $this->userService->getUser($params['user_id']);
        if (!$user || $user['profile_page_id']) {
            return $this->notFoundError();
        }
        $result = $this->getByUser($item, $params['user_id']);
        $result['index'] = $params['id'] . ':' . $params['user_id'];
        $result['quiz_id'] = $params['id'];
        return $this->success(QuizResultResource::populate($result)->lazyLoad(['user'])->toArray());
    }

    function create($params)
    {
        $params = $this->resolver->setDefined(['quiz_id', 'answers'])
            ->setRequired(['quiz_id', 'answers'])
            ->setAllowedTypes('quiz_id', 'int')
            ->setAllowedTypes('answers', 'array')
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        /** @var QuizResource $item */
        $item = NameResource::instance()->getApiServiceByResourceName(QuizResource::RESOURCE_NAME)->loadResourceById($params['quiz_id'], false, true);
        if (empty($item)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(QuizAccessControl::PLAY, QuizResource::populate($item));

        $result = $this->processCreate($params, $item);
        if (is_numeric($result)) {
            return $this->success([
                'id' => $params['quiz_id']
            ]);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    private function processCreate($params, $item)
    {
        if ($this->validateAnswers($params, $item)) {
            return $this->processService->answerQuiz($params['quiz_id'], $params['answers']);
        }
        return false;
    }

    /**
     * @param $values
     * @param $item
     *
     * @return array|bool|mixed
     */
    private function validateAnswers($values, $item)
    {
        $questionList = [];
        //Must answer all questions
        if (count($item['questions']) != count($values['answers'])) {
            return $this->error($this->getLocalization()->translate('you_need_to_answer_every_question'));
        }
        foreach ($item['questions'] as $question) {
            foreach ($question['answers'] as $answer) {
                $questionList[$question['question_id']][] = $answer['answer_id'];
            }
        }
        foreach ($values['answers'] as $questId => $answer) {
            if (!isset($questionList[$questId]) || !in_array($answer, $questionList[$questId])) {
                return $this->notFoundError($this->getLocalization()->translate('invalid_answer'));
            }
        }
        return true;
    }

    function update($params)
    {
        // TODO: Implement update() method.
    }

    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    function delete($params)
    {

    }

    function form($params = [])
    {
        // TODO: Implement form() method.
    }

    function loadResourceById($id, $returnResource = false)
    {
        return null;
    }

    public function processRow($item)
    {
        return QuizResultResource::populate($item)->toArray();
    }

    public function getByQuiz($aQuiz, $iPage, $iLimit)
    {
        // get the users who have taken this quiz
        $aAnswers = $this->database()->select('qa.*, qq.*')
            ->from(Phpfox::getT('quiz_answer'), 'qa')
            ->join(Phpfox::getT('quiz_question'), 'qq', 'qq.question_id = qa.question_id')
            ->where('qq.quiz_id = ' . $aQuiz['quiz_id'])
            ->order('qq.question_id ASC, qa.answer_id ASC')
            ->execute('getSlaveRows');

        $aTotalAnswers = [];
        foreach ($aAnswers as $aAnswer) {
            $aTotalAnswers[$aAnswer['question_id']] = (isset($aTotalAnswers[$aAnswer['answer_id']])) ? $aTotalAnswers[$aAnswer['answer_id']] + 1 : 0;
        }
        $iTotalAnswers = count($aTotalAnswers);

        $aUserResults = $this->database()->select(Phpfox::getUserField())
            ->from(Phpfox::getT('quiz_result'), 'qr')
            ->join(Phpfox::getT('user'), 'u', 'qr.user_id = u.user_id')
            ->where('qr.quiz_id = ' . $aQuiz['quiz_id'])
            ->order('time_stamp DESC')
            ->limit($iPage, $iLimit, $aQuiz['total_play'])
            ->group('qr.user_id')
            ->execute('getSlaveRows');

        $sUserId = implode(',', array_map(function ($item) {
            return $item['user_id'];
        }, $aUserResults));

        //No more result found
        if (empty($sUserId)) {
            return false;
        }
        // now we get the user's results
        $aResults = $this->database()->select('qr.*')
            ->from(Phpfox::getT('quiz_result'), 'qr')
            ->where('qr.quiz_id = ' . $aQuiz['quiz_id'] . ' AND qr.user_id IN (' . $sUserId . ')')
            ->order('time_stamp DESC')
            ->execute('getSlaveRows');

        $aTotalResults = [];
        foreach ($aResults as $aResult) {
            $userId = $aResult['user_id'];
            if (!isset($aTotalResults[$userId])) {
                $aTotalResults[$userId] = $aResult;
                $aTotalResults[$userId]['index'] = $aResult['quiz_id'] . ':' . $userId;
                $aTotalResults[$userId]['total_correct'] = 0;
                $aTotalResults[$userId]['percent_correct'] = 0;
            }

            // now check if the user answered correctly
            foreach ($aAnswers as $aAnswer) {
                if ($aResult['answer_id'] == $aAnswer['answer_id'] && $aAnswer['is_correct'] == 1) {
                    $aTotalResults[$userId]['total_correct']++;
                }
                $aTotalResults[$userId]['total_question'] = $iTotalAnswers;
            }

            // and get the success percentage so far
            if ($aTotalResults[$userId]['total_correct'] > 0) {
                $aTotalResults[$userId]['percent_correct'] = round((($aTotalResults[$userId]['total_correct'] / $aTotalResults[$userId]['total_question']) * 100));
            }
        }

        // add user info
        foreach ($aUserResults as $aUserResult) {
            $userId = $aUserResult['user_id'];
            if (isset($aTotalResults[$userId])) {
                $aTotalResults[$userId] = array_merge($aTotalResults[$userId], $aUserResult);
            }
        }

        return $aTotalResults;
    }

    public function getByUser($aQuiz, $iUserId)
    {
        $aAnswers = $this->database()->select('qq.question_id, qa.answer, qq.question, qa.answer_id')
            ->from(Phpfox::getT('quiz_question'), 'qq')
            ->join(Phpfox::getT('quiz_answer'), 'qa', 'qq.question_id = qa.question_id')
            ->where('qa.is_correct = 1 AND qq.quiz_id = ' . $aQuiz['quiz_id'] . ' ')
            ->order('qq.question_id ASC')
            ->execute('getSlaveRows');

        $aResults = $this->database()->select('*, ' . Phpfox::getUserField())
            ->from(Phpfox::getT('quiz_result'), 'qr')
            ->join(Phpfox::getT('quiz_answer'), 'qa', 'qa.answer_id = qr.answer_id')
            ->join(Phpfox::getT('user'), 'u', 'u.user_id = qr.user_id')
            ->where('qr.user_id = ' . (int)($iUserId) . ' AND qr.quiz_id = ' . $aQuiz['quiz_id'])
            ->execute('getSlaveRows');
        $aUsersAnswers = [];
        $iTotalCorrect = 0;
        //Each question have only one correct answer
        $iTotalQuestion = count($aAnswers);
        // now we check the user's answers vs the correct answers
        foreach ($aAnswers as $aAnswer) {
            // this is to initialize the array so any unanswered question caused by an edit will still show
            $aUsersAnswers[$aAnswer['question_id']] = [
                'questionText'      => $aAnswer['question'],
                'userAnswerText'    => $this->getLocalization()->translate('not_answered'),
                'userAnswer'        => '0',
                'questionId'        => intval($aAnswer['question_id']),
                'correctAnswerText' => $aAnswer['answer'],
                'correctAnswer'     => $aAnswer['answer_id'],
                'time_stamp'        => null
            ];

            foreach ($aResults as $aResult) {
                if ($aResult['question_id'] == $aAnswer['question_id']) { // its the same question
                    $aUsersAnswers[$aAnswer['question_id']] = [
                        'questionText'      => $aAnswer['question'],
                        'userAnswerText'    => $aResult['answer'],
                        'userAnswer'        => $aResult['answer_id'],
                        'questionId'        => intval($aAnswer['question_id']),
                        'correctAnswer'     => $aAnswer['answer_id'],
                        'correctAnswerText' => $aAnswer['answer'],
                        'time_stamp'        => $aResult['time_stamp']
                    ];
                    if ($aResult['answer_id'] == $aAnswer['answer_id']) {
                        $iTotalCorrect++;
                    }
                }
            }
        }
        return [
            'user_id'         => $iUserId,
            'user_results'    => $aUsersAnswers,
            'total_correct'   => $iTotalCorrect,
            'total_question'  => $iTotalQuestion,
            'percent_correct' => ($iTotalQuestion > 0) ? round(($iTotalCorrect / $iTotalQuestion) * 100) : 0
        ];
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new QuizAccessControl($this->getSetting(), $this->getUser());
    }

    function approve($params)
    {
        // TODO: Implement approve() method.
    }

    function feature($params)
    {
        // TODO: Implement feature() method.
    }

    function sponsor($params)
    {
        // TODO: Implement sponsor() method.
    }
}