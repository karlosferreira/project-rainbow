<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php 

 

?>

<?php if ($this->_aVars['bOnlyMobileLogin']): ?>
	<ul class="nav navbar-nav visible-xs visible-sm site-menu site_menu">
		<li>
			<div class="login-menu-btns-xs clearfix">
				<div class="<?php if (Phpfox ::getParam('user.allow_user_registration') && ! Phpfox ::getParam('user.invite_only_community')): ?>div01<?php endif; ?>">
					<a class="btn btn01 btn-success text-uppercase <?php if (Phpfox ::canOpenPopup('login')): ?>popup<?php else: ?>no_ajax<?php endif; ?>" rel="hide_box_title visitor_form" role="link" href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('login', [], false, false); ?>">
						<i class="fa fa-sign-in"></i> <?php echo _p('login_singular'); ?>
					</a>
				</div>
<?php if (Phpfox ::getParam('user.allow_user_registration') && ! Phpfox ::getParam('user.invite_only_community')): ?>
				<div class="div02">
					<a class="btn btn02 btn-warning text-uppercase <?php if (Phpfox ::canOpenPopup('login')): ?>popup<?php else: ?>no_ajax<?php endif; ?>" rel="hide_box_title visitor_form" role="link" href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('user.register', [], false, false); ?>">
<?php echo _p('register'); ?>
					</a>
				</div>
<?php endif; ?>
			</div>
		</li>
	</ul>
<?php else: ?>
<?php (($sPlugin = Phpfox_Plugin::get('core.template_block_template_menu_1')) ? eval($sPlugin) : false); ?>
	<div class="visible-xs visible-sm">
		<span class="btn-close">
			<span class="ico ico-close"></span>
		</span>
<?php Phpfox::getBlock('core.template-logo'); ?>
		<ul class="site-menu-small site_menu">
<?php if (Phpfox ::getUserBy('profile_page_id') <= 0 && isset ( $this->_aVars['aMainMenus'] )): ?>
<?php (($sPlugin = Phpfox_Plugin::get('theme_template_core_menu_list')) ? eval($sPlugin) : false); ?>
<?php if (( $this->_aVars['iMenuCnt'] = 0 )):  endif; ?>
<?php if (count((array)$this->_aVars['aMainMenus'])):  $this->_aPhpfoxVars['iteration']['menu'] = 0;  foreach ((array) $this->_aVars['aMainMenus'] as $this->_aVars['iKey'] => $this->_aVars['aMainMenu']):  $this->_aPhpfoxVars['iteration']['menu']++; ?>

<?php if (! isset ( $this->_aVars['aMainMenu']['is_force_hidden'] )): ?>
<?php $this->_aVars['iMenuCnt']++; ?>
<?php endif; ?>
                <li rel="menu<?php echo $this->_aVars['aMainMenu']['menu_id']; ?>" <?php if (( isset ( $this->_aVars['iTotalHide'] ) && isset ( $this->_aVars['iMenuCnt'] ) && $this->_aVars['iMenuCnt'] > $this->_aVars['iTotalHide'] )): ?> style="display:none;" <?php endif; ?> <?php if (( ( $this->_aVars['aMainMenu']['url'] == 'apps' && count ( $this->_aVars['aInstalledApps'] ) ) || ( isset ( $this->_aVars['aMainMenu']['children'] ) && count ( $this->_aVars['aMainMenu']['children'] ) ) ) || ( isset ( $this->_aVars['aMainMenu']['is_force_hidden'] ) )): ?>class="<?php if (( isset ( $this->_aVars['aMainMenu']['children'] ) && count ( $this->_aVars['aMainMenu']['children'] ) )): ?>menu-has-sub<?php endif; ?> <?php if (isset ( $this->_aVars['aMainMenu']['is_force_hidden'] ) && isset ( $this->_aVars['iTotalHide'] )): ?>is_force_hidden<?php else: ?>explore<?php endif;  if (( $this->_aVars['aMainMenu']['url'] == 'apps' && count ( $this->_aVars['aInstalledApps'] ) )): ?> explore_apps<?php endif; ?>"<?php endif; ?>>
                    <a <?php if (! isset ( $this->_aVars['aMainMenu']['no_link'] ) || $this->_aVars['aMainMenu']['no_link'] != true): ?>href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aMainMenu']['url'], [], false, false); ?>" <?php else: ?> href="#" onclick="return false;" <?php endif; ?> class="menu-link-item <?php if (isset ( $this->_aVars['aMainMenu']['is_selected'] ) && $this->_aVars['aMainMenu']['is_selected']): ?> menu_is_selected <?php endif;  if (isset ( $this->_aVars['aMainMenu']['external'] ) && $this->_aVars['aMainMenu']['external'] == true): ?>no_ajax_link <?php endif; ?>ajax_link">
