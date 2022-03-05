<?php

use Apps\Core_Blogs\Installation\Version\v453 as v453;
use Apps\Core_Blogs\Installation\Version\v464 as v464;
use Apps\Core_Blogs\Installation\Version\v468 as v468;

$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new v453())->process();
    (new v464())->process();
    (new v468())->process();
});
