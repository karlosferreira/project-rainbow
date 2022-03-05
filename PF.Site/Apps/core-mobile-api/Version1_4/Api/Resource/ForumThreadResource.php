<?php


namespace Apps\Core_MobileApi\Version1_4\Api\Resource;

use Apps\Core_MobileApi\Api\Exception\UndefinedResourceName;
use Apps\Core_MobileApi\Api\Resource\ForumPostResource;
use Apps\Core_MobileApi\Version1_4\Service\ForumPostApi;

class ForumThreadResource extends \Apps\Core_MobileApi\Api\Resource\ForumThreadResource
{
    public $post_starter;

    /**
     * @return array
     * @throws UndefinedResourceName
     */
    public function getPosts()
    {
        if (isset($this->rawData['posts'])) {
            $posts = [];
            foreach ($this->rawData['posts'] as $post) {
                if ($post['post_id'] == $this->start_id) {
                    continue;
                }
                $post['is_detail'] = true;
                $posts[] = ForumPostResource::populate($post)->loadFeedParam()->toArray();
            }
            $this->posts = $posts;
        }
        return $this->posts;
    }

    /**
     * @description getPostStarter() run before getPosts().
     * @return array|null
     */
    public function getPostStarter()
    {
        if ($this->start_id && $this->post_starter === null) {
            /** @var ForumPostResource $post */
            $postApi = (new ForumPostApi());
            $post = $postApi->loadResourceById($this->start_id, true);
            $post->extra = $postApi->getAccessControl()->getPermissions($post);
            $this->post_starter = !empty($post) ? $post->loadFeedParam()->toArray() : null;
        }
        return $this->post_starter;
    }
}