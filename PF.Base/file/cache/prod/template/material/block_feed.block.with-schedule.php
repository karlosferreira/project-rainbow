<?php defined('PHPFOX') or exit('NO DICE!'); ?>
<?php /* Cached: March 3, 2022, 2:34 pm */ ?>
<?php

?>
<a href="#" type="button" id="btn_display_with_schedule" class="activity_feed_share_this_one_btn js_btn_display_with_schedule parent js_hover_title dont-unbind-children" onclick="return false;">
    <span class="ico ico-clock-o"></span>
    <span class="js_hover_info">
<?php echo _p('schedule'); ?>
    </span>
</a>
<script type="text/javascript">
    $Behavior.prepareScheduleInit = function() {
        $Core.FeedSchedule.init();
    }
</script>
<?php echo Phpfox::getLib('template')->addScript('schedule-form.js', 'module_core',true); ?>

