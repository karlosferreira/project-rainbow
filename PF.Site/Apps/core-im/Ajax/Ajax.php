<?php

namespace Apps\PHPfox_IM\Ajax;

class Ajax extends \Phpfox_Ajax
{
    public function toogleHosting()
    {
        $status_key = 'im_host_status';
        storage()->del($status_key);
        storage()->set($status_key, strtolower($this->get('status')));
    }
}