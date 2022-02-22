<?php

namespace Apps\P_StatusBg\Service;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_File;
use Phpfox_Service;

class pstatusbg extends Phpfox_Service
{
    private static $_iLimitBackgrounds = 30;
    private $_sBTable;
    private $_sSBTable;

    public function __construct()
    {
        $this->_sTable = Phpfox::getT('pstatusbg_collections');
        $this->_sBTable = Phpfox::getT('pstatusbg_backgrounds');
        $this->_sSBTable = Phpfox::getT('pstatusbg_status_background');
    }

    public function getForManage($iLimit, $iPage, &$iCnt)
    {
        $iCnt = db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('is_deleted = 0')
            ->execute('getField');
        $aItems = [];
        if ($iCnt) {
            $aItems = db()->select('c.*, b.image_path, b.server_id')
                ->from($this->_sTable, 'c')
                ->leftJoin($this->_sBTable, 'b', 'c.main_image_id = b.background_id')
                ->limit($iPage, $iLimit, $iCnt)
                ->where('c.is_deleted = 0')
                ->order('c.is_active DESC, c.is_default ASC')
                ->execute('getSlaveRows');
            foreach ($aItems as $key => $aItem) {
                $this->getBackgroundImage($aItems[$key]);
            }
        }
        return $aItems;
    }

    /**
     * @param $aBackground
     * @param int $sSuffix
     * @param string $sName
     */
    public function getBackgroundImage(&$aBackground, $sSuffix = 300, $sName = 'full_path')
    {
        if (!empty($aBackground['image_path'])) {
            if ($aBackground['view_id'] > 0) {
                $aBackground[$sName] = Phpfox::getParam('core.path_actual') . 'PF.Site/Apps/p-status-background/assets/images/default-collection/' . ($sSuffix == 48 ? str_replace('-min', '-sm', $aBackground['image_path']) : $aBackground['image_path']);
            } else {
                $aBackground[$sName] = Phpfox::getLib('image.helper')->display([
                    'server_id' => $aBackground['server_id'],
                    'path' => 'core.url_pic',
                    'file' => $aBackground['image_path'],
                    'suffix' => '_' . $sSuffix,
                    'return_url' => true
                ]);
            }
        }
    }

    /**
     * @param $iId
     * @return array|bool|int|string
     */
    public function getForEdit($iId)
    {
        Phpfox::isAdmin(true);
        if (!$iId) {
            return false;
        }
        $aItem = db()->select('*')
            ->from($this->_sTable)
            ->where('is_deleted = 0 AND collection_id =' . (int)$iId)
            ->execute('getRow');
        $this->getBackgroundImage($aItem);
        return $aItem;
    }

    /**
     * @param null $aParams
     * @return array
     */
    public function getUploadParams($aParams = null)
    {
        if (isset($aParams['id'])) {
            $iTotalStickers = $this->countBackground($aParams['id']);
            $iRemainImage = self::$_iLimitBackgrounds - $iTotalStickers;
        } else {
            $iRemainImage = self::$_iLimitBackgrounds;
        }
        $iMaxFileSize = Phpfox::getLib('file')->getLimit(1);
        $aEvents = [
            'sending' => 'pstatusbg_admin.dropzoneOnSending',
            'success' => 'pstatusbg_admin.dropzoneOnSuccess',
            'queuecomplete' => 'pstatusbg_admin.dropzoneQueueComplete',
            'removedfile' => 'pstatusbg_admin.dropzoneOnRemoveFile',
            'error' => 'pstatusbg_admin.dropzoneOnError',
            'init' => 'pstatusbg_admin.dropzoneOnInit'
        ];
        return [
            'max_size' => ($iMaxFileSize === 0 ? null : $iMaxFileSize),
            'upload_url' => Phpfox::getLib('url')->makeUrl('admincp.pstatusbg.frame-upload'),
            'component_only' => true,
            'max_file' => $iRemainImage,
            'js_events' => $aEvents,
            'upload_now' => "false",
            'submit_button' => '',
            'first_description' => _p('drag_n_drop_multi_images_here_to_upload'),
            'upload_dir' => Phpfox::getParam('core.dir_pic') . 'pstatusbg/',
            'upload_path' => Phpfox::getParam('core.url_pic') . 'pstatusbg/',
            'update_space' => true,
            'no_square' => true,
            'type_list' => ['jpg', 'png'],
            'style' => '',
            'type_description' => _p('you_can_upload_a_jpg_jpeg_or_png_file'),
            'extra_description' => [
                _p('maximum_images_you_can_upload_is_number', ['number' => $iRemainImage])
            ],
            'thumbnail_sizes' => Phpfox::getParam('pstatusbg.thumbnail_sizes'),
            'max_size_description' => _p('the_file_size_limit_is_file_size_if_your_upload_does_not_work_try_uploading_a_smaller_image', ['file_size' => Phpfox_File::filesize($iMaxFileSize * 1048576)])
        ];
    }

