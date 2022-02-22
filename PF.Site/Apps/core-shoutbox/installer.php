<?php
use Apps\phpFox_Shoutbox\Installation\Version\v430 as v430;
use Apps\phpFox_Shoutbox\Installation\Version\v432 as v432;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v430())->process();
    (new v432())->process();
});