<?php
namespace Apps\Core_Subscriptions\Controller;

use Phpfox;
use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

class IndexController extends Phpfox_Component
{
    public function process()
    {
        Phpfox::isUser(true);
        if(!setting('subscribe.enable_subscription_packages')) {
            $this->url()->send('subscribe.list');
        }

        $aPackages = Phpfox::getService('subscribe')->getPackages();

        if (!empty($aPackages)) {
            $aSubscriptionsIdPurchasedByUser = Phpfox::getService('subscribe.purchase')->getSubscriptionsIdPurchasedByUser(Phpfox::getUserId());
            foreach($aPackages as $iKey => $aPackage)
            {
                if(in_array($aPackage['package_id'], $aSubscriptionsIdPurchasedByUser))
                {
                    $aPackages[$iKey]['purchased_by_current_user'] = true;
                }
            }
            $this->template()->setTitle(_p('membership_packages'))
                ->setBreadCrumb(_p('membership_packages'))
                ->assign(array(
                        'aPackages' => $aPackages,
                        'sDefaultImagePath' => setting('core.path_actual') . 'PF.Site/Apps/core-subscriptions/assets/images/membership_thumbnail.jpg'
                    )
                );
        } else {
            $this->template()->setTitle(_p('membership_notice'))->setBreadCrumb(_p('membership_notice'));
        }

        $this->template()->buildSectionMenu('subscribe', Phpfox::getService('subscribe')->getSectionMenu());

        $this->template()->setHeader('cache',[
            'masonry/masonry.min.js' => 'static_script'
        ]);

        if (!$aPackages) {
            return false;
        }
        return 'controller';
    }
}