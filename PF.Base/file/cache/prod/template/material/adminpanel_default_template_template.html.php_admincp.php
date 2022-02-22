<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: February 19, 2022, 2:44 am */ ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
	"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" dir="ltr" lang="<?php echo $this->_aVars['sLocaleCode']; ?>">
<?php if (! isset ( $this->_aVars['bShowClearCache'] )): ?>
<?php $this->assign('bShowClearCache', false); ?>
<?php endif; ?>
	<head>
    <title><?php echo $this->getTitle(); ?></title>
<?php echo $this->getHeader(); ?>
	</head>
	<body class="admincp-fixed-menu <?php if (! empty ( $this->_aVars['sBodyClass'] )):  echo $this->_aVars['sBodyClass'];  endif; ?>" >
		<div id="admincp_base"></div>
		<div id="global_ajax_message"></div>
		<div id="header" <?php if (! empty ( $this->_aVars['flavor_id'] )): ?>class="theme-<?php echo $this->_aVars['flavor_id']; ?>"<?php endif; ?>>
            <div class="admincp-toggle-nav-btn js_admincp_toggle_nav_btn">
                <i class="ico ico-navbar"></i>
            </div>
<?php Phpfox::getBlock('core.template-logo'); ?>
            <div class="js_admincp_toggle_search admincp-btn-toggle-search"onclick="$('body').toggleClass('show-search-header');"><i class="ico ico-search-o"></i></div>
            <div class="admincp_header_form admincp_search_settings">
                <span class="remove"><i class="fa fa-remove"></i></span>
                <input type="text" name="setting" placeholder="<?php echo _p('search_settings_dot'); ?>" autocomplete="off">
                <div class="admincp_search_settings_results hide">
                </div>
            </div>
            <div class="admincp_right_group">
                <div class="admincp_alert dropdown">
                    <a data-toggle="dropdown" role="button" id="js_admincp_alert" data-panel="#js_admincp_alert_panel">
                        <div class="ajax" data-url="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('admincp.alert.badge', [], false, false); ?>"></div>
                        <i class="ico ico-bell2-o"></i>
                    </a>
                    <div class="dropdown-menu dropdown-menu-right">
                        <div class="dropdown-panel-body" id="js_admincp_alert_panel">
                            <div class="item-loading"><i class="ico ico-loading-icon"></i></div>
                        </div>
                    </div>
                </div>
                <div class="admincp_user">
                    <div class="admincp_user_image">
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('user' => $this->_aVars['aUserDetails'],'suffix' => '_120_square')); ?>
                    </div>
                    <div class="admincp_user_content">
<?php echo '<span class="user_profile_link_span" id="js_user_name_link_' . Phpfox::getService('user')->getUserName($this->_aVars['aUserDetails']['user_id'], $this->_aVars['aUserDetails']['user_name']) . '">' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aUserDetails']['user_id']) ? '' : '<a href="' . Phpfox::getLib('phpfox.url')->makeUrl('profile', array($this->_aVars['aUserDetails']['user_name'], ((empty($this->_aVars['aUserDetails']['user_name']) && isset($this->_aVars['aUserDetails']['profile_page_id'])) ? $this->_aVars['aUserDetails']['profile_page_id'] : null))) . '">') . '' . Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('parse.output')->clean(Phpfox::getService('user')->getCurrentName($this->_aVars['aUserDetails']['user_id'], $this->_aVars['aUserDetails']['full_name'])), 0) . '' . (Phpfox::getService('user.block')->isBlocked(null, $this->_aVars['aUserDetails']['user_id']) ? '' : '</a>') . '</span>'; ?>
<?php echo $this->_aVars['aUserDetails']['user_group_title']; ?>
                    </div>
                </div>

<?php if (! Phpfox ::demoModeActive()): ?>
                <div class="admincp_view_site">
                    <a target="_blank" href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('', [], false, false); ?>"><span class="item-text"><?php echo _p('view_site'); ?>&nbsp;</span><i class="fas fa fa-external-link"></i></a>
                </div>
<?php endif; ?>
            </div>

		</div>
		<aside class="js_admincp_toggle_nav_content">
            <ul class="">
                <?php 
                    $this->_aVars['aAdminMenus'] =  Phpfox::getService('admincp.sidebar')->prepare()->get();
                 ?>
<?php if (count((array)$this->_aVars['aAdminMenus'])):  foreach ((array) $this->_aVars['aAdminMenus'] as $this->_aVars['sPhrase'] => $this->_aVars['sLink']): ?>
<?php if (is_array ( $this->_aVars['sLink'] )): ?>
<?php $this->assign('menuId', "id_menu_item_".$this->_aVars['sPhrase']); ?>
                        <li id="<?php echo $this->_aVars['menuId']; ?>" <?php if ($this->_aVars['sLastOpenMenuId'] == $this->_aVars['menuId']): ?>class="open"<?php endif; ?>>
                            <a href="<?php echo $this->_aVars['sLink']['link']; ?>" data-tags="<?php if (isset ( $this->_aVars['sLink']['tags'] )):  echo $this->_aVars['sLink']['tags'];  endif; ?>"
