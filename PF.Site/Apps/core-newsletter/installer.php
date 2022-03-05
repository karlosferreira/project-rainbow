<?php

use \Apps\Core_Newsletter\Installation\Version\v453 as v453;
use \Apps\Core_Newsletter\Installation\Version\v462 as v462;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v453())->process();
    (new v462())->process();
});
