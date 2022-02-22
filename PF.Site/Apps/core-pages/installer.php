<?php
$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new \Apps\Core_Pages\Installation\Version\v453())->process();
    (new \Apps\Core_Pages\Installation\Version\v460())->process();
    (new \Apps\Core_Pages\Installation\Version\v461())->process();
    (new \Apps\Core_Pages\Installation\Version\v470())->process();
    (new \Apps\Core_Pages\Installation\Version\v474())->process();
});
