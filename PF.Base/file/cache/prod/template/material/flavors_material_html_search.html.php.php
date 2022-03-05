<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php

?>

<?php if (! defined ( 'PHPFOX_IS_FORCED_404' ) && ! empty ( $this->_aVars['aSearchTool'] ) && is_array ( $this->_aVars['aSearchTool'] )): ?>
	<div class="header_bar_menu">
<?php if (isset ( $this->_aVars['aSearchTool']['search'] )): ?>
		<div class="header_bar_search">
			<form id="form_main_search" class="" method="GET" action="<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aSearchTool']['search']['action'])); ?>" onbeforesubmit="$Core.Search.checkDefaultValue(this,'<?php echo $this->_aVars['aSearchTool']['search']['default_value']; ?>');">
				<div class="hidden">
<?php if (( isset ( $this->_aVars['aSearchTool']['search']['hidden'] ) )): ?>
<?php echo $this->_aVars['aSearchTool']['search']['hidden']; ?>
<?php endif; ?>
				</div>
				<div class="header_bar_search_holder form-group has-feedback">
					<div class="header_bar_search_inner">
						<div class="input-group" style="width: 100%">

							<input type="search" class="form-control" name="search[<?php echo $this->_aVars['aSearchTool']['search']['name']; ?>]" value="<?php if (isset ( $this->_aVars['aSearchTool']['search']['actual_value'] )):  echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['aSearchTool']['search']['actual_value']));  endif; ?>" placeholder="<?php echo $this->_aVars['aSearchTool']['search']['default_value']; ?>" />
							<a class="form-control-feedback" data-cmd="core.search_items">
								<i class="ico ico-search-o"></i>
							</a>
						</div>
					</div>
				</div>
				<div id="js_search_input_holder">
					<div id="js_search_input_content right">
<?php if (isset ( $this->_aVars['sModuleForInput'] )): ?>
<?php Phpfox::getBlock('input.add', array('module' => $this->_aVars['sModuleForInput'],'bAjaxSearch' => true)); ?>
<?php endif; ?>
					</div>
				</div>
			
</form>

		</div>
<?php endif; ?>

<?php if (! empty ( $this->_aVars['aBreadCrumbTitle'] )): ?>
        <h1 class="header-page-title <?php if (empty ( $this->_aVars['aBreadCrumbTitle'][2] )): ?>item-title<?php endif; ?>">
            <a href="<?php echo $this->_aVars['aBreadCrumbTitle'][1]; ?>" class="ajax_link" rel="nofollow"><?php echo $this->_aVars['aBreadCrumbTitle'][0]; ?></a>
        </h1>
<?php endif; ?>

<?php if (isset ( $this->_aVars['aSearchTool']['filters'] ) && count ( $this->_aVars['aSearchTool']['filters'] )): ?>
		<div class="header-filter-holder">
<?php if (count((array)$this->_aVars['aSearchTool']['filters'])):  $this->_aPhpfoxVars['iteration']['fkey'] = 0;  foreach ((array) $this->_aVars['aSearchTool']['filters'] as $this->_aVars['sSearchFilterName'] => $this->_aVars['aSearchFilters']):  $this->_aPhpfoxVars['iteration']['fkey']++; ?>

<?php if (! isset ( $this->_aVars['aSearchFilters']['is_input'] ) && count ( $this->_aVars['aSearchFilters']['data'] )): ?>
			<div class="filter-options">
				<a class="dropdown-toggle" data-toggle="dropdown">
					<span><?php if (isset ( $this->_aVars['aSearchFilters']['active_phrase'] )):  echo $this->_aVars['aSearchFilters']['active_phrase'];  else:  echo $this->_aVars['aSearchFilters']['default_phrase'];  endif; ?></span>
					<span class="ico ico-caret-down"></span>
				</a>

				<ul class="dropdown-menu <?php if ($this->_aPhpfoxVars['iteration']['fkey'] < 2):  else: ?>dropdown-menu-left<?php endif; ?> dropdown-menu-limit dropdown-line">
<?php if (count((array)$this->_aVars['aSearchFilters']['data'])):  foreach ((array) $this->_aVars['aSearchFilters']['data'] as $this->_aVars['aSearchFilter']): ?>
					<li>
						<a href="<?php echo $this->_aVars['aSearchFilter']['link']; ?>" class="ajax_link <?php if (isset ( $this->_aVars['aSearchFilter']['is_active'] )): ?>active<?php endif; ?>" rel="nofollow">
<?php echo $this->_aVars['aSearchFilter']['phrase']; ?>
						</a>
					</li>
<?php endforeach; endif; ?>
<?php if (( isset ( $this->_aVars['aSearchFilters']['default'] ) )): ?>
					<li class="divider"></li>
					<li><a href="<?php echo $this->_aVars['aSearchFilters']['default']['url']; ?>" class="is_default" rel="nofollow"><?php echo $this->_aVars['aSearchFilters']['default']['phrase']; ?></a></li>
<?php endif; ?>
				</ul>
			</div>
<?php endif; ?>
<?php endforeach; endif; ?>
		</div>
<?php endif; ?>
	</div>
<?php elseif (! empty ( $this->_aVars['aBreadCrumbTitle'] )): ?>
    <h1 class="header-page-title <?php if (empty ( $this->_aVars['aBreadCrumbTitle'][2] )): ?>item-title<?php endif; ?> <?php if (isset ( $this->_aVars['aTitleLabel']['total_label'] ) && $this->_aVars['aTitleLabel']['total_label'] > 0): ?>header-has-label-<?php echo $this->_aVars['aTitleLabel']['total_label'];  endif; ?>">
        <a href="<?php echo $this->_aVars['aBreadCrumbTitle'][1]; ?>" class="ajax_link"><?php echo $this->_aVars['aBreadCrumbTitle'][0]; ?></a>
<?php if (isset ( $this->_aVars['aTitleLabel'] ) && isset ( $this->_aVars['aTitleLabel']['type_id'] ) && isset ( $this->_aVars['aTitleLabel']['label'] ) && count ( $this->_aVars['aTitleLabel']['label'] )): ?>
        <div class="<?php echo $this->_aVars['aTitleLabel']['type_id']; ?>-icon">
<?php if (count((array)$this->_aVars['aTitleLabel']['label'])):  foreach ((array) $this->_aVars['aTitleLabel']['label'] as $this->_aVars['sKey'] => $this->_aVars['aLabel']): ?>
            <div class="sticky-label-icon title-label sticky-<?php echo $this->_aVars['sKey']; ?>-icon" title="<?php echo _p($this->_aVars['sKey']); ?>">
                <span class="ico ico-<?php echo $this->_aVars['aLabel']['icon_class']; ?>"></span>
                <span class="<?php if (isset ( $this->_aVars['aLabel']['title_class'] )):  echo $this->_aVars['aLabel']['title_class'];  endif; ?>"><?php echo $this->_aVars['aLabel']['title']; ?></span>
            </div>
<?php endforeach; endif; ?>
        </div>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aPageExtraLink'] )): ?>
        <div class="view_item_link">
            <a href="<?php echo $this->_aVars['aPageExtraLink']['link']; ?>" class="page_section_menu_link" title="<?php echo $this->_aVars['aPageExtraLink']['phrase']; ?>" rel="nofollow">
                <span><?php echo $this->_aVars['aPageExtraLink']['phrase']; ?></span>
            </a>
        </div>
<?php endif; ?>
    </h1>
<?php endif; ?>


