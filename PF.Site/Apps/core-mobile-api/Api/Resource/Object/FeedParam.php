<?php

namespace Apps\Core_MobileApi\Api\Resource\Object;


class FeedParam
{
    public $item_id;

    public $comment_type_id;
    public $total_comment;

    public $like_type_id;
    public $total_like;

    public $feed_title;
    public $feed_link;
    public $feed_is_liked;
    public $feed_is_friend;

    public $report_module;

    public function toArray()
    {
        return (array)$this;
    }

    /**
     * @param mixed $item_id
     *
     * @return FeedParam
     */
    public function setItemId($item_id)
    {
        $this->item_id = $item_id;
        return $this;
    }

    /**
     * @param mixed $comment_type_id
     *
     * @return FeedParam
     */
    public function setCommentTypeId($comment_type_id)
    {
        $this->comment_type_id = $comment_type_id;
        return $this;
    }

    /**
     * @param mixed $total_comment
     *
     * @return FeedParam
     */
    public function setTotalComment($total_comment)
    {
        $this->total_comment = $total_comment;
        return $this;
    }

    /**
     * @param mixed $like_type_id
     *
     * @return FeedParam
     */
    public function setLikeTypeId($like_type_id)
    {
        $this->like_type_id = $like_type_id;
        return $this;
    }

    /**
     * @param mixed $total_like
     *
     * @return FeedParam
     */
    public function setTotalLike($total_like)
    {
        $this->total_like = $total_like;
        return $this;
    }

    /**
     * @param mixed $feed_is_liked
     *
     * @return FeedParam
     */
    public function setFeedIsLiked($feed_is_liked)
    {
        $this->feed_is_liked = $feed_is_liked;
        return $this;
    }

    /**
     * @param mixed $feed_is_friend
     *
     * @return FeedParam
     */
    public function setFeedIsFriend($feed_is_friend)
    {
        $this->feed_is_friend = $feed_is_friend;
        return $this;
    }

    /**
     * @param mixed $report_module
     *
     * @return FeedParam
     */
    public function setReportModule($report_module)
    {
        $this->report_module = $report_module;
        return $this;
    }

    /**
     * @param mixed $feed_title
     *
     * @return FeedParam
     */
    public function setFeedTitle($feed_title)
    {
        $this->feed_title = $feed_title;
        return $this;
    }

    /**
     * @param mixed $feed_link
     *
     * @return FeedParam
     */
    public function setFeedLink($feed_link)
    {
        $this->feed_link = $feed_link;
        return $this;
    }


}