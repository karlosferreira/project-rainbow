<?php

use Apps\P_StatusBg\Installation\Version\v410 as v410;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v410())->process();
});
