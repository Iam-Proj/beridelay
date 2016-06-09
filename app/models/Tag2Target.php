<?php namespace BeriDelay\Models;

use System\Models\Model;

/**
 * Модель связи тега с целью
 * @package BeriDelay\Models
 */
class Tag2Target extends Model
{
    public $table = 'tag2target';

    /**
     * @var int ID тега
     */
    public $tag_id;

    /**
     * @var int ID цели
     */
    public $target_id;
    
    /**
     * 
     * @param array $list - массив ID тегов
     * @param int $targetId - ID цели
     */
    public static function createDependancy($list,$targetId){
        foreach($list as $item){
            $T2T = new self();
            $T2T->tag_id = $item;
            $T2T->target_id = $targetId;
            $T2T->save();
        }
    }
    
}