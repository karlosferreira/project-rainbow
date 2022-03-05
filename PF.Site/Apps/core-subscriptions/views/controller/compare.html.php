{if !empty($aComparePackages)}
    {assign var=aPackages value=$aComparePackages}
    {template file='subscribe.block.compare'}
{else}
<div class="alert alert-danger">
    {_p var='subscribe_packages_for_comparison_not_found'}
</div>
{/if}
