<?php

use Aws\S3\S3Client;
use League\Flysystem\Adapter\Ftp;
use League\Flysystem\AwsS3v3\AwsS3Adapter;
use League\Flysystem\Filesystem;
use League\Flysystem\Sftp\SftpAdapter;

defined('PHPFOX') or define('PHPFOX', true);

require_once __DIR__ . '/vendor/autoload.php';

class ConvertVideo
{
    private $configs = [];

    public function executeQueue()
    {
        $files = scandir((dirname(dirname(__FILE__))) . '/RawVideo');
        $ext = ['3gp', 'aac', 'ac3', 'ec3', 'flv', 'm4f', 'mov', 'mj2', 'mkv', 'mp4', 'mxf', 'ogg', 'ts', 'webm', 'wmv', 'avi'];
        $configs = $this->getServerConfig();
        $i = 0;
        foreach ($files as $key => $file) {
            $fileExt = substr($file, strpos($file, '.') + 1);
            $fileTitle = substr($file, 0, strpos($file, '.'));
            if (empty($fileExt) || strpos($fileTitle, 'processing_') !== false) {
                //File is processing
                continue;
            }
            if (file_exists((dirname(dirname(__FILE__))) . '/RawVideo/processing_' . $file)) {
                //File is processing by another cron
                continue;
            }
            $time = time();
            if (!in_array(strtolower($fileExt), $ext)) {
                continue;
            }
            $path = (dirname(dirname(__FILE__))) . '/RawVideo/' . $file;
            //Check video is ready before convert
            $vals = ['check_only' => true, 'title' => $fileTitle, 'time' => $time, 'full_title' => $file];
            $callback = $this->executeCallback($vals);
            if ($callback == 'convert' && (!isset($configs['max_execute_files']) || $i < (int)$configs['max_execute_files'])) {
                $processPath = (dirname(dirname(__FILE__))) . '/RawVideo/processing_' . $file;
                rename($path, $processPath);
                $i++;
                $this->_convertVideo($processPath, $fileTitle, $file, $time, $path);
            } elseif ($callback == 'delete') {
                //Delete if don't need to convert anymore
                @unlink($path);
            }
        }
    }

