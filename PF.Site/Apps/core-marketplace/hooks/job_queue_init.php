<?php
\Core\Queue\Manager::instance()->addHandler('marketplace_convert_old_location', '\Apps\Core_Marketplace\Job\ConvertOldLocation');

