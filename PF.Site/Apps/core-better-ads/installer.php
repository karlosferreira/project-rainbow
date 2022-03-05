<?php

use Apps\Core_BetterAds\Installation\Version\v420;
use Apps\Core_BetterAds\Installation\Version\v421;
use Apps\Core_BetterAds\Installation\Version\v422;
use Apps\Core_BetterAds\Installation\Version\v424;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v420())->process();
    (new v421())->process();
    (new v422())->process();
    (new v424())->process();
});