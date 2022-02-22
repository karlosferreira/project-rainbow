<?php

namespace Apps\Core_BetterAds\Installation\Version;
defined('PHPFOX') or exit('NO DICE!');

/**
 * Class v421
 * @package Apps\Core_BetterAds\Installation\Version
 */
class v422
{
    public function process()
    {
        $bHasMenuLink = db()->select('menu_id')
            ->from(':menu')
            ->where('m_connection = \'footer\' AND module_id = \'ad\' AND url_value = \'ad.manage\'')
            ->limit(1)
            ->executeField();

        if (empty($bHasMenuLink)) {
            db()->insert(':menu', array(
                'm_connection' => 'footer',
                'module_id' => 'ad',
                'product_id' => 'phpfox',
                'var_name' => 'menu_ad',
                'is_active' => 1,
                'ordering' => 23,
                'url_value' => 'ad.manage',
                'version_id' => '4.2.2'
            ));
        }
    }
}