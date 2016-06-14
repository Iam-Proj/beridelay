<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;
use BeriDelay\Models\Category;
use BeriDelay\Models\Tag;
use BeriDelay\Models\Target;
use BeriDelay\Models\Tag2Target;
use System\Helpers\Logic;
use System\Exceptions\BaseException;
use BeriDelay\Exceptions\ApiException;

class TagsController extends ApiBaseController {
    
    protected $token;
    
    public function initialize(){
        try {
            $this->token = $this->hasPrivate();
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }
    
    public function getAction(){
        try{
            
            if($id = $this->request->getPost('id')){
                if(!$tag = Tag::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
                return [ 'tags' => [$tag->toArray()] ];
            }
            
            if($ids = $this->request->getPost('ids')){
                foreach($ids as $item){ if(!is_numeric($item)){ throw new ApiException(ApiException::PARAM_FORMAT); } }
                $tags = Tag::find('id IN ('.implode(',',$ids).')');
                return [ 'tags' => $tags->toArray() ];
            }
            
            $tagItem = Tag::query();
            $response = [];
            
            if($name = $this->request->getPost('name')){
                $tagItem->andWhere('name = "'.$name.'"');
            }
            
            if($count = $this->request->getPost('count')){
                $offset = is_numeric($this->request->getPost('offset'))? $this->request->getPost('offset') : 0 ;
                $count  = is_numeric($count)? $count : 0 ;
                $tagItem->limit($count,$offset);
            } else {
                $tagItem->limit(Tag::$countDefault,0);
            }
            
            
            $tagItem = $tagItem->execute();
            if(count($tagItem)){
                $response = $tagItem->toArray();
            }
            
            return [
                'tags' => $response,
                'count_tags' => Tag::count(),
            ];
            
        } catch (BaseException $e) { $this->errorException($e); }
    }
    
    public function createAction(){
        
        $tag;
        
        $name = $this->request->getPost('name');
        $color = $this->request->getPost('color');
        
        try{
            if(!$name || !$color){ throw new ApiException(ApiException::PARAM_FORMAT); }
            if(strlen($color) != 6){ throw new ApiException(ApiException::PARAM_FORMAT); }
        } catch (BaseException $e) { $this->errorException($e); }
        
        if(!$tag = Tag::findFirstByName($name)){
            $tag = new Tag();
            $tag->name = $name;
            $tag->color = $color;
            $tag->save();
        }
        
        return [ 'tags' => [$tag->toArray()] ];
        
    }
    
    public function editAction(){

        $tag;
        
        try {
            
            if(!$id = $this->request->getPost('id')){ throw new ApiException(ApiException::PARAM_FORMAT); }
            if(!$tag = Tag::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
            
            if($name = $this->request->getPost('name')){
                if($tag = Tag::findFirstByName($name)){ throw new ApiException(ApiException::PARAM_FORMAT); }
                $tag->name = $name;
            } else { throw new ApiException(ApiException::PARAM_FORMAT); }
            
            if($color = $this->request->getPost('color')){
                if(strlen($color) != 6){ throw new ApiException(ApiException::PARAM_FORMAT); }
                $tag->color = $color;
            } else { throw new ApiException(ApiException::PARAM_FORMAT); }
            
            $tag->save();
            
            return [ 'tags' => [$tag->toArray()] ];
            
        } catch (BaseException $e) { $this->errorException($e); }
    }
    
    public function deleteAction(){
        try {
            if($id = $this->request->getPost('id')){
                if(!$tag = Tag::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
                Tag2Target::findFirst('tag_id = '.$id)->delete();
            }
            if($ids = $this->request->getPost('ids')) {
                foreach($ids as $id){ if(!is_numeric($id)){ throw new ApiException(ApiException::PARAM_FORMAT); } }
                $tags = Tag::find('id IN ('.implode(',',$ids).')');
                if(count($tags)){
                    Tag2Target::find('tag_id IN ('.implode(',',$ids).')')->delete();
                } else { throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
                $status = true;
            }
            
            return $this->delete('BeriDelay\Models\Tag');
            
        } catch (BaseException $e) { $this->errorException($e); }
    }
    
}