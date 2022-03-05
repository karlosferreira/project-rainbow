<?php
defined('PHPFOX') or exit('NO DICE!');
?>
{if !count($aAdsConfig)}
<div class="extra_info">
    {_p var='no_ad_config_found'}
</div>
{else}
<div class="panel panel-default" id="js_mobile_manage_ad_config">
    <div class="panel-heading">
        <div class="panel-title">
            {_p var='ads_configs'}
        </div>
    </div>
    <div class="table-responsive">
        <table class="table table-bordered">
            <thead>
            <tr>
                <th class="w60"></th>
                <th>{_p var='name'}</th>
                <th class="w120">{_p var='type'}</th>
                <th class="w220">{_p var='frequency_capping'}</th>
                <th class="w80" style="width:60px;">{_p var='active__u'}</th>
            </tr>
            </thead>
            <tbody>
                {foreach from=$aAdsConfig item=aConfig}
                <tr>
                    <td class="t_center w60">
                        <a href="javascript:void(0)" class="js_drop_down_link" title="{_p var='Manage'}"></a>
                        <div class="link_menu">
                            <ul>
                                <li>
                                    <a href="{url link='admincp.mobile.add-ad-config' id=$aConfig.id}">{_p('Edit')}</a>
                                </li>
                                <li>
                                    <a href="{url link='admincp.mobile.manage-ads-config' delete=$aConfig.id}" class="sJsConfirm" data-message="{_p var='are_you_sure_to_delete_this_ad_config'}">{_p('Delete')}</a>
                                </li>
                            </ul>
                        </div>
                    </td>
                    <td>
                        {$aConfig.name|clean}
                    </td>
                    <td>
                        <?php
                            if (isset($this->_aVars['aAdTypes'][$this->_aVars['aConfig']['type']]))
                                echo $this->_aVars['aAdTypes'][$this->_aVars['aConfig']['type']];
                        ?>
                    </td>
                    <td>
                        <?php
                            if (!empty($this->_aVars['aFrequencyCapping'][$this->_aVars['aConfig']['frequency_capping']]))
                                echo $this->_aVars['aFrequencyCapping'][$this->_aVars['aConfig']['frequency_capping']];
                        ?>
                    </td>
                    <td class="on_off w80">
                        <div class="js_item_is_active js_ad_config_active_{$aConfig.id}" {if !$aConfig.is_active}style="display:none;"{/if}>
                            <a href="#?call=mobile.toggleActiveAdConfig&amp;id={$aConfig.id}&amp;active=0" class="js_item_active_link" title="{_p var='Deactivate'}"></a>
                        </div>
                        <div class="js_item_is_not_active js_ad_config_not_active_{$aConfig.id}" {if $aConfig.is_active}style="display:none;"{/if}>
                            <a href="#?call=mobile.toggleActiveAdConfig&amp;id={$aConfig.id}&amp;active=1" data-id="{$aConfig.id}" class="js_item_active_link js_manage_ad_enable_config" title="{_p var='Activate'}"></a>
                        </div>
                    </td>
                </tr>
                {/foreach}
            </tbody>
        </table>
    </div>
</div>
    {pager}
{/if}