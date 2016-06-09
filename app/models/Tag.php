<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;

/**
 * Model Tag
 * @package BeriDelay\Models
 */
class Tag extends Model
{
    use SoftDelete;
    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $color;

    /**
     *
     * @var array 
     */
    public static $fields = [
        'id',
        'name',
        'color',
    ];
    
    public $validation = [
        'name' => 'required',
        //'color' => '',
    ];
    
    public $attachOne = [
        'file' => ['System\Models\File']
    ];
    
    public static function checkIssetTags($tags){
        $idsTags = [];
        foreach ($tags as $tagName) {
            if ($tagItem = self::findFirstByName($tagName)) {
                $idsTags[$tagItem->id] = $tagItem->id;
            } else {
                $tgNew = new self();
                $tgNew->name = $tagName;
                $tgNew->color = '';
                $tgNew->save();
                $idsTags[$tgNew->id] = $tgNew->id;
            }
        }
        return $idsTags;
    }
    
}