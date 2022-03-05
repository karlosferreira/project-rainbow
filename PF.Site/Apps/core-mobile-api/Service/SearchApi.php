<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\SearchResource;
use Apps\Core_MobileApi\Service\Helper\Pagination;
use Phpfox;
use Search_Service_Search;

class SearchApi extends AbstractResourceApi
{
    /**
     * @var Search_Service_Search
     */
    private $searchService;

    public function __construct()
    {
        parent::__construct();
        $this->searchService = Phpfox::getService('search');
    }

    function findAll($params = [])
    {
        $params = $this->resolver->setDefined([
            'view', 'history', 'q', 'page', 'limit'
        ])
            ->setAllowedTypes('limit', 'int', [
                'min' => Pagination::DEFAULT_MIN_ITEM_PER_PAGE,
                'max' => Pagination::DEFAULT_MAX_ITEM_PER_PAGE
            ])
            ->setAllowedTypes('page', 'int')
            ->setRequired(['q'])
            ->setDefault([
                'limit' => Pagination::DEFAULT_ITEM_PER_PAGE,
                'page'  => 1
            ])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $minCharacter = Phpfox::getParam('core.min_character_to_search', 2);
        if (mb_strlen($params['q']) < $minCharacter) {
            return $this->error($this->getLocalization()->translate('please_try_to_search_with_at_latest_min_characters', ['min' => $minCharacter]));
        }

        $history = $params['history'];
        $results = $this->searchService->query($params['q'], $params['page'], $params['limit'], $params['view']);

        if (empty($history)) {
            $history = '';
            foreach ($results as $result) {
                if (isset($aSearchTypes[$result['item_type_id']])) {
                    continue;
                }

                $aSearchTypes[$result['item_type_id']] = true;
                $history .= $result['item_type_id'] . ',';
            }
            $history = rtrim($history, ',');
        }
        $menus = Phpfox::massCallback('getSearchTitleInfo');
        $rows = [
            'menu'    => $menus,
            'history' => $history,
            'results' => $results
        ];
        $this->processRows($rows);

        return $this->success($rows['results']);
    }

    /**
     * Process list of blog
     *
     * @param $aRows
     */
    public function processRows(&$aRows)
    {
        //Process menu
        $menus = [];
        if (!empty($aRows['menu'])) {
            foreach ($aRows['menu'] as $mKey => $menu) {
                $menus[] = [
                    'id'   => $mKey,
                    'name' => $menu['name']
                ];
            }
        }
        $aRows['menu'] = $menus;
        //Process result
        foreach ($aRows['results'] as $key => $result) {
            /*
             * @todo $result is no longer has $result['profile_page_id'] != 0;
             */
            if ($result['item_type_id'] && $result['item_type_id'] == 'user' && !empty($result['profile_page_id'])) {
                $result = $this->checkResultUser($result);
            }
            $aRows['results'][$key] = $this->processRow($result);
        }
    }

    public function processRow($item)
    {
        return SearchResource::populate($item)->toArray();
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
        // TODO: Implement updateAll() method.
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
        // TODO: Implement loadResourceById() method.
    }

    /**
     * @codeCoverageIgnore
     * @param $data
     *
     * @return mixed
     */
    private function checkResultUser($data)
    {
        $pageId = $data['profile_page_id'];
        $page = $this->database()->select('page_id, item_type')
            ->from(':pages')
            ->where('page_id =' . (int)$pageId)
            ->execute('getRow');
        $result = $data;
        if (!$page) {
            return $result;
        }
        $result['item_type_id'] = $page['item_type'] ? 'groups' : 'pages';
        $result['item_name'] = $this->getLocalization()->translate($page['item_type'] ? 'groups' : 'pages');
        $result['item_user_id'] = $page['page_id'];
        $result['item_id'] = $page['page_id'];
        return $result;
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