    /**
     * @param $videoPath
     * @param $title
     * @param $fileFullTitle
     * @param $time
     * @param $oldPath
     * @return bool
     */
    private function _convertVideo($videoPath, $title, $fileFullTitle, $time, $oldPath)
    {
        if (empty($videoPath)) {
            echo 'argument_was_not_a_valid_video';
            return false;
        }
        // Make sure FFMPEG path is set
        $ffmpeg_path = $this->getConfigByName('ffmpeg_path');

        if (!$ffmpeg_path) {
            echo 'ffmpeg_not_configured';
            return false;
        }
        // Make sure FFMPEG can be run
        if (!@file_exists($ffmpeg_path) || !@is_executable($ffmpeg_path)) {
            $output = null;
            $return = null;
            exec($ffmpeg_path . ' -version', $output, $return);
            if ($return > 0) {
                echo 'ffmpeg_found_but_is_not_executable';
                return false;
            }
        }

        // Check we can execute
        if (!function_exists('shell_exec')) {
            echo 'unable_to_execute_shell_commands_using_shell_exec_the_function_is_disabled';
            return false;
        }
        $outputPath = (dirname(dirname(__FILE__))) . '/ConvertedVideo/' . $time . '_' . $title . '.mp4';
        $thumbTempPath = (dirname(dirname(__FILE__))) . '/ConvertedVideo/' . $time . '_' . $title . '.jpg';

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
        $aVals = ['status' => $status, 'title' => $title, 'time' => $time, 'full_title' => $fileFullTitle];
        if (!$success) {
            try {
                if ($status == 3) {
                    echo 'your_video_conversion_failed_video_format_is_not_supported_by_ffmpeg';
                } elseif ($status == 5) {
                    echo 'your_video_conversion_failed_audio_files_are_not_supported';
                } else {
                    echo 'unknown_encoding_error';
                }
            } catch (\Exception $e) {
                return false;
            }
            //Callback when fail
            $this->executeCallback($aVals);
            @unlink($outputPath);
            @unlink($videoPath);
            @unlink($thumbTempPath);
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
            //Execute callback to get Storage System
            $fileSystem = $this->getStorageSystem();
            $pathPrefix = 'file/';
            if (empty($fileSystem)) {
                $pathPrefix = '';
                $params = $this->getServerConfig();
                $params['permPublic'] = 0777;
                $params['directoryPerm'] = 0777;
                $params['passive'] = true;
                $params['ssl'] = false;
                $params['ignorePassiveAddress'] = false;
                $fileSystem = new Filesystem(new SftpAdapter($params));
            }
            // Save video
            try {
                $resource = fopen($outputPath, 'r');
                if (!$resource) {
                    throw new InvalidArgumentException("$outputPath does not exists!");
                }
                $newVideoPath = date('Y') . '/' . date('m') . '/' . $title . '.mp4';
                $aVals['video_path'] = $newVideoPath;
                $aVals['video_size'] = filesize($outputPath);
                $destination = 'video/' . $newVideoPath;
                echo $destination;
                $result = $fileSystem->putStream($pathPrefix . $destination, $resource, ['visibility' => 'public']);
                echo ' Video result:' . $result . ' | ';
                fclose($resource);
            } catch (\Exception $e) {
                echo $e->getMessage();
                //Convert later
                rename($videoPath, $oldPath);
                @unlink($outputPath);
                @unlink($thumbTempPath);
                return false;
            }
            $subImagePath = [];
            // Resize thumbnail
            if ($thumbSuccess) {
                try {
                    $resource = fopen($thumbTempPath, 'r');
                    if (!$resource) {
                        throw new InvalidArgumentException("$thumbTempPath does not exists!");
                    }
                    $thumbName = date('Y') . '/' . date('m') . '/' . $title;
                    $newThumbPath = $thumbName . '.jpg';
                    $aVals['image_path'] = 'video/' . $thumbName . '%s.jpg';
                    $aVals['photo_size'] = filesize($thumbTempPath);
                    $thumbDestination = 'pic/video/' . $newThumbPath;
                    echo $thumbDestination;
                    $result = $fileSystem->putStream($pathPrefix . $thumbDestination, $resource, ['visibility' => 'public']);
                    $imageObj = new Video_Image();
                    foreach (['500', '1024'] as $size) {
                        $subImagePath[] = $destination = (dirname(dirname(__FILE__))) . '/ConvertedVideo/' . $time . '_' . $title . '_' . $size . '.jpg';
                        $newDestination = 'pic/video/' . $thumbName . '_' . $size . '.jpg';
                        if ($imageObj->createThumbnail($thumbTempPath, $destination, $size, $size)) {
                            $resourceThumb = fopen($destination, 'r');
                            if (!$resourceThumb) {
                                throw new InvalidArgumentException("$destination does not exists!");
                            }
                            $aVals['photo_size'] += filesize($destination);
                            $fileSystem->putStream($pathPrefix . $newDestination, $resourceThumb, ['visibility' => 'public']);
                        }
                    }
                    echo ' Thumb result:' . $result;
                    fclose($resource);
                } catch (\Exception $e) {
                    var_dump($e);
                }
            }
            // */3 * * * * php /video.php >> /cron.log 2>&1
            if ($this->executeCallback($aVals) == 'delete') {
                @unlink($outputPath);
                @unlink($videoPath);
                @unlink($thumbTempPath);
                foreach ($subImagePath as $path) {
                    @unlink($path);
                }
            } else {
                //If not ready, remove converted file, rename
                rename($videoPath, $oldPath);
                @unlink($outputPath);
                @unlink($thumbTempPath);
                foreach ($subImagePath as $path) {
                    @unlink($path);
                }
            }
        }

        return true;
    }

    public function getServerConfig()
    {
        if (empty($this->configs)) {
            if (file_exists(dirname(__FILE__) . '/config.php')) {
                require_once dirname(__FILE__) . '/config.php';
                $this->configs = isset($_aParams) ? $_aParams : [];
            }
        }
        return $this->configs;
    }

