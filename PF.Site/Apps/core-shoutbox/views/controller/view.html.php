<?php
defined('PHPFOX') or exit('NO DICE!');
?>
<div class="item_view no_manage shoutbox-message-detail">
    <div class="item-content shoutbox-container pt-1">
        <div class="row msg_container">
            <div class="msg_container_row shoutbox-item">
                <div class="item-outer">
                    <div class="item-inner">
                        <div>
                            <div class="messages_body item-message">
                                <div class="item-message-info item_view_content">
                                    {if !empty($aShoutbox.quoted_text)}
                                    <div class="item-quote-content">
                                        <div class="quote-user">{$aShoutbox.quoted_full_name}</div>
                                        <div class="quote-message">{$aShoutbox.quoted_text|parse}</div>
                                    </div>
                                    {/if}
                                    {$aShoutbox.text|parse}
                                </div>
                            </div>
                        </div>
                        <div>
                            <span class="item-time message_convert_time" data-id="{$aShoutbox.timestamp}">{$aShoutbox.timestamp|convert_time}</span>
                            <span class="item-time">{if (int)$aShoutbox.total_like > 0} . <a href="javascript:void(0);" onclick="appShoutbox.showLikedMembers({$aShoutbox.shoutbox_id});">{$aShoutbox.total_like} {if (int)$aShoutbox.total_like > 1}{_p var='likes'}{else}{_p var='like'}{/if}{/if}</a></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
