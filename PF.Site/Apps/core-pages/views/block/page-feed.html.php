<div class="page-app core-feed-item">
    <div class="item-media-banner">
        <a class="item-media" href="{$aPage.page_url}">
            <span class="item-media-src" style="background-image: url({if !empty($aCoverPhoto)}{img server_id=$aCoverPhoto.server_id title=$aCoverPhoto.title path='photo.url_photo' file=$aCoverPhoto.destination suffix='' return_url=true}{else}{param var='pages.default_cover_photo'}{/if})"  alt="{$aCoverPhoto.title}"></span>
        </a>
    </div>
    <div class="item-outer">
        <div class="item-avatar">
            {if !empty($aPage.image_path)}
            <span class="item-media-src" style="background-image: url({img server_id=$aPage.server_id title=$aPage.title path='pages.url_image' file=$aPage.image_path suffix='_200_square' no_default=false max_width=200 time_stamp=true return_url=true})"  alt="{$aPage.title}"></span>
            {else}
            {img thickbox=true server_id=$aPage.image_server_id title=$aPage.title path='pages.url_image' file=$aPage.pages_image_path suffix='_200_square' no_default=false max_width=200 time_stamp=true}
            {/if}
        </div>
        <div class="item-inner">
            <div class="item-title-wrapper">
                <div class="item-title">      
                    <a href="{$aPage.page_url}" class="core-feed-title line-2" itemprop="url">{$aPage.title}</a>
                </div>
                <div class="item-action js_page_feed_action_content" data-page-id="{$aPage.page_id}">
                    <span class="item-action-btn {if $aPage.is_liked_page}hide{/if}">
                        <a href="javascript:void(0);" class="btn btn-default btn-sm btn-icon js_page_feed_action" data-type="like" onclick="Core_Pages.processPageFeed(this);"><i class="ico ico-thumbup-o"></i>{_p var='like'}</a>
                    </span>
                    <span class="item-action-btn {if !$aPage.is_liked_page}hide{/if}">
                        <a href="javascript:void(0);" class="btn btn-default btn-sm btn-icon js_page_feed_action" data-type="unlike" onclick="Core_Pages.processPageFeed(this);"><i class="ico ico-thumbup"></i>{_p var='liked'}</a>
                    </span>
                </div>
            </div>
            <div class="item-info-wrapper core-feed-minor">
                <span class="item-info">
                        {if $aPage.parent_category_name}
                        <a href="{$aPage.type_link}">
                            {if Phpfox::isPhrase($this->_aVars['aPage']['parent_category_name'])}
                            {_p var=$aPage.parent_category_name}
                            {else}
                            {$aPage.parent_category_name|convert}
                            {/if}
                        </a> »
                        {/if}
                        {if $aPage.category_name}
                        <a href="{$aPage.category_link}">
                            {if Phpfox::isPhrase($this->_aVars['aPage']['category_name'])}
                            {_p var=$aPage.category_name}
                            {else}
                            {$aPage.category_name|convert}
                            {/if}
                        </a>
                        {/if}
                </span>
                <span class="item-info">{if $aPage.total_like != 1}{_p var='pages_number_people_liked_this_page' number=$aPage.total_like}{else}{_p var='1_person_liked_this_page'}{/if}</span>
            </div>
        </div>
    </div>
</div>
