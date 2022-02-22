<?php

namespace Apps\Core_RSS\Controller\Admin;

use Phpfox;
use Phpfox_Plugin;
use Phpfox_Component;

class AddController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        $bIsEdit = false;
        if (($iId = $this->request()->getInt('id'))) {
            if (($aFeed = Phpfox::getService('rss')->getForEdit($iId))) {
                $bIsEdit = true;
                $this->template()->assign('aForms', $aFeed);
            }
        }

        if (($aVals = $this->request()->getArray('val'))) {
            if ($aVals = $this->_validate($aVals)) {
                if (!Phpfox::isTechie()) {
                    $aVals = array_merge($aVals, ['product_id' => 'phpfox', 'module_id' => 'core']);
                }

                if ($bIsEdit && isset($aFeed)) {
                    if (Phpfox::getService('rss.process')->update($aFeed['feed_id'], $aVals)) {
                        $this->url()->send('admincp.rss.add', ['id' => $aFeed['feed_id']],
                            _p('feed_successfully_updated'));
                    }
                } else {
                    if (Phpfox::getService('rss.process')->add($aVals)) {
                        $this->url()->send('admincp.rss', null, _p('feed_successfully_added'));
                    }
                }
            }
        }

        $this->template()->setTitle((($bIsEdit && isset($aFeed)) ? _p('editing_feed') . ': #' . $aFeed['feed_id'] : _p('add_new_feed')))
            ->setBreadCrumb((($bIsEdit && isset($aFeed)) ? _p('editing_feed') . ': #' . $aFeed['feed_id'] : _p('add_new_feed')),
                null, true)
            ->assign([
                    'bIsEdit' => $bIsEdit,
                    'aGroups' => Phpfox::getService('rss.group')->getDropDown(),
                    'aLanguages' => Phpfox::getService('language')->getAll()
                ]
            );
    }

    /**
     * @param $aVals
     * @return mixed
     * @throws \Exception
     */
    private function _validate($aVals)
    {
        $return = \Phpfox::getService('language')->validateInput($aVals, 'title_var', false);
        if (!$return) {
            $des_return = \Phpfox::getService('language')->validateInput($aVals, 'description_var', false);
            \Phpfox_Error::reset();
            \Phpfox_Error::set(_p('at_least_one_title_for_the_feed_is_required'));
            if (!$des_return) {
                \Phpfox_Error::set(_p('at_least_one_description_for_the_feed_is_required'));
            }
        }
        else {
            $return = \Phpfox::getService('language')->validateInput($return, 'description_var', false);
            if (!$return) {
                \Phpfox_Error::reset();
                \Phpfox_Error::set(_p('at_least_one_description_for_the_feed_is_required'));
            }
        }
        return $return;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('rss.component_controller_admincp_add_clean')) ? eval($sPlugin) : false);
    }
}
