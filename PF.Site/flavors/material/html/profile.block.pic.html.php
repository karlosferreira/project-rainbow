<?php
    defined('PHPFOX') or exit('NO DICE!');
?>
<style type="text/css">
    .profiles_banner_bg .cover img.cover_photo
        {l}
        position: relative;
        left: 0;
        top: {$iCoverPhotoPosition}px;
    {r}
</style>
{literal}
<script>
    $Core.coverPhotoPositionTop = {/literal}{$iCoverPhotoPosition}{literal};
</script>
{/literal}
<div class="profiles_banner {if isset($aCoverPhoto.server_id)}has_cover{/if}">
    <div class="profiles_banner_bg">
        <div class="cover_bg"></div>
        <div class="cover-reposition-message">{_p var='drag_to_reposition_your_photo'}</div>

        {if !empty($sCoverPhotoLink)}
            <a href="{$sCoverPhotoLink}">
        {/if}
            <div class="cover" id="cover_bg_container">
                <div id="uploading-cover" style="display: none;">
                    <div class="progress">
                        <div class="progress-bar" role="progressbar" aria-valuenow="0"
                             aria-valuemin="0" aria-valuemax="100" style="width:0"></div>
                    </div>
                    <div>{_p var='uploading_your_photo'}...</div>
                </div>
                {if !empty($aCoverPhoto.destination)}
                    {img server_id=$aCoverPhoto.server_id path='photo.url_photo' file=$aCoverPhoto.destination suffix='' class="visible-lg cover_photo js_background_image"}
                {else}
                    <img class="_image_ image_deferred visible-lg cover_photo has_image js_background_image" style="display: none !important;">
                {/if}
                <span {if !empty($aCoverPhoto.destination)}style="background-image: url({img server_id=$aCoverPhoto.server_id path='photo.url_photo' file=$aCoverPhoto.destination suffix='_1024' return_url=true})"{/if} class="hidden-lg js_background_image is_responsive" {if empty($aCoverPhoto.destination)}style="display: none !important;"{/if}></span>
                {if empty($aCoverPhoto.destination) && !empty($sCoverDefaultUrl)}
                    <span class="js_default_background_image" style="background-image: url({$sCoverDefaultUrl})"></span>
                {/if}
            </div>
        {if !empty($aCoverPhoto.destination)}
        </a>
        {/if}
        <div class="cover-reposition-actions" id="js_cover_reposition_actions">
            <button role="button" class="btn btn-default" onclick="$Core.CoverPhoto.reposition.cancel()">{_p var='cancel'}</button>
            <button id="save_reposition_cover" class="btn btn-primary" onclick="$Core.CoverPhoto.reposition.save()">{_p var='save'}</button>
        </div>
    </div>

    {if Phpfox::getUserParam('profile.can_change_cover_photo')}
        {if Phpfox::getUserId() == $aUser.user_id}
            <div class="dropdown change-cover-block">
                <a title="{_p var='change_cover'}" role="button" data-toggle="dropdown" class=" btn btn-primary btn-gradient" id="js_change_cover_photo">
                    <span class="ico ico-camera"></span>
                </a>

                <ul class="dropdown-menu">
                    <li class="cover_section_menu_item">
                        <a href="{url link=$aUser.user_name'.photo'}">
                            {_p var='choose_from_photos'}
                        </a>
                    </li>
                    <li class="cover_section_menu_item">
                        <a role="button" id="js_change_cover_photo" onclick="return $Core.CoverPhoto.openUploadImage();">
                            {_p var='upload_photo'}
                        </a>
                    </li>
                    {if !empty($aUser.cover_photo)}
                        <li class="cover_section_menu_item reposition" role="presentation">
                            <a role="button" onclick="$Core.CoverPhoto.reposition.init('user', {$aUser.user_id}); return false;">{_p var='reposition'}</a></li>
                        <li class="cover_section_menu_item " role="presentation">
                            <a role="button" onclick="$('#cover_section_menu_drop').hide(); $.ajaxCall('user.removeLogo', 'user_id={$aUser.user_id}'); return false;">{_p var='remove_cover_photo'}</a></li>
                    {/if}
                </ul>
            </div>
        {elseif Phpfox::isAdmin() && !empty($aUser.cover_photo)}
            <div class="dropdown change-cover-block">
                <a title="{_p var='change_cover'}" role="button" data-toggle="dropdown" class=" btn btn-primary btn-gradient" id="js_change_cover_photo">
                    <span class="ico ico-camera"></span>
                </a>

                <ul class="dropdown-menu">
                    <li class="cover_section_menu_item " role="presentation">
                        <a role="button" onclick="$('#cover_section_menu_drop').hide(); $.ajaxCall('user.removeLogo', 'user_id={$aUser.user_id}'); return false;">{_p var='remove_cover_photo'}</a></li>
                </ul>
            </div>
        {/if}
    {/if}
	<div class="profile-info-block">
		<div class="profile-image">
            <div class="profile_image_holder">
                {if Phpfox::isModule('photo') && $aProfileImage}
                    <a href="<?php echo \Phpfox::permalink('photo', $this->_aVars['aProfileImage']['photo_id'], Phpfox::getParam('photo.photo_show_title', 1) ? $this->_aVars['aProfileImage']['title'] : null) ?>">
                        {$sProfileImage}
                    </a>
                {else}
                {$sProfileImage}
                {/if}
				{if Phpfox::getUserId() == $aUser.user_id}
				{literal}
				<script>
					function changingProfilePhoto() {
						if ($('.profile_image_holder').find('i.fa.fa-spin.fa-circle-o-notch').length > 0) {
							$('.profile_image_holder').find('a').show();
							$('.profile_image_holder').find('i.fa.fa-spin.fa-circle-o-notch').remove();
						}
						else {
							$('.profile_image_holder').find('a').hide();
							$('.profile_image_holder').append('<i class="fa fa-circle-o-notch fa-spin"></i>');
						}
					};
				</script>
				{/literal}
                <div class="profile_change_photo_pseudo">
                    <form action="#">
                        <label class="btn-primary btn-gradient" title="{_p var='change_photo'}" onclick="$Core.ProfilePhoto.update({if $sPhotoUrl}'{$sPhotoUrl}'{else}false{/if}, {$iServerId})"><span class="ico ico-camera"></span></label>
                    </form>
                </div>
				{/if}
            </div>
		</div>

		<div class="profile-info">
			<div class="profile-extra-info">
				<h1 {if Phpfox::getParam('user.display_user_online_status')}class="has-status-online"{/if}>
                    <a href="{if isset($aUser.link) && !empty($aUser.link)}{url link=$aUser.link}{else}{url link=$aUser.user_name}{/if}" title="{$aUser.full_name|clean} {if Phpfox::getUserParam('profile.display_membership_info')} &middot; {_p var=$aUser.title}{/if}">
                        {$aUser.full_name|clean}
                    </a>
                    {if Phpfox::getParam('user.display_user_online_status')}
                        {if $aUser.is_online}
                            <span class="user_is_online" title="{_p var='online'}"><i class="fa fa-circle js_hover_title"></i></span>
                        {else}
                            <span class="user_is_offline" title="{_p var='offline'}"><i class="fa fa-circle js_hover_title"></i></span>
                        {/if}
                    {/if}
				</h1>
				<div class="profile-info-detail">
					{if (!empty($aUser.gender_name))}
						{$aUser.gender_name}<b>.</b>
					{/if}
					{if User_Service_Privacy_Privacy::instance()->hasAccess('' . $aUser.user_id . '', 'profile.view_location') && (!empty($aUser.city_location) || !empty($aUser.country_child_id) || !empty($aUser.location))}
						<span>
						    {_p var='lives_in'}
                            {if !empty($aUser.city_location)}&nbsp;{$aUser.city_location}{/if}
						    {if !empty($aUser.city_location) && (!empty($aUser.country_child_id) || !empty($aUser.location))},{/if}
						    {if !empty($aUser.country_child_id)}&nbsp;{$aUser.country_child_id|location_child},{/if}
                            {if !empty($aUser.location)}&nbsp;{$aUser.location}{/if}<b>.</b></span>
					{/if}

					{if isset($aUser.birthdate_display) && is_array($aUser.birthdate_display) && count($aUser.birthdate_display)}
						<span>
						{foreach from=$aUser.birthdate_display key=sAgeType item=sBirthDisplay}
                            {if $aUser.dob_setting == '2'}
                                {if $sBirthDisplay == 1}
                                    {_p var='1_year_old'}
                                {else}
                                    {_p var='age_years_old' age=$sBirthDisplay}
                                {/if}
                            {else}
                                {_p var='born_on_birthday' birthday=$sBirthDisplay}
                            {/if}
						{/foreach}<b>.</b></span>
					{/if}
					{if Phpfox::getParam('user.enable_relationship_status') && isset($sRelationship) && $sRelationship != ''}<span>{$sRelationship}</span><b>.</b>{/if}
					{if isset($aUser.category_name)}<span>{$aUser.category_name|convert}</span>{/if}
				</div>

                {plugin call='profile.template_block_pic_info'}

            </div>

			<div class="profile-actions">
				{if Phpfox::getUserId() == $aUser.user_id}
                    <a class="btn btn-default btn-icon btn-round" role="link" href="{url link='user.profile'}">
                        <span class="ico ico-pencilline-o mr-1"></span>
                        {_p var='edit_profile'}
                    </a>
				{/if}
				{if Phpfox::getUserId() != $aUser.user_id}
                    {if (isset($aUser.is_friend_request) && $aUser.is_friend_request == 2)}
                        <div class="dropdown pending-request">
                            <a class="btn btn-default btn-round" data-toggle="dropdown">
                                <span class="ico ico-clock-o mr-1"></span>
                                {_p var='pending_friend_request'}
                                <span class="ico ico-caret-down ml-1"></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li class="item-delete">
                                    <a href="javascript:void(0)" onclick="$.ajaxCall('friend.removePendingRequest', 'id={$aUser.is_friend_request_id}','GET');">
                                        <span class="ico ico-ban mr-1"></span>
                                        {_p var='cancel_request'}
                                    </a>
                                </li>
                            </ul>
                        </div>
                    {/if}
				
				    <div class="profile-action-block profile-viewer-actions dropdown">
                        {if Phpfox::isUser() && Phpfox::isModule('friend') && !$aUser.is_friend && $aUser.is_friend_request !== 2}
                            {if $aUser.is_friend_request === 3}
                                <a class="btn btn-primary btn-icon btn-gradient btn-round add_as_friend_button" href="#" onclick="return $Core.addAsFriend('{$aUser.user_id}');" title="{_p var='add_to_friends'}">
                                    <span class="ico ico-user2-check-o"></span>
                                    <span class="">{_p var='confirm_friend_request'}</span>
                                </a>
                            {elseif empty($aUser.is_ignore_request) && Phpfox::getUserParam('friend.can_add_friends') && Phpfox::getService('user.privacy')->hasAccess('' . $aUser.user_id . '', 'friend.send_request')}
                                <a class="btn btn-primary btn-icon btn-gradient btn-round add_as_friend_button" href="#" onclick="return $Core.addAsFriend('{$aUser.user_id}');" title="{_p var='add_to_friends'}">
                                    <span class="ico ico-user1-plus-o"></span>
                                    <span class="">{_p var='add_to_friends'}</span>
                                </a>
                            {/if}
                        {/if}

                        {if $bCanSendMessage}
                            <a class="btn btn-default btn-icon btn-round" href="#" onclick="$Core.composeMessage({left_curly}user_id: {$aUser.user_id}{right_curly}); return false;">
                                <span class="ico ico-comment-o"></span>
                                <span class="">{_p var='message'}</span>
                            </a>
                        {/if}

					    {plugin call='profile.template_block_menu_more'}

                        {if (Phpfox::getUserBy('profile_page_id') <= 0) && (
                        (Phpfox::getUserParam('user.can_block_other_members') && isset($aUser.user_group_id) && Phpfox::getUserGroupParam('' . $aUser.user_group_id . '', 'user.can_be_blocked_by_others'))
                        || (Phpfox::isAppActive('Core_Activity_Points') && Phpfox::getUserParam('activitypoint.can_gift_activity_points'))
                        || (Phpfox::isModule('friend') && Phpfox::getUserParam('friend.link_to_remove_friend_on_profile') && isset($aUser.is_friend) && $aUser.is_friend === true)
                        || ($bCanPoke)
                        || (Phpfox::isUser() && $aUser.user_id != Phpfox::getUserId())
                        || (!empty($bShowRssFeedForUser))
                        )}
                            <div class="dropup btn-group">
                                <a class="btn" title="{_p var='more'}" data-toggle="dropdown" role="button">
                                    <span class="ico ico-dottedmore-o"></span>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-right">
                                    {if $bCanPoke}
                                    <li>
                                        <a class="inlinePopup" href="#" id="section_poke" onclick="$Core.box('poke.poke', 400, 'user_id={$aUser.user_id}'); return false;">
                                            <i class="ico ico-smile-o"></i>
                                            <span class="" >{_p var='poke' full_name=''}</span>
                                        </a>
                                    </li>
                                    {/if}

                                    {if Phpfox::getUserParam('user.can_block_other_members') && isset($aUser.user_group_id) && Phpfox::getUserGroupParam('' . $aUser.user_group_id . '', 'user.can_be_blocked_by_others')}
                                    <li>
                                        <a href="#?call=user.block&amp;height=120&amp;width=400&amp;user_id={$aUser.user_id}" class="inlinePopup js_block_this_user" title="{if $bIsBlocked}{_p var='unblock_this_user'}{else}{_p var='block_this_user'}{/if}"><span class="ico ico-ban mr-1"></span>{if $bIsBlocked}{_p var='unblock_this_user'}{else}{_p var='block_this_user'}{/if}</a>
                                    </li>
                                    {/if}

                                    {if Phpfox::isAppActive('Core_Activity_Points') && Phpfox::getUserParam('activitypoint.can_gift_activity_points')}
                                    <li>
                                        <a href="#?call=core.showGiftPoints&amp;height=120&amp;width=400&amp;user_id={$aUser.user_id}" class="inlinePopup js_gift_points" title="{_p var='gift_points'}">
                                            <span class="ico ico-gift-o mr-1"></span>
                                            {_p var='gift_points'}
                                        </a>
                                    </li>
                                    {/if}
                                    {if Phpfox::isUser() && $aUser.user_id != Phpfox::getUserId()}
                                    <li>
                                        <a href="#?call=report.add&amp;height=220&amp;width=400&amp;type=user&amp;id={$aUser.user_id}" class="inlinePopup" title="{_p var='report_this_user'}">
                                            <span class="ico ico-warning-o mr-1"></span>
                                            {_p var='report_this_user'}</a>
                                    </li>
                                    {/if}
                                    {if isset($bShowRssFeedForUser)}
                                        <li>
                                            <a href="{url link=''$aUser.user_name'.rss'}" class="no_ajax_link">
                                                <span class="ico ico-rss-o mr-1"></span>
                                                {_p var='subscribe_via_rss'}
                                            </a>
                                        </li>
                                    {/if}
                                    {if Phpfox::isModule('friend') && Phpfox::getUserParam('friend.link_to_remove_friend_on_profile') && isset($aUser.is_friend) && $aUser.is_friend === true}
                                    <li class="item-delete">
                                        <a href="#" onclick="$Core.jsConfirm({l}{r}, function(){l}$.ajaxCall('friend.delete', 'friend_user_id={$aUser.user_id}&reload=1');{r}, function(){l}{r}); return false;">
                                            <span class="ico ico-close-circle-o mr-1"></span>
                                            {_p var='remove_friend'}
                                        </a>
                                    </li>
                                    {/if}
                                    {plugin call='profile.template_block_menu'}
                                </ul>
                            </div>
                        {/if}
				    </div>
                    {if Phpfox::getUserParam('user.can_feature')}
                        <div class="btn-group dropup btn-gear">
                            <a class="btn" title="{_p var='options'}" data-toggle="dropdown">
                                <span class="ico ico-gear-o"></span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right">
                                {if Phpfox::getUserParam('user.can_feature')}
                                    <li {if !isset($aUser.is_featured) || (isset($aUser.is_featured) && !$aUser.is_featured)} style="display:none;" {/if} class="user_unfeature_member">
                                        <a href="#" title="{_p var='un_feature_this_member'}" onclick="$(this).parent().hide(); $(this).parents('.dropdown-menu').find('.user_feature_member:first').show(); $.ajaxCall('user.feature', 'user_id={$aUser.user_id}&amp;feature=0&amp;type=1&reload=1'); return false;"><span class="ico ico-diamond-o mr-1"></span>{_p var='unfeature'}</a>
                                    </li>
                                    <li {if isset($aUser.is_featured) && $aUser.is_featured} style="display:none;" {/if} class="user_feature_member">
                                        <a href="#" title="{_p var='feature_this_member'}" onclick="$(this).parent().hide(); $(this).parents('.dropdown-menu').find('.user_unfeature_member:first').show(); $.ajaxCall('user.feature', 'user_id={$aUser.user_id}&amp;feature=1&amp;type=1&reload=1'); return false;"><span class="ico ico-diamond-o mr-1"></span>{_p var='feature'}</a>
                                    </li>
                                {/if}
                            </ul>
                        </div>
                    {/if}
				{/if}
			</div>
		</div>
	</div>