<?php if (! empty ( $this->_aVars['sLink']['items'] )): ?>
                                   class="item-header <?php if (isset ( $this->_aVars['sLink']['is_active'] )): ?>is_active<?php endif; ?> <?php if (isset ( $this->_aVars['sLink']['class'] )):  echo $this->_aVars['sLink']['class'];  endif; ?>" data-cmd="admincp.open_sub_menu"
<?php else: ?>
                                   class="<?php if (isset ( $this->_aVars['sLink']['is_active'] )): ?>is_active<?php endif; ?> <?php if (isset ( $this->_aVars['sLink']['class'] )):  echo $this->_aVars['sLink']['class'];  endif; ?>"
<?php endif; ?> <?php if (isset ( $this->_aVars['sLink']['event'] )):  echo $this->_aVars['sLink']['event'];  endif; ?>>
<?php if (! empty ( $this->_aVars['sLink']['icon'] )): ?><i class="<?php echo $this->_aVars['sLink']['icon']; ?>"></i><?php endif; ?>
<?php echo $this->_aVars['sLink']['label']; ?>
<?php if (isset ( $this->_aVars['sLink']['items'] ) && ! empty ( $this->_aVars['sLink']['items'] )): ?>
                            <i class="fa fa-caret"></i>
<?php endif; ?>
<?php if (isset ( $this->_aVars['sLink']['badge'] ) && $this->_aVars['sLink']['badge'] > 0): ?>
                            <span class="badge"><?php echo $this->_aVars['sLink']['badge']; ?></span>
<?php endif; ?>
                            </a>

<?php if (isset ( $this->_aVars['sLink']['items'] ) && ! empty ( $this->_aVars['sLink']['items'] )): ?>
                                <ul>
<?php if (count((array)$this->_aVars['sLink']['items'])):  foreach ((array) $this->_aVars['sLink']['items'] as $this->_aVars['sLink2']): ?>
                                    <li>
                                        <a data-tags="<?php if (isset ( $this->_aVars['sLink2']['tags'] )):  echo $this->_aVars['sLink2']['tags'];  endif; ?>" href="<?php echo $this->_aVars['sLink2']['link']; ?>"
                                           class="<?php if (isset ( $this->_aVars['sLink2']['class'] )):  echo $this->_aVars['sLink2']['class'];  endif;  if (isset ( $this->_aVars['sLink2']['is_active'] )): ?>is_active<?php endif; ?>" <?php if (isset ( $this->_aVars['sLink2']['event'] )):  echo $this->_aVars['sLink2']['event'];  endif; ?>>
<?php if (! empty ( $this->_aVars['sLink2']['icon'] )): ?><i class="<?php echo $this->_aVars['sLink2']['icon']; ?>"></i><?php endif;  echo $this->_aVars['sLink2']['label']; ?>
                                        </a>
                                    </li>
<?php endforeach; endif; ?>
                                </ul>
<?php endif; ?>
                        </li>
<?php endif; ?>
<?php endforeach; endif; ?>
            </ul>
            <div id="global_remove_site_cache_item">
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('admincp.maintain.cache', array('all' => 1,'return' => $this->_aVars['sCacheReturnUrl']), false, false); ?>">
                    <i class="ico ico-trash-o"></i>
<?php echo _p('clear_all_caches'); ?>
                </a>
            </div>
<?php if ($this->_aVars['bEnableBundle']): ?>
            <div id="global_remove_site_cache_item">
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl('admincp.maintain.bundle', array('all' => 1,'return' => $this->_aVars['sCacheReturnUrl']), false, false); ?>">
                    <i class="ico ico-file-zip-o"></i>
<?php echo _p('bundle_js_css'); ?>
                </a>
            </div>
<?php endif; ?>
            <div id="copyright">
<?php echo Phpfox::getParam('core.site_copyright'); ?> &middot; <a href="#" id="select_lang_pack"><?php if (Phpfox ::getParam('language.display_language_flag') && ! empty ( $this->_aVars['sLocaleFlagId'] )): ?><img src="<?php echo $this->_aVars['sLocaleFlagId']; ?>" alt="<?php echo $this->_aVars['sLocaleName']; ?>" class="v_middle" /> <?php endif;  echo $this->_aVars['sLocaleName']; ?></a>
            </div>
            <br/>
            <br/>
            <br/>
            <br/>
		</aside>

        <!-- end action menu-->
        <div class="main_holder">
