<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 4, 2022, 12:39 am */ ?>
<?php 
 
 

?>

<?php if (! isset ( $this->_aVars['sPagingMode'] )):  $this->assign('sPagingMode', 'loadmore');  endif; ?>

<?php if (! empty ( $this->_aVars['bPopup'] )): ?>
<?php if (! empty ( $this->_aVars['aPager']['nextAjaxUrl'] )): ?>
        <div class="js_pager_popup_view_more_link">
            <a href="<?php echo $this->_aVars['aPager']['nextUrl']; ?>" class="button btn-small no_ajax_link" onclick="$.ajaxCall('<?php echo $this->_aVars['sAjax']; ?>', 'page=<?php echo $this->_aVars['aPager']['nextAjaxUrl'];  echo $this->_aVars['aPager']['sParamsAjax']; ?>', 'GET'); return false;">
<?php if (! empty ( $this->_aVars['aPager']['icon'] )): ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => $this->_aVars['aPager']['icon'],'class' => 'v_middle')); ?>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aPager']['phrase'] )):  echo $this->_aVars['aPager']['phrase'];  else:  echo _p('view_more');  endif; ?>
            </a>
        </div>
<?php endif;  elseif ($this->_aVars['sPagingMode'] == 'loadmore'): ?>
    <div class="js_pager_view_more_link">
<?php if (! empty ( $this->_aVars['bIsAdminCp'] ) && Phpfox ::isAdminPanel() && empty ( $this->_aVars['aAjaxPaging'] )): ?>
            <div class="pager_view_more_holder">
                <div class="pager_view_more_link">
<?php if (! empty ( $this->_aVars['aPager']['nextAjaxUrl'] )): ?>
                    <a href="<?php echo $this->_aVars['aPager']['nextUrl']; ?>" class="pager_view_more no_ajax_link" onclick="$.ajaxCall('<?php echo $this->_aVars['sAjax']; ?>', 'page=<?php echo $this->_aVars['aPager']['nextAjaxUrl'];  echo $this->_aVars['aPager']['sParamsAjax']; ?>', 'GET'); return false;">
<?php if (! empty ( $this->_aVars['aPager']['icon'] )): ?>
<?php echo Phpfox::getLib('phpfox.image.helper')->display(array('theme' => $this->_aVars['aPager']['icon'],'class' => 'v_middle')); ?>
<?php endif; ?>
<?php if (! empty ( $this->_aVars['aPager']['phrase'] )):  echo $this->_aVars['aPager']['phrase'];  else:  echo _p('view_more');  endif; ?>
                        <span><?php echo _p('displaying_of_total', array('displaying' => $this->_aVars['aPager']['displaying'],'total' => $this->_aVars['aPager']['totalRows'])); ?></span>
                    </a>
<?php endif; ?>
                </div>
            </div>
<?php elseif (! empty ( $this->_aVars['aAjaxPaging'] )): ?>
            <div class="js_pager_buttons" data-block="<?php echo $this->_aVars['aAjaxPaging']['block']; ?>" data-content-container="<?php echo $this->_aVars['aAjaxPaging']['container']; ?>">
                <a class="ajax-paging" <?php if (! empty ( $this->_aVars['aPager']['rel'] )): ?>rel="<?php echo $this->_aVars['aPager']['rel']; ?>"<?php endif; ?> data-params="<?php echo $this->_aVars['aAjaxPaging']['sParam']; ?>&page=<?php echo $this->_aVars['iNextPage']; ?>&type=loadmore" href="javascript:void(0);">
<?php echo _p('load_more'); ?>
                </a>
            </div>
<?php else: ?>
            <a href="<?php echo $this->_aVars['sNextUrl']; ?>" class="next_page" data-paging="<?php if (isset ( $this->_aVars['sPagingVar'] )):  echo $this->_aVars['sPagingVar'];  endif; ?>">
                <i class="fa fa-spin fa-circle-o-notch"></i>
                <span><?php echo _p('load_more'); ?></span>
            </a>
<?php endif; ?>
    </div>
<?php elseif (! empty ( $this->_aVars['aPagers'] ) && $this->_aVars['iTotalPagerItems'] = count ( $this->_aVars['aPagers'] )): ?>
    <div class="js_pager_buttons" <?php if (! empty ( $this->_aVars['aAjaxPaging'] )): ?>data-block="<?php echo $this->_aVars['aAjaxPaging']['block']; ?>" data-content-container="<?php echo $this->_aVars['aAjaxPaging']['container']; ?>"<?php endif; ?>>
        <ul class="pagination items-<?php echo $this->_aVars['iTotalPagerItems']; ?>">
<?php if (count((array)$this->_aVars['aPagers'])):  foreach ((array) $this->_aVars['aPagers'] as $this->_aVars['aPager']): ?>
            <li class="page-item<?php if (! empty ( $this->_aVars['aPager']['attr'] )): ?> <?php echo $this->_aVars['aPager']['attr'];  endif;  if (! empty ( $this->_aVars['aPager']['rel'] )): ?> <?php echo $this->_aVars['aPager']['rel'];  endif; ?>">
<?php if (! empty ( $this->_aVars['aPager']['attr'] ) && ( $this->_aVars['aPager']['attr'] == 'disabled' )): ?>
                <a class="page-link" href="javascript:void(0);" <?php if (! empty ( $this->_aVars['aPager']['rel'] )): ?>rel="<?php echo $this->_aVars['aPager']['rel']; ?>"<?php endif; ?>><?php echo $this->_aVars['aPager']['label']; ?></a>
<?php else: ?>
                <a class="page-link<?php if (! empty ( $this->_aVars['aAjaxPaging'] )): ?> ajax-paging<?php endif; ?>" <?php if (! empty ( $this->_aVars['aPager']['rel'] )): ?>rel="<?php echo $this->_aVars['aPager']['rel']; ?>"<?php endif; ?> <?php if (! empty ( $this->_aVars['aAjaxPaging'] )): ?>data-params="<?php echo $this->_aVars['aAjaxPaging']['sParam']; ?>&page=<?php echo $this->_aVars['aPager']['page_number']; ?>" href="javascript:void(0);"<?php else: ?>href="<?php echo $this->_aVars['aPager']['link']; ?>"<?php endif; ?>><?php echo $this->_aVars['aPager']['label']; ?></a>
<?php endif; ?>
            </li>
<?php endforeach; endif; ?>
        </ul>
    </div>
<?php endif; ?>
