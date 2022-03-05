<?php

use Apps\Core_RSS\Installation\Version\v453 as v453;
use Apps\Core_RSS\Installation\Version\v464 as v464;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v453())->process();
    (new v464())->process();
});
