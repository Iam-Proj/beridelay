<?php namespace System\Models;

use Phalcon\Http\Request\File as UploadedFile;
use System\Helpers\Filesystem as FileHelper;
use Exception;
use System\Helpers\Resizer;
use System\Traits\SoftDelete;
use Symfony\Component\HttpFoundation\File\File as FileObj;

/**
 * Базовая модель для работы с файлами
 *
 * @package System\Models
 * @author Alexey Bobkov, Samuel Georges
 * @author Pavel Babushkin
 */
class File extends Model
{

    use SoftDelete;

    public $table = 'file';

    /**
     * @var string Наименование класса
     */
    public $attachment_type;
    /**
     * @var integer ID модели
     */
    public $attachment_id;
    /**
     * @var string Поле модели
     */
    public $attachment_field;

    /**
     * @var string Название файла
     */
    public $name;

    /**
     * @var string Описание файла
     */
    public $description;

    /**
     * @var string Реальное имя файла
     */
    public $filename;

    /**
     * @var string MIME-тип
     */
    public $mime;

    /**
     * @var integer Разбер в байтах
     */
    public $size;

    /**
     * @var array Дополнительная информация о файле
     */
    public $info;

    /**
     * @var array Опубликован или нет файл
     */
    public $is_public;

    public $json = ['info'];

    /**
     * @var array Расширения файлов
     */
    public static $imageExtensions = ['jpg', 'jpeg', 'png', 'gif'];

    /**
     * @var mixed Локальное имя файла или экземпляр класса \Phalcon\Http\Request\File.
     */
    public $data = null;

    /**
     * @var array Mime-типы
     */
    protected $autoMimeTypes = [
        'docx' => 'application/msword',
        'xlsx' => 'application/excel',
        'gif'  => 'image/gif',
        'png'  => 'image/png',
        'jpg'  => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'pdf'  => 'application/pdf'
    ];

    /**
     * Создает объект файла из загруженного файла
     * @param \Phalcon\Http\Request\File $uploadedFile
     * @return $this
     */
    public function fromPost($uploadedFile)
    {
        if ($uploadedFile === null) return $this;

        $this->name = $uploadedFile->getName();
        $this->size = $uploadedFile->getSize();
        $this->mime = $uploadedFile->getRealType();
        $this->filename = $this->getDiskName();
        $this->info = '{}';

        $uploadedFile->moveTo($this->getDiskPath());

        return $this;
    }

    /**
     * Создает объект файла из локального файла
     * @param string $filePath Путь к файлу
     * @return $this
     */
    public function fromFile($filePath)
    {
        if ($filePath === null) return $this;

        $file = new FileObj($filePath);
        $this->name = $file->getFilename();
        $this->size = $file->getSize();
        $this->mime = $file->getMimeType();
        $this->filename = $this->getDiskName();

        $this->putFile($file->getRealPath(), $this->filename);

        return $this;
    }

    /**
     * Outputs the raw file contents.
     * @param string $disposition
     * @return void
     */
    public function output($disposition = 'inline')
    {
        header("Content-type: ".$this->getContentType());
        header('Content-Disposition: '.$disposition.'; filename="'.$this->name.'"');
        header('Cache-Control: private');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Accept-Ranges: bytes');
        header('Content-Length: '.$this->size);
        echo $this->getContents();
    }

    /**
     * Outputs the raw thumbfile contents.
     * @param integer $width
     * @param integer $height
     * @param array $options
     * @return void
     */
    public function outputThumb($width, $height, $options = [])
    {
        $disposition = array_get($options, 'disposition', 'inline');
        $this->thumb($width, $height, $options);
        $options = $this->getDefaultThumbOptions($options);
        $thumbFile = $this->getThumbFilename($width, $height, $options);
        $contents = $this->getContents($thumbFile);

        header("Content-type: ".$this->getContentType());
        header('Content-Disposition: '.$disposition.'; filename="'.basename($thumbFile).'"');
        header('Cache-Control: private');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Accept-Ranges: bytes');
        header('Content-Length: '.mb_strlen($contents, '8bit'));
        echo $contents;
    }

    /**
     * Returns the file name without path
     */
    public function getFilename()
    {
        return $this->name;
    }

    /**
     * Returns the file extension.
     */
    public function getExtension()
    {
        return FileHelper::instance()->extension($this->name);
    }

    /**
     * Returns the file content type.
     */
    protected function getContentType()
    {
        if ($this->mime !== null) {
            return $this->mime;
        }

        $ext = $this->getExtension();
        if (isset($this->autoMimeTypes[$ext])) {
            return $this->mime = $this->autoMimeTypes[$ext];
        }

        return null;
    }

    /**
     * Get file contents from storage device.
     * @param string $fileName
     * @return string
     */
    public function getContents($fileName = null)
    {
        if (!$fileName) $fileName = $this->filename;

        return file_get_contents($this->getUploadDirectory() . $this->getFileDirectory($fileName) . $fileName);
    }

    /**
     * Возвращает путь до файла относительно URL
     * @return string
     */
    public function getPath()
    {
        return $this->getUploadPath() . $this->getFileDirectory() . $this->filename;
    }

