<?php namespace System\Models;

use Exception;
use System\Helpers\Resizer;
use System\Helpers\Filesystem as FileHelper;

class Photo extends File
{
    /**
     * @var array Расширения файлов
     */
    public static $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    public function afterDelete()
    {
        parent::afterDelete();
        try {
            $this->deleteThumbs();
        }
        catch (Exception $ex) {}
    }

    /**
     * Проверяет по расширению, является ли файл изображением
     */
    public function isImage()
    {
        return in_array(strtolower($this->getExtension()), static::$imageExtensions);
    }

    /**
     * Генерирует миниатюру и возвращает путь к ней
     * @param integer $width
     * @param integer $height
     * @param array $options
     * @return string
     */
    public function thumb($width, $height, $options = [])
    {
        if (!$this->isImage()) {
            return $this->getPath();
        }

        $width = (int) $width;
        $height = (int) $height;

        $options = $this->getDefaultThumbOptions($options);

        $thumbFile = $this->getThumbFilename($width, $height, $options);
        $thumbPath = $this->getUploadDirectory() . $this->getFileDirectory() . $thumbFile;
        $thumbPublic = $this->getUploadPath() . $this->getFileDirectory() . $thumbFile;

        if (!file_exists($thumbPath)) {
            $this->makeThumb($thumbPath, $width, $height, $options);
        }

        return $thumbPublic;
    }

    /**
     * Генерирует имя для миниатюры
     * @param integer $width
     * @param integer $height
     * @param array $options
     * @return string
     */
    protected function getThumbFilename($width, $height, $options)
    {
        return 'thumb_' . $this->id . '_' . $width . 'x' . $height . '_' . $options['offset'][0] . '_' . $options['offset'][1] . '_' . $options['mode'] . '.' . $options['extension'];
    }

    /**
     * Возвращает настройки миниатюры по умолчанию
     * @param array $overrideOptions
     * @return array
     */
    protected function getDefaultThumbOptions($overrideOptions = [])
    {
        $defaultOptions = [
            'mode'      => 'auto',
            'offset'    => [0, 0],
            'quality'   => 95,
            'extension' => 'auto',
        ];

        if (!is_array($overrideOptions)) {
            $overrideOptions = ['mode' => $overrideOptions];
        }

        $options = array_merge($defaultOptions, $overrideOptions);

        $options['mode'] = strtolower($options['mode']);

        if ((strtolower($options['extension'])) == 'auto') {
            $options['extension'] = strtolower($this->getExtension());
        }

        return $options;
    }

    /**
     * Генерирует миниатюру на основе оригинального файла
     * @param string $thumbPath Путь к миниатюре
     * @param integer $width
     * @param integer $height
     * @param array $options
     */
    protected function makeThumb($thumbPath, $width, $height, $options)
    {
        $resizer = Resizer::open($this->getDiskPath());
        $resizer->resize($width, $height, $options['mode'], $options['offset']);
        $resizer->save($thumbPath, $options['quality']);

        FileHelper::instance()->chmod($thumbPath);
    }

    /*
     * Удаляет все миниатюры для данного файла
     */
    protected function deleteThumbs()
    {
        $pattern = 'thumb_'.$this->id.'_';

        $directory = $this->getUploadDirectory() . $this->getFileDirectory();
        $allFiles = FileHelper::instance()->allFiles($directory);

        $collection = [];
        foreach ($allFiles as $file) {
            if (starts_with(basename($file), $pattern)) {
                $collection[] = $file;
            }
        }

        if (!empty($collection)) {
            FileHelper::instance()->delete($collection);
        }
    }
    /**
     * Возвращает список поддерживаемых расширений
     * @param boolean $isImage
     * @return array
     */
    public static function getDefaultFileTypes($isImage = false)
    {
        if ($isImage) {
            return [
                'jpg',
                'jpeg',
                'bmp',
                'png',
                'gif',
                'svg'
            ];
        }
        else {
            return parent::getDefaultFileTypes();
        }
    }

}