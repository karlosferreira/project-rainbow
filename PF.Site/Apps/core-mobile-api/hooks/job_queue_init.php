<?php
\Core\Queue\Manager::instance()->addHandler('mobile_push_notification', '\Apps\Core_MobileApi\Job\PushNotification');
