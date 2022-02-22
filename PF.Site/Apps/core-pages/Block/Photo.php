<?php

namespace Apps\Core_Pages\Block;

use Phpfox;
use Phpfox_Component;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

class Photo extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (!defined('PHPFOX_IS_PAGES_VIEW')
            && !Phpfox_Component::__getParam('show_page_cover')) {
            return false;
        }

        $aPage = $this->getParam('aPage');

        if (!isset($aPage['page_id']) || empty($aPage['page_id'])) {
            return false;
        }

        $aCoverPhoto = ($aPage['cover_photo_id'] ? Phpfox::getService('photo')->getCoverPhoto($aPage['cover_photo_id']) : false);
        $iCoverPhotoPosition = $aPage['cover_photo_position'];
        $aPageMenus = Phpfox::getService('pages')->getMenu($aPage);

        if ($oProfileImage = storage()->get('user/avatar/' . $aPage['page_user_id'])) {
            $aProfileImage = Phpfox::getService('photo')->getPhoto($oProfileImage->value);
            $this->template()->assign('aProfileImage', $aProfileImage);
        }

        $bCanChangePhoto = Phpfox::getService('pages')->isAdmin($aPage) || Phpfox::getUserParam('pages.can_edit_all_pages');
        $bCanChangeCover = (Phpfox::getService('pages')->isAdmin($aPage) || Phpfox::getUserParam('pages.can_edit_all_pages')) &&
            Phpfox::getUserParam('pages.can_add_cover_photo_pages');
        $bCanClaim = !$aPage['is_admin'] && Phpfox::getUserParam('pages.can_claim_page') && empty($aPage['claim_id']);

        $this->template()->assign([
            'aCoverPhoto' => $aCoverPhoto,
            'iCoverPhotoPosition' => $iCoverPhotoPosition,
            'sDefaultCoverPath' => Phpfox::getParam('pages.default_cover_photo'),
            'aPageMenus' => $aPageMenus,
            'aCoverImage' => Phpfox::getService('photo')->getCoverPhoto($aPage['cover_photo_id']),
            'bCanChangePhoto' => $bCanChangePhoto,
            'bCanChangeCover' => $bCanChangeCover,
            'bCanClaim' => $bCanClaim,
            'iClamePageUser' => Phpfox::getParam('pages.admin_in_charge_of_page_claims')
        ]);

        if (empty($this->template()->getVar('aPage'))) {
            $this->template()->assign('aPage', $aPage);
        }

        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('pages.component_block_photo_clean')) ? eval($sPlugin) : false);
    }
}
