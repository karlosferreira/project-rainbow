<?php 

defined('PHPFOX') or exit('NO DICE!'); 

?>

<a href="#" type="button" id="btn_display_check_in{if isset($aForms.feed_id)}{$aForms.feed_id}{/if}" data-map-index="{if isset($aForms.feed_id)}{$aForms.feed_id}{/if}" class="dont-unbind-children activity_feed_share_this_one_link parent js_hover_title" onclick="return false;">
	<span class="ico ico-checkin-o"></span>
	<span class="js_hover_info">
		{_p var='check_in'}
	</span>
</a>

<script type="text/javascript">
    if (typeof $Core.FeedPlace === "undefined") {l}
        var script = document.createElement("script");
        script.type = "text/javascript";
        script.src = (oParams.hasOwnProperty('sAssetFileUrl') ? oParams.sAssetFileUrl : oParams.sJsHome) + 'module/feed/static/jscript/places.js';
        document.body.appendChild(script);
    {r}
	var bCheckinInit = false;
	$Behavior.prepareInitFeedPlaces = function()
	{l}
		$Core.FeedPlace.sIPInfoDbKey = '';
		$Core.FeedPlace.sGoogleKey = '{param var="core.google_api_key"}';
		
		{if isset($aVisitorLocation)}
			$Core.FeedPlace.setVisitorLocation({$aVisitorLocation.latitude}, {$aVisitorLocation.longitude} );
		{/if}
		$Core.FeedPlace.googleReady('{param var="core.google_api_key"}', '{if isset($aForms.feed_id)}{$aForms.feed_id}{/if}');
	{r}
</script>