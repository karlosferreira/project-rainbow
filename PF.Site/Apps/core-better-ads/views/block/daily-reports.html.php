<?php
    defined('PHPFOX') or exit('NO DICE!');
?>

<!-- daily report -->
{if $aDailyReports}
    {if !PHPFOX_IS_AJAX}
        <div class="bts-daily-reports table-responsive bts-table">
            {/if}
            <table class="table">
                <thead>
                    <tr>
                        <th class="text-uppercase fz-12 text-gray-dark text-center">{_p var='date'}</th>
                        <th class="text-uppercase fz-12 text-gray-dark text-center">{_p var='better_ads_click'}</th>
                        <th class="text-uppercase fz-12 text-gray-dark text-center">{_p var='view'}</th>
                    </tr>
                </thead>
                <tbody>
                    {foreach from=$aDailyReports item=report}
                        <tr>
                            <td class="text-center align-middle">{$report.start_time|date}</td>
                            <td class="text-center align-middle">{$report.total_click|intval}</td>
                            <td class="text-center align-middle">{$report.total_view|intval}</td>
                        </tr>
                    {/foreach}
                </tbody>
            </table>
            {pager}
            {if !PHPFOX_IS_AJAX}
        </div>
    {/if}
{/if}
