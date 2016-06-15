<?php namespace BeriDelay\Controllers;

use BeriDelay\Models\User;
use BeriDelay\Models\Invite;
use BeriDelay\Models\Task;
use BeriDelay\Models\Task2Target;
use BeriDelay\Models\Token;
use BeriDelay\Models\Session;
use BeriDelay\Models\Category;
use BeriDelay\Models\Target;
use BeriDelay\Models\History;
use System\Helpers\Logic;
use System\Exceptions\BaseException;
use BeriDelay\Exceptions\ApiException;

class TaskController extends ApiBaseController {
    
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
        $idsTargets = []; // массив ID целей
        
        // Цели для отдельного пользователя
        $targets = Target::find([
            'conditions' => 'salary = '.$this->token->user->salary,
            'limit' => Target::$countTargetUser,
            'order' => 'start_count ASC',
        ]);
        
        foreach($targets as $item){
            $idsTargets[] = $item->id;
        }
        
        // Создание задания
        $task = new Task();
        $task->user_id = $this->token->user_id;
        $task->save();
        
        // Создание зависимостей относительно задания
        foreach($idsTargets as $item){
            $taskTarget = new Task2Target();
            $taskTarget->task_id = $task->id;
            $taskTarget->target_id = $item;
            $taskTarget->save();
        }
        
        return [ 'task' => [
            'targets' => Target::find('id IN ('.implode(',',$idsTargets).')'),
            'id' => $task->id,
        ]];
    }
    
    public function changeAction(){
        try {
            
            // если не передан ID
            if (!$id = $this->request->getPost('id')) {
                throw new ApiException(ApiException::PARAM_FORMAT);
            }
            
            // если не передан TASK_ID
            if (!$task_id = $this->request->getPost('task_id')) {
                throw new ApiException(ApiException::PARAM_FORMAT);
            }
            
            // если объекта TASK не существует
            if (!$task = Task::findFirstById($task_id)){
                throw new ApiException(ApiException::OBJECT_NOT_FOUND);
            }
            
            // если такой цели не существует
            if (!$target = Target::findFirstById($id)) {
                throw new ApiException(ApiException::OBJECT_NOT_FOUND);
            }
            
            // если нет такой зависимости
            if (!$usedTargets = $task->Targets->toArray()) { throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
            
            $usedTargetsIds = [];
            
            foreach($usedTargets as $item){
                $usedTargetsIds[$item['id']] = $item['id'];
            }
            
            // если в задании не существует требуемой цели
            if(!isset($usedTargetsIds[$id])){
                throw new ApiException(ApiException::PARAM_FORMAT);
            }
            
            // все уже использование ID цели
            $histsByTaskIds = [];
            $histsByTask = History::find([
                'conditions' => 'user_id = '.$this->token->user_id.' AND task_id = '.$task_id,
            ]);
            
            foreach($histsByTask as $item){
                $histsByTaskIds[$item->target_id] = $item->target_id;
            }
            
            // массив использованных ID
            $histsByTaskIds = array_merge($histsByTaskIds,$usedTargetsIds);
            
            
            echo '<pre>';
            print_r(count($histsByTaskIds));
            echo '</pre>';
            
            echo '<pre>';
            print_r(Target::count('salary = '.$this->token->user->salary));
            echo '</pre>';
            
            // если цели закончились
            if(count($histsByTaskIds) >= Target::count('salary = '.$this->token->user->salary)){
                if(!$histsByTask->delete()){
                    foreach($histsByTask->getMessages() as $item){
                        echo '<pre>';
                        print_r($item);
                        echo '</pre>';
                    }
                }
                
                echo '<pre>';
                print_r($histsByTaskIds);
                echo '</pre>';
                
                $histsByTaskIds = $usedTargetsIds;
            }
            
            exit;
            
            
            
            // если существует, удаление элемента из Task2Target
            unset($usedTargetsIds[$id]);
            Task2Target::find('target_id = '.$id.' AND task_id = '.$task_id)->delete();
                
            // отметка о использовании определенной цели
            $hist = new History();
            $hist->user_id = $this->token->user_id;
            $hist->task_id = $task_id;
            $hist->target_id = $id;
            $hist->save();
            
            $itemTarget = [];
                
            // если есть массив использованных ID целей
            if($histsByTaskIds){
                $itemTarget = Target::find([
                    'conditions' => 'id NOT IN ('.implode(',',$histsByTaskIds).') AND salary = '.$this->token->user->salary,
                    'limit' => 1,
                    'order' => 'start_count ASC',
                ])->toArray();
            } else {
                $itemTarget = Target::find([
                    'conditions' => 'salary = '.$this->token->user->salary,
                    'limit' => 1,
                    'order' => 'start_count ASC',
                ])->toArray();
            }
                
            $taskTarget = new Task2Target();
            $taskTarget->task_id = $task->id;
            $taskTarget->target_id = $itemTarget[0]['id'];
            $taskTarget->save();
                
            $target = $itemTarget;

            return [ 'target' => $target ];
            
        } catch (BaseException $e) { return $this->errorException($e); }
    }
    
    
}