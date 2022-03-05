<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 27/4/18
 * Time: 10:57 AM
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\UserResource;
use Apps\Core_MobileApi\Api\Security\AccessControl;

class FriendTagApi extends AbstractResourceApi
{

    public function __naming()
    {
        return [
            'friend/tag' => [
                'get' => 'findAll',
            ]
        ];
    }

    /**
     * Get all tagged friend of a post
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        $params = $this->resolver
            ->setRequired(['item_type', 'item_id'])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $this->denyAccessUnlessGranted(AccessControl::IS_AUTHENTICATED);

        $friends = $this->database()->select('td.*, ' . \Phpfox::getUserField())
            ->from(':feed_tag_data', 'td')
            ->join(':user', 'u', 'u.user_id = td.user_id')
            ->where('td.item_id = ' . (int)$params['item_id'] . ' AND td.type_id = \'' . $params['item_type'] . '\'')
            ->execute('getSlaveRows');

        $result = [];
        foreach ($friends as $friend) {
            $result[] = UserResource::populate($friend)->displayShortFields()->toArray();
        }

        return $this->success($result);
    }

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function findOne($params)
    {
        // TODO: Implement findOne() method.
    }

    /**
     * Create new document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function create($params)
    {
        // TODO: Implement create() method.
    }

    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function update($params)
    {
        // TODO: Implement update() method.
    }

    /**
     * Update multiple document base on document query
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function patchUpdate($params)
    {
        // TODO: Implement updateAll() method.
    }

    /**
     * Delete a document
     * DELETE: /resource-name/:id
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    function delete($params)
    {
        // TODO: Implement delete() method.
    }

    /**
     * Get Create/Update document form
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    function form($params = [])
    {
        // TODO: Implement form() method.
    }

    function loadResourceById($id, $returnResource = false)
    {
        // TODO: Implement loadResourceById() method.
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