<?php

namespace Apps\Core_MobileApi\Api\Security\Poll;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\PollResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class PollAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const VOTE_WITH_CLOSE_TIME = 'vote_with_close_time';
    const VOTE = "vote";
    const CHANGE_VOTE = "change_vote";
    const VIEW_RESULT = "view_result";
    const VIEW_RESULT_BEFORE_VOTE = "view_result_before_vote";
    const VIEW_RESULT_AFTER_VOTE = "view_result_after_vote";
    const VIEW_HIDE_VOTE = "view_hide_vote";

    const FEATURE = "feature";
    const APPROVE = "approve";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = "purchase_sponsor";

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::EDIT, self::COMMENT,
            self::FEATURE, self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR, self::VIEW_HIDE_VOTE,
            self::VOTE_WITH_CLOSE_TIME, self::VOTE, self::CHANGE_VOTE, self::APPROVE, self::VIEW_RESULT, self::VIEW_RESULT_BEFORE_VOTE, self::VIEW_RESULT_AFTER_VOTE
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
        /** @var $resource PollResource */
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('poll.can_access_polls');
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('poll.can_create_poll');
                break;
            case self::EDIT:
                $granted = ($this->isGrantedSetting('poll.poll_can_edit_others_polls')
                    || ($this->isGrantedSetting('poll.poll_can_edit_own_polls') && $isOwner));
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('poll.poll_can_delete_others_polls')
                    || ($this->isGrantedSetting('poll.poll_can_delete_own_polls') && $isOwner));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('poll.poll_can_delete_own_polls');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['poll.can_post_comment_on_poll', 'poll.can_access_polls']) && (!$resource || !$resource->getIsPending());
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting('poll.can_sponsor_poll') && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = $this->isGrantedSetting('poll.can_purchase_sponsor_poll') && !$this->isGrantedSetting('poll.can_sponsor_poll') && \Phpfox::isAppActive('Core_BetterAds') && $isOwner && $resource && $resource->getCanPurchaseSponsor();
                break;
            case self::SPONSOR_IN_FEED:
                $granted = $resource && $resource->getCanSponsorInFeed() && (($isOwner && $this->isGrantedSetting('feed.can_purchase_sponsor')) || $this->isGrantedSetting('feed.can_sponsor_feed'));
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('poll.can_feature_poll');
                break;
            case self::APPROVE:
                $granted = $this->isGrantedSetting('poll.poll_can_moderate_polls') && (!$resource || $resource->getIsPending());
                break;
            case self::VOTE_WITH_CLOSE_TIME:
                $granted = $resource && (!$resource->close_time || $resource->close_time > time());
                break;
            case self::VOTE:
                $granted = $resource && !$resource->getIsPending() && !$resource->getUserVotedThisPoll()
                    && (!$isOwner || ($this->isGrantedSetting('poll.can_vote_in_own_poll')))
                    && (!$resource->close_time || $resource->close_time > time());
                break;
            case self::CHANGE_VOTE:
                $granted = $resource && !$resource->getIsPending() && $resource->getUserVotedThisPoll()
                    && $this->isGrantedSetting('poll.poll_can_change_own_vote') && (!$resource->close_time || $resource->close_time > time());
                break;
            case self::VIEW_RESULT:
                $granted = ($this->isGrantedSetting('poll.can_view_user_poll_results_other_poll')
                    || ($this->isGrantedSetting('poll.can_view_user_poll_results_own_poll') && $isOwner));
                break;
            case self::VIEW_RESULT_BEFORE_VOTE:
                $granted = $resource && $resource->getUserVotedThisPoll() == false && $this->isGrantedSetting('poll.view_poll_results_before_vote');
                break;
            case self::VIEW_RESULT_AFTER_VOTE:
                $granted = $resource && $resource->getUserVotedThisPoll() == true && $this->isGrantedSetting('poll.view_poll_results_after_vote');
                break;
            case self::VIEW_HIDE_VOTE:
                /** @var PollResource $resource */
                $granted = $resource && (!$resource->hide_vote || $isOwner);
                break;
        }

        return $granted;
    }

}