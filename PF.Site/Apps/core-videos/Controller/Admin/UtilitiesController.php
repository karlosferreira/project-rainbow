<?php

namespace Apps\PHPfox_Videos\Controller\Admin;

use Admincp_Component_Controller_App_Index;

defined('PHPFOX') or exit('NO DICE!');

class UtilitiesController extends Admincp_Component_Controller_App_Index
{
    public function process()
    {
        parent::process();
        $isError = false;
        $isErrorExec = false;
        $ffmpegPath = setting('pf_video_ffmpeg_path');
        $version = $format = "";
        $return = null;
        if (function_exists('exec')) {
            $output = null;
            if (!empty($ffmpegPath)) {
                exec($ffmpegPath . ' -version', $output, $return);
            }
        } else {
            $isErrorExec = true;
        }
        if (!empty($ffmpegPath) && $return == 0 && function_exists('shell_exec')) {
            $version = shell_exec(escapeshellcmd($ffmpegPath) . ' -version 2>&1');
            $format = shell_exec(escapeshellcmd($ffmpegPath) . ' -formats 2>&1')
                . shell_exec(escapeshellcmd($ffmpegPath) . ' -codecs 2>&1');
        } else {
            $isError = true;
            $isErrorExec = !function_exists('shell_exec');
        }
        if ($return != 0) {
            $isError = true;
        }
        $this->template()->setTitle(_p('ffmpeg_video_utilities'))
            ->setBreadcrumb(_p('ffmpeg_video_utilities'), $this->url()->makeUrl('admincp.v.utilities'))
            ->assign(array(
                'isError' => $isError,
                'isErrorExec' => $isErrorExec,
                'sVersion' => $version,
                'sFormat' => $format,
            ));
    }
}
