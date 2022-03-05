<?php

namespace Apps\Core_Newsletter\Installation\Version;

use Phpfox;

class v462
{
    public function process()
    {
        $newsletterTable = Phpfox::getT('newsletter');
        if (db()->isField($newsletterTable, 'job_id')) {
            db()->changeField($newsletterTable, 'job_id', [
                'type' => 'VCHAR:30',
                'null' => true,
            ]);
        }
    }
}
