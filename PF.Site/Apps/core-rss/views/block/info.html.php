<?php 
defined('PHPFOX') or exit('NO DICE!');
?>

<div style="font-size: 28pt; font-weight: bold;" class="t_center">
	<a href="{url link='rss.log'}">{$iRssCount}</a>
</div>
<div class="separate"></div>
<div class="p_top_4">
{_p var='rss_feed_url'}:
	<div class="p_4">
		<input name="#" value="{url link='profile.rss'}" size="22" onfocus="this.select();" style="width: 90%;" type="text">
	</div>
</div>
