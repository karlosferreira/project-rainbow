<?php

namespace Apps\Core_MobileApi\Service\Admincp;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Service;

class SettingService extends Phpfox_Service
{

    public function __construct()
    {

    }

    public function getAppLogo()
    {
        if ($aLogo = storage()->get('mobile-api/logo')) {
            $bIsDefault = false;
            $sLogo = Phpfox::getLib('image.helper')->display([
                'file'       => 'mobile/' . $aLogo->value->path,
                'server_id'  => $aLogo->value->server_id,
                'path'       => 'core.url_pic',
                'return_url' => true
            ]);
        } else {
            //Get site logo
            $bIsDefault = true;
            $sLogo = flavor()->active->logo_url();
        }
        return [$sLogo, $bIsDefault];
    }

    public function updateLogo()
    {
        $oFile = Phpfox_File::instance();
        $sPicStorage = Phpfox::getParam('core.dir_pic') . 'mobile/';
        if (isset($_FILES['logo']['name']) && ($_FILES['logo']['name'] != '')) {
            $aIcon = $oFile->load('logo', ['jpg', 'png']);
            if (!Phpfox_Error::isPassed()) {
                return false;
            }
            if ($aIcon !== false) {
                $sLogoName = $oFile->upload('logo', $sPicStorage, 'logo');
                $aUpdate = [
                    'path'      => $sLogoName,
                    'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')
                ];
                //Remove Old Logo
                $this->deleteLogo();
                storage()->set('mobile-api/logo', $aUpdate);
            }
        }
        return true;
    }

    public function deleteLogo()
    {
        $aLogo = storage()->get('mobile-api/logo');
        if (!empty($aLogo)) {
            $sLogo = Phpfox::getParam('core.dir_pic') . 'mobile/' . sprintf($aLogo->value->path, '');
            Phpfox_File::instance()->unlink($sLogo);
        } else {
            return false;
        }
        storage()->del('mobile-api/logo');
        return true;
    }

    public function getSmartBannerScript()
    {
        $aBannerConfig = $this->getAppBannerInfo();
        if (empty($aBannerConfig['banner_title']) || empty($aBannerConfig['banner_logo'])
            || empty($aBannerConfig['banner_author']) || empty($aBannerConfig['banner_apple_store_id'])
            || empty($aBannerConfig['banner_google_store_id'])) {
            return '';
        }
        $sPath = Phpfox::getParam('core.path_actual') . PHPFOX_DS . 'PF.Site' . PHPFOX_DS . 'Apps' . PHPFOX_DS . 'core-mobile-api' . PHPFOX_DS . 'assets' . PHPFOX_DS;
        $sResult = '<script src="' . ($sPath . 'jscript' . PHPFOX_DS . 'smartbanner' . PHPFOX_DS . 'smartbanner.min.js') . '"></script>';
        $sResult .= '<meta name="apple-itunes-app" content="app-id=' . $aBannerConfig['banner_apple_store_id'] . '">';
        $sResult .= '<link rel="stylesheet" href="' . ($sPath . 'jscript' . PHPFOX_DS . 'smartbanner' . PHPFOX_DS . 'smartbanner.min.css') . '"/>';
        $sResult .=
            '<script>mobileApiSmartbannerConfig = {
                icon: "'. $aBannerConfig['banner_logo'] .'", 
                title: "'. $aBannerConfig['banner_title'] .'", 
                author: "' . $aBannerConfig['banner_author'] . '", 
                price: "' . $aBannerConfig['banner_price'] . '", 
                idAppStore: "' . $aBannerConfig['banner_apple_store_id'] . '", 
                idPlayStore: "' . $aBannerConfig['banner_google_store_id'] . '"
            }</script>';
        return $sResult;
    }

    public function updateAppBannerInfo($aVals)
    {
        $oFile = Phpfox_File::instance();
        $sPicStorage = Phpfox::getParam('core.dir_pic') . 'mobile/';
        $oInput = Phpfox::getLib('parse.input');
        $sCacheKey = 'mobile-api/app-banner';
        $oldData = storage()->get($sCacheKey);
        $aUpdate = [
            'banner_title' => !empty($aVals['banner_title']) ? $oInput->clean($aVals['banner_title']) : '',
            'banner_author' => !empty($aVals['banner_author']) ? $oInput->clean($aVals['banner_author']) : '',
            'banner_price' => !empty($aVals['banner_author']) ? $oInput->clean($aVals['banner_price']) : 'FREE',
            'banner_apple_store_id' => !empty($aVals['banner_apple_store_id']) ? $oInput->clean($aVals['banner_apple_store_id']) : '',
            'banner_google_store_id' => !empty($aVals['banner_google_store_id']) ? $oInput->clean($aVals['banner_google_store_id']) : '',
        ];
        if (!empty($oldData) && isset($oldData->value->banner_logo)) {
            $aUpdate['banner_logo'] = (array)$oldData->value->banner_logo;
        }
        if (isset($_FILES['banner_logo']['name']) && ($_FILES['banner_logo']['name'] != '')) {
            $aIcon = $oFile->load('banner_logo', ['jpg', 'png']);
            if (!Phpfox_Error::isPassed()) {
                return false;
            }
            if ($aIcon !== false) {
                $sLogoName = $oFile->upload('banner_logo', $sPicStorage, 'banner_logo');
                if (!empty($sLogoName) && !empty($aUpdate['banner_logo']['path'])) {
                    $sLogo = Phpfox::getParam('core.dir_pic') . 'mobile/' . sprintf($aUpdate['banner_logo']['path'], '');
                    Phpfox_File::instance()->unlink($sLogo);
                }
                $aUpdate['banner_logo'] = [
                    'path'      => $sLogoName,
                    'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')
                ];
            } else {
                storage()->set('mobile-api/logo', $aUpdate);
            }
        } elseif (empty($aUpdate['banner_logo'])) {
            return Phpfox_Error::set(_p('app_logo_is_required'));
        }
        storage()->del($sCacheKey);
        storage()->set($sCacheKey, $aUpdate);

        return true;
    }

    public function getAppBannerInfo()
    {
        $sCacheKey = 'mobile-api/app-banner';
        $oldData = storage()->get($sCacheKey);
        $aForms = [];
        if (!empty($oldData)) {
            $aForms = (array)$oldData->value;
            if (!empty($aForms['banner_logo'])) {
                $aBannerLogo = (array)$aForms['banner_logo'];
                $aForms['banner_logo'] = Phpfox::getLib('image.helper')->display([
                    'file'       => 'mobile/' . $aBannerLogo['path'],
                    'server_id'  => $aBannerLogo['server_id'],
                    'path'       => 'core.url_pic',
                    'return_url' => true
                ]);
            }
        }
        return $aForms;
    }
}