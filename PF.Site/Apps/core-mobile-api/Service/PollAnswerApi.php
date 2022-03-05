<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\PollAnswerResource;
use Apps\Core_Polls\Service\Poll;
use Apps\Core_Polls\Service\Process;
use Phpfox;

class PollAnswerApi extends AbstractResourceApi
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
        return null;
    }

    function findOne($params)
    {
        return null;
    }

    function create($params)
    {
        // TODO: Implement create() method.
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
        return $this->database()->select('pa.*, p.user_id')
            ->from(':poll_answer', 'pa')
            ->join(':poll', 'p', 'pa.poll_id = p.poll_id')
            ->where('pa.answer_id = ' . (int)$id)
            ->execute('getSlaveRow');
    }

    public function processRow($item)
    {
        return PollAnswerResource::populate($item)->toArray();
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