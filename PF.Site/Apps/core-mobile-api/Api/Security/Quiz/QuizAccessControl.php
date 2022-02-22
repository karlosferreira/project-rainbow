<?php

namespace Apps\Core_MobileApi\Api\Security\Quiz;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\QuizResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class QuizAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const PLAY = "play";

    const FEATURE = "feature";
    const APPROVE = "approve";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = "purchase_sponsor";
    const VIEW_OTHER_RESULT = "view_other_result";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::EDIT, self::COMMENT,
            self::FEATURE, self::SPONSOR, self::SPONSOR_IN_FEED,
            self::PURCHASE_SPONSOR, self::APPROVE, self::PLAY,
            self::VIEW_OTHER_RESULT
        ]);
    }

    /**
     * @inheritdoc
     *
     * @param $resource PollResource
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (in_array($permission, [self::IS_AUTHENTICATED, self::SYSTEM_ADMIN])) {
            return parent::isGranted($permission);
        }
        if (in_array($permission, [self::LIKE, self::SHARE, self::REPORT])) {
            return parent::isGranted($permission, $resource);
        }
        if (!parent::isGranted($permission, $resource)) {
            return false;
        }
        $isOwner = false;
        // Item Owner always able to do any permission
        /** @var $resource QuizResource */
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('quiz.can_access_quiz');
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('quiz.can_create_quiz');
                break;
            case self::EDIT:
                $granted = ($this->isGrantedSetting('quiz.can_edit_others_questions')
                    || ($this->isGrantedSetting('quiz.can_edit_own_questions') && $isOwner));
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('quiz.can_delete_others_quizzes')
                    || ($this->isGrantedSetting('quiz.can_delete_own_quiz') && $isOwner));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('quiz.can_delete_own_quiz');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['quiz.can_post_comment_on_quiz', 'quiz.can_access_quiz']) && (!$resource || !$resource->getIsPending());
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting('quiz.can_sponsor_quiz') && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = $this->isGrantedSetting('quiz.can_purchase_sponsor_quiz') && !$this->isGrantedSetting('quiz.can_sponsor_quiz') && \Phpfox::isAppActive('Core_BetterAds') && $isOwner && $resource && $resource->getCanPurchaseSponsor();
                break;
            case self::SPONSOR_IN_FEED:
                $granted = $resource && $resource->getCanSponsorInFeed() && (($isOwner && $this->isGrantedSetting('feed.can_purchase_sponsor')) || $this->isGrantedSetting('feed.can_sponsor_feed'));
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('quiz.can_feature_quiz');
                break;
            case self::APPROVE:
                $granted = $this->isGrantedSetting('quiz.can_approve_quizzes') && (!$resource || $resource->getIsPending());
                break;
            case self::PLAY:
                $granted = $resource && !$resource->is_user_played && !$resource->getIsPending() && (($this->isGrantedSetting('quiz.can_answer_own_quiz') && $isOwner) || !$isOwner);
                break;
            case self::VIEW_OTHER_RESULT:
                $granted = $resource && !$resource->getIsPending() && ($isOwner || $resource->is_user_played || $this->isGrantedSetting('quiz.can_view_results_before_answering'));
                break;
        }

        return $granted;
    }

}