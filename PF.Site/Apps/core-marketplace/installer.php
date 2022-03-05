<?php
$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new \Apps\Core_Marketplace\Installation\Version\v460())->process();
    (new \Apps\Core_Marketplace\Installation\Version\v462())->process();
    (new \Apps\Core_Marketplace\Installation\Version\v463())->process();
    (new \Apps\Core_Marketplace\Installation\Version\v464())->process();
});