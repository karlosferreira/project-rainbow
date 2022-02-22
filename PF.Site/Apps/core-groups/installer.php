<?php

$installer = new Core\App\Installer();
$installer->onInstall(function () {
    (new Apps\PHPfox_Groups\Installation\Version\v460())->process();
    (new Apps\PHPfox_Groups\Installation\Version\v464())->process();
    (new Apps\PHPfox_Groups\Installation\Version\v470())->process();
    (new Apps\PHPfox_Groups\Installation\Version\v474())->process();
});
