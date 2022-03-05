<?php
defined('PHPFOX') or exit('NO DICE!');
?>

<div class="content-text" id="js_comment_text_{$aComment.comment_id}">{$aComment.text|comment_parse|shorten:'300':'comment.view_more':true}</div>
{if !empty($aComment.extra_data)}
    {if $aComment.extra_data.extra_type == 'photo'}
        <div class="content-photo">
            <span class="item-photo">
                {img server_id=$aComment.extra_data.server_id path='core.url_pic' file="comment/".$aComment.extra_data.image_path suffix='_500' thickbox=true}
            </span>
        </div>
    {elseif $aComment.extra_data.extra_type == 'sticker'}
        <div class="content-sticker">
            <span class="item-sticker">
                {$aComment.extra_data.full_path}
            </span>
        </div>
    {elseif $aComment.extra_data.extra_type == 'preview' && !Phpfox::getParam('core.disable_all_external_urls')}
        <div class="comment-link" id="js_link_preview_{$aComment.comment_id}">
            <div class="content-link-{if !empty($aComment.extra_data.params.is_image_link)}photo{else}normal{/if}">
                {if !empty($aComment.extra_data.params.default_image)}
                    <a href="{$aComment.extra_data.params.actual_link|clean}" class="item-image no_ajax {if isset($aComment.extra_data.params.custom_css)}{$aComment.extra_data.params.custom_css}{/if}" target="_blank"><img src="{$aComment.extra_data.params.default_image}" alt="{if isset($aComment.extra_data.params.title)}{$aComment.extra_data.params.title|clean}{/if}"></a>
                {/if}
                {if empty($aComment.extra_data.params.is_image_link)}
                    <div class="item-inner">
                        <a href="{$aComment.extra_data.params.actual_link|clean}" class="item-title no_ajax {if isset($aComment.extra_data.params.custom_css)}{$aComment.extra_data.params.custom_css}{/if}" target="_blank">
                            {if isset($aComment.extra_data.params.title)}
                                {$aComment.extra_data.params.title|clean}
                            {else}
                                {$aComment.extra_data.params.link}
                            {/if}
                        </a>
                        {if isset($aComment.extra_data.params.host)}
                            <a href="{$aComment.extra_data.params.actual_link|clean}" class="item-info no_ajax {if isset($aComment.extra_data.params.custom_css)}{$aComment.extra_data.params.custom_css}{/if}" target="_blank">
                                {$aComment.extra_data.params.host}
                            </a>
                        {/if}
                    </div>
                {/if}
            </div>
        </div>
    {/if}
{/if}