</div>

<div class="profiles-menu set_to_fixed" data-class="profile_menu_is_fixed">
	<ul data-component="menu">
		<div class="overlay"></div>
		<li class="profile-image-holder hidden">
            <a href="{url link=$aUser.user_name}">
                {$sProfileImage}
            </a>
		</li>
		<li>
			<a href="{url link=$aUser.user_name}" class="{if $sModule == ''}active{/if}">
				<span class="ico ico-user-circle-o"></span>
				{_p var='profile'}
			</a>
		</li>
		<li>
			<a href="{url link=''$aUser.user_name'.info'}" class="{if $sModule == 'info'}active{/if}">
				<span class="ico ico-user1-text-o"></span>
				{_p var='info'}
			</a>
		</li>
        {if Phpfox::isModule('friend')}
		    <li class="">
				<a href="{url link=''$aUser.user_name'.friend'}" class="{if $sModule == 'friend'}active{/if}">
					<span class="ico ico-user1-two-o"></span>
					<span>
						{if $aUser.total_friend > 0}
						<span>{$aUser.total_friend}</span>
						{/if}
						{_p var='friends'}
					</span>
				</a>
			</li>
		{/if}
        {if $aProfileLinks}
            {foreach from=$aProfileLinks item=aProfileLink}
                <li class="">
                    <a href="{url link=$aProfileLink.url}" class="ajax_link {if isset($aProfileLink.is_selected)}active{/if}">
                        {if !empty($aProfileLink.icon_class)}
                            <span class="{$aProfileLink.icon_class} mr-1"></span>
                        {else}
                            <span class="ico ico-box-o mr-1"></span>
                        {/if}
                            <span>
                            {if isset($aProfileLink.total)}
                                <span class="badge_number_inline hide">{$aProfileLink.total|number_format}</span>
                            {/if}
                            {$aProfileLink.phrase}
                            </span>
                        {if isset($aProfileLink.total)}<span class="badge_number">{$aProfileLink.total|number_format}</span>{/if}
                    </a>
                </li>
            {/foreach}
		{/if}
		<li class="dropdown dropdown-overflow hide explorer">
            <a data-toggle="dropdown" role="button">
                <span class="ico ico-caret-down"></span>
            </a>
            <ul class="dropdown-menu dropdown-menu-right">
            </ul>
        </li>
	</ul>
    {template file='core.block.actions-buttons'}
</div>

<div class="js_cache_check_on_content_block" style="display:none;"></div>
<div class="js_cache_profile_id" style="display:none;">{$aUser.user_id}</div>
<div class="js_cache_profile_user_name" style="display:none;">{if isset($aUser.user_name)}{$aUser.user_name}{/if}</div>

{if Phpfox::getUserParam('profile.can_change_cover_photo') && Phpfox::getUserId() == $aUser.user_id}
    {template file='profile.block.upload-cover-form'}
{/if}

