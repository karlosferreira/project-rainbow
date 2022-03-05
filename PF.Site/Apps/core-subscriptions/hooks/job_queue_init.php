<?php
\Core\Queue\Manager::instance()->addHandler('subscribe_process_active_subscription_after_delete_package', '\Apps\Core_Subscriptions\Job\ProcessActiveSubscriptionAfterDeletePackage');
