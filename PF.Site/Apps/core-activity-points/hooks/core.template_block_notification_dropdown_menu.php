<?php
if(Phpfox::isAppActive('Core_Activity_Points') && setting('activitypoint.enable_activity_points')){
    $url = url('/activitypoint');
    $label = _p('activitypoint_title');
    echo '<li role="presentation">
       <a href="'. $url .'">
           <i class="ico ico-star-circle-o"></i>
           ' . $label . '
       </a>
   </li>';
}