<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;
use BeriDelay\Models\Category;
use BeriDelay\Models\Target;
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
        
        // По ID категории
        if($id = $this->request->getPost('id')){
            
            try{ if(!$cat = Category::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }  }
            catch (BaseException $e) { return $this->errorException($e); }
            
            $cat = $cat->toArray();
            $cat['count_childs'] = Category::countChilds($cat['id']);
            $cat['count_targets'] = Target::countTargetsByCategory($cat['id']);
            
            return [ 'categories' => $cat ];
        }
        
        // По массиву ID категорий
        if($ids = $this->request->getPost('ids')){
            foreach($ids as $item){ 
                try{ if(!is_numeric($item)){ throw new ApiException(ApiException::PARAM_FORMAT); }  }
                catch (BaseException $e) { return $this->errorException($e); }
            }
            $ids = implode(',',$ids);
            $cats = Category::find('id IN ('.$ids.')')->toArray();
            if($cats){
                foreach($cats as &$item){
                    $item['count_childs'] = Category::countChilds($item['id']);
                    $item['count_targets'] = Target::countTargetsByCategory($item['id']);
                }
            }
            return [ 'categories' => $cats ];
        }
        
        // Все корневые категории
        $dataPost = $this->request->getPost();
        unset($dataPost['token_access']);
        if(!$dataPost){
            $catsItems = Category::find('category_id = 0')->toArray();
            if(count($catsItems)){
                foreach($catsItems as &$item){
                    $item['count_childs'] = Category::countChilds($item['id']);
                    $item['count_targets'] = Target::countTargetsByCategory($item['id']);
                }
            }
            return [ 'categories' => $catsItems ];
        }
        
        $cats = Category::query();
        
        // По имени
        if($name = $this->request->getPost('name')){
            $cats->andWhere('name LIKE "%'.$name.'%"');
        }
        
        // По родительской категории
        if($catId = $this->request->getPost('category_id')){
            $cats->andWhere('category_id = '.$catId);
        }
        
        $response = [];
        if($cats->getWhere()){
            $cats = $cats->execute();
            if(count($cats)){
                $response = $cats->toArray();
                foreach($response as &$item){
                    $item['count_childs'] = Category::countChilds($item['id']);
                    $item['count_targets'] = Target::countTargetsByCategory($item['id']);
                }
            }
        }
        
        return [ 'categories' => $response, ];
        
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