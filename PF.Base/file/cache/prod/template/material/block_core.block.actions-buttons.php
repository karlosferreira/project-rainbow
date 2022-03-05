<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php if (( ( isset ( $this->_aVars['aSubMenus'] ) && count ( $this->_aVars['aSubMenus'] ) || isset ( $this->_aVars['aCustomMenus'] ) ) ) && Phpfox ::isUser() && empty ( $this->_aVars['bNotShowActionButton'] )): ?>
<div class="app-addnew-block">
    <div class="btn-app-addnew">
<?php if (count ( $this->_aVars['aSubMenus'] ) == 1 && empty ( $this->_aVars['aCustomMenus'] ) && ( $this->_aVars['aSubMenu'] = reset ( $this->_aVars['aSubMenus'] ) )): ?>
        <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aSubMenu']['url'], [], false, false); ?>" class="btn btn-success btn-gradient js_hover_title <?php if (! empty ( $this->_aVars['aSubMenu']['css_name'] )):  echo $this->_aVars['aSubMenu']['css_name'];  endif; ?>">
            <span class="ico ico-plus"></span>
            <span class="js_hover_info">
<?php if (isset ( $this->_aVars['aSubMenu']['text'] )): ?>
<?php echo $this->_aVars['aSubMenu']['text']; ?>
<?php else: ?>
<?php echo _p($this->_aVars['aSubMenu']['var_name']); ?>
<?php endif; ?>
            </span>
        </a>
<?php elseif (empty ( $this->_aVars['aSubMenus'] ) && ! empty ( $this->_aVars['aCustomMenus'] ) && count ( $this->_aVars['aCustomMenus'] ) == 1 && ( $this->_aVars['aSubMenu'] = reset ( $this->_aVars['aCustomMenus'] ) )): ?>
        <a href="<?php echo $this->_aVars['aSubMenu']['url']; ?>" class="btn btn-success btn-gradient js_hover_title <?php if (( isset ( $this->_aVars['aSubMenu']['css_class'] ) )): ?> <?php echo $this->_aVars['aSubMenu']['css_class'];  endif; ?>" <?php if (! empty ( $this->_aVars['aSubMenu']['extra'] )):  echo $this->_aVars['aSubMenu']['extra'];  endif; ?>>
            <span class="ico ico-plus"></span>
            <span class="js_hover_info"><?php echo $this->_aVars['aSubMenu']['title']; ?></span>
        </a>
<?php else: ?>
        <a role="button" class="btn btn-success btn-gradient" data-toggle="dropdown">
            <span class="ico ico-plus"></span>
        </a>
        <ul class="dropdown-menu dropdown-menu-right">
<?php if (( isset ( $this->_aVars['aCustomMenus'] ) )): ?>
<?php if (count((array)$this->_aVars['aCustomMenus'])):  $this->_aPhpfoxVars['iteration']['menu'] = 0;  foreach ((array) $this->_aVars['aCustomMenus'] as $this->_aVars['iKey'] => $this->_aVars['aMenu']):  $this->_aPhpfoxVars['iteration']['menu']++; ?>

            <li>
                <a class="<?php if (( isset ( $this->_aVars['aMenu']['css_class'] ) )): ?> <?php echo $this->_aVars['aMenu']['css_class'];  endif; ?>" href="<?php echo $this->_aVars['aMenu']['url']; ?>" <?php if (! empty ( $this->_aVars['aMenu']['extra'] )):  echo $this->_aVars['aMenu']['extra'];  endif; ?>>
<?php if (! empty ( $this->_aVars['aMenu']['icon_class'] )): ?>
                    <span class="<?php echo $this->_aVars['aMenu']['icon_class']; ?>"></span>
<?php else: ?>
                    <span class="ico ico-compose-alt"></span>
<?php endif; ?>
<?php echo $this->_aVars['aMenu']['title']; ?>
                </a>
            </li>
<?php endforeach; endif; ?>
<?php endif; ?>

<?php if (count((array)$this->_aVars['aSubMenus'])):  $this->_aPhpfoxVars['iteration']['submenu'] = 0;  foreach ((array) $this->_aVars['aSubMenus'] as $this->_aVars['iKey'] => $this->_aVars['aSubMenu']):  $this->_aPhpfoxVars['iteration']['submenu']++; ?>

            <li>
<?php if (isset ( $this->_aVars['aSubMenu']['module'] ) && ( isset ( $this->_aVars['aSubMenu']['var_name'] ) || isset ( $this->_aVars['aSubMenu']['text'] ) )): ?>
                <a href="<?php echo Phpfox::getLib('phpfox.url')->makeUrl($this->_aVars['aSubMenu']['url'], [], false, false); ?>"<?php if (( isset ( $this->_aVars['aSubMenu']['css_name'] ) )): ?> class="<?php echo $this->_aVars['aSubMenu']['css_name']; ?> no_ajax"<?php else: ?>class=""<?php endif; ?>>
<?php if (! empty ( $this->_aVars['aSubMenu']['icon_class'] )): ?>
                <span class="<?php echo $this->_aVars['aMenu']['icon_class']; ?>"></span>
<?php else: ?>
                <span class="ico ico-compose-alt"></span>
<?php endif; ?>
<?php if (isset ( $this->_aVars['aSubMenu']['text'] )): ?>
<?php echo $this->_aVars['aSubMenu']['text']; ?>
<?php else: ?>
<?php echo _p($this->_aVars['aSubMenu']['var_name']); ?>
<?php endif; ?>
                </a>
<?php endif; ?>
            </li>
<?php endforeach; endif; ?>
        </ul>
<?php endif; ?>
    </div>
</div>
<?php endif; ?>
