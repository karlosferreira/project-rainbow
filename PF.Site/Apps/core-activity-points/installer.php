<?php
use Apps\Core_Activity_Points\Installation\Version\v470 as v470;
use Apps\Core_Activity_Points\Installation\Version\v474 as v474;
use Apps\Core_Activity_Points\Installation\Version\v475 as v475;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v470())->process();
    (new v474())->process();
    (new v475())->process();
});
