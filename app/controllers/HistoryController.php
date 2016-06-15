<?php namespace BeriDelay\Controllers;

use Carbon\Carbon;
use System\Models\File;
use System\Helpers\Logic;
use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Task;
use BeriDelay\Models\Task2Target;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;
use BeriDelay\Models\Category;
use BeriDelay\Models\Target;
use BeriDelay\Models\History;
use System\Exceptions\BaseException;
use BeriDelay\Exceptions\ApiException;
use BeriDelay\Exceptions\TaskException;

class HistoryController extends ApiBaseController {
    
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
                if(!$hist = History::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
                return [ 'history' => [$hist->toArray()] ];
            }
            if($ids = $this->request->getPost('ids')){ 
                foreach($ids as $id){ if(!is_numeric($id)){ throw new ApiException(ApiException::PARAM_FORMAT); } }
                $hists = History::find('id IN ('.implode(',',$ids).')');
                return [ 'history' => [$hists->toArray()] ];
            }
            
            $hist = History::query();
            
            // По описанию
            if($description = $this->request->getPost('description')){
                $hist->andWhere('description = "'.$description.'"');
            }
            
            // По ID пользователя
            if($this->token->user->is_admin){
                if($user_id = $this->request->getPost('user_id')){
                    $hist->andWhere('user_id = "'.$user_id.'"');
                }
            }
            
            // По ID цели
            if($target_id = $this->request->getPost('target_id')){
                $hist->andWhere('target_id = "'.$target_id.'"');
            }
            
            // По ID задания
            if($task_id = $this->request->getPost('task_id')){
                $hist->andWhere('task_id = "'.$task_id.'"');
            }
            
            $response = [];
            $histItems = $hist->execute();
            
            if(count($histItems)){
                
                // Получение с контентом
                $contents = [];
                if($this->request->getPost('with_content')){
                    foreach($histItems as $itmHist){
                        $arrItem = $itmHist->toArray();
                        
                        echo '<pre>';
                        print_r($arrItem);
                        echo '</pre>';
                        
                    }
                }
            }
            
            
            
            
            
            
            
            
            
            
            
            
            /*
            
            content — параметры работы с коллекицями, применяются к вложенному контенту
            
            */
            
            
            
        
        } catch (BaseException $e) { return $this->errorException($e); }
    }
    
    public function editAction(){
        try{
            if(!$id = $this->request->getPost('id')){ throw new ApiException(ApiException::PARAM_FORMAT); }
            if(!$hist = History::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
            
            // Описание
            if(!$description = $this->request->getPost('description')){
                $hist->description = $description;
            } 
            
            // Файл
            if($this->request->getUploadedFiles()){
                foreach ($this->request->getUploadedFiles() as $upload) {
                    $file = new File();
                    $file->data = $upload;
                    $hist->attachFile($file);
                }
            } 
            
            if($hist->save()){ return [ 'success' => true ]; } 
            else { return [ 'success' => false ]; }
            
        } catch (BaseException $e) { return $this->errorException($e); }
    }
    
}
