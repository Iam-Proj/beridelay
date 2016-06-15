<?php namespace BeriDelay\Controllers;

use Carbon\Carbon;
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
use BeriDelay\Exceptions\TaskException;

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
        try {
            if($id = $this->request->getPost('id')){
                if(!$task = Task::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
                return ['task' => [$task->toArray()]];
            }
            if($ids = $this->request->getPost('ids')){
                foreach($ids as $id){ if(!is_numeric($id)){ throw new ApiException(ApiException::PARAM_FORMAT); } }
                $tasks = Task::find('id IN ('.implode(',',$ids).')');
                return ['task' => $tasks->toArray()];
            }
            
            $task = Task::query();
            
            if($this->token->user->is_admin){
                
                // по пользователю
                if($user_id = $this->request->getPost('user_id')){
                    if(!is_numeric($user_id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
                    $task->andWhere('user_id = '.$user_id);
                }
                
            }
            
            // по статусу
            $status = $this->request->getPost('status');
            if($status != null){
                $status = (int)$status;
                if(!is_numeric($status)){ throw new ApiException(ApiException::PARAM_FORMAT); }
                if(!isset(Task::$statuses[$status])){ throw new ApiException(ApiException::PARAM_FORMAT); }
                $task->andWhere('status = '.$status);
            }
            
            return ['task' => $task->execute()->toArray()];
            
        } catch (BaseException $e) { return $this->errorException($e); }
    }
    
    public function editAction(){
        try {
            
            if(!$id = $this->request->getPost('id')){ throw new ApiException(ApiException::PARAM_FORMAT); }
            if(!$task = Task::findFirstById($id)){ throw new ApiException(ApiException::OBJECT_NOT_FOUND); }
            
            // админ
            if($this->token->user->is_admin){

                // статус
                if($status = $this->request->getPost('status')){
                    if(!is_numeric($status)){ throw new ApiException(ApiException::PARAM_FORMAT); }
                    if(!isset(Task::$statuses[$status])){ throw new ApiException(ApiException::PARAM_FORMAT); }
                    $task->status = $status;
                }
                
                // Причина
                if($reason = $this->request->getPost('reason')){
                    $task->reason = $reason;
                }
                
                // Комментарий
                if($comment = $this->request->getPost('comment')){
                    $task->comment = $comment;
                }
                
                // Время блокировки
                if($finished_at = $this->request->getPost('finished_at')){
                    $task->finished_at = Carbon::createFromTimestamp($finished_at);
                }
                
                if($task->save()){ return ['success' => true]; }
                else { return ['success' => false]; }
                
            } else { 
                
                // статус
                $status = $this->request->getPost('status');
                if($status != null){
                    if(!is_numeric($status)){ throw new ApiException(ApiException::PARAM_FORMAT); }
                    if((int)$status < 0 || (int)$status > 2){ throw new ApiException(ApiException::PARAM_FORMAT); }
                    $task->status = $status;
                } else { throw new ApiException(ApiException::PARAM_FORMAT); }
                
                if($task->save()){ return ['success' => true]; }
                else { return ['success' => false]; }
                
            }
            
        } catch (BaseException $e) { return $this->errorException($e); }
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
            
            // если цели закончились
            if(count($histsByTaskIds) >= Target::count('salary = '.$this->token->user->salary)){
                foreach($histsByTask as $itmHist){
                    $itmHist->delete();
                }
                $histsByTaskIds = $usedTargetsIds;
            }

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
            
            // Создание новой зависимости
            $taskTarget = new Task2Target();
            $taskTarget->task_id = $task->id;
            $taskTarget->target_id = $itemTarget[0]['id'];
            $taskTarget->save();
                
            $target = $itemTarget;

            return [ 'target' => $target ];
            
        } catch (BaseException $e) { return $this->errorException($e); }
    }
    
    
}