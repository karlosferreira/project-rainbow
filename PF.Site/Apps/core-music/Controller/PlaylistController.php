<?php

namespace Apps\Core_Music\Controller;

defined('PHPFOX') or exit('NO DICE!');

use Phpfox;
use Phpfox_Component;
use Phpfox_Validator;

class PlaylistController extends Phpfox_Component
{
    /**
     * Controller
     */
    public function process()
    {
        Phpfox::isUser(true);
        Phpfox::getUserParam('music.can_access_music', true);
        $bIsEdit = false;
        $aPlaylist = [];
        $aVals = $this->request()->getArray('val');
        $sAction = $this->request()->get('req3');
        $iEditId = (int)$this->request()->getInt('id');
        if (!$iEditId) {
            Phpfox::getService('music.playlist')->canCreateNewPlaylist();
        }

        if ($iEditId) {
            $bIsEdit = true;
            $aPlaylist = Phpfox::getService('music.playlist')->getForEdit($iEditId);
            $aSongs = Phpfox::getService('music.playlist')->getAllSongs($iEditId, true);

            $this->template()->assign([
                'aForms'   => $aPlaylist,
                'aSongs'   => $aSongs,
                'bNoTitle' => true
            ]);
        }

        $aValidation = [
            'name' => _p('provide_a_name_for_this_playlist'),
        ];

        $oValidator = Phpfox_Validator::instance()->set([
                'sFormName' => 'js_music_playlist_form',
                'aParams'   => $aValidation
            ]
        );
        if ($aVals) {
            if ($oValidator->isValid($aVals)) {
                if ($bIsEdit) {
                    if (isset($aVals['save_info'])) {
                        $bReturn = Phpfox::getService('music.playlist.process')->update($iEditId, $aVals);
                    } else {
                        //Save manage song
                        $bReturn = Phpfox::getService('music.playlist.process')->updateManageSongs($iEditId, $aVals);
                        $sAction = 'manage';
                    }
                    if ($bReturn) {
                        switch ($sAction) {
                            case 'manage':
                                $this->url()->send('music.playlist', ['id' => $iEditId, 'tab' => 'manage'],
                                    _p('successfully_updated_playlist_s_songs'));
                                break;
                            default:
                                $this->url()->send('music.playlist', [
                                    'id'  => $iEditId,
                                    'tab' => empty($aVals['current_tab']) ? '' : $aVals['current_tab']
                                ], _p('successfully_updated_playlist'));
                                break;
                        }
                    }
                } else {
                    if ($iId = Phpfox::getService('music.playlist.process')->add($aVals)) {
                        $this->url()->permalink('music.playlist', $iId, $aVals['name'], true, _p('successfully_added_playlist'));
                    }
                }
            }
        }

        if (!empty($iEditId) && !empty($aPlaylist)) {
            $this->template()->buildPageMenu('js_music_playlist',
                [
                    'detail' => _p('playlist_info'),
                    'manage' => _p('manage_songs')
                ],
                [
                    'link'   => Phpfox::permalink('music.playlist', $iEditId, $aPlaylist['name']),
                    'phrase' => _p('view_playlist')
                ]
            );
        }
        $this->template()->setTitle(($bIsEdit ? _p('editing_playlist') . ': ' . $aPlaylist['name'] : _p('create_playlist')))
            ->setBreadCrumb(_p('music'), $this->url()->makeUrl('music'))
            ->setBreadCrumb(($bIsEdit ? _p('editing_playlist') . ': ' . $aPlaylist['name'] : _p('create_playlist')),
                $this->url()->makeUrl('current'), true)
            ->assign([
                'bIsEdit' => $bIsEdit
            ]);

        if (Phpfox::isModule('attachment')) {
            $this->setParam('attachment_share', [
                    'type'    => 'music_playlist',
                    'id'      => 'js_music_add_playlist_form',
                    'edit_id' => ($bIsEdit ? $iEditId : 0)
                ]
            );
        }
    }

    /**
     * Garbage collector. Is executed after this class has completed
     * its job and the template has also been displayed.
     */
    public function clean()
    {
        (($sPlugin = \Phpfox_Plugin::get('music.component_controller_playlist_clean')) ? eval($sPlugin) : false);
    }
}