<?php
if ($this->request()->get('req2') == 'pstatusbg' || $this->request()->get('id') == 'P_StatusBg') {
    $this->template()->setHeader([
        'css/admin.css' => 'app_p-status-background',
        'jscript/admin.js' => 'app_p-status-background'
    ])->setPhrase([
        'error',
        'notice',
        'collection_updated_successfully',
        'collection_added_successfully',
        'please_remove_all_error_files_first',
        'title_of_collection_is_required'
    ]);
}
