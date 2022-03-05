<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\PHPfox_IM\Controller;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_File;

/**
 *
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author           phpFox LLC
 * @package          Module_IM
 * @version          $Id: DownloadController.php 2626 2011-05-24 13:24:52Z phpFox LLC $
 */
class DownloadController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::isUser(true);
        $url = $this->request()->get('url');

        if (empty($url) || !Phpfox::isModule('attachment')) {
            return Phpfox_Error::display(_p('no_such_download_found'));
        }

        $aRow = Phpfox::getService('attachment')->getForDownload($url, true);

        if (!isset($aRow['destination'])) {
            return Phpfox_Error::display(_p('no_such_download_found'));
        }

        $sPath = Phpfox::getParam('core.dir_attachment') . sprintf($aRow['destination'], '');

        if (Phpfox::hasCallback($aRow['category_id'], 'attachmentControl')) {
            $bAllowed = Phpfox::callback($aRow['category_id'] . '.attachmentControl', $aRow['item_id']);
            if ($bAllowed == false) {
                return Phpfox_Error::display(_p('you_are_not_allowed_to_download_this_attachment'));
            }
        }
        Phpfox::getService('attachment.process')->updateCounter($aRow['attachment_id']);

        if (in_array($aRow['mime_type'], ['video/mov', 'video/mp4', 'video/ogg']) && (strstr($_SERVER['HTTP_USER_AGENT'], 'iPhone') || strstr($_SERVER['HTTP_USER_AGENT'], 'iPad'))) {
            $sPath = Phpfox::getLib('cdn')->getUrl(str_replace(PHPFOX_DIR, Phpfox::getParam('core.path_file'), $sPath), $aRow['server_id']);
            $this->url()->forward($sPath);
        } else {
            Phpfox_File::instance()->forceDownload($sPath, $aRow['file_name'], $aRow['mime_type'], $aRow['file_size'], $aRow['server_id']);
        }
        exit;
    }
}