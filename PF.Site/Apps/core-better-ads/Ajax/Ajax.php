<?php

namespace Apps\Core_BetterAds\Ajax;

use Phpfox;
use Phpfox_Ajax;

/**
 * Class Ajax
 * @package Apps\Core_BetterAds\Ajax
 */
class Ajax extends Phpfox_Ajax
{
    public function sample()
    {
        echo '<iframe src="' . Phpfox::getLib('url')->makeUrl('ad', array(
                'sample',
                'get-block-layout' => 'true',
                'click' => ($this->get('click') ? '1' : '0'),
                'no-click' => ($this->get('no-click') ? '1' : '0')
            )) . '" width="100%" frameborder="0" scrolling="yes"></iframe>';

        echo '<script>$Core.Ads.resizePreview();</script>';
    }

    public function preview()
    {
        $aVals = ['preview', 'get-block-layout' => true];
        $iAdId = $this->get('ad_id');
        if ($iAdId) {
            $aVals['ad_id'] = $iAdId;
            $aVals['location'] = $this->get('location');
        } else {
            $aVals = array_merge($aVals, ['val' => $this->get('val')]);
            if (!empty($iAdId = $this->get('ads_id'))) {
                $aVals['ad_id'] = $iAdId;
            }
        }

        echo '<iframe src="' . Phpfox::getLib('url')->makeUrl('ad',
                $aVals) . '" width="100%" frameborder="0" scrolling="yes"></iframe>';
        echo '<script>$Core.Ads.resizePreview();</script>';
    }

    public function updateAdActivity()
    {
        Phpfox::getService('ad.process')->updateActivityAjax($this->get('id'), $this->get('active'));
        $this->call('$Core.closeAjaxMessage();');
    }

    public function updateSponsorActivity()
    {
        Phpfox::getService('ad.process')->updateSponsorActivity($this->get('id'), $this->get('active'));
        $this->call('$Core.closeAjaxMessage();');
    }

    public function updateAdActivityUser()
    {
        Phpfox::getService('ad.process')->updateActivityAjax($this->get('id'), $this->get('active'), Phpfox::getUserId());
    }

    public function updateAdPlacementActivity()
    {
        Phpfox::getService('ad.process')->updateAdPlacementActivity($this->get('id'), $this->get('active'));
    }

    public function hideAds()
    {
        if (user('better_ads_allow_hide_ads')) {
            $iId = $this->get('id');
            Phpfox::getService('ad.process')->hideAds($iId);
            $this->call("\$Core.Ads.hideAd($iId);");
        }
    }

    public function deleteCategory()
    {
        $this->setTitle(_p('delete_placement'));
        Phpfox::getBlock('ad.delete-placement');
    }

    public function cancelInvoice()
    {
        $iInvoiceId = $this->get('id');
        if (!$iInvoiceId) {
            return;
        }

        Phpfox::getService('ad.process')->cancelInvoice($iInvoiceId);
    }

    public function migrateAd()
    {
        $this->setTitle(_p('migrate_ad'));
        $aParams = [];
        if ($iId = $this->get('id')) {
            $aParams['id'] = $this->get('id');
        } elseif ($aVals = $this->get('val')) {
            $aParams['ids'] = $aVals['id'];
        }
        Phpfox::getBlock('ad.migrate-ad', $aParams);
    }

    public function processImportAd()
    {
        $adId = $this->get('import');
        $iPlacementId = $this->get('placement');
        if (is_array($adId)) {
            foreach ($adId as $id) {
                Phpfox::getService('ad.migrate')->importAd($id, $iPlacementId);
            }
            $iNumberOfAds = count($adId);
            if ($iNumberOfAds == 1) {
                $sMessage = _p('1_ad_has_been_imported_successfully');
            } else {
                $sMessage = _p('number_ads_have_been_imported_successfully', ['number' => $iNumberOfAds]);
            }
            $this->call('$Core.Ads.alertThenReload("' . $sMessage . '");');
        } else {
            Phpfox::getService('ad.migrate')->importAd($adId, $iPlacementId);
            $this->call('$Core.Ads.alertThenReload("' . _p('1_ad_has_been_imported_successfully') . '");');
        }
    }

    public function migrateSponsorship()
    {
        Phpfox::setAdminPanel();
        $aVals = $this->get('val');
        if (!empty($aVals['id']) && is_array($aVals['id'])) {
            foreach ($aVals['id'] as $id) {
                Phpfox::getService('ad.migrate')->importSponsorship($id);
            }
            $iNumberOfSponsorships = count($aVals['id']);
            if ($iNumberOfSponsorships == 1) {
                $sMessage = _p('1_sponsorship_has_been_imported_successfully');
            } else {
                $sMessage = _p('number_sponsorships_have_been_imported_successfully',
                    ['number' => $iNumberOfSponsorships]);
            }
            $this->call('$Core.Ads.alertThenReload("' . $sMessage . '");');
        } elseif ($sponsorId = $this->get('id')) {
            Phpfox::getService('ad.migrate')->importSponsorship($sponsorId);
            $this->call('$Core.Ads.alertThenReload("' . _p('1_sponsorship_has_been_imported_successfully') . '");');
        }
    }

    public function removeSponsor()
    {
        if (Phpfox::isModule('feed') && (Phpfox::getUserParam('feed.can_purchase_sponsor') || Phpfox::getUserParam('feed.can_sponsor_feed')) && ($iSponsorId = Phpfox::getService('feed')->canSponsoredInFeed($this->get('type_id'), $this->get('item_id')))) {
            if ($iSponsorId === true) {
                $this->alert(_p('Cannot find the feed!'));
                return false;
            }
            if (Phpfox::getService('ad.process')->deleteSponsor($iSponsorId, true)) {
                Phpfox::addMessage(_p('This item in feed has been unsponsored successfully!'));
                $this->call('$Core.reloadPage();');
            } else {
                $this->alert(_p('Cannot unsponsor this item in feed!'));
                return false;
            }

        } else {
            $this->alert(_p('Cannot unsponsor this item in feed!'));
            return false;
        }
        return true;
    }

}
