<?php
namespace Apps\phpFox_Shoutbox;

use Phpfox_Module;

Phpfox_Module::instance()
    ->addServiceNames([
        'shoutbox.callback' => '\Apps\phpFox_Shoutbox\Service\Callback',
        'shoutbox.process' => '\Apps\phpFox_Shoutbox\Service\Process',
        'shoutbox.get' => '\Apps\phpFox_Shoutbox\Service\Get',
    ])
    ->addTemplateDirs([
        'shoutbox' => (new Install())->path . PHPFOX_DS . 'views',
    ])
    ->addAliasNames('shoutbox', 'phpFox_Shoutbox')
    ->addComponentNames('block', [
        'shoutbox.chat' => '\Apps\phpFox_Shoutbox\Block\Chat',
        'shoutbox.edit-message' => '\Apps\phpFox_Shoutbox\Block\EditMessage'
    ])
    ->addComponentNames('controller', [
        'shoutbox.polling' => Controller\PollingController::class,
        'shoutbox.view' => Controller\ViewController::class
    ])
    ->addComponentNames('ajax', [
        'phpFox_Shoutbox.ajax' => '\Apps\phpFox_Shoutbox\Ajax\Ajax',
        'shoutbox.ajax' => '\Apps\phpFox_Shoutbox\Ajax\Ajax',
    ]);

route('/shoutbox/polling/', 'shoutbox.polling');
route('/shoutbox/view/', 'shoutbox.view');