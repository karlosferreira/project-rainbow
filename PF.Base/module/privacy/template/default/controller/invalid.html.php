<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package  		Module_Privacy
 * @version 		$Id: invalid.html.php 3661 2011-12-05 15:42:26Z phpFox LLC $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
<div class="message">
	{_p var='the_item_or_section_you_are_trying_to_view_has_specific_privacy_settings_enabled_and_cannot_be_viewed_at_this_time'}
</div>
<ul>
	<li><a href="#" onclick="history.back(); return false;">{_p var='go_back'}</a></li>
	<li><a href="{url link=''}">{_p var='go_to_our_homepage'}</a></li>
</ul>