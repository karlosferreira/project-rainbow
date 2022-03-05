<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php if (Phpfox ::getUserBy('profile_page_id') <= 0 && isset ( $this->_aVars['aMainMenus'] )):  (($sPlugin = Phpfox_Plugin::get('theme_template_core_menu_list')) ? eval($sPlugin) : false);  if (count((array)$this->_aVars['aMainMenus'])):  $this->_aPhpfoxVars['iteration']['menu'] = 0;  foreach ((array) $this->_aVars['aMainMenus'] as $this->_aVars['iKey'] => $this->_aVars['aMainMenu']):  $this->_aPhpfoxVars['iteration']['menu']++; ?>

    <li rel="menu<?php echo $this->_aVars['aMainMenu']['menu_id']; ?>" <?php if (( isset ( $this->_aVars['iTotalHide'] ) && isset ( $this->_aVars['iMenuCnt'] ) && $this->_aVars['iMenuCnt'] > $this->_aVars['iTotalHide'] )): ?> style="display:none;" <?php endif; ?> <?php if (( ( $this->_aVars['aMainMenu']['url'] == 'apps' && count ( $this->_aVars['aInstalledApps'] ) ) || ( isset ( $this->_aVars['aMainMenu']['children'] ) && count ( $this->_aVars['aMainMenu']['children'] ) ) ) || ( isset ( $this->_aVars['aMainMenu']['is_force_hidden'] ) )): ?>class="<?php if (isset ( $this->_aVars['aMainMenu']['is_force_hidden'] ) && isset ( $this->_aVars['iTotalHide'] )): ?>is_force_hidden<?php else: ?>explore<?php endif;  if (( $this->_aVars['aMainMenu']['url'] == 'apps' && count ( $this->_aVars['aInstalledApps'] ) )): ?> explore_apps<?php endif; ?>"<?php endif; ?>>
        <a <?php if (! isset ( $this->_aVars['aMainMenu']['no_link'] ) || $this->_aVars['aMainMenu']['no_link'] != true): ?>href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aMainMenu']['url'], [], false, false); ?>" <?php else: ?> href="#" onclick="return false;" <?php endif; ?> class="<?php if (isset ( $this->_aVars['aMainMenu']['is_selected'] ) && $this->_aVars['aMainMenu']['is_selected']): ?> menu_is_selected <?php endif;  if (isset ( $this->_aVars['aMainMenu']['external'] ) && $this->_aVars['aMainMenu']['external'] == true): ?>no_ajax_link <?php endif; ?>ajax_link">
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
<?php endforeach; endif;  endif; ?>
