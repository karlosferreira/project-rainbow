<?php
use Apps\P_SavedItems\Installation\Version\v411 as v411;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v411())->process();
});