<?php if (! empty ( $this->_aVars['aAdmincpBreadCrumb'] ) || ! empty ( $this->_aVars['sSectionTitle'] )): ?>
            <div class="breadcrumbs">
<?php if (! empty ( $this->_aVars['aAdmincpBreadCrumb'] )): ?>
<?php if (count ( $this->_aVars['aAdmincpBreadCrumb'] ) > 1): ?>
<?php if (count((array)$this->_aVars['aAdmincpBreadCrumb'])):  foreach ((array) $this->_aVars['aAdmincpBreadCrumb'] as $this->_aVars['sUrl'] => $this->_aVars['sPhrase']): ?>
                        <a href="<?php if (! empty ( $this->_aVars['sUrl'] )):  echo $this->_aVars['sUrl'];  else: ?>#<?php endif; ?>"><?php echo $this->_aVars['sPhrase']; ?></a>
<?php endforeach; endif; ?>
<?php endif; ?>
<?php elseif (! empty ( $this->_aVars['sSectionTitle'] )): ?>
                    <a href="#"><?php echo $this->_aVars['sSectionTitle']; ?></a>
<?php endif; ?>
            </div>
<?php endif; ?>

<?php if (! empty ( $this->_aVars['sLastBreadcrumb'] )): ?>
                <h1 class="page-title"><?php echo $this->_aVars['sLastBreadcrumb']; ?></h1>
<?php elseif (! empty ( $this->_aVars['sSectionTitle'] )): ?>
                <h1 class="page-title"><?php echo $this->_aVars['sSectionTitle']; ?></h1>
<?php endif; ?>

<?php if (! empty ( $this->_aVars['aActionMenu'] ) || ! empty ( $this->_aVars['aSectionAppMenus'] )): ?>
                <div class="toolbar-top">
<?php if (! empty ( $this->_aVars['aSectionAppMenus'] )): ?>
                        <div class="btn-group acp-header-section js-acp-header-section">
<?php if (count ( $this->_aVars['aSectionAppMenus'] ) <= 6): ?>
<?php if (count((array)$this->_aVars['aSectionAppMenus'])):  foreach ((array) $this->_aVars['aSectionAppMenus'] as $this->_aVars['sPhrase'] => $this->_aVars['aMenu']): ?>
                                    <a <?php if (isset ( $this->_aVars['aMenu']['cmd'] )): ?>data-cmd="<?php echo $this->_aVars['aMenu']['cmd']; ?>"<?php endif; ?>  href="<?php if (( substr ( $this->_aVars['aMenu']['url'] , 0 , 1 ) == '#' )):  echo $this->_aVars['aMenu']['url'];  else:  echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aMenu']['url'], [], false, false);  endif; ?>"
                                    class="<?php if (isset ( $this->_aVars['aMenu']['is_active'] ) && $this->_aVars['aMenu']['is_active']): ?>active<?php endif; ?>"><?php echo $this->_aVars['sPhrase']; ?></a>
<?php endforeach; endif; ?>
<?php else: ?>
<?php if (count((array)$this->_aVars['aSectionAppMenus'])):  $this->_aPhpfoxVars['iteration']['fkey'] = 0;  foreach ((array) $this->_aVars['aSectionAppMenus'] as $this->_aVars['sPhrase'] => $this->_aVars['aMenu']):  $this->_aPhpfoxVars['iteration']['fkey']++; ?>

<?php if ($this->_aPhpfoxVars['iteration']['fkey'] < 6): ?>
                                        <a <?php if (isset ( $this->_aVars['aMenu']['cmd'] )): ?>data-cmd="<?php echo $this->_aVars['aMenu']['cmd']; ?>"<?php endif; ?>  href="<?php if (( substr ( $this->_aVars['aMenu']['url'] , 0 , 1 ) == '#' )):  echo $this->_aVars['aMenu']['url'];  else:  echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aMenu']['url'], [], false, false);  endif; ?>"
                                        class="<?php if (isset ( $this->_aVars['aMenu']['is_active'] ) && $this->_aVars['aMenu']['is_active']): ?>active<?php endif; ?>"><?php echo $this->_aVars['sPhrase']; ?></a>
<?php endif; ?>
<?php if ($this->_aPhpfoxVars['iteration']['fkey'] == 6): ?>
                                    <div class="acp-menu-dropdown"> <!-- div dropdown -->
                                        <a class="dropdown-toggle" id="dropdownMenu1" href="" data-toggle="dropdown" aria-expanded="true" aria-haspopup="true">
<?php echo _p("more"); ?>
                                            <span class="caret"></span>
                                        </a>
                                        <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="dropdownMenu1">
