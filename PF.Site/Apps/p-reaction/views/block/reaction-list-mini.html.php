<?php
    defined('PHPFOX') or exit('NO DICE!');
?>
{if !empty($aMostReacted)}
    <div class="p-reaction-list-mini dont-unbind-children">
        {for $i = 0; $i <= 2; $i++}
            {if isset($aMostReacted[$i])}
            <div class="p-reaction-item js_reaction_item {if count($aMostReacted) == 1}only-1{/if}">
                <a href="javascript:void(0)" class="item-outer"
                   data-action="p_reaction_show_list_user_react_cmd"
                   data-type_id="{$sType}"
                   data-item_id="{$iItemId}"
                   data-total_reacted="{$aMostReacted[$i].total_reacted}"
                   data-react_id="0"
                   data-table_prefix="{$sPrefix}"
                >
                    <img src="{$aMostReacted[$i].full_path}" alt="">
                </a>
            </div>
            {/if}
        {/for}
        <div class="p-reaction-liked-total">
          <span class="p-reaction-liked-number">{$iTotalReact|short_number}</span>
          <div class="p-reaction-tooltip-total js_p_reactiontooltip">
              <div class="item-tooltip-content js_p_reactionpreview_reacted">
                  {for $i = 0; $i <= 4; $i++}
                    {if isset($aMostReacted[$i])}
                        <div class="item-user"><img src="{$aMostReacted[$i].full_path}" alt="" width="16px"><span class="item-number">{$aMostReacted[$i].total_reacted|short_number}</span></div>
                    {/if}
                  {/for}
                  {if $iTotalReactType > 5}
                      <div class="item-user t_center item-more">...</div>
                  {/if}
              </div>
          </div>
        </div>
    </div>
{/if}
 