<?php if (isset ( $this->_aVars['aMainMenu']['mobile_icon'] ) && $this->_aVars['aMainMenu']['mobile_icon']): ?>
                            <i class="<?php echo $this->_aVars['aMainMenu']['mobile_icon']; ?>"></i>
<?php else: ?>
                            <i class="ico ico-box-o"></i>
<?php endif; ?>
                        <span>
<?php echo _p($this->_aVars['aMainMenu']['var_name']);  if (isset ( $this->_aVars['aMainMenu']['suffix'] )):  echo $this->_aVars['aMainMenu']['suffix'];  endif; ?>
                        </span>
                    </a>
<?php if (! empty ( $this->_aVars['aMainMenu']['children'] )): ?>
                        <a data-toggle="dropdown" role="button" class="menu-link-sub-more <?php if (isset ( $this->_aVars['aMainMenu']['is_selected'] ) && $this->_aVars['aMainMenu']['is_selected']): ?> menu_is_selected <?php endif; ?>">
                            <span class="ico ico-caret-down"></span>
                        </a>
                        <ul class="site_sub_menu">
<?php if (count((array)$this->_aVars['aMainMenu']['children'])):  $this->_aPhpfoxVars['iteration']['cmenu'] = 0;  foreach ((array) $this->_aVars['aMainMenu']['children'] as $this->_aVars['cKey'] => $this->_aVars['aChildMenu']):  $this->_aPhpfoxVars['iteration']['cmenu']++; ?>

                                <li rel="menu<?php echo $this->_aVars['aChildMenu']['menu_id']; ?>">
                                    <a <?php if (! isset ( $this->_aVars['aChildMenu']['no_link'] ) || $this->_aVars['aChildMenu']['no_link'] != true): ?>href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aChildMenu']['url'], [], false, false); ?>" <?php else: ?> href="#" onclick="return false;" <?php endif; ?> class="<?php if (isset ( $this->_aVars['aChildMenu']['is_selected'] ) && $this->_aVars['aChildMenu']['is_selected']): ?> menu_is_selected <?php endif;  if (isset ( $this->_aVars['aChildMenu']['external'] ) && $this->_aVars['aChildMenu']['external'] == true): ?>no_ajax_link <?php endif; ?>ajax_link">
<?php if (isset ( $this->_aVars['aChildMenu']['mobile_icon'] ) && $this->_aVars['aChildMenu']['mobile_icon']): ?>
                                            <i class="<?php echo $this->_aVars['aChildMenu']['mobile_icon']; ?>"></i>
<?php else: ?>
                                            <i class="ico ico-box-o"></i>
<?php endif; ?>
                                        <span>
<?php echo _p($this->_aVars['aChildMenu']['var_name']);  if (isset ( $this->_aVars['aChildMenu']['suffix'] )):  echo $this->_aVars['aChildMenu']['suffix'];  endif; ?>
                                        </span>
                                    </a>
                                </li>
<?php endforeach; endif; ?>
                        </ul>
<?php endif; ?>
                </li>
<?php endforeach; endif; ?>
<?php endif; ?>
		</ul>
	</div>

<?php if (! $this->_aVars['iGlobalProfilePageId']): ?>
	<div class="visible-md visible-lg">
		<ul class="site-menu site_menu" data-component="menu">
            <div class="overlay"></div>
