<?php

namespace Apps\Core_MobileApi\Version1_7_3\Api\Security\Quiz;

use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;


class QuizAccessControl extends \Apps\Core_MobileApi\Api\Security\Quiz\QuizAccessControl
{
    /**
     * @inheritdoc
     *
     * @param $resource PollResource
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (!parent::isGranted($permission, $resource)) {
            return false;
        }
        $granted = true;
        // Check Pages/Group permission
        if ($this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('quiz.view_browse_quizzes');
                    break;
                case self::ADD:
                    $granted = ($this->appContext->hasPermission('quiz.share_quizzes')
                        && $this->appContext->hasPermission('quiz.view_browse_quizzes'));
                    break;
            }
        }

        return $granted;
    }
}