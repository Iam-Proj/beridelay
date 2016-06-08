<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;
use BeriDelay\Models\Category;
use System\Helpers\Logic;
use System\Exceptions\BaseException;
use BeriDelay\Exceptions\ApiException;

class CategoriesController extends ApiBaseController {
    
    protected $token;
    
    public function initialize(){
        try {
            $this->token = $this->hasPrivate();
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }
    
    public function getAction(){
        if($id = $this->request->getPost('id')){
            $cats = Category::findById($id)->toArray();
        }
        
        if($ids = $this->request->getPost('ids')){
            foreach($ids as $k => $v){ 
                try{ if(!is_numeric($v)){ throw new ApiException(ApiException::PARAM_FORMAT); }  }
                catch (BaseException $e) { return $this->errorException($e); }
            }
            $ids = implode(',',$ids);
            $cats = Category::find('id IN ('.$ids.')');
        }
        
        if(!$id && !$ids){
            $cats = Logic::recursionGet(Category::find(),'category_id');
        }
        
        return [ 'categories' => $cats, ];
        
    }
    
    public function createAction(){
        $name   = $this->request->getPost('name');
        $parent = $this->request->getPost('category_id')? (int)$this->request->getPost('category_id') : 0 ;
        $hide   = $this->request->getPost('is_hide')? $this->request->getPost('is_hide') : 0 ;
 
        $cat = new Category();
        $cat->category_id = $parent;
        $cat->name        = $name;
        $cat->is_hide     = $hide;
        $cat->save();
        
        return [ 'category' => $cat->toArray(['name','category_id','is_hide']), ];
    }
    
    public function editAction(){
        $id = $this->request->getPost('id');
        
        // если объект не найден
        try{ if(!$cat = Category::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }  }
        catch (BaseException $e) { return $this->errorException($e); }
        
        // если id и id родителя совпадают
        try{ if($id == $this->request->getPost('category_id')){
            throw new ApiException(ApiException::PARAM_FORMAT); }  
        } catch (BaseException $e) { return $this->errorException($e); }
        
        $cat->name = $this->request->getPost('name');
        $cat->category_id = $this->request->getPost('category_id')? $this->request->getPost('category_id') : 0;
        $cat->is_hide = $this->request->getPost('is_hide')? 1 : 0;
        
        $cat->save();
        
        return [ 'category' => $cat->toArray(['name','category_id','is_hide']), ];
    }
    
}