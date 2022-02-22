<?php
/**
 * Created by PhpStorm.
 * User: pro
 * Date: 9/8/18
 * Time: 3:28 PM
 */

namespace Apps\Core_MobileApi\Api\Resource\FeedEmbed;


abstract class FeedEmbed
{
    protected $feedData;

    public function __construct(&$feedData)
    {
        $this->feedData = $feedData;
    }

    abstract public function toArray();
}