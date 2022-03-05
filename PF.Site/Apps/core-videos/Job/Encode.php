<?php

namespace Apps\PHPfox_Videos\Job;

use Core\Queue\JobAbstract;
use Phpfox;

/**
 * Class Convert
 *
 * @package Apps\PHPfox_Videos\Job
 */
class  Encode extends JobAbstract
{
    /**
     * @throws \Exception
     */
    public function perform()
    {
        $encoding = storage()->get('pf_video_' . $this->getJobId());
        $encodingValue = $encoding->value;
        $sPath = $encodingValue->path;
        $sTitle = $encodingValue->id;
        $aConverts = $this->_convertVideo($sPath, $sTitle);

        if ($aConverts && $aConverts['status'] == 1) {
            $userId = $encodingValue->user_id;
            $serverId = Phpfox::getLib('request')->getServer('PHPFOX_SERVER_ID');
            $aVals = [
                'privacy' => $encodingValue->privacy,
                'privacy_list' => json_decode($encodingValue->privacy_list),
                'callback_module' => $encodingValue->callback_module,
                'callback_item_id' => $encodingValue->callback_item_id,
                'parent_user_id' => $encodingValue->parent_user_id,
                'title' => $encodingValue->title,
                'category' => json_decode($encodingValue->category),
                'text' => $encodingValue->text,
                'status_info' => $encodingValue->status_info,
                'is_stream' => 0,
                'view_id' => $encodingValue->view_id,
                'user_id' => $userId,
                'server_id' => $serverId,
                'path' => $aConverts['video_path'],
                'ext' => $encodingValue->ext,
                'image_path' => $aConverts['image_path'],
                'image_server_id' => $serverId,
                'duration' => $aConverts['duration'],
                'video_size' => $aConverts['video_size'],
                'photo_size' => $aConverts['photo_size'],
                'feed_values' => isset($encodingValue->feed_values) ? json_decode($encodingValue->feed_values) : [],
                'location_name' => $encodingValue->location_name,
                'location_latlng' => $encodingValue->location_latlng,
                'tagged_friends' => $encodingValue->tagged_friends,
                'resolution_x' => $aConverts['resolution_x'],
                'resolution_y' => $aConverts['resolution_y'],
                'is_scheduled' => $encodingValue->is_scheduled,
            ];
            if (!defined('PHPFOX_FEED_NO_CHECK')) {
                define('PHPFOX_FEED_NO_CHECK', true);
            }

            // try to reconnect for a long task.
            Phpfox::getLib('database')->reconnect();

            if (empty($encodingValue->is_scheduled)) {
                $iId = Phpfox::getService('v.process')->addVideo($aVals);

                if (Phpfox::isModule('notification')) {
                    Phpfox::getService('notification.process')->add('v_ready', $iId, $userId, $userId, true);
                }
                $sTitle = (!empty($aVals['title']) ? Phpfox::getLib('parse.output')->clean($aVals['title'], 255) : _p('untitled_video'));
                Phpfox::getLib('mail')->to($userId)
                    ->subject(['email_your_video_title_is_ready', ['title' => $sTitle]])
                    ->message(['your_video_title_is_ready_click_on_link', ['title' => $sTitle, 'link' => Phpfox::permalink('video.play', $iId, $sTitle)]])
                    ->notification('v.email_notification')
                    ->send();
            } else {
                $aVals['feed_values'] = (array)$aVals['feed_values'];
                Phpfox::getService('core.schedule')->redefineScheduleItem($encodingValue->schedule_id, $aVals);
            }
            storage()->del('pf_video_' . $this->getJobId());
        }
        $this->delete();
    }