    public function executeCallback($aVal, $getResponse = false)
    {
        $url = $this->getConfigByName('callback_url');
        if (!$url) return false;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($aVal));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($ch);
        curl_close($ch);
        if (!empty($return)) {
            $return = json_decode($return, true);
        }
        return $getResponse ? $return : (isset($return['action']) ? $return['action'] : 'skip');
    }

    public function getConfigByName($name)
    {
        $config = $this->getServerConfig();

        return isset($config[$name]) ? $config[$name] : null;
    }

    public function getStorageSystem()
    {
        $storage = $this->executeCallback(['get_storage' => 1], true);
        $fileSystem = null;
        if (!empty($storage)) {
            $config = isset($storage['config']) ? json_decode($storage['config'], true) : [];
            try {
                switch ($storage['service_id']) {
                    case 'sftp':
                        $params = [
                            'host'                 => $config['host'],
                            'port'                 => $config['port'],
                            'username'             => $config['username'],
                            'password'             => $config['password'],
                            'base_url'             => $config['base_url'],
                            'root'                 => $config['base_path'],
                            'permPublic'           => 0644,
                            'directoryPerm'        => 0755,
                            'passive'              => true,
                            'timeout'              => isset($config['timeout']) ? $config['timeout'] : 30,
                            'ssl'                  => isset($config['ssl']) ? $config['ssl'] : false,
                            'ignorePassiveAddress' => isset($config['ignore_passive_address']) ? $config['ignore_passive_address'] : false
                        ];
                        $fileSystem = new Filesystem(new SftpAdapter($params));
                        break;
                    case 'ftp':
                        $params = [
                            'host'                 => $config['host'],
                            'username'             => $config['username'],
                            'password'             => $config['password'],
                            'port'                 => $config['port'],
                            'root'                 => $config['base_path'],
                            'passive'              => $config['passive'],
                            'timeout'              => isset($config['timeout']) ? $config['timeout'] : 30,
                            'ssl'                  => isset($config['ssl']) ? $config['ssl'] : false,
                            'permPublic'           => 0644,
                            'directoryPerm'        => 0755,
                            'ignorePassiveAddress' => isset($config['ignore_passive_address']) ? $config['ignore_passive_address'] : false
                        ];
                        $fileSystem = new Filesystem(new Ftp($params));
                        break;
                    case 's3':
                        $params = array_merge([
                            'cloudfront_enabled' => false,
                            'cloudfront_url'     => '',
                            'bucket'             => '',
                            'prefix'             => '',
                            'key'                => 's3-key',
                            'secret'             => 's3-secret',
                            'version'            => 'latest',
                        ], $config);
                        $s3Client = new S3Client([
                            'credentials' => [
                                'key'    => $params['key'],
                                'secret' => $params['secret']
                            ],
                            'region'      => $params['region'],
                            'version'     => $params['version'],
                            'scheme'      => 'https'
                        ]);
                        $options = [];
                        if (!empty($params['metadata'])
                            && version_compare(phpversion(), '7.1') >= 0) {
                            foreach ($params['metadata'] as $key => $value) {
                                if (isset($value) && $value != '') {
                                    $options[$key] = $value;
                                }
                            }
                        }
                        $adapter = new AwsS3Adapter($s3Client, $params['bucket'], $params['prefix'], $options);
                        $fileSystem = new Filesystem($adapter);
                        break;
                    case 'dospace':
                        $params = array_merge([
                            'bucket'       => '',
                            'key'          => '',
                            'secret'       => '',
                            'version'      => 'latest',
                            'endpoint'     => '',
                            'cdn_enabled'  => false,
                            'cdn_base_url' => '',
                            'prefix'       => '',
                        ], $config);

                        $endpoint = sprintf("https://%s.digitaloceanspaces.com", $params['region']);
                        $s3Client = new S3Client([
                            'credentials' => [
                                'key'    => $params['key'],
                                'secret' => $params['secret']
                            ],
                            'endpoint'    => $endpoint,
                            'region'      => $params['region'],
                            'version'     => $params['version']
                        ]);
                        $options = [];
                        if (!empty($params['metadata'])
                            && version_compare(phpversion(), '7.1') >= 0) {
                            foreach ($params['metadata'] as $key => $value) {
                                if (isset($value) && $value != '') {
                                    $options[$key] = $value;
                                }
                            }
                        }
                        $adapter = new AwsS3Adapter($s3Client, $params['bucket'], $params['prefix'], $options);
                        $fileSystem = new Filesystem($adapter);
                        break;
                    case 's3compatible':
                        $params = array_merge([
                            'bucket'       => '',
                            'key'          => '',
                            'secret'       => '',
                            'version'      => 'latest',
                            'endpoint'     => '',
                            'base_url'     => '',
                            'cdn_enabled'  => false,
                            'cdn_base_url' => '',
                            'prefix'       => '',
                        ], $config);
                        $options = [];
                        $s3Client = new S3Client([
                            'credentials' => [
                                'key'    => $params['key'],
                                'secret' => $params['secret']
                            ],
                            'endpoint'    => !empty($params['endpoint']) ? $params['endpoint'] : null,
                            'region'      => $params['region'],
                            'version'     => $params['version'],
                        ]);
                        $adapter = new AwsS3Adapter($s3Client, $params['bucket'], $params['prefix'], $options);
                        $fileSystem = new Filesystem($adapter);
                        break;
                    default:
                        break;
                }
            } catch (\Exception $e) {
                echo 'Error Load Storage' . $e->getMessage();
            }
        }
        return $fileSystem;
    }
}

