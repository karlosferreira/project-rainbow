<?php
ignore_user_abort(true);
/**
 * Key to include phpFox
 *
 */
define('PHPFOX', true);
/**
 * Directory Separator
 *
 */
define('PHPFOX_DS', DIRECTORY_SEPARATOR);
/**
 * phpFox Root Directory
 *
 */
define('PHPFOX_DIR', dirname(dirname(dirname(dirname(__FILE__)))) . PHPFOX_DS . 'PF.Base' . PHPFOX_DS);
/**
 * No SESSIONS
 *
 */
define('PHPFOX_NO_SESSION', true);
/**
 * Do not set user sessions
 *
 */
define('PHPFOX_NO_USER_SESSION', true);
/**
 * Do not run
 */
define('PHPFOX_NO_RUN', true);
define('PHPFOX_CRON', true);
// Require all phpfox methods
require PHPFOX_DIR . 'start.php';
(new \Apps\Core_Photos\Job\RemoveTemporaryPhotos())->perform();