<?php
\Core\Queue\Manager::instance()->addHandler('core_activitypoint_update_points',
    '\Apps\Core_Activity_Points\Job\UpdatePoints');
