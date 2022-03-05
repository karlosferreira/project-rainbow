<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php



 if (isset ( $this->_aVars['aFilterMenus'] ) && is_array ( $this->_aVars['aFilterMenus'] ) && count ( $this->_aVars['aFilterMenus'] )): ?>
<div class="block" id="js_block_border_core_menusub">
    <div class="title">
<?php if (isset ( $this->_aVars['sMenuBlockTitle'] )): ?>
<?php echo $this->_aVars['sMenuBlockTitle']; ?>
<?php else: ?>
<?php echo _p('menu'); ?>
<?php endif; ?>
    </div>
    <div class="content">
        <div class="sub-section-menu header_display">
<?php if (! empty ( $this->_aVars['aMainSelectedMenu'] ) && ! empty ( $this->_aVars['aMainSelectedMenu']['var_name'] )): ?>
            <div class="app-name"><?php echo _p($this->_aVars['aMainSelectedMenu']['var_name']);  if (isset ( $this->_aVars['aMainSelectedMenu']['suffix'] )):  echo $this->_aVars['aMainSelectedMenu']['suffix'];  endif; ?></div>
<?php endif; ?>
            <ul class="action" <?php if (! isset ( $this->_aVars['sMenuBlockTitle'] )): ?>data-component="menu"<?php endif; ?>>
                <div class="overlay"></div>
<?php if (count((array)$this->_aVars['aFilterMenus'])):  $this->_aPhpfoxVars['iteration']['filtermenu'] = 0;  foreach ((array) $this->_aVars['aFilterMenus'] as $this->_aVars['aFilterMenu']):  $this->_aPhpfoxVars['iteration']['filtermenu']++; ?>

<?php if (! isset ( $this->_aVars['aFilterMenu']['name'] )): ?>
                    <li class="menu_line"></li>
<?php else: ?>
                    <li class="<?php if ($this->_aVars['aFilterMenu']['active']): ?>active<?php endif; ?>">
                        <?php
                        if (!empty($this->_aVars['aFilterMenusIcons'][$this->_aVars['aFilterMenu']['name']])):
                            echo sprintf("<span class='%s'></span>", $this->_aVars['aFilterMenusIcons'][$this->_aVars['aFilterMenu']['name']]);
                        endif;
                        ?>
                        <a href="<?php echo $this->_aVars['aFilterMenu']['link']; ?>">
<?php echo $this->_aVars['aFilterMenu']['name']; ?>
                        </a>
                    </li>
<?php endif; ?>
<?php endforeach; endif; ?>
                <li class="hide explorer">
                    <a data-toggle="dropdown" role="button">
                        <span class="ico ico-dottedmore-o"></span>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-right">
                    </ul>
                </li>
            </ul>
        </div>

        <div class="sub-section-menu-mobile dropdown <?php if (! empty ( $this->_aVars['aSubMenus'] ) || ! empty ( $this->_aVars['aCustomMenus'] )): ?>has-btn-addnew<?php endif; ?>">
            <span class="btn-toggle" data-toggle="dropdown" aria-expanded="false" role="button">
                <span class="ico ico-angle-down"></span>
            </span>
            <ul class="dropdown-menu">
<?php if (count((array)$this->_aVars['aFilterMenus'])):  $this->_aPhpfoxVars['iteration']['filtermenu'] = 0;  foreach ((array) $this->_aVars['aFilterMenus'] as $this->_aVars['aFilterMenu']):  $this->_aPhpfoxVars['iteration']['filtermenu']++; ?>

<?php if (! isset ( $this->_aVars['aFilterMenu']['name'] )): ?>
                <li class="menu_line"></li>
<?php else: ?>
                <li class="<?php if ($this->_aVars['aFilterMenu']['active']): ?>active<?php endif; ?>">
                    <a href="<?php echo $this->_aVars['aFilterMenu']['link']; ?>">
<?php echo $this->_aVars['aFilterMenu']['name']; ?>
                    </a>
                </li>
<?php endif; ?>
<?php endforeach; endif; ?>
            </ul>
        </div>
    </div>
</div>
<?php endif; ?>


							
