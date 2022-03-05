<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 4/6/18
 * Time: 3:56 PM
 */

namespace Apps\Core_MobileApi\Api\Security\Comment;


use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Adapter\Utility\ArrayUtility;
use Apps\Core_MobileApi\Api\Resource\CommentResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Apps\Core_MobileApi\Service\NameResource;
use Phpfox;
use Phpfox_Error;

class CommentAccessControl extends AccessControl
{
    const ADD = "add";
    const REPLY = "reply";
    const EDIT = "edit";
    const HIDE = "hide";

    public function __construct(SettingInterface $setting, UserInterface $userContext)
    {
        parent::__construct($setting, $userContext);
        ArrayUtility::append($this->supports, [self::ADD, self::REPLY, self::EDIT, self::HIDE]);
    }

    /**
     * @param                                   $permission
     * @param CommentResource|ResourceBase|null $resource
     *
     * @return bool|mixed
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
        $granted = false;

        switch ($permission) {
            case self::VIEW:
                if ($resource) {
                    $this->setParameters(array_merge($this->getParameters(), [
                        'item_type' => $resource->getItemType(),
                        'item_id'   => $resource->getItemId()
                    ]));
                }
                $granted = $this->hasAccessCommentVar(true);
                break;
            case self::REPLY:
                $granted = $this->hasAccessReplyComment();
                break;
            case self::ADD:
                $granted = $this->hasAccessAddComment();
                break;
            case self::EDIT:
                $granted = $resource && $this->hasAccessEditComment($resource);
                break;
            case self::DELETE:
                $granted = $resource && $this->hasAccessDeleteComment($resource);
                break;
            case self::HIDE:
                $granted = $resource && (!$resource->getAuthor() || !$this->userContext->compareWith($resource->getAuthor()));
                break;
        }

        return $granted;
    }

    private function hasAccessCommentVar($isView = false)
    {
        $isGrant = true;
        $parameters = $this->getParameters();
        if (!$isView && isset($parameters['item_type']) && $parameters['item_type'] != 'app' && Phpfox::hasCallback($parameters['item_type'], 'getAjaxCommentVar')) {
            $sVar = Phpfox::callback($parameters['item_type'] . '.getAjaxCommentVar');
            if ($sVar !== null) {
                $isGrant = $this->isGrantedSetting($sVar);
            }
        }
        $isErrorPass = Phpfox_Error::isPassed();
        // Check permission to view parent item.
        if (!empty($parameters['item_type']) && !empty($parameters['item_id'])) {
            if (Phpfox::hasCallback($parameters['item_type'], 'canViewItem')) {
                if (!Phpfox::callback($parameters['item_type'] . '.canViewItem', $parameters['item_id'])) {
                    if ($isErrorPass) {
                        //Reset error set by app's callback, it's useless for API
                        Phpfox_Error::reset();
                    }
                    $isGrant = false;
                }
            } else {
                $itemType = $parameters['item_type'];
                if ($itemType == 'v') {
                    $itemType = 'video';
                }
                $item = NameResource::instance()->getPermissionByResourceName(str_replace('_', '-', $itemType), $parameters['item_id'], self::VIEW, isset($parameters['api_version_name']) ? $parameters['api_version_name'] : 'mobile');
                if ($item !== null && !$item) {
                    $isGrant = false;
                }
            }
        }
        return $isGrant;
    }

    private function hasAccessAddComment()
    {
        if (!$this->hasAccessCommentVar()
            || !$this->isGrantedSetting(['comment.can_post_comments'])) {
            return false;
        }

        $parameters = $this->getParameters();

        if (!empty($parameters['item_type'])
            && !empty($parameters['item_id'])) {
            return $this->checkCommentFlood($parameters['item_type']);
        }

        return true;
    }

    private function hasAccessReplyComment()
    {
        if (!$this->hasAccessCommentVar()) {
            return false;
        }
        return $this->isGrantedSetting(['comment.can_post_comments']) && Phpfox::getParam('comment.comment_is_threaded');
    }

    /**
     * @param ResourceBase $resource
     *
     * @return mixed
     */
    private function hasAccessEditComment(ResourceBase $resource)
    {
        $allow = Phpfox::getService('comment')->hasAccess($resource->getId(), 'edit_own_comment', 'edit_user_comment');
        return !!$allow;
    }

    /**
     * @param CommentResource $resource
     *
     * @return mixed
     */
    private function hasAccessDeleteComment(CommentResource $resource)
    {
        $allow = Phpfox::getService('comment')->hasAccess($resource->getId(), 'delete_own_comment', 'delete_user_comment') || $resource->getCanDelete();
        return !!$allow;
    }

    private function checkCommentFlood($itemType)
    {
        if ($itemType && ($flood = Phpfox::getUserParam('comment.comment_post_flood_control')) !== 0) {
            $floodParams = [
                'action' => 'last_post', // The SPAM action
                'params' => [
                    'field'      => 'time_stamp',
                    // The time stamp field
                    'table'      => Phpfox::getT('comment'),
                    // Database table we plan to check
                    'condition'  => 'type_id = "' . db()->escape($itemType) . '" AND user_id = ' . Phpfox::getUserId(),
                    // Database WHERE query
                    'time_stamp' => $flood * 60
                    // Seconds);
                ]
            ];

            // actually check if flooding
            if (Phpfox::getLib('spam')->check($floodParams)) {
                $this->setErrorMessage($this->getLocalization()->translate('posting_a_comment_a_little_too_soon_total_time', ['total_time' => Phpfox::getLib('spam')->getWaitTime()]));
                return false;
            }
        }

        return true;
    }
}