<?php


namespace Apps\Core_MobileApi\Api;

/**
 * Interface ActivityFeedInterface
 * @package Apps\Core_MobileApi\Api
 */
interface ActivityFeedInterface
{
    /**
     * Get for display on activity feed
     *
     * @param $param
     * @param $item array of data get from database
     *
     * @return array
     */
    public function getFeedDisplay($param, $item);
}