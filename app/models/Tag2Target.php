<?php namespace BeriDelay\Models;

use System\Models\Model;
use BeriDelay\Models\Tag;
use BeriDelay\Models\Target;

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
    
    /**
     * 
     * @param int $targetId - ID цели
     */
    public static function removeOldDependancyTags($targetId){
        if($deps = self::find('target_id = '.$targetId)){
            if(count($deps)){
                $deps->delete();
            }
        }
    }
    
    /**
     * 
     * @param int $targetId - ID цели
     */
    public static function getAllTagsByTarget($targetId){
        $deps = self::find('target_id = '.$targetId);
        $ids = [];
        $tagsItems = [];
        if(count($deps)){ foreach($deps as $item){ $ids[$item->tag_id] = $item->tag_id; } }
        if($ids){
            $tags = Tag::find([
                'conditions' => 'id IN ('.implode(',',$ids).')',
                'columns' => implode(',',Tag::$fields),
            ]);
            if(count($tags)){ foreach($tags as $item){ $tagsItems[] = $item->toArray(); } }
        }
        return $tagsItems;
    }
    
}