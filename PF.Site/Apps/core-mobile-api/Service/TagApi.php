<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 27/4/18
 * Time: 10:57 AM
 */

namespace Apps\Core_MobileApi\Service;


use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\ReducerInterface;
use Apps\Core_MobileApi\Api\Resource\TagResource;

class TagApi extends AbstractResourceApi implements ReducerInterface
{

    /**
     * @var TagResource[]
     */
    private $fetchedTags;

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    function findAll($params = [])
    {
        // TODO: Implement findAll() method.
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

    /**
     * Fetch Tags by category_id and item_id[]
     *
     * @param array $conditions 'category_id' and 'item_id'
     *
     * @return array|mixed
     */
    function reduceFetchAll($conditions)
    {
        $conditions = $this->resolver->clearConfigure()
            ->setRequired(['category_id', 'item_id'])
            ->setAllowedTypes('category_id', 'string')
            ->setAllowedTypes('item_id', 'array')
            ->resolve($conditions)
            ->getParameters();

        if (!$this->resolver->isValid()) {
            return;
        }

        $where = [
            'AND category_id = "' . $conditions['category_id'] . '"',
            'AND item_id in (' . implode(',', $conditions['item_id']) . ')'
        ];
        $this->fetchedTags = $this->database()->select("*")
            ->from(":tag")
            ->where($where)
            ->execute("getSlaveRows");

        $this->processRows($this->fetchedTags);
    }

    /**
     * Query fetched data
     *
     * @param array $condition
     *
     * @return array|mixed
     */
    function reduceQuery($condition)
    {
        $condition = $this->resolver->clearConfigure()
            ->setRequired(['category_id', 'item_id'])
            ->resolve($condition)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return null;
        }
        $result = [];

        /** @var TagResource $fetchedTag */
        foreach ($this->fetchedTags as $fetchedTag) {
            if ($fetchedTag->category_id == $condition['category_id']
                && $fetchedTag->item_id == $condition['item_id']) {
                $result[] = $fetchedTag;
            }
        }

        return $result;
    }

    /**
     * Get tag by item type and item id
     *
     * @param string $category
     * @param int    $itemId
     *
     * @return TagResource[]
     */
    public function getTagsBy($category, $itemId)
    {
        $where = [
            'AND category_id = "' . $category . '"',
            'AND item_id =' . (int)$itemId
        ];
        $tags = $this->database()->select("*")
            ->from(":tag")
            ->where($where)
            ->execute("getSlaveRows");

        $this->processRows($tags);
        return $tags;
    }

    /**
     * Convert to resource object
     *
     * @param array $rows
     */
    public function processRows(&$rows)
    {
        $rows = array_map(function ($item) {
            return $this->populateResource(TagResource::class, $item)->displayShortFields();
        }, $rows);
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