    /**
     * Возвращает абсолютный путь до файла относительно сервера
     * @return string
     */
    public function getDiskPath()
    {

        $destinationPath = $this->getUploadDirectory() . $this->getFileDirectory();
        if (
            !FileHelper::instance()->isDirectory($destinationPath) &&
            !FileHelper::instance()->makeDirectory($destinationPath, 0777, true, true) &&
            !FileHelper::instance()->isDirectory($destinationPath)
        ) {
            trigger_error(error_get_last(), E_USER_WARNING);
        }
        return $destinationPath . $this->filename;
    }

    /**
     * Возвращает размер файла как строку
     * @return string
     */
    public function sizeToString()
    {
        return FileHelper::instance()->sizeToString($this->filename);
    }

    /**
     * Перед сохранением модели проверям, какого типа у нас файл, и сохраняем его
     */
    public function beforeValidation()
    {
        /*
         * Process the data property
         */
        if ($this->data !== null) {
            if ($this->data instanceof UploadedFile) {
                $this->fromPost($this->data);
            }
            else {
                $this->fromFile($this->data);
            }

            $this->data = null;
        }
    }

    /**
     * После удаления модели очищаем все файлы на диске
     */
    public function afterDelete()
    {
        try {
            $this->deleteThumbs();
            $this->deleteFile();
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
     * Генерирует дисковое имя файла
     * @return string
     */
    protected function getDiskName()
    {
        if ($this->filename !== null)
            return $this->filename;

        $ext = strtolower($this->getExtension());
        $name = str_replace('.', '', uniqid(null, true));

        return $this->filename = $ext !== null ? $name.'.'.$ext : $name;
    }

    /**
     * Возвращает папку загрузок
     * @return string
     */
    protected function getUploadDirectory()
    {
        return $this->getDI()->get('config')->application->uploadDir;
    }

    protected function getUploadPath()
    {
        return $this->getDI()->get('config')->application->uploadUri;
    }

    /**
     * Возвращает путь к подпапкам файла
     * @param string $filename
     * @return string
     */
    protected function getFileDirectory($filename = null)
    {
        if ($filename === null) $filename = $this->filename;
        $dir1 = substr($filename, 0, 2);
        $dir2 = substr($filename, 2, 2);
        return $dir1 . '/' . $dir2 . '/';
    }

    /**
     * Удаляет файл с диска.
     * @param string $fileName
     */
    protected function deleteFile($fileName = null)
    {
        if (!$fileName) $fileName = $this->filename;

        $directory = $this->getUploadDirectory() . $this->getFileDirectory($fileName);
        $filePath = $directory . $fileName;

        if (file_exists($filePath)) {
            FileHelper::instance()->delete($filePath);
        }

        $this->deleteEmptyDirectory($directory);
    }

    /**
     * Проверяет, является ли диреектория пустой, и удаляет ее, если это так
     * @param string $dir
     */
    protected function deleteEmptyDirectory($dir = null)
    {
        if (!$this->isDirectoryEmpty($dir))
            return;

        FileHelper::instance()->deleteDirectory($dir);

        $dir = dirname($dir);
        if (!$this->isDirectoryEmpty($dir))
            return;

        FileHelper::instance()->deleteDirectory($dir);

        $dir = dirname($dir);
        if (!$this->isDirectoryEmpty($dir))
            return;

        FileHelper::instance()->deleteDirectory($dir);
    }

    /**
     * Проверяет, является ли директория пустой
     * @param string $dir
     * @return boolean
     */
    protected function isDirectoryEmpty($dir)
    {
        if (!$dir) return null;

        return count(FileHelper::instance()->allFiles($dir)) === 0;
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
            return [
                'jpg',
                'jpeg',
                'bmp',
                'png',
                'gif',
                'svg',
                'js',
                'map',
                'ico',
                'css',
                'less',
                'scss',
                'pdf',
                'swf',
                'txt',
                'xml',
                'xls',
                'eot',
                'woff',
                'woff2',
                'ttf',
                'flv',
                'wmv',
                'mp3',
                'ogg',
                'wav',
                'avi',
                'mov',
                'mp4',
                'mpeg',
                'webm',
                'mkv'
            ];
        }
    }

    /**
     * Сохраняет файл
     * @param string $sourcePath Абсолютный путь к исходному файлу
     * @param string $destinationFileName Новое имя файла
     * @return boolean;
     */
    protected function putFile($sourcePath, $destinationFileName = null)
    {
        if (!$destinationFileName) $destinationFileName = $this->filename;

        $destinationPath = $this->getUploadDirectory() . $this->getFileDirectory($this->filename);
        if (
            !FileHelper::instance()->isDirectory($destinationPath) &&
            !FileHelper::instance()->makeDirectory($destinationPath, 0777, true, true) &&
            !FileHelper::instance()->isDirectory($destinationPath)
        ) {
            trigger_error(error_get_last(), E_USER_WARNING);
        }

        return FileHelper::instance()->copy($sourcePath, $destinationPath . $destinationFileName);
    }
}