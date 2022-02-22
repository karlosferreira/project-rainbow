<?php


namespace Apps\Core_MobileApi\Service\Helper;


class Pagination
{
    const STRATEGY_LATEST = "LATEST";
    const STRATEGY_PAGER = "PAGER";

    const DEFAULT_ITEM_PER_PAGE = 10;
    const DEFAULT_MIN_ITEM_PER_PAGE = 1;
    const DEFAULT_MAX_ITEM_PER_PAGE = 100;

    /**
     * @param string $strategy
     *
     * @return LatestStrategy|PagerStrategy
     */
    public static function strategy($strategy = self::STRATEGY_PAGER)
    {
        $pagination = null;
        switch ($strategy) {
            case self::STRATEGY_LATEST:
                $pagination = new LatestStrategy();
                break;
            case self::STRATEGY_PAGER:
            default:
                $pagination = new PagerStrategy();

        }
        return $pagination;
    }

}

class LatestStrategy
{

    private $lastId;

    public function setParam($lastId)
    {
        $this->lastId = $lastId;
        return $this;
    }

    public function getPagination()
    {
        return [
            'last_id' => $this->lastId
        ];
    }
}

class PagerStrategy
{

    private $totalItem;
    private $itemPerPage;
    private $currentPage;

    public function setParam($totalItem, $itemPerPage = Pagination::DEFAULT_ITEM_PER_PAGE, $currentPage = 1)
    {
        $this->totalItem = $totalItem;
        $this->itemPerPage = $itemPerPage;
        $this->currentPage = $currentPage;
        return $this;
    }

    public function getPagination()
    {
        return [
            'total'        => $this->totalItem,
            'limit'        => $this->itemPerPage,
            'current_page' => $this->currentPage,
            'next_page'    => ($this->currentPage + 1),
            'prev_page'    => ($this->currentPage - 1 > 0 ? $this->currentPage - 1 : null),
        ];
    }
}