    /**
     * @param $videoPath
     * @param $sTitle
     * @return array
     */
    private function _convertVideo($videoPath, $sTitle)
    {
        if (empty($videoPath)) {
            echo _p('argument_was_not_a_valid_video');

            return [];
        }
        // Make sure FFMPEG path is set
        $ffmpeg_path = setting('pf_video_ffmpeg_path');
        if (!$ffmpeg_path) {
            echo _p('ffmpeg_not_configured');

            return [];
        }
        // Make sure FFMPEG can be run
        if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path)) {
            $output = null;
            $return = null;
            exec($ffmpeg_path . ' -version', $output, $return);
            if ($return > 0) {
                echo _p('ffmpeg_found_but_is_not_executable');

                return [];
            }
        }

        // Check we can execute
        if (!function_exists('shell_exec')) {
            echo _p('unable_to_execute_shell_commands_using_shell_exec_the_function_is_disabled');

            return [];
        }

        // Check the video directory
        $tmpDir = PHPFOX_DIR_FILE . 'video' . PHPFOX_DS;
        if (!is_dir($tmpDir)) {
            if (!mkdir($tmpDir, 0777, true)) {
                echo _p('video_directory_did_not_exist_and_could_not_be_created');

                return [];
            }
        }
        if (!is_writable($tmpDir)) {
            echo _p('video_directory_is_not_writable');

            return [];
        }
        if (!file_exists($videoPath)) {
            echo _p('could_not_pull_to_temporary_file');

            return [];
        }

        $iToken = rand();
        $outputPath = PHPFOX_DIR_FILE . 'static' . PHPFOX_DS . $iToken . '_' . PHPFOX_TIME . '_vconvert.mp4';
        $thumbTempPath = PHPFOX_DIR_FILE . 'static' . PHPFOX_DS . $iToken . '_' . PHPFOX_TIME . '_vthumb_large.jpg';

        //Convert to Mp4 (h264 - HTML5, mpeg4 - IOS)
        $videoCommand = $ffmpeg_path . ' '
            . '-i ' . escapeshellarg($videoPath) . ' '
            . '-ab 64k' . ' '
            . '-ar 44100' . ' '
            . '-q:v 5' . ' '
            . '-r 25' . ' ';

        $videoCommand .= '-vcodec libx264' . ' '
            . '-acodec aac' . ' '
            . '-strict experimental' . ' '
            . '-preset fast' . ' '
            . '-f mp4' . ' ';

        $videoCommand .=
            '-y ' . escapeshellarg($outputPath) . ' '
            . '2>&1';
        // Prepare output header
        $output = PHP_EOL;
        $output .= $videoPath . PHP_EOL;
        $output .= $outputPath . PHP_EOL;
        // Execute video encode command
        $videoOutput = $output . $videoCommand . PHP_EOL . shell_exec($videoCommand);

        if (defined('PHPFOX_DEBUG') && PHPFOX_DEBUG) {
            Phpfox::getLog('v_encode.log')->info('Encode Log: ' . $videoOutput);
        }

        // Check for failure
        $success = true;
        $status = 0;
        // Unsupported format
        if (preg_match('/Unknown format/i', $videoOutput) || preg_match('/Unsupported codec/i',
                $videoOutput) || preg_match('/patch welcome/i', $videoOutput) || preg_match('/Audio encoding failed/i',
                $videoOutput) || !is_file($outputPath) || filesize($outputPath) <= 0) {
            $success = false;
            $status = 3;
        } // This is for audio files
        else {
            if (preg_match('/video:0kB/i', $videoOutput)) {
                $success = false;
                $status = 5;
            }
        }
        $aVals = ['status' => $status];
        if (!$success) {
            try {
                if ($status == 3) {
                    echo _p('your_video_conversion_failed_video_format_is_not_supported_by_ffmpeg');
                } elseif ($status == 5) {
                    echo _p('your_video_conversion_failed_audio_files_are_not_supported');
                } else {
                    echo _p('unknown_encoding_error');
                }
            } catch (\Exception $e) {
            }
        } else {
            // Get duration of the video to caculate where to get the thumbnail
            if (preg_match('/Duration:\s+(.*?)[.]/i', $videoOutput, $matches)) {
                list($hours, $minutes, $seconds) = preg_split('[:]', $matches[1]);
                $duration = ceil($seconds + ($minutes * 60) + ($hours * 3600));
            } else {
                $duration = 0;
            }

            $resolutionX = $resolutionY = null;
            if (preg_match('/([0-9]{2,}x[0-9]+)/', $videoOutput, $matches)) {
                list($resolutionX, $resolutionY) = preg_split('[x]', $matches[1]);
            }
            $aVals['resolution_x'] = $resolutionX;
            $aVals['resolution_y'] = $resolutionY;
            $aVals['duration'] = $duration;
            // Fetch where to take the thumbnail
            $thumb_splice = $duration / 2;

            // Thumbnail proccess command
            $thumbCommand = $ffmpeg_path . ' ' . '-i ' . escapeshellarg($outputPath) . ' ' . '-f image2' . ' ' . '-ss ' . $thumb_splice . ' ' . '-vframes ' . '1' . ' ' . '-v 2' . ' ' . '-y ' . escapeshellarg($thumbTempPath) . ' ' . '2>&1';

            // Process thumbnail
            $thumbOutput = $output . $thumbCommand . PHP_EOL . shell_exec($thumbCommand);

            // Check output message for success
            $thumbSuccess = true;
            if (preg_match('/video:0kB/i', $thumbOutput)) {
                $thumbSuccess = false;
            }
            // Resize thumbnail
            if ($thumbSuccess) {
                try {
                    if (is_file($thumbTempPath)) {
                        $sNewsPicStorage = Phpfox::getParam('core.dir_pic') . 'video';
                        if (!is_dir($sNewsPicStorage)) {
                            @mkdir($sNewsPicStorage, 0777, 1);
                            @chmod($sNewsPicStorage, 0777);
                        }
                        $ThumbNail = Phpfox::getLib('file')->getBuiltDir($sNewsPicStorage . PHPFOX_DS) . md5('image_' . $iToken . '_' . PHPFOX_TIME) . '%s.jpg';
                        Phpfox::getLib('image')->createThumbnail($thumbTempPath, sprintf($ThumbNail, '_' . 500), 500,
                            500);
                        Phpfox::getLib('image')->createThumbnail($thumbTempPath, sprintf($ThumbNail, '_' . 1024), 1024,
                            1024);
                        $sFileName = str_replace(Phpfox::getParam('core.dir_pic'), "", $ThumbNail);
                        $sFileName = str_replace("\\", "/", $sFileName);
                        $aVals['image_path'] = $sFileName;
                        $aVals['status'] = 1;
                        $iPhotoSize = 0;
                        if (file_exists(sprintf($ThumbNail, '_' . 500))) {
                            $iPhotoSize += filesize(sprintf($ThumbNail, '_' . 500));
                        }
                        if (file_exists(sprintf($ThumbNail, '_' . 1024))) {
                            $iPhotoSize += filesize(sprintf($ThumbNail, '_' . 1024));
                        }
                        $aVals['photo_size'] = $iPhotoSize;
                        @unlink($thumbTempPath);
                    }
                } catch (\Exception $e) {
                }
            }
            // Save video
            try {
                $saveVideoPath = Phpfox::getLib('file')->upload($outputPath, PHPFOX_DIR_FILE . 'video' . PHPFOX_DS,
                    $sTitle);
                $aVals['video_path'] = sprintf($saveVideoPath, '');
                if (file_exists($outputPath)) {
                    $aVals['video_size'] = filesize($outputPath);
                }

                // delete the files from temp dir
                @unlink($outputPath);
                @unlink($videoPath);

            } catch (\Exception $e) {
                @unlink($videoPath);
                @unlink($outputPath);
            }

        }

        return $aVals;
    }
}
