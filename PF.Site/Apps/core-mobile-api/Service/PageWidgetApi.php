<?php
/**
 * @author  phpFox LLC
 * @license phpfox.com
 */

namespace Apps\Core_MobileApi\Service;

use Apps\Core_MobileApi\Api\AbstractResourceApi;
use Apps\Core_MobileApi\Api\Resource\PageWidgetResource;
use Apps\Core_MobileApi\Api\Resource\ResourceBase;
use Apps\Core_MobileApi\Api\Security\Page\PageAccessControl;
use Apps\Core_Pages\Service\Facade;
use Apps\Core_Pages\Service\Pages;
use Apps\Core_Pages\Service\Process;
use Phpfox;


class PageWidgetApi extends AbstractResourceApi
{
    const TYPE_MENU = 'menu';
    const TYPE_BLOCK = 'block';
    const TYPE_ALL = 'all';

    /**
     * @var Pages
     */
    private $pageService;

    /**
     * @var Process
     */
    private $processService;

    public function __construct()
    {
        parent::__construct();
        $this->pageService = Phpfox::getService('pages');
        $this->processService = Phpfox::getService('pages.process');
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function findAll($params = [])
    {
        $params = $this->resolver->setRequired(['page_id'])
            ->setDefined(['widget_type'])
            ->setDefault([
                'widget_type' => self::TYPE_ALL
            ])
            ->setAllowedValues('widget_type', [self::TYPE_ALL, self::TYPE_BLOCK, self::TYPE_MENU])
            ->resolve($params)
            ->getParameters();
        if (!$this->resolver->isValid()) {
            return $this->validationParamsError($this->resolver->getInvalidParameters());
        }
        $this->denyAccessUnlessGranted(PageAccessControl::VIEW);
        $result = $this->getWidgets($params['page_id'], $params['widget_type']);
        $this->processRows($result);
        return $this->success($result);
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function findOne($params)
    {
        $id = $this->resolver->resolveId($params);
        $this->denyAccessUnlessGranted(PageAccessControl::VIEW);
        /** @var PageWidgetResource $widget */
        $widget = $this->loadResourceById($id, true);
        if (empty($widget)) {
            return $this->notFoundError();
        }
        return $this->success($widget->toArray());
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function create($params)
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function update($params)
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function patchUpdate($params)
    {
        return null;
    }

    /**
     * @param $params
     *
     * @return mixed
     */
    function delete($params)
    {
        return null;
    }

    /**
     * @param array $params
     *
     * @return mixed
     */
    function form($params = [])
    {
        return null;
    }

    /**
     * @param $id
     *
     * @param bool $returnResource
     * @return mixed
     */
    function loadResourceById($id, $returnResource = false)
    {
        $iItemType = $this->getFacade()->getItemTypeId();
        $widget = $this->database()->select('pw.*, pwt.text_parsed AS text')
            ->from(':pages_widget', 'pw')
            ->join(':pages', 'p', "p.page_id = pw.page_id AND p.item_type = {$iItemType}")
            ->join(':pages_widget_text', 'pwt', 'pwt.widget_id = pw.widget_id')
            ->where('pw.widget_id = ' . (int)$id)
            ->order('pw.ordering ASC')
            ->executeRow();
        if (empty($widget['widget_id'])) {
            return null;
        }
        if ($returnResource) {
            return PageWidgetResource::populate($widget);
        }
        return $widget;
    }

    public function processRow($item)
    {
        return PageWidgetResource::populate($item)->setViewMode(ResourceBase::VIEW_LIST)->displayShortFields()->toArray();
    }

    function approve($params)
    {
        return null;
    }

    function feature($params)
    {
        return null;
    }

    function sponsor($params)
    {
        return null;
    }

    public function getWidgets($pageId, $type = null)
    {
        $cacheId = $this->cache()->set('pages_' . $pageId . '_widgets');
        if (($widgets = $this->cache()->get($cacheId)) === false) {
            $iItemType = $this->getFacade()->getItemTypeId();
            $widgets = $this->database()->select('pw.*, pwt.text_parsed AS text')
                ->from(':pages_widget', 'pw')
                ->join(':pages', 'p', "p.page_id = pw.page_id AND p.item_type = {$iItemType}")
                ->join(':pages_widget_text', 'pwt', 'pwt.widget_id = pw.widget_id')
                ->where('pw.page_id = ' . (int)$pageId)
                ->order('pw.ordering ASC')
                ->execute('getSlaveRows');
            $this->cache()->save($cacheId, $widgets);
        }
        $widgetMenus = [];
        $widgetBlocks = [];
        foreach ($widgets as $widget) {
            if (!$widget['is_block']) {
                $widget['url'] = $this->pageService->getUrl($widget['page_id']) . $widget['url_title'] . '/';
                $widgetMenus[] = $widget;
            } else {
                $widgetBlocks[] = $widget;
            }
        }
        switch ($type) {
            case self::TYPE_BLOCK:
                $results = $widgetBlocks;
                break;
            case self::TYPE_MENU:
                $results = $widgetMenus;
                break;
            default:
                $results = array_merge($widgetMenus, $widgetBlocks);
                break;
        }
        return $results;
    }

    public function createAccessControl()
    {
        $this->accessControl = new PageAccessControl($this->getSetting(), $this->getUser());
    }

    /**
     * @return Facade|mixed
     */
    private function getFacade()
    {
        return Phpfox::getService('pages.facade');
    }

}