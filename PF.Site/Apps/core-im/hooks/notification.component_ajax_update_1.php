<?php
if (setting('pf_im_chat_server', 'nodejs') == 'nodejs') {
    $this->call('$Core_IM.start_im();');
}