<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Token;
use BeriDelay\Models\Target;
use BeriDelay\Models\Tag2Target;
use BeriDelay\Models\Tag;
use BeriDelay\Models\Session;
use BeriDelay\Models\Category;
use System\Helpers\Logic;
use System\Exceptions\BaseException;
use BeriDelay\Exceptions\ApiException;

class TargetsController extends ApiBaseController {
    
    protected $token;
    
    public function initialize(){
        try {
            $this->token = $this->hasPrivate();
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }
    
    public function getAction(){
        
        
        
        
        
    }
    
    public function editAction(){
        try{
            if(!$id = $this->request->getPost('id')){ throw new ApiException(ApiException::PARAM_FORMAT); }
            if(!$target = Target::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
            
            if($name = $this->request->getPost('name')){
                $target->name = $name;
            }
            if($description = $this->request->getPost('description')){
                $target->description = $description;
            }
            if($category_id = $this->request->getPost('category_id')){
                if(!is_numeric($category_id)){ throw new ApiException(ApiException::PARAM_FORMAT); }
                $target->category_id = $category_id;
            }
            if($is_hide = $this->request->getPost('is_hide')){
                if(!is_numeric($is_hide)){ throw new ApiException(ApiException::PARAM_FORMAT); }
                $target->is_hide = $is_hide;
            }
            
            $arrTarget = $target->toArray();
            
            if($tags = $this->request->getPost('tags')){
                if(is_array($tags) && $tags){
                    Tag2Target::removeOldDependancyTags($target->id);
                    $idsTags = Tag::checkIssetTags($tags);
                    Tag2Target::createDependancy($idsTags,$target->id);
                    $arrTarget['tags'] = Tag::find([
                        'conditions' => 'id IN ('.implode(',',$idsTags).')',
                        'columns'    => implode(',',Tag::$fields),
                    ])->toArray();
                } else {  throw new ApiException(ApiException::PARAM_FORMAT); }
            }
            
            $target->save();
            return [ 'target' => $arrTarget ];
            
        } catch (Exception $ex) { return $this->errorException($e);  }
    }
    
    public function createAction(){
        $target = new Target();
        try{
            
            if(!$target->name = $this->request->getPost('name')){
                throw new ApiException(ApiException::PARAM_FORMAT);
            }
            
            if(!$target->description = $this->request->getPost('description')){
                throw new ApiException(ApiException::PARAM_FORMAT);
            }
            
            $category_id = $this->request->getPost('category_id');
            if($category_id === null){
                throw new ApiException(ApiException::PARAM_FORMAT);
            }
            $target->category_id = $category_id;
            
            $target->is_hide = $this->request->getPost('is_hide')? $this->request->getPost('is_hide') : 0 ;
            $target->save();
            
            // Проверка на наличие тегов (если нет, то создаются)
            $tags = $this->request->getPost('tags');
            $idsTags = [];
            if(is_array($tags) && $tags){ $idsTags = Tag::checkIssetTags($tags); }
            $arrTarget = $target->toArray(['id','category_id','name','description','is_hide']);
            if($idsTags){
                Tag2Target::createDependancy($idsTags,$arrTarget['id']);
                $idsTags = implode(',',$idsTags);
                $arrTarget['tags'] = Tag::find([
                        'conditions' => 'id IN ('.$idsTags.')',
                        'columns'    => implode(',',Tag::$fields),
                    ])->toArray();
            }

            return [ 'target' => $arrTarget ];
        } catch (BaseException $e) { return $this->errorException($e); }
    }
    
}