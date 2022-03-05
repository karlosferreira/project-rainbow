<?php
defined('PHPFOX') or exit('NO DICE!');

if (isset($this->_aUser['user_id']) && $this->_aUser['user_id'] > 0 && Phpfox::getParam('subscribe.subscribe_is_required_on_sign_up') && $this->_aUser['user_group_id'] == '2' && $this->_aUser['subscribe_id'] > 0)
{
	$bSetDirect = (((Phpfox_Request::instance()->get('req1') == 'subscribe' && Phpfox_Request::instance()->get('req2') == 'renew-method')) || ((Phpfox_Request::instance()->get('req1') == 'subscribe' && Phpfox_Request::instance()->get('req2') == 'register')) || ((Phpfox_Request::instance()->get('req1') == 'user' && Phpfox_Request::instance()->get('req2') == 'logout'))) ? false : true;

	if ($bSetDirect === true)
	{
        require_once(PHPFOX_DIR_SITE . 'Apps' . PHPFOX_DS . 'core-subscriptions' . PHPFOX_DS . 'Service' . PHPFOX_DS . 'Purchase' . PHPFOX_DS . 'Purchase.php' );
        (new Apps\Core_Subscriptions\Service\Purchase\Purchase())->setRedirectId($this->_aUser['subscribe_id']);
	}
}
