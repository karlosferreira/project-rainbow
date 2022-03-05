<?php
if (Phpfox::isAppActive('P_SavedItems')) {
    echo '<li role="presentation">
       <a href="' . url('saved') . '">
           <i class="ico ico-bookmark-o"></i>
           ' . _p('module_saveditems') . '
       </a>
   </li>';
}