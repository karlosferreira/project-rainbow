<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 11/4/18
 * Time: 11:16 AM
 */

namespace Apps\Core_MobileApi\Api;


use Apps\Core_MobileApi\Service\AbstractApi;
use Apps\Core_MobileApi\Service\Helper\BrowseHelper;
use Apps\Core_MobileApi\Service\Helper\SearchHelper;
use Symfony\Component\HttpFoundation\Request;

abstract class AbstractResourceApi extends AbstractApi implements ResourceInterface
{

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get list of documents, filter by
     *
     * @param array $params
     *
     * @return array|mixed
     * @throws \Exception
     */
    abstract function findAll($params = []);

    /**
     * Find detail one document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    abstract function findOne($params);

    /**
     * Create new document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    abstract function create($params);

    /**
     * Update existing document
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    abstract function update($params);

    /**
     * Update multiple document base on document query
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    abstract function patchUpdate($params);

    /**
     * Delete a document
     * DELETE: /resource-name/:id
     *
     * @param $params
     *
     * @return mixed
     * @throws \Exception
     */
    abstract function delete($params);

    /**
     * Get Create/Update document form
     *
     * @param array $params
     *
     * @return mixed
     * @throws \Exception
     */
    abstract function form($params = []);

    /**
     * Approve pending item
     * PUT: /resource-name/approve/:id
     *
     * @param $params
     *
     * @return mixed
     */
    abstract function approve($params);

    /**
     * Feature / Un-feature item
     * PUT: /resource-name/feature/:id
     *
     * @param $params
     *
     * @return mixed
     */
    abstract function feature($params);

    /**
     * Sponsor / Un-sponsor item
     * PUT: /resource-name/sponsor/:id
     *
     * @param $params
     *
     * @return mixed
     */
    abstract function sponsor($params);

    /**
     * Load item resource by id
     *
     * @param      $id
     * @param bool $returnResource
     *
     * @return mixed
     */
    abstract function loadResourceById($id, $returnResource = false);

    /**
     * Manage API request parameters
     * @return ApiRequestInterface|Request
     */
    protected function request()
    {
        return $this->psrRequest;
    }

    /**
     * Server Lib for browse search
     * @return SearchHelper
     */
    protected function search()
    {
        return \Phpfox::getService('mobile.helper.search');
    }

    /**
     * Browser helper
     * @return BrowseHelper
     */
    protected function browse()
    {
        return $this->_oBrowse;
    }

    /**
     * @return array
     */
    public function getActions()
    {
        return [];
    }

    /**
     * @param $param
     *
     * @return array
     */
    public function getScreenSetting($param)
    {
        return [];
    }

    /**
     * Moderation items
     * PUT: /resource-name/moderation
     *
     * @param $params
     *
     * @return mixed
     *
     * @codeCoverageIgnore
     */
    public function moderation($params)
    {
        return $params;
    }
}