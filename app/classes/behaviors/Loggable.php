<?php namespace System\Behaviors;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use System\Models\Log;

class Loggable extends Behavior implements BehaviorInterface
{
    public function notify($type, \Phalcon\Mvc\ModelInterface $model)
    {
        /**
         * @var \System\Models\Model $model
         */
        switch ($type) {
            case 'beforeCreate':
                $model->setSnapshotData($model->toArray());
                break;
            case 'afterCreate':
                Log::log(get_class($model), 'create', $model->id);
                break;
            case 'afterUpdate':
                $old_data = $model->getSnapshotData();
                $fields = $model->getChangedFields();
                $old_value = [];
                $new_value = [];

                foreach($fields as $field) {
                    $old_value[$field] = $old_data[$field];
                    $new_value[$field] = $model->$field;
                }

                Log::log(get_class($model), 'update', $model->id, $old_value, $new_value);
                break;
            case 'afterDelete':
                Log::log(get_class($model), 'delete', $model->id);
                break;
        }
        return true;
    }

    public function missingMethod(\Phalcon\Mvc\ModelInterface $model, $method, $arguments = null)
    {
        if ($method == 'addLogEvent') {
            switch (count($arguments)) {
                case 0: Log::log(get_class($model), 'unknown', $model->id); break;
                case 1: Log::log(get_class($model), $arguments[0], $model->id); break;
                case 2: Log::log(get_class($model), $arguments[0], $model->id, $arguments[1]); break;
                case 3:
                default:
                    Log::log(get_class($model), $arguments[0], $model->id, $arguments[1], $arguments[2]);
                break;
            }
            return true;
        }
    }
}