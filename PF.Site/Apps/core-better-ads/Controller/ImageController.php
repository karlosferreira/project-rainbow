<?php

namespace Apps\Core_BetterAds\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_File;
use Phpfox_Image;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Class ImageController
 * @package Apps\Core_BetterAds\Controller
 */
class ImageController extends Phpfox_Component
{
    public function process()
    {
        if (!Phpfox::isUser()) {
            exit;
        }

        $aImage = Phpfox_File::instance()->load('image', array('jpg', 'gif', 'png'));

        if ($aImage === false) {
            echo '<script type="text/javascript">window.parent().$(\'#js_image_error\').show();</script>';
            exit;
        }
        //default ads size
        $aParts = [
            300,
            300,
        ];
        if ($sFileName = Phpfox_File::instance()->upload('image', Phpfox::getParam('ad.dir_image'),
            Phpfox::getUserId() . uniqid())) {
            Phpfox_Image::instance()->createThumbnail(Phpfox::getParam('ad.dir_image') . sprintf($sFileName, ''),
                Phpfox::getParam('ad.dir_image') . sprintf($sFileName, '_thumb'), ($aParts[0] / 3), ($aParts[1] - 20));

            Phpfox_File::instance()->unlink(Phpfox::getParam('ad.dir_image') . sprintf($sFileName, ''));

            rename(Phpfox::getParam('ad.dir_image') . sprintf($sFileName, '_thumb'),
                Phpfox::getParam('ad.dir_image') . sprintf($sFileName, ''));

            Phpfox::getLib('cdn')->put(Phpfox::getParam('ad.dir_image') . sprintf($sFileName, ''));

            echo '<script type="text/javascript">window.parent.$(\'.js_ad_image\').html(\'<a href="#ad-link"><img src="' . Phpfox::getParam('ad.url_image') . sprintf($sFileName,
                    '') . '" alt="" /></a>\').show(); window.parent.$(\'#js_image_holder_message\').hide(); window.parent.$(\'#js_image_holder_link\').show(); window.parent.$(\'#js_image_id\').val(\'' . sprintf($sFileName,
                    '') . '\');</script>';
        }

        exit;
    }

    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('ad.component_controller_image_clean')) ? eval($sPlugin) : false);
    }
}