<?php endif; ?>
<?php if ($this->_aPhpfoxVars['iteration']['fkey'] >= 6): ?>
                                            <li role="menuitem">
                                                <a <?php if (isset ( $this->_aVars['aMenu']['cmd'] )): ?>data-cmd="<?php echo $this->_aVars['aMenu']['cmd']; ?>"<?php endif; ?>  href="<?php if (( substr ( $this->_aVars['aMenu']['url'] , 0 , 1 ) == '#' )):  echo $this->_aVars['aMenu']['url'];  else:  echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aMenu']['url'], [], false, false);  endif; ?>"
                                                class="<?php if (isset ( $this->_aVars['aMenu']['is_active'] ) && $this->_aVars['aMenu']['is_active']): ?>active<?php endif; ?>"><?php echo $this->_aVars['sPhrase']; ?></a>
                                            </li>
<?php endif; ?>
<?php endforeach; endif; ?>
                                        </ul>
                                    </div> <!-- end div dropdown -->
<?php endif; ?>
                        </div>
<?php endif; ?>
<?php if (isset ( $this->_aVars['aActionMenu'] )): ?>
                        <div class="btn-group acp-action-menus">
<?php if ($this->_aVars['bMoreThanOneActionMenu']): ?>
                                <a role="button" class="btn btn-primary" data-toggle="dropdown"><?php echo _p('actions'); ?> <span class="ico ico-caret-down"></span></a>
                                <ul class="dropdown-menu dropdown-menu-right">
<?php endif; ?>
<?php if (count((array)$this->_aVars['aActionMenu'])):  foreach ((array) $this->_aVars['aActionMenu'] as $this->_aVars['sPhrase'] => $this->_aVars['sUrl']): ?>
<?php if (is_array ( $this->_aVars['sUrl'] )): ?>
<?php if ($this->_aVars['bMoreThanOneActionMenu']): ?>
                                    <li>
<?php endif; ?>
                                    <a <?php if (isset ( $this->_aVars['sUrl']['cmd'] )): ?>data-cmd="<?php echo $this->_aVars['sUrl']['cmd']; ?>"<?php endif; ?>  href="<?php echo $this->_aVars['sUrl']['url']; ?>" class="<?php if ($this->_aVars['bMoreThanOneActionMenu']):  echo $this->_aVars['sUrl']['dropdown_class'];  else: ?>btn <?php echo $this->_aVars['sUrl']['class'];  endif; ?>" <?php if (isset ( $this->_aVars['sUrl']['custom'] )): ?> <?php echo $this->_aVars['sUrl']['custom'];  endif; ?>><?php echo $this->_aVars['sPhrase']; ?></a>
<?php if ($this->_aVars['bMoreThanOneActionMenu']): ?>
                                    </li>
<?php endif; ?>
<?php else: ?>
<?php if ($this->_aVars['bMoreThanOneActionMenu']): ?>
                                    <li>
<?php endif; ?>
                                    <a href="<?php echo $this->_aVars['sUrl']; ?>"><?php echo $this->_aVars['sPhrase']; ?></a>
<?php if ($this->_aVars['bMoreThanOneActionMenu']): ?>
                                    </li>
<?php endif; ?>
<?php endif; ?>
<?php endforeach; endif; ?>
<?php if ($this->_aVars['bMoreThanOneActionMenu']): ?>
                            </ul>
<?php endif; ?>
                        </div>
<?php endif; ?>
                </div>
<?php endif; ?>

<?php if (( isset ( $this->_aVars['has_upgrade'] ) && $this->_aVars['has_upgrade'] )): ?>
                <br/>
                <div class="alert alert-danger mb-base">
<?php echo _p("There is an update available for this product."); ?> <a class="btn btn-link" href="<?php echo $this->_aVars['store']['install_url']; ?>"><?php echo _p("Update Now"); ?></a>
                </div>
<?php endif; ?>
            <div id="js_content_container">
                <div id="main">
<?php if (isset ( $this->_aVars['aSectionAppMenus'] )): ?>
                    <div class="apps_content">
<?php endif; ?>

<?php if (!$this->bIsSample):  $this->getLayout('error');  endif; ?>
                        <div class="_block_content">
<?php if (!$this->bIsSample): ?><div id="site_content"><?php if (isset($this->_aVars['bSearchFailed'])): ?><div class="message">Unable to find anything with your search criteria.</div><?php else:  $sController = "admincp.maintain/cache";  Phpfox::getLib('phpfox.module')->getControllerTemplate();  endif; ?></div><?php endif; ?>
                        </div>

<?php if (isset ( $this->_aVars['aSectionAppMenus'] )): ?>
                    </div>
<?php endif; ?>
                </div>
            </div>
        </div>
<?php (($sPlugin = Phpfox_Plugin::get('theme_template_body__end')) ? eval($sPlugin) : false); ?>
<?php echo $this->_sFooter; ?>
        <div class="admincp-nav-bg js_admincp_toggle_nav_btn"></div>
	</body>
</html>
