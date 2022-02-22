<?php

namespace Apps\Core_BetterAds\Installation\Version;
use Phpfox;
defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v421
 * @package Apps\Core_BetterAds\Installation\Version
 */
class v421
{
    public function process()
    {
        db()->update(':module', ['is_active' => '1'], ['module_id' => 'ad']);
        db()->update(Phpfox::getT('apps'), ['apps_alias' => 'ad', 'apps_name' => 'Ad'], ['apps_id' => 'Core_BetterAds']);
        db()->delete(':user_group_setting', ['module_id' => 'betterads']);
        db()->delete(':setting', ['module_id' => 'betterads']);
    }
}