class Video_Image
{
    /**
     * Check to identify if a thumbnail is larger then the actual image being uploaded
     *
     * @var bool
     */
    public $thumbLargeThenPic = false;

    /**
     * Resource for the image we are creating
     *
     * @var resource
     */
    private $_hImg;

    protected $sPath;

    protected $_aTypes = array('', 'gif', 'jpg', 'png', 'jpeg');

    protected $_aInfo = array();

    protected $sType;

    protected $nW;

    protected $nH;

    protected $sMimeType;

    /**
     * Create a thumbnail for an image
     *
     * @param string $sImage Full path of the original image
     * @param string $sDestination Full path for the newly created thumbnail
     * @param int $nMaxW Max width of the thumbnail
     * @param int $nMaxH Max height of the thumbnail
     * @param bool $bRatio TRUE to keep the aspect ratio and FALSE to not keep it
     * @param bool $bSkipCdn Skip the CDN routine
     * @return mixed FALSE on failure, TRUE or NULL on success
     */
    public function createThumbnail($sImage, $sDestination, $nMaxW, $nMaxH)
    {
        if (!$this->_load($sImage)) {
            echo ' Could not load ' . $sImage;
            return false;
        }

        list($nNewW, $nNewH) = $this->_calcSize($nMaxW, $nMaxH);

        if ($this->nW < $nNewW || $this->nH < $nNewH || ($this->nW == $nNewW && $this->nH == $nNewH)) {
            @copy($this->sPath, $sDestination);
            return true;
        }

        switch ($this->_aInfo[2]) {
            case 1:
                $hFrm = @imagecreatefromgif($this->sPath);
                break;
            case 3:
                $hFrm = @imagecreatefrompng($this->sPath);
                break;
            default:
                $hFrm = @imagecreatefromjpeg($this->sPath);
                break;
        }

        if ((int)$nNewH === 0) {
            $nNewH = 1;
        }

        if ((int)$nNewW === 0) {
            $nNewW = 1;
        }

        $hTo = imagecreatetruecolor($nNewW, $nNewH);

        switch ($this->sType) {
            case 'gif':
                $iBlack = imagecolorallocate($hTo, 0, 0, 0);
                imagecolortransparent($hTo, $iBlack);
                break;
            case 'jpeg':
            case 'jpg':
            case 'jpe':
                imagealphablending($hTo, true);
                break;
            case 'png':
                imagealphablending($hTo, false);
                imagesavealpha($hTo, true);
                break;
        }

        if ($this->thumbLargeThenPic === false && $this->nH <= $nNewH && $this->nW <= $nNewW) {
            $hTo = $hFrm;
        } else {
            if ($hFrm) {
                imagecopyresampled($hTo, $hFrm, 0, 0, 0, 0, $nNewW, $nNewH, $this->nW, $this->nH);
            }
        }

        switch ($this->sType) {
            case 'gif':
                if (!$hTo) {
                    @copy($this->sPath, $sDestination);
                } else {
                    @imagegif($hTo, $sDestination);
                }
                break;
            case 'png':
                imagepng($hTo, $sDestination);
                imagealphablending($hTo, false);
                imagesavealpha($hTo, true);
                break;
            default:
                @imagejpeg($hTo, $sDestination);
                break;
        }

        @imagedestroy($hTo);
        @imagedestroy($hFrm);

        if (in_array($this->sType, ['jpg', 'jpeg']) && function_exists('exif_read_data')) {
            @getimagesize($sImage, $aInfo);
            if (isset($aInfo['APP1']) && preg_match('/exif/i', $aInfo['APP1'])) {
                $exif = @exif_read_data($sImage);
                if (!empty($exif['Orientation'])) {
                    switch ($exif['Orientation']) {
                        case 1:
                        case 2:
                            break;
                        case 3:
                        case 4:
                            // 90 degrees
                            $this->rotate($sDestination, 'right');
                            // 180 degrees
                            $this->rotate($sDestination, 'right');
                            break;
                        case 5:
                        case 6:
                            // 90 degrees right
                            $this->rotate($sDestination, 'right');
                            break;
                        case 7:
                        case 8:
                            // 90 degrees left
                            $this->rotate($sDestination, 'left');
                            break;
                        default:
                            break;
                    }
                }
            }
        }
        return true;
    }

