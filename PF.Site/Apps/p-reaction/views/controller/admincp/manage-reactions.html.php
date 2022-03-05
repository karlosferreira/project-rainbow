<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if count($aReactions)}
<form method="post" id="manage_reactions" action="{url link='admincp.preaction.manage-reactions'}">
    <div class="panel panel-default">
        <div class="table-responsive flex-sortable">
            <table class="table table-bordered" id="_sort" data-sort-url="{url link='admincp.preaction.reactions-order'}">
                <thead>
                <tr>
                    <th class="w40"></th>
                    <th class="w40"></th>
                    <th class="w80 t_center">{_p var='icon_u'}</th>
                    <th>{_p var='title'}</th>
                    <th class="t_center w140">{_p var='active_u'}</th>
                </tr>
                </thead>
                <tbody>
                    {foreach from=$aReactions key=iKey item=aItem}
                    <tr class="tr" data-sort-id="{$aItem.id}">
                        <td class="t_center w40">
                            <i class="fa fa-sort"></i>
                        </td>
                        <td class="t_center w60">
                            {if $aItem.view_id != 2}
                                <a href="#" class="js_drop_down_link" title="{_p var='Manage'}"></a>
                                <div class="link_menu">
                                    <ul>
                                        <li><a href="{url link='admincp.preaction.add-reaction' id=$aItem.id}">{_p var='edit'}</a></li>
                                        <li><a href="{url link='admincp.preaction.manage-reactions' delete=$aItem.id}" class="sJsConfirm">{_p var='delete'}</a></li>
                                    </ul>
                                </div>
                            {/if}
                        </td>
                        <td class="w80 t_center">
                            {if !empty($aItem.full_path)}
                            <div style="display: inline-flex;height: 32px;align-items: flex-start;">
                                <img src="{$aItem.full_path}" alt="" width="32px">
                            </div>
                            {/if}
                        </td>
                        <td class="td-flex">
                            {$aItem|preaction_color_title}
                        </td>
                        <td class="t_center w140">
                            {if $aItem.view_id != 2}
                                <div class="js_item_is_active"{if !$aItem.is_active} style="display:none;"{/if}>
                                    <a href="#?call=preaction.toggleActiveReaction&amp;id={$aItem.id}&amp;active=0" class="js_item_active_link" title="{_p var='Deactivate'}"></a>
                                </div>
                                <div class="js_item_is_not_active"{if $aItem.is_active} style="display:none;"{/if}>
                                    <a href="#?call=preaction.toggleActiveReaction&amp;id={$aItem.id}&amp;active=1" class="js_item_active_link" title="{_p var='Activate'}"></a>
                                </div>
                            {/if}
                        </td>
                    </tr>
                    {/foreach}
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <div class="extra_info">
                {_p var='like_is_default_action_you_can_add_more_maximum_11_reactions'}
            </div>
        </div>
    </div>
</form>
{/if}

{literal}
<script type="text/javascript">
    $Behavior.onLoadManageReaction = function() {
        var btnAdd = $('.toolbar-top .btn-group').find('a.popup');
        {/literal}
            {if $iTotalReaction >= 12}
                btnAdd.hide();
            {/if}
        {literal}
        btnAdd.removeClass('popup');
    }
</script>
{/literal}