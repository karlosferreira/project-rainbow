<?php
if ($sClass == 'feed.display' && !defined('PHPFOX_IS_PAGES_VIEW')) {
    defined('PHPFOX_CHECK_FEEDS_FOR_PAGES') || define('PHPFOX_CHECK_FEEDS_FOR_PAGES', true);
}