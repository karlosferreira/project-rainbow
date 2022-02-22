<?php

namespace Apps\Core_Comments\Controller;

use Phpfox;
use Phpfox_Component;
use Phpfox_Error;
use Phpfox_Plugin;

defined('PHPFOX') or exit('NO DICE!');

/**
 *
 *
 * @copyright        [PHPFOX_COPYRIGHT]
 * @author           phpFox LLC
 * @package          Phpfox_Component
 * @version          $Id: view.class.php 3686 2011-12-06 11:29:46Z phpFox LLC $
 */
class ViewController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        if (($iCommentId = $this->request()->getInt('req3'))) {
            $aComment = Phpfox::getService('comment')->getComment($iCommentId);

            if (!isset($aComment['comment_id'])) {
                return Phpfox_Error::display(_p('comment_does_not_exist'));
            }

            if (Phpfox::hasCallback('comment', 'getRedirectRequest')) {
                $this->url()->forward(Phpfox::callback('comment.getRedirectRequest', $aComment['comment_id']));
            }

            if (Phpfox::hasCallback($aComment['type_id'], 'getParentItemCommentUrl')) {
                $sNewUrl = Phpfox::callback($aComment['type_id'] . '.getParentItemCommentUrl', $aComment);
                if ($sNewUrl !== false) {
                    $aComment['callback_url'] = $sNewUrl;
                }
            }

            $this->template()->setTitle(_p('viewing_comment'))
                ->setBreadCrumb(_p('viewing_comment'))
                ->assign([
                        'aComment' => $aComment
                    ]
                );
        } else {
            $aComment = Phpfox::getService('comment')->getComment($this->request()->getInt('id'));

            if (!isset($aComment['comment_id'])) {
                return Phpfox_Error::display(_p('comment_does_not_exist'));
            }

            $this->url()->forward(Phpfox::callback('comment.getRedirectRequest', $aComment['comment_id']));
        }
        return null;
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = Phpfox_Plugin::get('comment.component_controller_view_clean')) ? eval($sPlugin) : false);
    }
}