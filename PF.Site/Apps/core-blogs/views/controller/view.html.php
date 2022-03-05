<?php 
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="blog-view">
	<div class="blog-info">
        <div class="blog-info-image">{img user=$aItem suffix='_120_square'}</div>
        <div class="blog-info-main">
            <span class="blog-author">{_p var='by_user' full_name=$aItem|user:'':'':50:'':'author'}</span>
		    <span class="blog-time">{_p var='on'} {$aItem.time_stamp|convert_time:'core.global_update_time'}</span>
        </div>
	</div>
	<div class="blog-total-viewlike item-comment">
        {if $aItem.is_approved == 1}
            <div>
                {module name='feed.mini-feed-action'}
            </div>
        {/if}
        <span class="blog-total-view item-total-view"><span class="blog-view-number blog-number">{$aItem.total_view|number_format}</span> {if $aItem.total_view == 1}{_p var='view_lowercase'}{else}{_p var='views_lowercase'}{/if}</span>
    </div>
	{if $aItem.permission_enable}
        <div class="item_bar blog-button-option">
            <div class="item_bar_action_holder">
                <a data-toggle="dropdown" class="item_bar_action"><span>{_p var='actions'}</span><i class="ico ico-gear-o"></i></a>
                <ul class="dropdown-menu dropdown-menu-right" id="js_blog_entry_options_{$aItem.blog_id}">
                    {template file='blog.block.link'}
                </ul>
            </div>
        </div>
	{/if}
    {if $aItem.is_approved != 1}
        {template file='core.block.pending-item-action'}
    {/if}
    {if !empty($aItem.image)}
    <a class="blog-image" href="{permalink module='blog' id=$aItem.blog_id title=$aItem.title}">
        <span style="background-image: url({$aItem.image})"></span>
    </a>
    {/if}
    <div class="blog-post item_content item_view_content">
        {$aItem.text|parse}
    </div>
    {if $aItem.total_attachment}
        <span>
            {module name='attachment.list' sType=blog iItemId=$aItem.blog_id}
        </span>
    {/if}
    {if (isset($sCategories) && $sCategories)}
        <div class="blog-category">
            {_p var='posted_in'}: {$sCategories}
        </div>
	{/if}
    {if isset($aItem.tag_list)}
        {module name='tag.item' sType=$sTagType sTags=$aItem.tag_list iItemId=$aItem.blog_id iUserId=$aItem.user_id sMicroKeywords='keywords'}
    {/if}

    {if $aItem.post_status != BLOG_STATUS_DRAFT}
    {addthis url=$aItem.bookmark_url title=$aItem.title}
    {/if}

	{plugin call='blog.template_controller_view_end'}
    {if $aItem.is_approved == 1}
        <div class="item-detail-feedcomment">
            {module name='feed.comment'}
        </div>
    {/if}

</div>
