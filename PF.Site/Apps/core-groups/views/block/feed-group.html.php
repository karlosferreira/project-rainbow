<div class="group-app core-feed-item">
    <div class="item-outer">
        <!-- image thumbnail -->
        <div class="item-avatar">
            {if !empty($aGroup.image_path)}
            <span class="item-media-src" style="background-image: url({img server_id=$aGroup.server_id title=$aGroup.title path='pages.url_image' file=$aGroup.image_path suffix='_200_square' no_default=false max_width=200 time_stamp=true return_url=true})"  alt="{$aGroup.title}"></span>
            {else}
            {img thickbox=true server_id=$aGroup.image_server_id title=$aGroup.title path='pages.url_image' file=$aGroup.pages_image_path suffix='_200_square' no_default=false max_width=200 time_stamp=true}
            {/if}
        </div>
        <div class="item-inner">
            <div class="item-title">      
                <a href="{$aGroup.group_url}" class="core-feed-title line-1" itemprop="url">{$aGroup.title}</a>
            </div>
            <div class="item-info-wrapper core-feed-minor">
                <span class="item-info">
                    {if $aGroup.parent_category_name}
                        <a href="{$aGroup.type_link}">
                            {if Phpfox::isPhrase($this->_aVars['aPage']['parent_category_name'])}
                            {_p var=$aGroup.parent_category_name}
                            {else}
                            {$aGroup.parent_category_name|convert}
                            {/if}
                        </a> »
                        {/if}
                        {if $aGroup.category_name}
                        <a href="{$aGroup.category_link}">
                            {if Phpfox::isPhrase($this->_aVars['aPage']['category_name'])}
                            {_p var=$aGroup.category_name}
                            {else}
                            {$aGroup.category_name|convert}
                            {/if}
                        </a>
                        {/if}
                </span>
                <span class="item-info">{$aGroup.total_like} {if $aGroup.total_like > 1}{_p var='groups_members'}{else}{_p var='groups_member'}{/if}</span>
            </div>
            <div class="item-desc-wrapper">
                <div class="item-desc core-feed-description line-2">
                    {$aGroup.text_parsed|parse}
                </div>
                <div class="item-action js_group_feed_action_content" data-group-id="{$aGroup.page_id}" data-privacy="{$aGroup.reg_method}">
                    <!-- Please check and show only 1 button for suitable case -->
                    <span class="item-action-btn {if !empty($aGroup.is_requested) || $aGroup.is_joined_group}hide{/if}">
                        <button class="btn btn-default btn-sm js_group_action_btn" onclick="return $Core.Groups.processGroupFeed(this);" data-type="like" data-ajax="{if $aGroup.reg_method == 1}groups.signup{else}like.add{/if}">{_p var='groups_join_group'}</button>
                    </span>
                    <span class="item-action-btn {if !empty($aGroup.is_requested) || !$aGroup.is_joined_group}hide{/if}">
                        <button class="btn btn-default btn-sm js_group_action_btn" onclick="return $Core.Groups.processGroupFeed(this);" data-type="unlike" data-ajax="like.delete">{_p var='groups_joined_group'}</button>
                    </span>
                    <span class="item-action-btn {if empty($aGroup.is_requested)}hide{/if}">
                        <button class="btn btn-default btn-sm js_group_action_btn" onclick="return $Core.Groups.processGroupFeed(this);" data-type="request" data-ajax="groups.deleteRequest">{_p var='delete_request'}</button>
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>