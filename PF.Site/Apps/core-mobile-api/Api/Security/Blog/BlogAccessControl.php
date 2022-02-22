<?php

namespace Apps\Core_MobileApi\Api\Security\Blog;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\BlogResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;


class BlogAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";

    const PUBLISH = "publish";
    const FEATURE = "feature";
    const APPROVE = "approve";
    const SPONSOR = "sponsor";
    const SPONSOR_IN_FEED = "sponsor_in_feed";
    const PURCHASE_SPONSOR = 'purchase_sponsor';

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::EDIT, self::COMMENT,
            self::PUBLISH, self::FEATURE, self::APPROVE, self::SPONSOR, self::SPONSOR_IN_FEED, self::PURCHASE_SPONSOR
        ]);
    }

    /**
     * @inheritdoc
     *
     * @param $resource BlogResource
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
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }

        /** @var BlogResource $resource */
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('blog.view_blogs');
                $parameters = $this->getParameters();
                if ($granted && !empty($parameters['view'])) {
                    if ($parameters['view'] == 'spam' || $parameters['view'] == 'pending') {
                        $granted = $this->isGrantedSetting('blog.can_approve_blogs');
                    } else if ($parameters['view'] == 'my') {
                        $granted = parent::isGranted(self::IS_AUTHENTICATED);
                    }
                }
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('blog.add_new_blog');
                break;
            case self::EDIT:
                $granted = ($this->isGrantedSetting('blog.edit_user_blog')
                    || ($this->isGrantedSetting('blog.edit_own_blog') && $isOwner));
                break;
            case self::DELETE:
                $granted = ($this->isGrantedSetting('blog.delete_user_blog')
                    || ($this->isGrantedSetting('blog.delete_own_blog') && $isOwner) || ($this->appContext && $this->appContext->isAdmin($this->userContext->getId())));
                break;
            case self::DELETE_OWN:
                $granted = $this->isGrantedSetting('blog.delete_own_blog');
                break;
            case self::COMMENT:
                $granted = $this->isGrantedSetting(['blog.view_blogs', 'blog.can_post_comment_on_blog']) && (!$resource || !$resource->getIsPending());
                break;
            case self::APPROVE:
                $granted = $this->isGrantedSetting(['blog.can_approve_blogs']) && (!$resource || $resource->getIsPending());
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting(['blog.can_feature_blog']);
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting(['blog.can_sponsor_blog']) && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = \Phpfox::isAppActive('Core_BetterAds') && $resource && $resource->getCanPurchaseSponsor() && !$this->isGrantedSetting('blog.can_sponsor_blog');
                break;
            case self::SPONSOR_IN_FEED:
                $granted = $resource && $resource->getCanSponsorInFeed();
                break;
            case self::PUBLISH:
                $granted = $resource && $isOwner && $resource->getIsDraft();
                break;
        }

        // Check Pages/Group permission
        if ($granted && $this->appContext) {
            switch ($permission) {
                case self::VIEW:
                    $granted = $this->appContext->hasPermission('blog.view_browse_blogs');
                    break;
                case self::ADD:
                    $granted = ($this->appContext->hasPermission('blog.share_blogs')
                        && $this->appContext->hasPermission('blog.view_browse_blogs'));
                    break;
            }
        }

        return $granted;
    }

}