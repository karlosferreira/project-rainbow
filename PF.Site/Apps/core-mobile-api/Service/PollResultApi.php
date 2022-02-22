<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\PollAnswerResource;
use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\PollResultResource;
use Apps\Core_MobileApi\Api\Security\AppContextFactory;
use Apps\Core_MobileApi\Api\Security\Poll\PollAccessControl;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Apps\Core_Polls\Service\Poll;
use Apps\Core_Polls\Service\Process;
use Phpfox;

class PollResultApi extends AbstractResourceApi
{
    /**
     * @var Poll
     */
    private $pollService;

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
        $this->pollService = Phpfox::getService('poll');
        $this->processService = Phpfox::getService('poll.process');
        $this->userService = Phpfox::getService('user');
    }


    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'answer_id', 'limit', 'page'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setAllowedTypes('answer_id', 'int')
            ->setRequired(['answer_id'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $answer = NameResource::instance()->getApiServiceByResourceName(PollAnswerResource::RESOURCE_NAME)
            ->loadResourceById($params['answer_id']);
        if (!$answer) {
            return $this->notFoundError();
        }
        $poll = $this->pollService->getPollById($answer['poll_id']);
        if (!$poll || ($poll['view_id'] == 1 && !Phpfox::getUserParam('poll.poll_can_moderate_polls') && $poll['user_id'] != Phpfox::getUserId())) {
            return $this->notFoundError();
        }
        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $poll['user_id'])) {
            return $this->permissionError();
        }
        if (Phpfox::isModule('privacy') && !Phpfox::getService('privacy')->check('poll', $poll['poll_id'], $poll['user_id'], $poll['privacy'],
                $poll['is_friend'], true)) {
            return $this->permissionError();
        }
        $canViewResult = false;
        if ((Phpfox::getUserParam('poll.can_view_user_poll_results_own_poll') && $poll['user_id'] == Phpfox::getUserId()) || Phpfox::getUserParam('poll.can_view_user_poll_results_other_poll')) {
            $canViewResult = true;
        }
        if (isset($poll['user_voted_this_poll']) && ($poll['user_voted_this_poll'] == false && Phpfox::getUserParam('poll.view_poll_results_before_vote')) || ($poll['user_voted_this_poll'] == true && Phpfox::getUserParam('poll.view_poll_results_after_vote'))) {
            $canViewResult = true;
        }
        if (!$canViewResult) {
            return $this->permissionError();
        }
        $items = $this->pollService->getVotesByAnswer($params['answer_id'], $params['page'], $params['limit'], $count);
        $this->processRows($items);
        return $this->success($items);
    }

    function findOne($params)
    {
        return null;
    }

    function create($params)
    {
        $params = $this->resolver->setDefined(['poll_id', 'answers'])
            ->setRequired(['poll_id', 'answers'])
            ->setAllowedTypes('poll_id', 'int')
            ->setAllowedTypes('answers', 'array')
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        /** @var PollResource $item */
        $item = NameResource::instance()->getApiServiceByResourceName(PollResource::RESOURCE_NAME)->loadResourceById($params['poll_id']);
        if (empty($item)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PollAccessControl::VOTE, PollResource::populate($item));

        $id = $this->processCreate($params, $item);
        if ($id) {
            return $this->success([
                'id' => $params['poll_id']
            ]);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    private function processCreate($values, $item)
    {
        $this->validateVoting($values, $item);
        return $this->processService->addVote($this->getUser()->getId(), (int)$values['poll_id'],
            $values['answers']);
    }

    private function validateVoting($values, $item)
    {
        //Check Single choice
        if (!count($values['answers'])) {
            return $this->permissionError($this->getLocalization()->translate('poll_you_must_choose_at_least_one_option'));
        }
        if (!$item['is_multiple'] && count($values['answers']) > 1) {
            return $this->permissionError($this->getLocalization()->translate('this_poll_does_not_allow_multiple_choice'));
        }
        $listAns = [];
        foreach ($item['answer'] as $ans) {
            $listAns[] = $ans['answer_id'];
        }
        foreach ($values['answers'] as $answer) {
            if (!is_numeric($answer) || !in_array($answer, $listAns)) {
                return $this->notFoundError($this->getLocalization()->translate('invalid_answer'));
            }
        }
        return true;
    }

    function update($params)
    {
        $params = $this->resolver->setDefined(['id', 'answers'])
            ->setRequired(['id', 'answers'])
            ->setAllowedTypes('id', 'int')
            ->setAllowedTypes('answers', 'array')
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getMissing());
        }
        /** @var PollResource $item */
        $item = NameResource::instance()->getApiServiceByResourceName(PollResource::RESOURCE_NAME)->loadResourceById($params['id']);
        if (empty($item)) {
            return $this->notFoundError();
        }
        $this->denyAccessUnlessGranted(PollAccessControl::CHANGE_VOTE, PollResource::populate($item));

        $id = $this->processUpdate($params['id'], $params, $item);
        if ($id) {
            return $this->success([
                'id' => $params['id']
            ]);
        } else {
            return $this->error($this->getErrorMessage());
        }
    }

    private function processUpdate($id, $values, $item)
    {
        $this->validateVoting($values, $item);
        return $this->processService->addVote($this->getUser()->getId(), $id,
            $values['answers']);
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

    public function processRow($poll)
    {
        return PollResultResource::populate($poll)->toArray();
    }

    /**
     * Create custom access control layer
     */
    public function createAccessControl()
    {
        $this->accessControl =
            new PollAccessControl($this->getSetting(), $this->getUser());

        $moduleId = $this->request()->get("module_id");
        $itemId = $this->request()->get("item_id");

        if ($moduleId) {
            $context = AppContextFactory::create($moduleId, $itemId);
            if ($context === null) {
                return $this->notFoundError();
            }
            $this->accessControl->setAppContext($context);
        }
        return true;
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