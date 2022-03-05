<?php
use Apps\Core_Messages\Installation\Version\v470 as v470;
use Apps\Core_Messages\Installation\Version\v471 as v471;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v470())->process();
    (new v471())->process();
});