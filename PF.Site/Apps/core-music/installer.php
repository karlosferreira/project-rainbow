<?php
$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new \Apps\Core_Music\Installation\Version\v453())->process();
    (new \Apps\Core_Music\Installation\Version\v463())->process();
    (new \Apps\Core_Music\Installation\Version\v467())->process();
});