    /**
     * Rotate an image (left or right)
     *
     * @param string $sImage Full path to the image
     * @param string $sCmd Command to perform. Must be "left" or "right" (without quotes)
     * @param null $sActualFile
     * @param bool $bInCdn If image in cdn, set it to true
     * @return mixed FALSE on failure, NULL on success
     */
    public function rotate($sImage, $sCmd, $sActualFile = null, $bInCdn = true)
    {
        if (!$this->_load($sImage)) {
            echo ' Could not load ' . $sImage;
            return false;
        }

        switch ($this->_aInfo[2]) {
            case 1:
                $hFrm = @imagecreatefromgif($this->sPath);
                break;
            case 3:
                $hFrm = @imagecreatefrompng($this->sPath);
                break;
            default:
                $hFrm = @imagecreatefromjpeg($this->sPath);
                break;
        }

        if (substr($this->sPath, 0, 7) != 'http://') {
            @unlink($this->sPath);
        }

        if (function_exists('imagerotate')) {
            if ($sCmd == 'left') {
                $im2 = imagerotate($hFrm, 90, 0);
            } else {
                $im2 = imagerotate($hFrm, 270, 0);
            }
        } else {
            $wid = imagesx($hFrm);
            $hei = imagesy($hFrm);
            $im2 = imagecreatetruecolor($hei, $wid);

            switch ($this->sType) {
                case 'jpeg':
                case 'jpg':
                case 'jpe':
                    imagealphablending($im2, true);
                    break;
                case 'png':
                    break;
            }

            for ($i = 0; $i < $wid; $i++) {
                for ($j = 0; $j < $hei; $j++) {
                    $ref = imagecolorat($hFrm, $i, $j);
                    if ($sCmd == 'right') {
                        imagesetpixel($im2, ($hei - 1) - $j, $i, $ref);
                    } else {
                        imagesetpixel($im2, $j, $wid - $i, $ref);
                    }
                }
            }
        }

        switch ($this->sType) {
            case 'gif':
                @imagegif($im2, $this->sPath);
                break;
            case 'png':
                imagealphablending($im2, false);
                imagesavealpha($im2, true);
                @imagepng($im2, $this->sPath);
                break;
            default:
                @imagejpeg($im2, $this->sPath);
                break;
        }

        imagedestroy($hFrm);
        imagedestroy($im2);

        // only run below code if image uploaded to cdn place
        if ($bInCdn) {
            Phpfox::getLib('cdn')->put($this->sPath, $sActualFile);
        }
    }

    /**
     * Load an image and attempt to get as much meta information about the image
     *
     * @param string $sPath Full path to where the image is located
     * @return bool TRUE on success, FALSE on failure
     */
    protected function _load($sPath)
    {
        $this->sPath = $sPath;

        if (file_exists($sPath) && $this->_aInfo = @getimagesize($sPath)) {
            if (!isset($this->_aTypes[$this->_aInfo[2]])) {
                return false;
            }

            $this->nW = $this->_aInfo[0];
            $this->nH = $this->_aInfo[1];
            $this->sType = $this->_aTypes[$this->_aInfo[2]];
            $this->sMimeType = $this->_aInfo['mime'];

            return true;
        }

        return false;
    }

    /**
     * Calculates size for resizing.
     *
     * @param int $nMaxW maximum width
     * @param int $nMaxH maximum height
     * @return array new size (width, height)
     */
    protected function _calcSize($nMaxW, $nMaxH)
    {
        $w = $nMaxW;
        $h = $nMaxH;

        if ($this->nW > $nMaxW) {
            $w = $nMaxW;
            $h = floor($this->nH * $nMaxW / $this->nW);
            if ($h > $nMaxH) {
                $h = $nMaxH;
                $w = floor($this->nW * $nMaxH / $this->nH);
            }
        } elseif ($this->nH > $nMaxH) {
            $h = $nMaxH;
            $w = floor($this->nW * $nMaxH / $this->nH);
        }

        return array($w, $h);
    }
}

$class = new ConvertVideo();
$class->executeQueue();