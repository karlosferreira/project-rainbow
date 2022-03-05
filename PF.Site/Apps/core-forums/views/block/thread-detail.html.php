<?php
	defined('PHPFOX') or exit('NO DICE!');
?>

{if !isset($bIsPostUpdateText)}
	<article class="">
        {if (!isset($sPermaView) || $sPermaView === null) && $aPost.count != 0 && ((isset($bShowModerator) && $bShowModerator)|| (isset($bIsAdmin) && $bIsAdmin))}
        <div class="moderation_row">
            <label class="item-checkbox">
                <input type="checkbox" class="js_global_item_moderate" name="item_moderate[]" value="{$aPost.post_id}" id="check{$aPost.post_id}" />
                <i class="ico ico-square-o"></i>
            </label>
        </div>
        {/if}
		<div id="post{$aPost.post_id}" class="item-outer">
			{/if}
				<div class="forum_outer">
					<div class="forum_outer-inner">
						<div class="item-media">
							{img user=$aPost suffix='_120_square'}
						</div>

						<div class="item-inner ml-1">
							<div class="item-author">
								{if isset($aPost.cache_name) && $aPost.cache_name}
									<span class="user_profile_link_span"><a href="#">{$aPost.cache_name|clean}</a></span>
								{else}
									{$aPost|user:'':'':25}
								{/if}
							</div>
							<div class="item-time forum-text-overflow">
                                <time>{$aPost.time_stamp|convert_time}</time>
                            </div>
						</div>
						<div class="item-option">
							<a class="forum_post_count" href="{ permalink module='forum.thread' id=$aPost.thread_id title=$aThread.title}view_{$aPost.post_id}">#{$aPost.count}</a>
							{if $aPost.count == 0 && $aThread.hasPermission}
								<div class="dropdown">
									<span data-toggle="dropdown" class="row_edit_bar_action"><i class="ico ico-angle-down"></i></span>
									<ul class="dropdown-menu dropdown-menu-right">
				                        {template file='forum.block.menu'}
									</ul>
								</div>
							{/if}
						</div>
					</div>
                    {if $aPost.view_id}
                        <div class="mt-2">
                            {assign var=aPendingItem value=$aPost.pending_action}
                            {template file='core.block.pending-item-action'}
                        </div>
                    {/if}
					<div class="item-description mt-2 item_view_content" id="js_post_edit_text_{$aPost.post_id}">
						{$aPost.text|parse|split:55}
					</div>
                    
					{if !empty($aPoll.question) && $aPost.count == 0}
						<div class="table_info">
							{_p var='poll'}: {$aPoll.question|clean}
						</div>
						<div class="forum_poll_content">
							{template file='poll.block.entry'}
						</div>
					{/if}
                    {if isset($aPost.attachments)}
                        {module name='attachment.list' sType=forum attachments=$aPost.attachments}
                    {/if}
					{if isset($aThread.tag_list) && $aPost.count == 0}
                        {module name='tag.item' sType='forum' sTags=$aThread.tag_list iItemId=$aThread.thread_id iUserId=$aThread.user_id sMicroKeywords='keywords'}
                    {/if}
                    {if Phpfox::getUserParam('core.can_view_update_info') && !empty($aPost.update_user)}
                    <div class="help-block p_10">
                        <i>{$aPost.last_update_on}</i>
                    </div>
                    {/if}
				</div>
			{if !isset($bIsPostUpdateText)}
		</div>

		{if isset($aPost.aFeed) && $aPost.view_id == 0}
			<div class="forum_time_stamp item-detail-feedcomment">
				{if Phpfox::isModule('feed')}
					{module name='feed.comment' aFeed=$aPost.aFeed}
				{else}
					<div class="js_feed_comment_border comment-content">
					<div class="js_parent_feed_entry parent_item_feed">
						<div class="comment_mini_content_border">
							<div class="feed-options-holder item-options-holder " data-component="feed-options">
								<a role="button" data-toggle="dropdown" href="#" class="feed-options item-options" aria-expanded="true">
									<span class="ico ico-dottedmore-o"></span>
								</a>
								<ul class="dropdown-menu dropdown-menu-right">
									{if Phpfox::isModule('report') && isset($aPost.aFeed.report_module) && isset($aPost.aFeed.force_report)}
										<li><a href="#?call=report.add&amp;height=100&amp;width=400&amp;type={$aPost.aFeed.report_module}&amp;id={$aPost.aFeed.item_id}" class="inlinePopup activity_feed_report" title="{$aPost.aFeed.report_phrase}"><i class="fa fa-exclamation-triangle"></i>{_p var='report'}</a></li>
									{/if}
								    {plugin call='core.template_block_comment_border_new'}
								</ul>
							</div>
                            <div class="comment-mini-content-commands" >
                                <div class="button-like-share-block">
                                    {plugin call='feed.template_block_comment_commands_1'}
                                    {plugin call='feed.template_block_comment_commands_2'}
                                </div>
                            </div>
						</div>
					</div>
					</div>
				{/if}
			</div>
		{/if}

	</article>
{/if}