    /**
     * @param $iId
     * @return array|int|string
     */
    public function countBackground($iId)
    {
        return db()->select('total_background')
            ->from($this->_sTable)
            ->where('collection_id =' . (int)$iId)
            ->execute('getField');
    }

    /**
     * @param null $iId
     * @return array|int|string
     */
    public function countTotalActiveCollection($iId = null)
    {
        return db()->select('COUNT(*)')
            ->from($this->_sTable)
            ->where('is_active = 1 AND is_deleted = 0' . ($iId ? ' AND collection_id <> ' . (int)$iId : ''))
            ->execute('getField');
    }

    public function getCollectionsList($bActive = true, $bGetBg = true)
    {
        $sCond = 'sc.is_deleted = 0';
        if ($bActive) {
            $sCond .= ' AND sc.is_active = 1';
        }
        $sCacheId = $this->cache()->set('pstatusbg_collection_listing_' . md5($sCond . '_' . $bGetBg));
        $this->cache()->group('pstatusbg', $sCacheId);
        if (!($aRows = $this->cache()->get($sCacheId))) {
            $aRows = db()->select('sc.*')
                ->from($this->_sTable, 'sc')
                ->where($sCond)
                ->order('sc.is_default ASC')
                ->execute('getSlaveRows');
            if ($bGetBg && count($aRows)) {
                foreach ($aRows as $key => $aRow) {
                    $aRows[$key]['backgrounds_list'] = $this->getImagesByCollection($aRow['collection_id'], null, 48);
                    if (!count($aRows[$key]['backgrounds_list'])) {
                        unset($aRows[$key]);
                    }
                }
            }
            $this->cache()->save($sCacheId, $aRows);
        }
        return $aRows;
    }

    /**
     * @param $iId
     * @param null $iLimit
     * @param int $sSuffix
     * @return array|int|string
     */
    public function getImagesByCollection($iId, $iLimit = null, $sSuffix = 300)
    {
        if ($iLimit != null) {
            db()->limit($iLimit);
        }
        $aImages = db()->select('*')
            ->from($this->_sBTable)
            ->where('is_deleted = 0 AND collection_id = ' . (int)$iId)
            ->order('ordering ASC, background_id ASC')
            ->execute('getSlaveRows');
        if ($aImages) {
            foreach ($aImages as $key => $aImage) {
                $this->getBackgroundImage($aImages[$key], $sSuffix);
            }
        }
        return $aImages;
    }

    public function getFeedStatusBackground($iItemId = 0, $sType = '', $iUserId = 0, $bNoImage = false)
    {
        if (!$iItemId || ($sType != 'user_status' && !preg_match('/^(.*)_comment$/i', $sType))) {
            return false;
        }
        $aBackground = db()->select(($bNoImage ? 'sb.*,' : '') . 'b.*')
            ->from($this->_sSBTable, 'sb')
            ->join($this->_sBTable, 'b', 'b.background_id = sb.background_id')
            ->where('sb.item_id = ' . (int)$iItemId . ' AND sb.type_id = \'' . $sType . '\' AND sb.user_id =' . (int)$iUserId . ($bNoImage ? '' : ' AND sb.is_active = 1'))
            ->execute('getRow');
        if ($bNoImage) {
            return $aBackground;
        }
        if ($aBackground) {
            $this->getBackgroundImage($aBackground, 1024);
        }
        return isset($aBackground['full_path']) ? $aBackground['full_path'] : '';
    }
}