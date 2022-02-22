<?php
if (Phpfox::isAppActive('P_SavedItems')) {
    $phrases = [
        'by',
        'saveditems_collection_item',
        'saveditems_collection_items',
        'saveditems_create_new_collection_uc_first',
        'saveditems_oldest',
        'saveditems_unopened',
        'saveditems_opened',
        'saveditems_saved_to_title',
        'saveditems_number_collections',
        'saveditems_collections',
        'saveditems_latest'
    ];
    foreach ($phrases as $phrase) {
        if (!in_array($phrase, $phrasesList)) {
            $phrasesList[] = $phrase;
        }
    }
}