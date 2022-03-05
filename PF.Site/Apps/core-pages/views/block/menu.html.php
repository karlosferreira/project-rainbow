<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="page-block-action block">
    <div class="item-inner">
        <div class="item-info-main">
            <div class="item-image">
                <a href="{$aPage.link}" title="{$aPage.title}">
                    {if !empty($aPage.pages_image_path)}
                    <div class="img-wrapper">
                        {img server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.pages_image_path suffix='_200_square' no_default=false time_stamp=true}
                    </div>
                    {else}
                    {img server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.pages_image_path suffix='_200_square' no_default=false max_width=40 max_height=40 time_stamp=true}
                    {/if}
                </a>
            </div>
            <div class="item-info">
                <div class="item-title">
                    <span class="user_profile_link_span" id="js_user_name_link_{if !empty($aPage.page_user_id)}{$aPage.page_user_id}{/if}">
                        <a href="{$aPage.link}">{$aPage.full_name|clean}</a>
                    </span>
                </div>
                <div class="item-like-count">
                    {if $aPage.total_like == 1}
                        {_p var='1_like'}
                    {else}
                        {_p var='total_like_likes' total_like=$aPage.total_like}
                    {/if}
                </div>
            </div> 
        </div>

        {if $hasPermtoViewPublishDate}
        <div class="item-publish-date">
            <span class="item-title">{_p var='pages_publish_date_title'}: </span>
            <span class="item-date">{$aPage.time_stamp|convert_time}</span>

        </div>
        {/if}

        {if !empty($aPage.is_liked) && !Phpfox::getUserBy('profile_page_id')}
        <div class="item-btn-like dropdown">
            <button class="btn btn-primary btn-round dropdown-toggle btn-sm btn-icon" type="button" data-toggle="dropdown"><span class="ico ico-check"></span>{_p var='liked'}
                <span class="caret"></span></button>
            <ul class="dropdown-menu">
                <li><a role="button" onclick="$.ajaxCall('like.delete', 'type_id=pages&item_id={$aPage.page_id}&reload=true'); return false;"><span class="ico ico-thumbdown-o"></span>{_p var='unlike'}</a></li>
            </ul>
        </div>
        {else}
        <a href="#" class="btn btn-round btn-icon btn-default btn-sm pages_like_join" onclick="$(this).remove(); $.ajaxCall('like.add', 'type_id=pages&amp;item_id={$aPage.page_id}&amp;reload=1'); return false;">
            <span class="ico ico-thumbup-o"></span>{_p var='like'}
        </a>
        {/if}
    </div>
    <ul class="item-page-action">
        {if $aPage.is_admin}
            <li><a href="{url link='pages.add' id=$aPage.page_id}"><i class="ico ico-pencil"></i>{_p var='edit_page'}</a></li>
        {/if}
        {if $aPage.view_id == 0}
            {module name='share.link' type='pages' url=$aPage.link title=$aPage.title display='menu' sharefeedid=$aPage.page_id sharemodule='pages' extra_content='<i class="ico ico-share"></i>'}
        {/if}
        {if !$aPage.is_admin && Phpfox::getUserParam('pages.can_claim_page') && empty($aPage.claim_id)}
            <li>
                {if (int)$iClamePageUser ==  Phpfox::getUserId()}
                    <a href="javascript:void(0);" onclick="$.ajaxCall('pages.claimPageWithSameAdmin','page_id={$aPage.page_id}')">
                        <i class="fa fa-paper-plane"></i>{_p var='claim_page'}
                    </a>
                {else}
                    {if $bIsMessageActive == true }
                    <a href="#?call=contact.showQuickContact&amp;height=600&amp;width=600&amp;page_id={$aPage.page_id}" class="inlinePopup" title="{_p var='claim_page'}" data-app="core_pages" data-component="claim">
                        <i class="fa fa-paper-plane"></i>{_p var='claim_page'}
                    </a>
                    {else}
                    <a href="javascript:void(0);" onclick="$.ajaxCall('pages.claimPageWithSameAdmin','page_id={$aPage.page_id}')">
                        <i class="fa fa-paper-plane"></i>{_p var='claim_page'}
                    </a>
                    {/if}
                {/if}
            </li>
        {/if}
        {if Phpfox::isModule('report') && ($aPage.user_id != Phpfox::getUserId())}
        <li><a href="#?call=report.add&amp;height=210&amp;width=400&amp;type=pages&amp;id={$aPage.page_id}" class="inlinePopup" title="{_p var='Report this Page'}"><i class="ico ico-warning"></i>{_p var='report'}</a></li>
        {/if}
    </ul>
</div>