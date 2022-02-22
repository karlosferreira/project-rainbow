<?php
namespace Apps\Core_Activity_Points\Controller;

use Phpfox_Component;

defined('PHPFOX') or exit('NO DICE!');

/**
 * Return for Paypal
 * Class CompleteController
 * @package Apps\Core_Activity_Points\Controller
 */
class CompleteController extends Phpfox_Component
{
    public function process()
    {
        $this->url()->send('activitypoint.package',null, _p('Purchase package successfully.'));
    }
}