<?php
$installer = new Core\App\Installer();
$installer->onInstall(function () use ($installer) {
    (new \Apps\Core_MobileApi\Installation\Version\v410())->process();
    (new \Apps\Core_MobileApi\Installation\Version\v421())->process();
    (new \Apps\Core_MobileApi\Installation\Version\v440())->process();
    (new \Apps\Core_MobileApi\Installation\Version\v460())->process();
    (new \Apps\Core_MobileApi\Installation\Version\v464())->process();
    (new \Apps\Core_MobileApi\Installation\Version\v466())->process();
    (new \Apps\Core_MobileApi\Installation\Version\v469())->process();
});