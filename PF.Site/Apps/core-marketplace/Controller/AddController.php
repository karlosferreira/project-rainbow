<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Marketplace\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;
use Phpfox_Validator;

defined('PHPFOX') or exit('NO DICE!');


class AddController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::isUser(true);
        $bIsEdit = false;
        $bIsSetup = $this->request()->get('req4') == 'setup';
        $sAction = $this->request()->get('req3');

        $sModule = $this->request()->get('module');
        $iItemId = $this->request()->getInt('item');

        $aListing = [];
        if ($iEditId = $this->request()->getInt('id')) {
            if (($aListing = Phpfox::getService('marketplace')->getForEdit($iEditId))) {
                // Check permission before edit
                if (!empty($aListing['module_id']) && !empty($aListing['item_id'])) {
                    if (isset($aListing['module_id']) && Phpfox::isModule($aListing['module_id']) && Phpfox::hasCallback($aListing['module_id'],
                            'checkPermission')) {
                        if (!Phpfox::callback($aListing['module_id'] . '.checkPermission', $aListing['item_id'],
                            'marketplace.view_browse_marketplace_listings')) {
                            return Phpfox_Error::display(_p('unable_to_view_this_item_due_to_privacy_settings'));
                        }
                    }
                    $sModule = $aListing['module_id'];
                    $iItemId = $aListing['item_id'];
                }

                $bIsEdit = true;
                $this->setParam('aListing', $aListing);
                $this->template()->setHeader([
                        '<script type="text/javascript">$Behavior.marketplaceEditCategory = function(){ var aCategories = explode(\',\', \'' . $aListing['categories'] . '\'); for (i in aCategories) { $(\'#js_mp_holder_\' + aCategories[i]).show(); $(\'#js_mp_category_item_\' + aCategories[i]).prop(\'selected\', true); } }</script>'
                    ]
                )
                    ->assign([
                            'aForms' => $aListing
                        ]
                    );
            }
        } else {
            Phpfox::getUserParam('marketplace.can_create_listing', true);

            if (!Phpfox::getService('marketplace')->checkLimitation()) {
                return Phpfox_Error::display(_p('marketplace_you_have_reached_your_limit_to_create_new_listings'));
            }

            $this->template()->assign('aForms', ['price' => '0.00']);
        }

        if (!empty($sModule) && !empty($iItemId)) {
            $this->template()->assign([
                'sModule' => $sModule,
                'iItem' => $iItemId
            ]);
        }

        $aValidation = [
            'title' => _p('provide_a_name_for_this_listing'),
            'location' => _p('provide_a_location_for_this_listing'),
            'price' => [
                'def' => 'money',
                'title' => _p('please_type_valid_price')
            ]
        ];

        $oValidator = Phpfox_Validator::instance()->set([
                'sFormName' => 'js_marketplace_form',
                'aParams' => $aValidation
            ]
        );

        $aCallback = null;
        if (!empty($sModule) && Phpfox::hasCallback($sModule, 'getItem')) {
            $aCallback = Phpfox::callback($sModule . '.getItem', $iItemId);
            if ($aCallback === false) {
                return Phpfox_Error::display(_p('Cannot find the parent item.'));
            }
            $bCheckParentPrivacy = true;
            if (!$bIsEdit && Phpfox::hasCallback($sModule, 'checkPermission')) {
                $bCheckParentPrivacy = Phpfox::callback($sModule . '.checkPermission', $iItemId, 'marketplace.share_marketplace_listings');
            }

            if (!$bCheckParentPrivacy) {
                return Phpfox_Error::display(_p('unable_to_view_this_item_due_to_privacy_settings'));
            }

            if ($bIsEdit && !empty($aListing)) {
                $sUrl = $this->url()->makeUrl('marketplace', ['add', 'id' => $iEditId]);
                $sCrumb = _p('editing_listing') . ': ' . Phpfox::getLib('parse.output')->shorten($aListing['title'],
                        Phpfox::getService('core')->getEditTitleSize(), '...');
            } else {
                $sUrl = $this->url()->makeUrl('marketplace',
                    ['add', 'module' => $aCallback['module'], 'item' => $iItemId]);
                $sCrumb = _p('create_a_listing');
            }

            $this->template()
                ->setBreadCrumb(isset($aCallback['module_title']) ? $aCallback['module_title'] : _p($sModule), $this->url()->makeUrl($sModule))
                ->setBreadCrumb($aCallback['title'], Phpfox::permalink($sModule, $iItemId))
                ->setBreadCrumb(_p('marketplace'), $this->url()->makeUrl($sModule, [$iItemId, 'marketplace']))
                ->setBreadCrumb($sCrumb, $sUrl, true);

        } else {
            if (!empty($sModule) && !empty($iItemId) && $sModule != 'marketplace' && $aCallback === null) {
                return Phpfox_Error::display(_p('Cannot find the parent item.'));
            }
            $this->template()
                ->setBreadCrumb(_p('marketplace'), $this->url()->makeUrl('marketplace'))
                ->setBreadCrumb(($bIsEdit ? _p('editing_listing') . ': ' . $aListing['title'] : _p('create_a_listing')), $bIsEdit ? $this->url()->makeUrl('marketplace.add', ['id' => $iEditId]) : $this->url()->makeUrl('marketplace.add'), true);
        }

        if ($aVals = $this->request()->get('val')) {
            if ($oValidator->isValid($aVals)) {
                if ($bIsEdit) {
                    if (Phpfox::getService('marketplace.process')->update($aListing['listing_id'], $aVals)) {
                        (($sPlugin = Phpfox_Plugin::get('marketplace.component_controller_add_process_update_complete')) ? eval($sPlugin) : false);

                        if ($bIsSetup) {
                            switch ($sAction) {
                                case 'customize':
                                    $this->url()->send('marketplace.add.invite.setup',
                                        ['id' => $aListing['listing_id']],
                                        _p('successfully_uploaded_images_for_this_listing'));
                                    break;
                                case 'invite':
                                    $this->url()->permalink('marketplace', $aListing['listing_id'], $aListing['title'],
                                        true, _p('successfully_invited_users_for_this_listing'));
                                    break;
                            }

                        } else {
                            switch ($this->request()->get('page_section_menu')) {
                                case 'js_mp_block_customize':
                                    $sMessage = _p('successfully_uploaded_images');
                                    break;
                                case 'js_mp_block_invite':
                                    $sMessage = _p('successfully_invited_users');
                                    break;
                                default:
                                    $sMessage = _p('listing_successfully_updated');
                                    break;
                            }

                            $this->url()->send('marketplace.add', ['id' => $aListing['listing_id'], 'tab' => empty($aVals['current_tab']) ? '' : $aVals['current_tab']], $sMessage);
                        }
                    }
                } else {
                    if (($iFlood = Phpfox::getUserParam('marketplace.flood_control_marketplace')) !== 0) {
                        $aFlood = [
                            'action' => 'last_post', // The SPAM action
                            'params' => [
                                'field' => 'time_stamp', // The time stamp field
                                'table' => Phpfox::getT('marketplace'), // Database table we plan to check
                                'condition' => 'user_id = ' . Phpfox::getUserId(), // Database WHERE query
                                'time_stamp' => $iFlood * 60 // Seconds);
                            ]
                        ];

                        // actually check if flooding
                        if (Phpfox::getLib('spam')->check($aFlood)) {
                            Phpfox_Error::set(_p('you_are_creating_a_listing_a_little_too_soon') . ' ' . Phpfox::getLib('spam')->getWaitTime());
                        }
                    }

                    if (Phpfox_Error::isPassed()) {
                        if ($iId = Phpfox::getService('marketplace.process')->add($aVals)) {
                            if ($aListing = Phpfox::getService('marketplace')->getForEdit($iId)) {
                                $this->url()->send('marketplace.add.customize.setup', ['id' => $iId],
                                    _p('listing_successfully_added'));
                            } else {
                                $this->url()->permalink('marketplace', $iId, $aVals['title'], true,
                                    _p('listing_successfully_added'));
                            }
                        }
                    }
                }
            }
        }

        $aCurrencies = Phpfox::getService('core.currency')->get();
        if (!$aCurrencies || !count($aCurrencies)) {
            return Phpfox_Error::display(_p('marketplace_missing_currency'));
        }
        foreach ($aCurrencies as $iKey => $aCurrency) {
            $aCurrencies[$iKey]['is_default'] = '0';

            if (Phpfox::getService('core.currency')->getDefault() == $iKey) {
                $aCurrencies[$iKey]['is_default'] = '1';
            }
        }

        $iTotalImage = 0;
        if ($bIsEdit) {
            $aMenus = [
                'detail' => _p('listing_details'),
                'customize' => _p('photos'),
                'invite' => _p('invite')
            ];

            if (!$bIsSetup) {
                $aMenus['manage'] = _p('manage_invites');
            }

            $iTotalImage = Phpfox::getService('marketplace')->countImages($aListing['listing_id']);
            $this->template()->buildPageMenu('js_mp_block',
                $aMenus,
                [
                    'link' => $this->url()->permalink('marketplace', $aListing['listing_id'], $aListing['title']),
                    'phrase' => _p('view_this_listing')
                ]
            );
        }
        list ($bCanSellListing, $bHaveGateway, $bAllowActivityPoint, $aValidConvertRate) = Phpfox::getService('marketplace')->canSellItemOnMarket(!empty($aListing) ? $aListing['user_id'] : Phpfox::getUserId());
        if ($aCallback !== null && $sModule == 'groups') {
            $iGroupRegMethod = db()->select('reg_method')
                ->from(':pages')
                ->where(['page_id' => $iItemId, 'item_type' => 1])
                ->executeField();
            $bIsRestrictGroup = (int)$iGroupRegMethod != 0;
            $this->template()->assign([
                'bIsRestrictGroup' => $bIsRestrictGroup
            ]);
        }

        $this->template()->setTitle(_p('marketplace'))
            ->setTitle(($bIsEdit ? _p('editing_listing') . ': ' . $aListing['title'] : _p('create_a_listing')))
            ->setEditor()
            ->setPhrase([
                    'select_a_file_to_upload'
                ]
            )
            ->setHeader([
                    'country.js' => 'module_core',
                    '<script>var marketplace_valid_convert_rate = \''. json_encode($aValidConvertRate) .'\'</script>'
                ]
            )
            ->setHeader('cache', [
                'invite.js' => 'app_core-marketplace'
            ])
            ->assign([
                    'sMyEmail' => Phpfox::getUserBy('email'),
                    'sCreateJs' => $oValidator->createJS(),
                    'sGetJsForm' => $oValidator->getJsForm(false),
                    'bIsEdit' => $bIsEdit,
                    'sCategories' => Phpfox::getService('marketplace.category')->get(),
                    'iMaxFileSize' => (Phpfox::getUserParam('marketplace.max_upload_size_listing') === 0 ? '' : (Phpfox::getUserParam('marketplace.max_upload_size_listing'))),
                    'aCurrencies' => $aCurrencies,
                    'iTotalImage' => $iTotalImage,
                    'iTotalImageLimit' => Phpfox::getUserParam('marketplace.total_photo_upload_limit'),
                    'iRemainUpload' => Phpfox::getUserParam('marketplace.total_photo_upload_limit') - $iTotalImage,
                    'sUserSettingLink' => $this->url()->makeUrl('user.setting'),
                    'bCanSellListing' => $bCanSellListing,
                    'bAllowActivityPoint' => $bAllowActivityPoint,
                    'bHaveGateway' => $bHaveGateway
                ]
            );
        if (Phpfox::isModule('attachment')) {
            $this->setParam('attachment_share', [
                    'type' => 'marketplace',
                    'id' => 'js_marketplace_form',
                    'edit_id' => ($bIsEdit ? $iEditId : 0)
                ]
            );
        }
        (($sPlugin = Phpfox_Plugin::get('marketplace.component_controller_add_process')) ? eval($sPlugin) : false);
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('marketplace.component_controller_add_clean')) ? eval($sPlugin) : false);
    }
}