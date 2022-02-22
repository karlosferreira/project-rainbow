<?php

namespace Apps\Core_BetterAds\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Display
 * @package Apps\Core_BetterAds\Block
 */
class Display extends Phpfox_Component
{
    /**
     * Class process method which is used to execute this component.
     */
    public function process()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_display_process__start')) ? eval($sPlugin) : false);

        if ($this->request()->get('is_ajax_popup')) {
            return false;
        }

        $aPreview = $this->getParam('preview', []);
        if (!empty($aPreview)) {
            $this->_previewAd($aPreview);

            return 'block';
        }

        if (!Phpfox::getParam('ad.better_enable_ads', true)) {
            return false;
        }

        $iBlockId = $this->getParam('block_id');
        $aAds = Phpfox::getService('ad')->getForBlock($iBlockId);

        if (empty($aAds)) {
            return false;
        }

        if (Phpfox::getUserParam('ad.better_can_create_ad_campaigns') && Phpfox::getParam('ad.better_ads_show_create_ads_button')) {
            $this->template()->assign([
                'aFooter' => [
                    _p('create_an_ad') => $this->url()->makeUrl('ad.add')
                ]
            ]);
        }

        if (in_array($iBlockId, [1, 3, 9, 10])) {
            $this->template()->assign(['sHeader' => _p('better_ads_sponsored')]);
        }

        $this->template()->assign([
                'aBlockAds'   => $aAds,
                'iBlockId'    => $iBlockId,
                'bCanHideAds' => Phpfox::getUserParam('ad.better_ads_allow_hide_ads')
            ]
        );

        (($sPlugin = Phpfox_Plugin::get('ad.component_block_display_process__end')) ? eval($sPlugin) : false);

        return 'block';
    }

    private function _previewAd($aPreview)
    {
        $aTempImage = !empty($aPreview['image_temp_id']) ? Phpfox::getService('core.temp-file')->get($aPreview['image_temp_id']) : null;
        if ($aPreview['ad_id']) {
            $aAd = Phpfox::getService('ad.get')->getForEdit($aPreview['ad_id']);
            $aLocation = Phpfox::getService('ad.get')->getPlacement($aAd['location']);
            $iBlockId = $aLocation['block_id'];
            $aBlockAds = [
                'ads_id'             => $aPreview['ad_id'],
                'type_id'            => $aAd['type_id'],
                'title'              => !empty($aAd['title']) ? $aAd['title'] : '',
                'body'               => !empty($aAd['body']) ? $aAd['body'] : '',
                'trimmed_url'        => $aAd['url_link'],
                'image_tooltip_text' => $aAd['image_tooltip_text'],
                'image_path'         => !empty($aTempImage) ? $aTempImage['path'] : $aAd['image_path'],
                'server_id'          => !empty($aTempImage) ? $aTempImage['server_id'] : $aAd['server_id']
            ];
        } else {
            $iBlockId = $aPreview['block'];
            $aBlockAds = [
                'ads_id'             => 0,
                'type_id'            => $aPreview['type_id'],
                'title'              => $aPreview['title'],
                'body'               => $aPreview['body'],
                'trimmed_url'        => $aPreview['url_link'],
                'image_tooltip_text' => $aPreview['image_tooltip_text'],
                'image_path'         => !empty($aTempImage) ? $aTempImage['path'] : '',
                'server_id'          => !empty($aTempImage) ? $aTempImage['server_id'] : ''
            ];
        }

        $this->template()->assign([
            'aBlockAds'         => [$aBlockAds],
            'iBlockId'          => $iBlockId,
            'bBlockIdForAds'    => false,
            'bShowCreateButton' => false,
            'bCanHideAds'       => false,
            'sCustomClassName'  => 'no-toggle',
        ]);

        if (in_array($iBlockId, [1, 3, 9, 10])) {
            $this->template()->assign(['sHeader' => _p('better_ads_sponsored')]);
        }

        (($sPlugin = Phpfox_Plugin::get('ad.component_block_display_preview_ad')) ? eval($sPlugin) : false);
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_block_display_clean')) ? eval($sPlugin) : false);
    }
}
