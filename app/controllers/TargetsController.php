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
            if(is_array($tags) && $tags){
                foreach($tags as $tagName){
                    if($tagItem = Tag::findFirst('name = "'.$tagName.'"')){ 
                        $idsTags[$tagItem->id] = $tagItem->id;
                    } else {
                        $tgNew = new Tag();
                        $tgNew->name = $tagName;
                        $tgNew->color = '';
                        $tgNew->save();
                        $idsTags[$tgNew->id] = $tgNew->id;
                    }
                }
            }
            
            if($idsTags){
                foreach($idsTags as $item){
                    $T2T = new Tag2Target();
                    $T2T->tag_id = $item;
                    $T2T->target_id = $target->id;
                    $T2T->save();
                }
                $idsTags = implode(',',$idsTags);
                $target->tags = Tag::find('name IN ('.$idsTags.')')->toArray();
            }

            return [ 'target' => $target->toArray()];
        } catch (BaseException $e) { return $this->errorException($e); }
    }
    
}