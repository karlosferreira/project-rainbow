<?php

namespace Apps\Core_Captcha\Block;

use Phpfox_Component;
use Phpfox_Plugin;
use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class Form extends Phpfox_Component
{
    public function process()
    {
        $sCaptchaType = Phpfox::getParam('captcha.captcha_type');
        $sRecaptchaType = Phpfox::getParam('captcha.recaptcha_type', 2);
        $sRecaptchaV3Img = setting('core.path_actual') . 'PF.Site/Apps/core-captcha/assets/image/recaptcha_v3.png';

        $this->template()->assign(array(
                'sCaptchaType' => $sCaptchaType,
                'sRecaptchaType' => $sRecaptchaType,
                'sRecaptchaPublicKey' => Phpfox::getParam('captcha.recaptcha_public_key'),
                'sImage' => $this->url()->makeUrl('captcha.image', array('id' => md5(rand(100, 1000)))),
                'sCaptchaData' => null,
                'sCatpchaType' => $this->getParam('captcha_type', null),
                'bCaptchaPopup' => $this->getParam('captcha_popup', false),
                'sRecaptchaV3Img' => $sRecaptchaV3Img

            )
        );

        (($sPlugin = Phpfox_Plugin::get('captcha.component_block_form_process')) ? eval($sPlugin) : false);
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('captcha.component_block_form_clean')) ? eval($sPlugin) : false);
    }
}
