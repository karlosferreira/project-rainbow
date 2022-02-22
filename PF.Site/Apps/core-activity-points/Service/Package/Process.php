<?php
namespace Apps\Core_Activity_Points\Service\Package;

use Phpfox;
use Phpfox_Error;
use Phpfox_File;
use Phpfox_Image;
use Phpfox_Request;
use Phpfox_Service;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class Process
 * @package Apps\Core_Activity_Points\Service\Package
 */
class Process extends Phpfox_Service
{
    /**
     * Process constructor.
     */
    public function __construct()
    {
        $this->_sTable = Phpfox::getT('activitypoint_package');
    }

    /**
     * Create new Point Package
     * @param $aVals
     * @param $iUpdateId
     * @return int
     */
    public function add($aVals, $iUpdateId = null)
    {
        $sDefaultLanguageCode = Phpfox::getService('language')->getDefaultLanguage();
        //Add phrase for title
        $aLanguages = Phpfox::getService('language')->getAll();
        foreach ($aLanguages as $aLanguage) {
            if (empty($aVals['title'][$aLanguage['language_id']])) {
                $aVals['title'][$aLanguage['language_id']] = $aVals['title'][$sDefaultLanguageCode];
            }
        }

        if(!empty($iUpdateId))
        {
            $aPackage = Phpfox::getService('activitypoint.package')->getPackage($iUpdateId);
            $bAlreadyPhrase = true;
            foreach ($aLanguages as $aLanguage) {
                $iPhraseId = Phpfox::getLib('database')->select('phrase_id')
                    ->from(':language_phrase')
                    ->where('language_id="' . $aLanguage['language_id'] . '" AND var_name="' . $aPackage['title'] . '"' )
                    ->executeField();
                if ($iPhraseId) {
                    Phpfox::getService('language.phrase.process')->update($iPhraseId, $aVals['title'][$aLanguage['language_id']]);
                } else {
                    $bAlreadyPhrase = false;
                    break;
                }
            }
            if (!$bAlreadyPhrase) {
                $sTitleVarName = 'activitypoint_package_title_' . md5($sDefaultLanguageCode . time());
                \Core\Lib::phrase()->addPhrase($sTitleVarName, $aVals['title']);
            }
            else
            {
                $sTitleVarName = $aPackage['title'];
            }
        }
        else
        {
            $sTitleVarName = 'activitypoint_package_title_' . md5($sDefaultLanguageCode . time());
            \Core\Lib::phrase()->addPhrase($sTitleVarName, $aVals['title']);
        }

        if (!empty($_FILES['image']['name'])) {
            $aImage = Phpfox_File::instance()->load('image', array('jpg', 'gif', 'png'));

            if ($aImage === false) {
                return false;
            }
        }

        $aInsert = [
            'title' => $sTitleVarName,
            'price' => serialize($aVals['price']),
            'points' => $aVals['points'],
            'is_active' => $aVals['is_active'],
            'server_id' => Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID')
        ];

        if(!empty($iUpdateId))
        {
            $iId = $iUpdateId;
            $aInsert['time_updated'] = PHPFOX_TIME;
            db()->update($this->_sTable, $aInsert, 'package_id = '. (int)$iUpdateId);
        }
        else
        {
            $aInsert['time_stamp'] = PHPFOX_TIME;
            $aInsert['time_updated'] = PHPFOX_TIME;
            $iId = db()->insert($this->_sTable, $aInsert);
        }

        if (!empty($_FILES['image']['name']) && ($sFileName = Phpfox_File::instance()->upload('image',
                Phpfox::getParam('activitypoint.dir_image'), $iId))) {
            $this->database()->update($this->_sTable, array(
                'image_path' => $sFileName,
                'server_id' => Phpfox_Request::instance()->getServer('PHPFOX_SERVER_ID')
            ), 'package_id = ' . (int)$iId);
            $aSize = [80, 120];
            foreach($aSize as $iSize)
            {
                Phpfox_Image::instance()->createThumbnail(Phpfox::getParam('activitypoint.dir_image') . sprintf($sFileName, ''),
                    Phpfox::getParam('activitypoint.dir_image') . sprintf($sFileName, '_'.$iSize), $iSize, $iSize);
            }

            unlink(Phpfox::getParam('activitypoint.dir_image') . sprintf($sFileName, ''));
        }
        if(!empty($iUpdateId))
        {
            \Core\Lib::phrase()->clearCache();
            $this->cache()->remove('activitypoint_point_packages_' . $iUpdateId);
        }

        $this->_clearGlobalCache();
        return $iId;
    }

