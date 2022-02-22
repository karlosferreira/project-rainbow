<?php
/**
 * [PHPFOX_HEADER]
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author           phpFox LLC
 * @package          Phpfox
 * @version          $Id: view-admincp-login.html.php 1407 2010-01-21 12:35:36Z
 *                   phpFox LLC $
 */

defined('PHPFOX') or exit('NO DICE!');

?>
<div class="panel panel-default">
        <div class="panel-heading">
            <div class="panel-title">{_p var='log_details'}</div>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered">
                <tbody>
                <tr>
                    <th class="w130">{_p var='attempt'}</th>
                    <td>
                        {$aLog.attempt}
                    </td>
                </tr>
                <tr>
                    <th>{_p var='user'}</th>
                    <td>
                        {$aLog|user}
                    </td>
                </tr>
                <tr>
                    <th>{_p var='time_stamp'}</th>
                    <td>
                        {if Phpfox::isAppActive('Core_Messages')}
                        {$aLog.time_stamp|date:'mail.mail_time_stamp'}
                        {else}
                        {$aLog.time_stamp|date:'core.global_update_time'}
                        {/if}
                    </td>
                </tr>
                <tr>
                    <th>{_p var='ip_address'}</th>
                    <td>
                        {$aLog.ip_address}
                    </td>
                </tr>
                <tr>
                    <th>{_p var='location'}</th>
                    <td>
                        {$aLog.cache_data.location}
                    </td>
                </tr>
                <tr>
                    <th>{_p var='referrer'}</th>
                    <td>
                        {$aLog.cache_data.referrer}
                    </td>
                </tr>
                <tr>
                    <th>{_p var='user_agent'}</th>
                    <td>
                        {$aLog.cache_data.location}
                    </td>
                </tr>
                <tr>
                    <th>{if Phpfox::getParam('core.enable_register_with_phone_number')} {_p var='email_or_phone_number'}{else}{_p var='email'}{/if}</th>
                    <td>
                        {$aLog.cache_data.email}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        <div class="panel-footer">
            <input type="button" value="{_p var='close'}" class="btn btn-danger"
                   onclick="tb_remove();"/>
        </div>
    </div>
