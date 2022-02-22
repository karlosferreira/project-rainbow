<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
{if count($aPages)}
<div class="help-block">{_p var='select_switch_below_to_use_this_site_as_a_page'}</div>

<div class="login-as-page-items">
    {foreach from=$aPages name=pages item=aPage}
    <div class="item-outer">
        <div class="item-inner">
            <div class="item-media-src s-6 mr-1">
                <a href="{$aPage.link}" title="{$aPage.title}">
                    {if !empty($aPage.image_path)}
                    <div class="img-wrapper">
                        {img server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.image_path suffix='_200_square' no_default=false max_width=50 max_height=50 time_stamp=true}
                    </div>
                    {else}
                    {img server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.image_path suffix='_200_square' no_default=false max_width=50 max_height=50 time_stamp=true}
                    {/if}

                </a>
            </div>
            <div class="item-info">
                <a href="{$aPage.link}">{$aPage.title|clean|split:20}</a>
                <button type="button" name="switch" class="btn btn-sm btn-gradient btn-primary" onclick="$.ajaxCall('pages.processLogin', 'page_id={$aPage.page_id}', 'GET')">
                    {_p var='switch'}
                </button>
            </div>
        </div>
    </div>
    {/foreach}
</div>
{else}
<div class="help-block">
    {_p var='you_currently_do_not_have_any_pages' link=$sLink}
</div>
{/if}