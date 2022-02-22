<?php
$specialApps = [
    'Core_Subscriptions' => 'subscribe'
];

if(isset($param) && in_array($param['productId'], array_keys($specialApps))) {
    $moduleId = $specialApps[$param['productId']];
    $hasTransaction = db()->select('COUNT(transaction_id)')
        ->from(Phpfox::getT('activitypoint_transaction'))
        ->where(['module_id' => $moduleId, 'is_hidden' => 1])
        ->executeField();
    if(!empty($hasTransaction)) {
        db()->update(Phpfox::getT('activitypoint_transaction'), ['is_hidden' => 0], ['module_id' => $moduleId]);
    }
}