<?php if (Phpfox ::getUserBy('profile_page_id') <= 0 && isset ( $this->_aVars['aMainMenus'] )): ?>
<?php (($sPlugin = Phpfox_Plugin::get('theme_template_core_menu_list')) ? eval($sPlugin) : false); ?>
<?php $this->assign('iMenuPos', 0); ?>
<?php if (count((array)$this->_aVars['aMainMenus'])):  $this->_aPhpfoxVars['iteration']['menu'] = 0;  foreach ((array) $this->_aVars['aMainMenus'] as $this->_aVars['iKey'] => $this->_aVars['aMainMenu']):  $this->_aPhpfoxVars['iteration']['menu']++; ?>

                    <li rel="menu<?php echo $this->_aVars['aMainMenu']['menu_id']; ?>" class="<?php if (( isset ( $this->_aVars['aMainMenu']['children'] ) && count ( $this->_aVars['aMainMenu']['children'] ) )): ?>menu-has-sub<?php endif; ?> <?php if ($this->_aVars['iMenuPos'] == 0 && $this->_aVars['aMainMenu']['url'] == ''): ?>menu-home <?php endif;  if (( ( $this->_aVars['aMainMenu']['url'] == 'apps' && count ( $this->_aVars['aInstalledApps'] ) ) || ( isset ( $this->_aVars['aMainMenu']['children'] ) && count ( $this->_aVars['aMainMenu']['children'] ) ) ) || ( isset ( $this->_aVars['aMainMenu']['is_force_hidden'] ) )):  if (isset ( $this->_aVars['aMainMenu']['is_force_hidden'] ) && isset ( $this->_aVars['iTotalHide'] )): ?>is_force_hidden<?php else: ?>explore<?php endif;  if (( $this->_aVars['aMainMenu']['url'] == 'apps' && count ( $this->_aVars['aInstalledApps'] ) )): ?> explore_apps<?php endif;  endif; ?>">
                        <a <?php if (! isset ( $this->_aVars['aMainMenu']['no_link'] ) || $this->_aVars['aMainMenu']['no_link'] != true): ?>href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aMainMenu']['url'], [], false, false); ?>" <?php else: ?> href="#" onclick="return false;" <?php endif; ?> class="menu-link-item <?php if (isset ( $this->_aVars['aMainMenu']['is_selected'] ) && $this->_aVars['aMainMenu']['is_selected']): ?> menu_is_selected <?php endif;  if (isset ( $this->_aVars['aMainMenu']['external'] ) && $this->_aVars['aMainMenu']['external'] == true): ?>no_ajax_link <?php endif; ?>ajax_link">
<?php if ($this->_aVars['iMenuPos'] == 0 && $this->_aVars['aMainMenu']['url'] == ''): ?>
                                <i class="ico ico-home menu-home"></i>
<?php else: ?>
                                <span>
<?php echo _p($this->_aVars['aMainMenu']['var_name']);  if (isset ( $this->_aVars['aMainMenu']['suffix'] )):  echo $this->_aVars['aMainMenu']['suffix'];  endif; ?>
                                </span>
<?php endif; ?>
                        </a>
                        
<?php if (! empty ( $this->_aVars['aMainMenu']['children'] )): ?>
                        <a data-toggle="dropdown" role="button" class="menu-link-sub-more">
                            <span class="ico ico-caret-down"></span>
                        </a>
                            <div class="dropdown-menu dropdown-menu-right dropdown-menu-sub js_dropdown_menu_sub">
                                <ul class="site_sub_menu">
<?php if (count((array)$this->_aVars['aMainMenu']['children'])):  $this->_aPhpfoxVars['iteration']['cmenu'] = 0;  foreach ((array) $this->_aVars['aMainMenu']['children'] as $this->_aVars['cKey'] => $this->_aVars['aChildMenu']):  $this->_aPhpfoxVars['iteration']['cmenu']++; ?>

                                    <li rel="menu<?php echo $this->_aVars['aChildMenu']['menu_id']; ?>">
                                        <a <?php if (! isset ( $this->_aVars['aChildMenu']['no_link'] ) || $this->_aVars['aChildMenu']['no_link'] != true): ?>href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aChildMenu']['url'], [], false, false); ?>" <?php else: ?> href="#" onclick="return false;" <?php endif; ?> class="<?php if (isset ( $this->_aVars['aChildMenu']['is_selected'] ) && $this->_aVars['aChildMenu']['is_selected']): ?> menu_is_selected <?php endif;  if (isset ( $this->_aVars['aChildMenu']['external'] ) && $this->_aVars['aChildMenu']['external'] == true): ?>no_ajax_link <?php endif; ?>ajax_link">
                                            <!-- current hide icon on this case theme -->
                                            <!-- <?php if (isset ( $this->_aVars['aChildMenu']['mobile_icon'] ) && $this->_aVars['aChildMenu']['mobile_icon']): ?>
                                                <i class="<?php echo $this->_aVars['aChildMenu']['mobile_icon']; ?>"></i>
<?php else: ?>
                                                <i class="ico ico-box-o"></i>
<?php endif; ?> -->
                                            <span>
<?php echo _p($this->_aVars['aChildMenu']['var_name']);  if (isset ( $this->_aVars['aChildMenu']['suffix'] )):  echo $this->_aVars['aChildMenu']['suffix'];  endif; ?>
                                            </span>
                                        </a>
                                    </li>
<?php endforeach; endif; ?>
                                </ul>
                            </div>
<?php endif; ?>
                    </li>
<?php $this->assign('iMenuPos', $this->_aVars['iKey']); ?>
<?php endforeach; endif; ?>

                <li class="dropdown dropdown-overflow hide explorer menu-overflow">
                    <a data-toggle="dropdown" role="button">
                        <span class="ico ico-dottedmore-o"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                    </ul>
                </li>
<?php endif; ?>
		</ul>
	</div>
<?php endif;  endif; ?>
