<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_4\Service;

use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\Forum\ForumAnnouncementAccessControl;
use Apps\Core_MobileApi\Version1_4\Api\Resource\ForumAnnouncementResource;
use Phpfox;

class ForumAnnouncementApi extends \Apps\Core_MobileApi\Service\ForumAnnouncementApi
{
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
        $conditions = 'ft.thread_id = ' . $id . '';
        list(, $item) = Phpfox::getService('forum.thread')->getThread($conditions, [],
            'fp.time_stamp ASC');
        if (!$item || !$item['is_announcement']) {
            return $this->notFoundError();
        }
        $item['is_detail'] = true;
        /** @var ForumAnnouncementResource $resource */
        $resource = $this->populateResource(ForumAnnouncementResource::class, $item);

        $this->setHyperlinks($resource, true);

        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->lazyLoad(['user'])
            ->toArray());
    }

    public function processRow($item)
    {
        /** @var ForumAnnouncementResource $resource */
        $resource = $this->populateResource(ForumAnnouncementResource::class, $item);
        $this->setHyperlinks($resource);
        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray();
    }

    private function setHyperlinks(ForumAnnouncementResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            ForumAnnouncementAccessControl::VIEW   => $this->createHyperMediaLink(ForumAnnouncementAccessControl::VIEW, $resource,
                HyperLink::GET, 'forum-announcement/:id', ['id' => $resource->getId()]),
            ForumAnnouncementAccessControl::DELETE => $this->createHyperMediaLink(ForumAnnouncementAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'forum-announcement/:id', ['id' => $resource->getId()]),
            ForumAnnouncementAccessControl::EDIT   => $this->createHyperMediaLink(ForumAnnouncementAccessControl::EDIT, $resource,
                HyperLink::GET, 'forum-announcement/form/:id', ['id' => $resource->getId()]),
        ]);

        if ($includeLinks) {
            $resource->setLinks([
                'likes' => $this->createHyperMediaLink(null, $resource, HyperLink::GET, 'like', ['item_id' => $resource->getPostStarter()['id'], 'item_type' => 'forum_post']),
            ]);
        }
    }
}