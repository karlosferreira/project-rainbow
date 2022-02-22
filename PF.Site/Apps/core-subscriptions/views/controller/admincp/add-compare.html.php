<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright		[PHPFOX_COPYRIGHT]
 * @author  		phpFox LLC
 * @package 		Phpfox
 * @version 		$Id: controller.html.php 64 2009-01-19 15:05:54Z phpFox LLC $

[feature-id][title]
[feature-id][package][package-id] = array
[feature-id][package][package-id][radio] = [0|1|2]
[feature-id][package][package-id][text] = text
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="panel panel-default core-subscriptions-admincp-add-compare">
    <div class="panel-heading">
        <div class="panel-title">{_p var='subscribe_add_feature_comparison'}</div>
    </div>
    {if count($aPackages)}
        <div class="panel-body">
            <form method="post" action="{url link='admincp.subscribe.add-compare'}">
                {if $bIsEdit}
                <input type="hidden" name="id" value="{$iCompareId}">
                {/if}
                <input type="hidden" id="default_language" value="{$sDefaultLanguage}">
                <div class="form-group">
                    {field_language phrase='sPhraseTitle' label='Feature Name' field='feature_title' format='val[feature_title][' size=40 maxlength=100 required=true}
                    <div class="max-character">
                        <span class="warning">{_p var='subscribe_max_numbers_character' number='100'}. </span>
                    </div>
                </div>
                <div class="form-group">
                    <div class="title">{_p var='subscribe_in_which_package_is_this_feature_supported'}</div>
                    {foreach from=$aPackages key=packagekey item=aPackage}
                        {if $bIsEdit}
                            <div class="col-md-12 package-feature"  {if $packagekey < count($aPackages) - 1}style="border-bottom: 1px solid #dddddd;"{/if}>
                                <div class="package-title col-md-4">{_p var=$aPackage.title}</div>
                                <div class="feature-compare col-md-8">
                                    <div>
                                        <label>
                                            <input type="radio" value="1" name="val[features][{$aPackage.package_id}][option]" data-id="{$aPackage.package_id}" {if !empty($aPackage.compare_feature.option)}{if (int)$aPackage.compare_feature.option == 1}checked="checked"{/if}{/if}>{_p var='Yes'}
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" value="2" name="val[features][{$aPackage.package_id}][option]"  data-id="{$aPackage.package_id}" {if !empty($aPackage.compare_feature.option)}{if (int)$aPackage.compare_feature.option == 2}checked="checked"{/if}{else}checked="checked"{/if}>{_p var='No'}
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" value="3" name="val[features][{$aPackage.package_id}][option]" data-id="{$aPackage.package_id}" {if !empty($aPackage.compare_feature.option)}{if (int)$aPackage.compare_feature.option == 3}checked="checked"{/if}{/if}>{_p var='Text Field'}
                                        </label>
                                    </div>
                                    <div class="text-selection-{$aPackage.package_id}" {if !empty($aPackage.compare_feature.option)}{if (int)$aPackage.compare_feature.option != 3}style="display: none"{/if}{else}style="display: none"{/if} data-id="{$aPackage.package_id}">
                                        {if !empty($aPackage.compare_feature)}
                                        {field_language phrase=$aPackage.compare_feature.text label='Content Feature' field='text_field_'.$aPackage.package_id format='val[features]['.$aPackage.package_id.'][text][' size=40 maxlength=200 type='textarea'}
                                        {else}
                                        {field_language label='Content Feature' field='text_field_'.$aPackage.package_id format='val[features]['.$aPackage.package_id.'][text][' size=40 maxlength=200 type='textarea'}
                                        {/if}
                                        <div class="max-character">
                                            <span class="warning">{_p var='subscribe_max_numbers_character' number='200'}. </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {else}
                            <div class="col-md-12 package-feature" {if $packagekey < count($aPackages) - 1}style="border-bottom: 1px solid #dddddd;"{/if}>
                                <div class="package-title col-md-4">{_p var=$aPackage.title}</div>
                                <div class="feature-compare col-md-8">
                                    <div>
                                        <label>
                                            <input type="radio" value="1" name="val[features][{$aPackage.package_id}][option]" data-id="{$aPackage.package_id}">{_p var='Yes'}
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" value="2" name="val[features][{$aPackage.package_id}][option]" checked="checked" data-id="{$aPackage.package_id}">{_p var='No'}
                                        </label>
                                    </div>
                                    <div>
                                        <label>
                                            <input type="radio" value="3" name="val[features][{$aPackage.package_id}][option]" data-id="{$aPackage.package_id}">{_p var='Text Field'}
                                        </label>
                                    </div>
                                    <div class="text-selection-{$aPackage.package_id} text-selection-init"data-id="{$aPackage.package_id}">
                                        {field_language  label='Content Feature' field='text_field_'.$aPackage.package_id format='val[features]['.$aPackage.package_id.'][text][' size=40 maxlength=200 type='textarea'}
                                        <div class="max-character">
                                            <span class="warning">{_p var='subscribe_max_numbers_character' number='200'}. </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        {/if}
                    {/foreach}
                </div>
                <div class="form-group button-group">
                    <button type="submit" class="btn btn-success">{_p var='save'}</button>
                    {if !empty($isAjaxPopup)}
                        <button class="btn btn-default" onclick="js_box_remove(this); return false;">{_p var='cancel'}</button>
                    {/if}
                </div>
            </form>
        </div>
    {else}
        <div class="alert alert-empty">
            {_p var='subscribe_no_packages_found_for_comparing_features'}
        </div>
    {/if}
</div>


