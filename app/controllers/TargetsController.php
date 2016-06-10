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
        try{
            
            // По ID
            if($id = $this->request->getPost('id')){
                if(is_numeric($id)){
                    $target = Target::findFirstById($id);
                    if(!$target){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
                    $arrTarget = $target->toArray();
                    $arrTarget['tags'] = $target->Tags->toArray();
                    return [ 'target' => $arrTarget ];
                } else { throw new ApiException(ApiException::PARAM_FORMAT); }
            }
            
            // По массиву ID
            if($ids = $this->request->getPost('ids')){
                if(is_array($ids)){
                    foreach($ids as $item){ if(!is_numeric($item)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); } }
                    $targets = Target::find('id IN ('.implode(',',$ids).')');
                    if(!count($targets)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
                    $targetsItems = [];
                    foreach($targets as $item){
                        $tags = $item->Tags->toArray();
                        $item = $item->toArray();
                        $item['tags'] = $tags;
                        $targetsItems[] = $item;
                    }
                    return [ 'targets' => $targetsItems ];
                } else { throw new ApiException(ApiException::PARAM_FORMAT); }
            }
            
            // По всем остальным фильтрам 
            $target = Target::query();
            
            // По категории
            if($this->request->getPost('category_id')){
                $category_id = $this->request->getPost('category_id');
                if(!is_numeric($category_id)){ throw new ApiException(ApiException::PARAM_FORMAT); }
                $target->andWhere('category_id = '.$category_id);
            }
            
            // По тегам
            $tags = $this->request->getPost('tags');
            if(is_array($tags)){
                $idsTags = [];
                $ids = [];
                $targetsItems = [];
                foreach($tags as &$item){ $item = '"'.$item.'"'; }
                $tgs = Tag::find('name IN ('.implode(',',$tags).')');
                foreach($tgs as $itmTgs){ $idsTags[$itmTgs->id] = $itmTgs->id; }
                if($idsTags){
                    $targs = Tag2Target::find('tag_id in ('.implode(',',$idsTags).')');
                    foreach($targs as $itm){
                        $targetsItems[$itm->target_id] = $itm->target_id;
                    }
                }
                if($targetsItems){
                    $target->andWhere('id IN ('.implode(',',$targetsItems).')');
                }
            }
            
            // Остальные фильтры
            if($data = $this->request->getPost()){
                $target = Target::getFiltered($target,$data);
            }
            
            $response = [];
            
            if($target->getWhere()){
                $targets = $target->execute();
                if(count($targets)){
                    foreach($targets as $item){
                        $tags = $item->Tags->toarray();
                        $item = $item->toArray();
                        $item['tags'] = $tags;
                        $response[] = $item;
                    }
                }
            }
            
            return [ 'targets' => $response ];
            
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
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