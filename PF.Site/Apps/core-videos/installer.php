<?php
$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new \Apps\PHPfox_Videos\Installation\Version\v452())->process();
    (new \Apps\PHPfox_Videos\Installation\Version\v453())->process();
    (new \Apps\PHPfox_Videos\Installation\Version\v454())->process();
    (new \Apps\PHPfox_Videos\Installation\Version\v460())->process();
    (new \Apps\PHPfox_Videos\Installation\Version\v461())->process();
    (new \Apps\PHPfox_Videos\Installation\Version\v470())->process();
    (new \Apps\PHPfox_Videos\Installation\Version\v479())->process();
    (new \Apps\PHPfox_Videos\Installation\Version\v4710())->process();
    Phpfox::getLib('database')->delete(':module', 'module_id="PHPfox_Videos"');
});
