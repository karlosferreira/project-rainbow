<?php

namespace Apps\Core_MobileApi\Api\Security\Group;

use Apps\Core_MobileApi\Adapter\Setting\SettingInterface;
use Apps\Core_MobileApi\Api\Resource\GroupResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\AccessControl;
use Apps\Core_MobileApi\Api\Security\UserInterface;
use Phpfox;


class GroupAccessControl extends AccessControl
{
    const EDIT = "edit";
    const ADD = "add";
    const APPROVE = 'approve';
    const FEATURE = 'feature';
    const SPONSOR = 'sponsor';
    const PURCHASE_SPONSOR = 'purchase_sponsor';
    const ADD_COVER = "add_cover";
    const REMOVE_COVER = "remove_cover";
    const VIEW_PUBLISH_DATE = "view_publish_date";
    const DELETE_MEMBER = 'delete_member';
    const DELETE_ADMIN = 'delete_admin';
    const VIEW_MEMBER = 'view_member';
    const VIEW_ADMIN = 'view_admin';
    const REASSIGN_OWNER = 'reassign_owner';

    public function __construct(SettingInterface $setting, UserInterface $context)
    {
        parent::__construct($setting, $context);

        $this->supports = $this->mergePermissions([
            self::ADD, self::DELETE, self::EDIT, self::VIEW, self::APPROVE, self::PURCHASE_SPONSOR, self::VIEW_MEMBER,
            self::FEATURE, self::SPONSOR, self::ADD_COVER, self::VIEW_PUBLISH_DATE, self::REMOVE_COVER, self::DELETE_MEMBER,
            self::DELETE_ADMIN, self::VIEW_ADMIN, self::REASSIGN_OWNER
        ]);
    }

    /**
     * @inheritdoc
     */
    public function isGranted($permission, ResourceBase $resource = null)
    {
        if (in_array($permission, [self::IS_AUTHENTICATED, self::SYSTEM_ADMIN])) {
            return parent::isGranted($permission);
        }

        /** @var $resource GroupResource */
        if (in_array($permission, [self::LIKE, self::SHARE, self::REPORT])) {
            if ($resource && $permission == self::SHARE && $resource->reg_method == 2) {
                return false;
            }
            return parent::isGranted($permission, $resource);
        }

        if (!parent::isGranted($permission, $resource) && $permission != self::VIEW) {
            return false;
        }
        $isOwner = false;
        // Item Owner always able to do any permission
        /** @var $resource GroupResource */
        if ($resource instanceof ResourceBase) {
            if ($this->userContext->compareWith($resource->getAuthor())) {
                $isOwner = true;
            }
        }
        $granted = false;
        switch ($permission) {
            case self::VIEW:
                $granted = $this->isGrantedSetting('groups.pf_group_browse') &&
                    (!$resource || !$this->setting->getAppSetting('core.friends_only_community') || (Phpfox::isModule('like') && Phpfox::getService('like')->didILike('groups', $resource->id)));;
                break;
            case self::ADD:
                $granted = $this->isGrantedSetting('groups.pf_group_add');
                break;
            case self::EDIT:
                $granted = $this->isGrantedSetting('groups.can_edit_all_groups') || ($resource && Phpfox::getService('groups')->isAdmin($resource->id));
                break;
            case self::DELETE:
                $granted = $this->isGrantedSetting('groups.can_delete_all_groups') || $isOwner;
                break;
            case self::DELETE_OWN:
                $granted = true;
                break;
            case self::APPROVE:
                $granted = $this->isGrantedSetting('groups.can_approve_groups');
                break;
            case self::FEATURE:
                $granted = $this->isGrantedSetting('groups.can_feature_group') && (!$resource || !$resource->getIsPending());
                break;
            case self::SPONSOR:
                $granted = $this->isGrantedSetting('groups.can_sponsor_groups') && (!$resource || !$resource->getIsPending()) && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::PURCHASE_SPONSOR:
                $granted = $resource && $resource->getCanPurchaseSponsor() && !$this->isGrantedSetting('groups.can_sponsor_groups') && $this->isGrantedSetting('groups.can_purchase_sponsor_groups') && !$resource->getIsPending() && \Phpfox::isAppActive('Core_BetterAds');
                break;
            case self::ADD_COVER:
                $granted = $this->isGrantedSetting('groups.pf_group_add_cover_photo');
                break;
            case self::REMOVE_COVER:
                $granted = $resource && $this->isGrantedSetting('groups.pf_group_add_cover_photo') && $resource->cover_photo_id;
                break;
            case self::VIEW_PUBLISH_DATE:
                $granted = $resource && Phpfox::getService("groups")->hasPerm($resource->getId(), 'groups.view_publish_date');
                break;
            case self::DELETE_MEMBER:
                $granted = $resource && Phpfox::getService('groups')->isAdmin($resource->id);
                break;
            case self::VIEW_MEMBER:
                $granted = $resource && $resource->getCanViewMember();
                break;
            case self::VIEW_ADMIN:
                $granted = $resource && Phpfox::getService('groups')->hasPerm($resource->getId(), 'groups.view_admins');
                break;
            case self::DELETE_ADMIN:
                $granted = $resource && $isOwner;
                break;
            case self::REASSIGN_OWNER:
                $granted = $resource && ($isOwner || Phpfox::isAdmin());
                break;
        }

        return $granted;
    }

}