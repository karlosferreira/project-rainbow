<?php

use Apps\Core_Subscriptions\Installation\Version\v460 as v460;
use Apps\Core_Subscriptions\Installation\Version\v461 as v461;
use Apps\Core_Subscriptions\Installation\Version\v462 as v462;
use Apps\Core_Subscriptions\Installation\Version\v463 as v463;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v460())->process();
    (new v461())->process();
    (new v462())->process();
    (new v463())->process();
});
