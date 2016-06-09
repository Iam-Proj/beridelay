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
            if(!$target->category_id = $this->request->getPost('category_id')){
                throw new ApiException(ApiException::PARAM_FORMAT);
            }
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