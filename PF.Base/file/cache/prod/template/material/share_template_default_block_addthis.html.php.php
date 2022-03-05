<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<div class="addthis_block">
<?php (($sPlugin = Phpfox_Plugin::get('share.template_block_addthis_start')) ? eval($sPlugin) : false); ?>
    <script type="text/javascript" src="//s7.addthis.com/js/300/addthis_widget.js#pubid=<?php echo $this->_aVars['sAddThisPubId']; ?>"></script>
<?php if ($this->_aVars['sAddThisShareButton']): ?>
<?php echo $this->_aVars['sAddThisShareButton']; ?>
<?php else: ?>
    <div class="addthis_toolbox addthis_32x32_style">
        <a class="addthis_button_facebook"></a>
        <a class="addthis_button_twitter"></a>
        <a class="addthis_button_email"></a>
        <a class="addthis_button_linkedin"></a>
        <a class="addthis_button_compact"></a>
    </div>
<?php endif; ?>
    <?php echo '
    <script language="javascript" type="text/javascript">
        $Behavior.onLoadAddthis = function () {
            if (typeof addthis != \'undefined\') {
                $(\'.addthis_block\').length > 0 && typeof addthis.layers.refresh === \'function\' && addthis.layers.refresh();
                $(\'.addthis_toolbox\').length > 0 && addthis.toolbox(\'.addthis_toolbox\');
            }
        }
    </script>
    '; ?>

<?php if (( $this->_aVars['sAddthisUrl'] || $this->_aVars['sAddthisTitle'] || $this->_aVars['sAddthisDesc'] || $this->_aVars['sAddthisMedia'] )): ?>
    <?php echo '
        <script type="text/javascript">
            $Behavior.onUpdateAddthis = function () {
                if (typeof addthis != \'undefined\') {
                    addthis.update(\'share\', \'url\', "';  echo $this->_aVars['sAddthisUrl'];  echo '");
                    addthis.update(\'share\', \'title\', "';  echo Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean($this->_aVars['sAddthisTitle']));  echo '");
                    addthis.update(\'share\', \'description\', "';  echo Phpfox::getLib('phpfox.parse.output')->shorten(Phpfox::getLib('phpfox.parse.output')->cleanPhrases(Phpfox::getLib('phpfox.parse.output')->clean(strip_tags($this->_aVars['sAddthisDesc']))), 200, '...');  echo '");
                    addthis.update(\'share\', \'media\', "';  echo $this->_aVars['sAddthisMedia'];  echo '");
                }
            }
        </script>
    '; ?>

<?php endif; ?>
<?php (($sPlugin = Phpfox_Plugin::get('share.template_block_addthis_end')) ? eval($sPlugin) : false); ?>
</div>
