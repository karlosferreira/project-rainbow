<?php

use Apps\Core_Comments\Installation\Data\v410 as v410;
use Apps\Core_Comments\Installation\Data\v412 as v412;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v410())->process();
    (new v412())->process();
});
