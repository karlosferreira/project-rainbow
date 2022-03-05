<?php
defined('PHPFOX') or exit('NO DICE!');
if (Phpfox::isAppActive('Core_Subscriptions') && (Phpfox::getParam('subscribe.enable_subscription_packages') || Phpfox::getService('subscribe.purchase')->hasAnyPurchases())) {
    echo '<li role="presentation">' .
            '<a href="'. Phpfox_Url::instance()->makeUrl('subscribe') .'">' .
                '<i class="fa fa-address-card-o"></i>&nbsp;' .
                _p('membership') .
            '</a>' .
        '</li>';
}