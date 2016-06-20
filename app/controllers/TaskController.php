<?php namespace BeriDelay\Controllers;

use Carbon\Carbon;
use BeriDelay\Models\Task;
use BeriDelay\Models\Task2Target;
use System\Exceptions\BaseException;
use BeriDelay\Exceptions\ApiException;
use System\Exceptions\ValidationException;

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
            $data = $this->request->getPost();

            //если фильтр заполнен
            if (isset($data['id']) || isset($data['ids']) || isset($data['user_id'])) {
                if (!$this->token->user->is_admin) throw new ApiException(ApiException::PARAM_ACCESS);
                return Task::get($data);
            } else {
                $filter = ['user_id' => $this->token->user_id];
                if (isset($data['status'])) $filter['status'] = $data['status'];
                return Task::get($filter);
            }
            
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }

    public function createAction()
    {
        try {
            $task = new Task();
            $task->user_id = $this->token->user_id;
            $task->finished_at = Carbon::now()->addWeek();
            $task->save();

            $targets = $task->generate($this->token->user->salary);

            return [
                'response' => [
                    'id' => $task->id,
                    'targets' => $targets
                ]
            ];
        } catch (BaseException $e) {
            return $this->errorException($e);
        }

    }
    
    public function editAction()
    {
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
            
        } catch (BaseException $e) {
            return $this->errorException($e);
        }
    }
    
    public function changeAction()
    {
        try {
            
            $data = $this->request->getPost();
            $rules = [
                'task_id' => 'required|integer',
                'target_id' => 'required|integer'
            ];

            //валидация данных
            if (!Task::validateData($rules, $data)) throw new ValidationException(Task::$validationMessages);

            //если не существует задания или связки задание-цель
            if (!$task = Task::findFirstById($data['task_id'])) throw new ApiException(ApiException::OBJECT_NOT_FOUND);
            $relation = Task2Target::findFirst([
                'conditions' => 'target_id = :target_id: and task_id = :task_id:',
                'bind' => ['target_id' => $data['target_id'], 'task_id' => $data['task_id']]
            ]);
            if (!$relation) throw new ApiException(ApiException::OBJECT_NOT_FOUND);

            $relation->delete();
            
            $targets = $task->generate($this->token->user->salary, 1);

            return [
                'response' => $targets[0]
            ];
            
        } catch (BaseException $e) { 
            return $this->errorException($e); 
        }
    }
    
}