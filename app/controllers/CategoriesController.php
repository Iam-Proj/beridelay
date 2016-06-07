<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;
use BeriDelay\Models\Category;
use System\Helpers\Logic;

class CategoriesController extends ApiBaseController {
    
    public function getAction(){
        $token = $this->hasPrivate();
        if($id = $this->request->getPost('id')){
            $cats = Category::findById($id)->toArray();
        }
        if($ids = $this->request->getPost('ids')){
            foreach($ids as $k => $v){ if(!is_numeric($v)){ unset($ids[$k]); } }
            $ids = implode(',',$ids);
            $cats = Logic::recursionGet(Category::find('id IN ('.$ids.')'),'category_id');
        }
        if(!$id && !$ids){
            $cats = Logic::recursionGet(Category::find(),'category_id');
        }
        return [
            'token_access'  => $token->value,
            'categories'    => $cats,
        ];
    }
    
    /**
     * 
     * @return token_access
     */
    public function createAction(){
        $token = $this->hasPrivate();
        
        $name   = $this->request->getPost('name');
        $parent = $this->request->getPost('category_id')? (int)$this->request->getPost('category_id') : 0 ;
        $hide   = $this->request->getPost('is_hide')? $this->request->getPost('is_hide') : 0 ;
 
        $cat = new Category();
        $cat->category_id = $parent;
        $cat->name        = $name;
        $cat->is_hide     = $hide;
        $cat->save();
        
        return [
            'token_access'  => $token->value,
            'category'      => $cat->toArray(['name','category_id','is_hide']),
        ];
    }
    
    public function editAction(){
        $this->hasPrivate();
        
        
        
    }
    
    
    
}