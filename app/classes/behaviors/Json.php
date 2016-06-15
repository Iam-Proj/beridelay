<?php namespace System\Behaviors;

use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Поведение "JSON"
 * Автоматическая конвертация JSON в массик и обратно
 * @package System\Behaviors
 */
class Json extends Behavior implements BehaviorInterface
{
    public function notify($type, ModelInterface $model)
    {
        /** @var \System\Models\Model $model */
        switch ($type) {
            case 'beforeSave':
                foreach ($model::$json as $field) {
                    $data = json_encode($model->$field);
                    $model->$field = $data;
                }
            break;

            case 'afterFetch':
            case 'afterSave':
                foreach ($model::$json as $field) {
                    $data = json_decode($model->$field, true);
                    $model->$field = $data;
                }
            break;
        }
        return true;
    }
}