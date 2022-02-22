<?php

namespace Apps\Core_Pages\Controller;

use Phpfox;

defined('PHPFOX') or exit('NO DICE!');

class FrameController extends \Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (($aVals = $this->request()->getArray('val'))) {
            if ($this->request()->get('widget_id') ? Phpfox::getService('pages.process')->addWidget($this->request()->get('val'),$this->request()->get('widget_id')) : Phpfox::getService('pages.process')->addWidget($this->request()->get('val')))
            {
                $aVals = $this->request()->get('val');
                echo '<script type="text/javascript">window.parent.location.href = \'' . \Phpfox_Url::instance()->makeUrl('pages.add.widget',
                        ['id' => $aVals['page_id'], 'sub_tab' => !empty($aVals['is_block']) ? 'widget' : 'menu']) . '\';</script>';
            } else {
                echo '<script type="text/javascript">';
                echo 'window.parent.$(\'#js_pages_widget_error\').html(\'<div class="error_message">' . implode('<br>',
                        \Phpfox_Error::get()) . '</div>\');window.parent.Core_Pages.resetSubmit();';
                echo '</script>';
            }
            exit;
        } else {
            $this->url()->send('pages');
        }
    }
}
