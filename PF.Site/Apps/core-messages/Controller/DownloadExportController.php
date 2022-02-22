<?php
/**
 * [PHPFOX_HEADER]
 */

namespace Apps\Core_Messages\Controller;

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
class DownloadExportController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $url = $this->request()->get('url');


        if (empty($url) || !Phpfox::isModule('attachment')) {
            return Phpfox_Error::display(_p('no_such_download_found'));
        }

        $aRow = Phpfox::getService('attachment')->getForDownload($url, true);



        if (!isset($aRow['destination'])) {
            return Phpfox_Error::display(_p('no_such_download_found'));
        }

        $sPath = Phpfox::getParam('core.dir_attachment') . sprintf($aRow['destination'], '');

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