    /**
     * Delete package image
     * @param $iId
     * @return bool
     */
    public function deleteImage($iId)
    {
        Phpfox::getUserParam('admincp.has_admin_access', true);
        $aPackage = $this->database()->select('package_id, image_path, server_id')
            ->from($this->_sTable)
            ->where('package_id = ' . (int)$iId)
            ->execute('getSlaveRow');
        if (!isset($aPackage['package_id'])) {
            return Phpfox_Error::set(_p('unable_to_find_the_package'));
        }
        if (!empty($aPackage['image_path'])) {
            $aSize = [80, 120];
            foreach($aSize as $iSize)
            {
                $sImage = Phpfox::getParam('activitypoint.dir_image') . sprintf($aPackage['image_path'], '_'. $iSize);
                if (file_exists($sImage)) {
                    unlink($sImage);
                }
                //Todo Delete on CDN?
            }
            $this->database()->update($this->_sTable, array('image_path' => null, 'server_id' => '0'),
                'package_id = ' . $aPackage['package_id']);
        }
        return true;
    }

    /**
     * Update package with active or deactive when admin update package status in "On" or "Off" in admincp
     * @param $iPackageId
     * @param bool $bActive
     */
    public function updateActivity($iPackageId, $bActive = true)
    {
        if ($success = db()->update($this->_sTable, ['is_active' => (int)$bActive],'package_id = '. (int)$iPackageId)) {
            $this->_clearGlobalCache();
        }
        return $success;
    }

    /**
     * Delete package without no one activated
     * @param $iPackageId
     *
     * @return bool
     */
    public function delete($iPackageId)
    {
        if(!$aPackage = Phpfox::getService('activitypoint.package')->getPackage($iPackageId))
        {
            return Phpfox_Error::set(_p('Invalid Package'));
        }
        if((int)$aPackage['total_active'] > 0)
        {
            return Phpfox_Error::set(_p('Can not delete package activated by user'));
        }
        if ($success = db()->delete($this->_sTable,'package_id = '. (int)$iPackageId)) {
            $this->_clearGlobalCache();
        }
        return $success;
    }

    /**
     * Create new package purchasement
     * @param $iPackageId
     * @param $iUserId
     * @param $sCurrency
     * @param $iPoints
     * @return int
     */
    public function createPurchase($iPackageId, $iUserId, $sCurrency, $iPoints)
    {
        if(empty($iUserId))
        {
            $iUserId = Phpfox::getUserId();
        }
        $aInsert = [
            'user_id' => (int)$iUserId,
            'package_id' => (int)$iPackageId,
            'currency_id' => $sCurrency,
            'points' => (int)$iPoints
        ];
        $iPurchaseId = db()->insert(Phpfox::getT('activitypoint_package_purchase'), $aInsert);
        return $iPurchaseId;
    }

    /**
     * Handling callback data from Paypal
     * @param $aParams
     * @param $aPurchase
     */
    public function processPurchase($aParams, $aPurchase)
    {
        if($aParams['status'] == "completed")
        {
            db()->updateCounter('activitypoint_package','total_active','package_id',$aPurchase['package_id']);
            db()->update(Phpfox::getT('activitypoint_package_purchase'),['status' => $aParams['status'], 'time_stamp' => PHPFOX_TIME, 'price' => $aParams['total_paid'], 'payment_method' => $aParams['gateway']],'purchase_id = '.(int)$aPurchase['purchase_id']);
            db()->insert(Phpfox::getT('activitypoint_transaction'),[
                'user_id' => $aPurchase['user_id'],
                'module_id' => 'activitypoint',
                'type' => 'Bought',
                'action' => 'activitypoint_bought_package_action',
                'points' => $aPurchase['points'],
                'time_stamp' => PHPFOX_TIME
            ]);
            $aPoints = db()->select('a.activity_points, s.total_bought')
                                ->from(Phpfox::getT('user_activity'),'a')
                                ->leftJoin(Phpfox::getT('activitypoint_statistics'),'s','s.user_id = a.user_id')
                                ->where('a.user_id = '.(int)$aPurchase['user_id'])
                                ->execute('getSlaveRow');
            if (isset($aPoints['activity_points'])) {
                db()->update(Phpfox::getT('user_activity'), ['activity_points' => (int)$aPoints['activity_points'] + (int)$aPurchase['points']], 'user_id = '.(int)$aPurchase['user_id']);
            } else {
                db()->insert(Phpfox::getT('user_activity'), ['activity_points' => (int)$aPurchase['points'], 'user_id' => (int)$aPurchase['user_id']]);
            }
            if (isset($aPoints['total_bought']) && $aPoints['total_bought'] !== null) {
                db()->update(Phpfox::getT('activitypoint_statistics'),['total_bought' => (int)$aPoints['total_bought'] + (int)$aPurchase['points']], 'user_id = '.(int)$aPurchase['user_id']);
            } else {
                db()->insert(Phpfox::getT('activitypoint_statistics'), ['total_bought' => (int)$aPurchase['points'], 'user_id' => (int)$aPurchase['user_id']]);
            }
        }
    }

    private function _clearGlobalCache()
    {
        $this->cache()->removeGroup('activitypoint_packages');
    }
}