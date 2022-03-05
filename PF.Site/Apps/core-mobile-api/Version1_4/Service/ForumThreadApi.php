<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Version1_4\Service;

use Apps\Core_MobileApi\Api\Resource\Object\HyperLink;
use Apps\Core_MobileApi\Api\Security\Forum\ForumThreadAccessControl;
use Apps\Core_MobileApi\Version1_4\Api\Resource\ForumThreadResource;
use Phpfox;

class ForumThreadApi extends \Apps\Core_MobileApi\Service\ForumThreadApi
{
    function findOne($params)
    {
        $params = $this->resolver
            ->setDefined([
                'limit', 'page', 'post'
            ])
            ->setRequired(['id'])
            ->resolve(array_merge(['limit' => Phpfox::getParam('forum.total_posts_per_thread'), 'page' => 1], $params))
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->missingParamsError($this->resolver->getMissing());
        }
        if (!Phpfox::getUserParam('forum.can_view_forum')) {
            return $this->permissionError();
        }
        $conditions = 'ft.thread_id = ' . $params['id'] . '';
        $permaView = isset($params['post']) && $params['post'] > 0 ? $params['post'] : null;
        list(, $item) = $this->getThread($conditions, [],
            'fp.time_stamp ASC', $params['page'], $params['limit'], $permaView);
        if (!$item) {
            return $this->notFoundError();
        }
        if ($item['forum_id'] && (!$this->forumService->hasAccess($item['forum_id'], 'can_view_forum') || !$this->forumService->hasAccess($item['forum_id'], 'can_view_thread_content'))) {
            return $this->permissionError();
        }

        if ($item['view_id'] != '0' && $item['user_id'] != Phpfox::getUserId()) {
            if (!Phpfox::getUserParam('forum.can_approve_forum_thread') && !$this->moderatorService->hasAccess($item['forum_id'],
                    'approve_thread')
            ) {
                return $this->notFoundError();
            }
        }
        $updateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!Phpfox::getUserBy('is_invisible')) {
                if (!$item['is_seen']) {
                    $updateCounter = true;
                    Phpfox::getService('track.process')->add('forum', $item['thread_id']);
                } else {
                    if (!setting('track.unique_viewers_counter')) {
                        $updateCounter = true;
                        Phpfox::getService('track.process')->add('forum', $item['thread_id']);
                    } else {
                        Phpfox::getService('track.process')->update('forum_thread', $item['thread_id']);
                    }
                }
            }
        } else {
            $updateCounter = true;
        }
        if ($updateCounter) {
            $this->processService->updateTrack($item['thread_id'], true);
        }
        $item['is_detail'] = true;
        /** @var ForumThreadResource $resource */
        $resource = $this->populateResource(ForumThreadResource::class, $item);
        $this->setHyperlinks($resource, true);
        return $this->success($resource
            ->setExtra($this->getAccessControl()->getPermissions($resource))
            ->lazyLoad(['user'])
            ->toArray());
    }

    public function processRow($item)
    {
        /** @var ForumThreadResource $resource */
        $resource = $this->populateResource(ForumThreadResource::class, $item);
        $this->setHyperlinks($resource);

        $view = $this->request()->get('view');
        $shortFields = [];

        if (in_array($view, ['sponsor'])) {
            $shortFields = [
                'resource_name', 'title', 'user', 'statistic', 'id', 'creation_date', 'description', 'sponsor_id', 'order_id'
            ];
        }

        return $resource->setExtra($this->getAccessControl()->getPermissions($resource))->displayShortFields()->toArray($shortFields);
    }

    private function setHyperlinks(ForumThreadResource $resource, $includeLinks = false)
    {
        $resource->setSelf([
            ForumThreadAccessControl::VIEW   => $this->createHyperMediaLink(ForumThreadAccessControl::VIEW, $resource,
                HyperLink::GET, 'forum-thread/:id', ['id' => $resource->getId()]),
            ForumThreadAccessControl::EDIT   => $this->createHyperMediaLink(ForumThreadAccessControl::EDIT, $resource,
                HyperLink::GET, 'forum-thread/form/:id', ['id' => $resource->getId()]),
            ForumThreadAccessControl::DELETE => $this->createHyperMediaLink(ForumThreadAccessControl::DELETE, $resource,
                HyperLink::DELETE, 'forum-thread/:id', ['id' => $resource->getId()]),
        ]);
        if ($includeLinks) {
            $resource->setLinks([
                'likes' => $this->createHyperMediaLink(null, $resource, HyperLink::GET, 'like', [
                    'item_id'   => $resource->start_id,
                    'item_type' => 'forum_post'
                ]),
                'posts' => $this->createHyperMediaLink(ForumThreadAccessControl::VIEW, $resource,
                    HyperLink::GET, 'forum-post', ['thread' => $resource->getId()]),
            ]);
        }
    }
}