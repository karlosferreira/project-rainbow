<?php
$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new \Apps\Core_Forums\Installation\Version\v460())->process();
    (new \Apps\Core_Forums\Installation\Version\v463())->process();
});