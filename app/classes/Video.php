<?php namespace System\Models;

class Video extends File
{
    /**
     * @var array Расширения файлов
     */
    public static $videoExtensions = ['avi', 'mov', '3gp', 'mp4', 'mpg'];

    /**
     * Проверяет по расширению, является ли файл видеозаписью
     */
    public function isVideo()
    {
        return in_array(strtolower($this->getExtension()), static::$videoExtensions);
    }

}