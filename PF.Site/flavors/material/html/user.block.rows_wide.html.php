<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="item-outer">
    <div class="item-inner">
        <div class="item-media">
            {img user=$aUser suffix='_200_square' max_width=200 max_height=200}
        </div>
        <div class="user-info">
            <div class="user-title">
                {$aUser|user}
            </div>
            {module name='user.friendship' friend_user_id=$aUser.user_id type='icon' extra_info=true no_button=true mutual_list=true}
            {module name='user.info' friend_user_id=$aUser.user_id number_of_info=2}

            {if Phpfox::getUserParam('user.can_feature')}
            <div class="dropdown admin-actions">
                <a href="" data-toggle="dropdown" class="btn btn-sm s-4">
                    <span class="ico ico-gear-o"></span>
                </a>

                <ul class="dropdown-menu dropdown-menu-right">
                    <li {if !isset($aUser.is_featured) || (isset($aUser.is_featured) && !$aUser.is_featured)} style="display:none;" {/if} class="user_unfeature_member">
                    <a href="#" title="{_p var='un_feature_this_member'}" onclick="$(this).parent().hide(); $(this).parents('.dropdown-menu').find('.user_feature_member:first').show(); $.ajaxCall('user.feature', 'user_id={$aUser.user_id}&amp;feature=0&amp;type=1&reload=1'); return false;"><span class="ico ico-diamond-o mr-1"></span>{_p var='unfeature'}</a>
                    </li>
                    <li {if isset($aUser.is_featured) && $aUser.is_featured} style="display:none;" {/if} class="user_feature_member">
                    <a href="#" title="{_p var='feature_this_member'}" onclick="$(this).parent().hide(); $(this).parents('.dropdown-menu').find('.user_unfeature_member:first').show(); $.ajaxCall('user.feature', 'user_id={$aUser.user_id}&amp;feature=1&amp;type=1&reload=1'); return false;"><span class="ico ico-diamond-o mr-1"></span>{_p var='feature'}</a>
                    </li>
                </ul>
            </div>
            {/if}
        </div>
        {if Phpfox::isUser() && $aUser.user_id != Phpfox::getUserId()}
        <div class="dropup friend-actions js_friend_actions_{$aUser.user_id}">
            {template file='user.block.friend-action'}
        </div>
        {/if}
    </div>
    {if isset($aUser.is_featured) && $aUser.is_featured}
    <div class="item-featured" title="{_p var='featured'}">
        <span class="ico ico-diamond"></span>
    </div>
    {/if}
</div>