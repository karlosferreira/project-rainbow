<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php



?>
<div class="breadcrumbs_right_section" id="breadcrumbs_menu">
<?php if (( ! defined ( 'PHPFOX_IS_PAGES_VIEW' ) || ! PHPFOX_IS_PAGES_VIEW ) && ( ! defined ( 'PHPFOX_IS_USER_PROFILE' ) || ! PHPFOX_IS_USER_PROFILE )): ?>
    <?php
						Phpfox::getLib('template')->getBuiltFile('core.block.actions-buttons');
						?>
<?php endif; ?>
</div>
<?php if (! empty ( $this->_aVars['aBreadCrumbs'] ) && count ( $this->_aVars['aBreadCrumbs'] ) >= 2 && ! Phpfox ::isAdminPanel()): ?>
    <div class="container" id="js_block_border_core_breadcrumb">
        <div class="content">
            <div class="row breadcrumbs-holder">
                <div class="clearfix breadcrumbs-top">
                    <div class="breadcrumbs-container">
                        <div class="breadcrumbs-list">
                            <ol class="breadcrumb" data-component="breadcrumb">
<?php if (count((array)$this->_aVars['aBreadCrumbs'])):  $this->_aPhpfoxVars['iteration']['link'] = 0;  foreach ((array) $this->_aVars['aBreadCrumbs'] as $this->_aVars['sLink'] => $this->_aVars['sCrumb']):  $this->_aPhpfoxVars['iteration']['link']++; ?>

                                    <li>
                                        <a <?php if (! empty ( $this->_aVars['sLink'] )): ?>href="<?php echo $this->_aVars['sLink']; ?>" <?php endif; ?> class="ajax_link">
<?php echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['sCrumb'])); ?>
                                        </a>
                                    </li>
<?php endforeach; endif; ?>
                            </ol>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>
