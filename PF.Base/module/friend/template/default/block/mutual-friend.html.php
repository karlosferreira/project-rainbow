<?php 
/**
 * [PHPFOX_HEADER]
 * 
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: mutual-friend.html.php 2536 2011-04-14 19:37:29Z phpFox LLC $
 */
 
defined('PHPFOX') or exit('NO DICE!'); 

?>
<div class="user_rows_mini">
	{foreach from=$aMutualFriends key=iKey name=friend item=aUser}
	{template file='user.block.rows'}
	{/foreach}
</div>