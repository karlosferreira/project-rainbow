<?php

namespace Apps\Core_MobileApi\Api;

interface ReducerInterface
{

    /**
     * Fetch All data and temporary store in memory for query
     *
     * @param $conditions
     *
     * @return array|mixed
     */
    function reduceFetchAll($conditions);

    /**
     * Query fetched data
     *
     * @param $condition
     *
     * @return array|mixed
     */
    function reduceQuery($condition);
}