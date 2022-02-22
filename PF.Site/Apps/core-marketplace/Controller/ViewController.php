<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Marketplace\Controller;


use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Module;
use Phpfox_Plugin;
use Phpfox_Url;


defined('PHPFOX') or exit('NO DICE!');

class ViewController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if ($this->request()->get('req2') == 'view' && ($sLegacyTitle = $this->request()->get('req3')) && !empty($sLegacyTitle)) {
            Phpfox::getService('core')->getLegacyItem([
                    'field' => ['listing_id', 'title'],
                    'table' => 'marketplace',
                    'redirect' => 'marketplace',
                    'title' => $sLegacyTitle
                ]
            );
        }

        Phpfox::getUserParam('marketplace.can_access_marketplace', true);

        if (!($iListingId = $this->request()->get('req2'))) {
            $this->url()->send('marketplace');
        }

        if (!($aListing = Phpfox::getService('marketplace')->getListing($iListingId))) {
            return Phpfox_Error::display(_p('the_listing_you_are_looking_for_either_does_not_exist_or_has_been_removed'));
        }

        if (isset($aListing['module_id']) && !empty($aListing['item_id']) && !Phpfox::isModule($aListing['module_id'])) {
            return Phpfox_Error::display(_p('Cannot find the parent item.'));
        }

        if (Phpfox::isUser() && Phpfox::getService('user.block')->isBlocked(null, $aListing['user_id'])) {
            return Phpfox_Module::instance()->setController('error.invalid');
        }
        Phpfox::getService('marketplace')->getPermissions($aListing);
        $this->setParam('aListing', $aListing);

        if (Phpfox::isUser() && $aListing['invite_id'] && !$aListing['visited_id'] && $aListing['user_id'] != Phpfox::getUserId()) {
            Phpfox::getService('marketplace.process')->setVisit($aListing['listing_id'], Phpfox::getUserId());
        }

        if (Phpfox::isModule('notification') && $aListing['user_id'] == Phpfox::getUserId()) {
            Phpfox::getService('notification.process')->delete('marketplace_approved', $aListing['listing_id'],
                Phpfox::getUserId());
        }

        if (Phpfox::isModule('privacy')) {
            Phpfox::getService('privacy')->check('marketplace', $aListing['listing_id'], $aListing['user_id'],
                $aListing['privacy'], $aListing['is_friend']);
        }

        if (isset($aListing['module_id']) && Phpfox::isModule($aListing['module_id']) && Phpfox::hasCallback($aListing['module_id'],
                'checkPermission')) {
            if (!Phpfox::callback($aListing['module_id'] . '.checkPermission', $aListing['item_id'],
                'marketplace.view_browse_marketplace_listings')) {
                return Phpfox_Error::display(_p('unable_to_view_this_item_due_to_privacy_settings'));
            }
        }
        // Increment the view counter
        $bUpdateCounter = false;
        if (Phpfox::isModule('track')) {
            if (!$aListing['is_viewed']) {
                $bUpdateCounter = true;
                Phpfox::getService('track.process')->add('marketplace', $aListing['listing_id']);
            } else {
                if (!setting('track.unique_viewers_counter')) {
                    $bUpdateCounter = true;
                    Phpfox::getService('track.process')->add('marketplace', $aListing['listing_id']);
                } else {
                    Phpfox::getService('track.process')->update('marketplace', $aListing['listing_id']);
                }
            }
        } else {
            $bUpdateCounter = true;
        }
        if ($bUpdateCounter) {
            Phpfox::getService('marketplace.process')->updateView($aListing['listing_id']);
            $aListing['total_view'] += 1;
        }
        $this->setParam('aRatingCallback', [
                'type' => 'user',
                'default_rating' => $aListing['total_score'],
                'item_id' => $aListing['user_id'],
                'stars' => range(1, 10)
            ]
        );

        $this->setParam('aFeed', [
                'comment_type_id' => 'marketplace',
                'privacy' => $aListing['privacy'],
                'comment_privacy' => $aListing['privacy_comment'],
                'like_type_id' => 'marketplace',
                'feed_is_liked' => $aListing['is_liked'],
                'feed_is_friend' => $aListing['is_friend'],
                'item_id' => $aListing['listing_id'],
                'user_id' => $aListing['user_id'],
                'total_comment' => $aListing['total_comment'],
                'total_like' => $aListing['total_like'],
                'feed_link' => $this->url()->permalink('marketplace', $aListing['listing_id'], $aListing['title']),
                'feed_title' => $aListing['title'],
                'feed_display' => 'view',
                'feed_total_like' => $aListing['total_like'],
                'report_module' => 'marketplace',
                'report_phrase' => _p('report_this_listing_lowercase')
            ]
        );

        $sExchangeRate = '';
        if ($aListing['currency_id'] != Phpfox::getService('core.currency')->getDefault()) {
            if (($sAmount = Phpfox::getService('core.currency')->getXrate($aListing['currency_id'],
                $aListing['price']))
            ) {
                $sExchangeRate .= ' (' . Phpfox::getService('core.currency')->getCurrency($sAmount) . ')';
            }
        }
        $aTitleLabel = [
            'type_id' => 'marketplace'
        ];

        if ($aListing['is_featured']) {
            $aTitleLabel['label']['featured'] = [
                'title' => '',
                'title_class' => 'flag-style-arrow',
                'label_class' => 'flag_style',
                'icon_class' => 'diamond'

            ];
        }
        if ($aListing['is_sponsor']) {
            $aTitleLabel['label']['sponsored'] = [
                'title' => '',
                'title_class' => 'flag-style-arrow',
                'label_class' => 'flag_style',
                'icon_class' => 'sponsor'

            ];
        }
        if ($aListing['image_path']) {
            $sImage = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aListing['listing_id'],
                    'path' => 'marketplace.url_image',
                    'file' => $aListing['image_path'],
                    'suffix' => '_400_square',
                    'return_url' => true
                ]
            );
        } else {
            $sImage = Phpfox::getParam('marketplace.marketplace_default_photo');
        }
        $aTitleLabel['total_label'] = isset($aTitleLabel['label']) ? count($aTitleLabel['label']) : 0;

        if ($aListing['view_id'] == 1) {
            $aTitleLabel['label']['pending'] = [
                'title' => '',
                'title_class' => 'flag-style-arrow',
                'icon_class' => 'clock-o'

            ];
            $aPendingItem = [
                'message' => _p('listing_is_pending_approval'),
                'actions' => []
            ];
            if ($aListing['canApprove']) {
                $aPendingItem['actions']['approve'] = [
                    'is_ajax' => true,
                    'label' => _p('approve'),
                    'action' => '$.ajaxCall(\'marketplace.approve\', \'inline=true&amp;listing_id=' . $aListing['listing_id'] . '\')'
                ];
            }
            if ($aListing['canEdit']) {
                $aPendingItem['actions']['edit'] = [
                    'label' => _p('edit'),
                    'action' => $this->url()->makeUrl('marketplace.add', ['id' => $aListing['listing_id']]),
                ];
            }
            if ($aListing['canDelete']) {
                $aPendingItem['actions']['delete'] = [
                    'is_confirm' => true,
                    'confirm_message' => _p('are_you_sure_you_want_to_delete_this_listing_permanently'),
                    'label' => _p('delete'),
                    'action' => $this->url()->makeUrl('marketplace', ['delete' => $aListing['listing_id']]),
                ];
            }

            $this->template()->assign([
                'aPendingItem' => $aPendingItem
            ]);
        }

        // get related listing
        $aListings = Phpfox::getService('marketplace')->getRelatedListings($aListing['category_id'], $aListing['listing_id']);
        foreach ($aListings as $iKey => $value) {
            $aListings[$iKey]['url'] = Phpfox_Url::instance()->permalink('marketplace', $value['listing_id'], $value['title']);
        }

        if (isset($aListing['module_id']) && Phpfox::hasCallback($aListing['module_id'], 'getItem')) {
            $aCallback = Phpfox::callback($aListing['module_id'] . '.getItem', $aListing['item_id']);
            if ($aCallback === false) {
                return Phpfox_Error::display(_p('Cannot find the parent item.'));
            }

            $this->template()
                ->setBreadCrumb(isset($aCallback['module_title']) ? $aCallback['module_title'] : _p($aListing['module_id']),
                    $this->url()->makeUrl($aListing['module_id']))
                ->setBreadCrumb($aCallback['title'], Phpfox::permalink($aListing['module_id'], $aListing['item_id']))
                ->setBreadCrumb(_p('listings'), $this->url()->makeUrl($aListing['module_id'], array($aListing['item_id'], 'marketplace')));
        } else {
            $this->template()->setBreadCrumb(_p('listings'), $this->url()->makeUrl('marketplace'));
        }
        list (, $bHaveGateway, ) = Phpfox::getService('marketplace')->canSellItemOnMarket($aListing['user_id']);
        $this->template()->setTitle($aListing['title'])
            ->setMeta('description', $aListing['description'])
            ->setMeta('description', Phpfox::getParam('marketplace.marketplace_meta_description'))
            ->setMeta('keywords', $this->template()->getKeywords($aListing['title'] . $aListing['description']))
            ->setMeta('keywords', Phpfox::getParam('marketplace.marketplace_meta_keywords'))
            ->setMeta('og:image', $sImage)
            ->setBreadCrumb($aListing['title'],
                $this->url()->permalink('marketplace', $aListing['listing_id'], $aListing['title']), true)
            ->setHeader('cache', [
                    'jquery/plugin/star/jquery.rating.js' => 'static_script',
                    'jquery/plugin/jquery.highlightFade.js' => 'static_script',
                    'jquery/plugin/jquery.scrollTo.js' => 'static_script',
                    'masterslider.min.js' => 'module_core',
                    'switch_legend.js' => 'static_script',
                    'switch_menu.js' => 'static_script',
                    'masterslider.css' => 'module_core'
                ]
            )
            ->setEditor([
                    'load' => 'simple'
                ]
            )
            ->assign([
                    'core_path' => str_replace('index.php', '', Phpfox::getParam('core.path')),
                    'aListing' => $aListing,
                    'aListings' => $aListings,
                    'sMicroPropType' => 'Product',
                    'aImages' => Phpfox::getService('marketplace')->getImages($aListing['listing_id']),
                    'sShareDescription' => str_replace(["\n", "\r", "\r\n"], '', $aListing['description']),
                    'bCanMessageOwner' => Phpfox::isUser() && Phpfox::getUserId() != $aListing['user_id'] && Phpfox::getUserParam('mail.can_compose_message') && Phpfox::isAppActive('Core_Messages') && Phpfox::getService('mail')->canMessageUser($aListing['user_id']),
                    'aTitleLabel' => $aTitleLabel,
                    'bIsDetail' => true,
                    'bHaveGateway' => $bHaveGateway
                ]
            );

        if (Phpfox::isModule('rate')) {
            $this->template()
                ->setHeader([
                        'rate.js' => 'module_rate',
                        '<script type="text/javascript">$Behavior.rateMarketplaceUser = function() { $Core.rate.init({display: false}); }</script>',
                    ]
                );
        }

        Phpfox::getService('marketplace')->buildSectionMenu();

        (($sPlugin = Phpfox_Plugin::get('marketplace.component_controller_view_process_end')) ? eval($sPlugin) : false);
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('marketplace.component_controller_view_clean')) ? eval($sPlugin) : false);
    }
}