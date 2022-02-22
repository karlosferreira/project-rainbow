<?php

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ActivityFeedInterface;
use Apps\Core_MobileApi\Api\Resource\LinkResource;
use Phpfox;

class LinkApi extends AbstractResourceApi implements ActivityFeedInterface
{

    public function __naming()
    {
        return [
            'link/fetch' => [
                'post' => 'fetch',
            ]
        ];
    }

    public function fetch($params)
    {
        $url = $this->resolver->resolveSingle($params, 'link');
        //Set to open source agent
        $_SERVER['HTTP_USER_AGENT'] = 'okhttp/3.14.9';

        $link = $this->linkService()->getLink($url);

        if ($this->isPassed() && !empty($link['title'])) {
            return $this->success(LinkResource::populate($link)->toArray(['title', 'resrouce_name', 'description', 'host', 'link', 'default_image', 'embed_code', 'duration']));
        }

        return $this->error($this->getLocalization()->translate('not_a_valid_link'));
    }

    /**
     * @return mixed|\Link_Service_Link
     */
    private function linkService()
    {
        return Phpfox::getService('link');
    }

    public function getFeedDisplay($param, $item)
    {
        return LinkResource::populate($item)->displayShortFields()->toArray();
    }

    function findAll($params = [])
    {
        // TODO: Implement findAll() method.
    }

    function findOne($params)
    {
        // TODO: Implement findOne() method.
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
        // TODO: Implement patchUpdate() method.
    }

    function delete($params)
    {
        // TODO: Implement delete() method.
    }

    function form($params = [])
    {
        // TODO: Implement form() method.
    }

    function loadResourceById($id, $returnResource = false)
    {
        $item = Phpfox::getService('link')->getLinkById($id);
        if (!$item) {
            return null;
        }
        if ($returnResource) {
            return LinkResource::populate($item);
        }
        return $item;
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