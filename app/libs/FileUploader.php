<?php

class FileUploader
{
    private $app;
    public $max_size = 10485760; //10M

    public static $allowed_mime_types = [
        'gif' => 'image/gif', 
        'jpg' => 'image/jpeg', 
        'png' => 'image/png',
        'svg' => 'image/svg+xml',
        'tif' => 'image/tiff',
        'mp4' => 'video/mp4',
        'webm' => 'video/webm',
        'ogv' => 'video/ogg',
    ];

    public function __construct($app)
    {
        $this->app = $app;
    }

    public static function isAllowedType($mime_type)
    {
        if (in_array($mime_type, self::$allowed_mime_types)) {
            return true;
        }
        return false;
    }

    public static function fileExtension($mime_type)
    {
        $extension = array_search($mime_type, self::$allowed_mime_types);
        if ($extension) {
            return ".{$extension}";
        }
        return null;
    }

    public function parseHash($hash)
    {
        if (isset($this->uid)) {
            return date('Ymd', time()) . "/" . $this->uid . "/" . $hash;
        } else {
            return date('Ymd', time()) . "/" . $hash;
        }
    }

    private function parseParams($params)
    {
        foreach($params as $key => $value) {
            $this->$key = $value;
        }
    }

    public function upload($params = [])
    {
        $app = $this->app;
        $errors = [];
        $rows = [];
        $files = $app->request->getUploadedFiles();
        $this->parseParams($params);

        foreach ($files as $file) {
            $original_name = $file->getName();
            $temp_name = $file->getTempName();
            $size = $file->getSize();

            if ($size <= 0) {
                $errors[$original_name] = ['code' => 1003, 'message' => 'empty file'];
                continue;
            }

            if ($size > $this->max_size) {
                $errors[$original_name] = ['code' => 1201, 'message' => 'size over limit'];
                continue;
            }

            $mime_type = $file->getRealType();
            if (!self::isAllowedType($mime_type)) {
                $errors[$original_name] = ['code' => 1202, 'message' => 'file type is not allowed'];
                continue;
            }

            // $image = new \Phalcon\Image\Adapter\GD($temp_name);
            // $app->logger->debug(strval($image->getWidth()));
            // if ($image->getWidth() > $this->max_width) {
            //     $errors[$original_name] = ['code' => 1203, 'message' => 'width is over limit'];
            //     continue;
            // }

            // $app->logger->debug(strval($image->getHeight()));
            // if ($image->getHeight() > $this->max_height) {
            //     $errors[$original_name] = ['code' => 1204, 'message' => 'height is over limit'];
            //     continue;
            // }

            $hash = md5_file($temp_name);
            $filename = $app->config->file->path . $this->parseHash($hash) . self::fileExtension($mime_type);
            $app->logger->debug($filename);

            if (!file_exists($filename)) {
                $dir = dirname($filename);
                if (!file_exists($dir)) {
                    mkdir($dir, 0755, true);
                }
                if (!$file->moveTo($filename)) {
                    $errors[$original_name] = ['code' => 1000, 'message' => 'upload failed'];
                    continue;
                }
            }
            
            $rows[] = [
                'file_name' => $this->parseHash($hash) . self::fileExtension($mime_type),
            ];

        }

        $app->logger->debug("error: " . json_encode($errors));
        return ['uploaded' => $rows, 'errors' => $errors];
    }
}
