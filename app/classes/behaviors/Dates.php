<?php namespace System\Behaviors;

use Carbon\Carbon;
use Phalcon\Mvc\Model\Behavior;
use Phalcon\Mvc\Model\BehaviorInterface;
use Phalcon\Mvc\ModelInterface;

/**
 * Поведение "Даты"
 * Автоматическая конвертация дат
 * @package System\Behaviors
 */
class Dates extends Behavior implements BehaviorInterface
{
    public function notify($type, ModelInterface $model)
    {

        /** @var \System\Models\Model $model */
        switch ($type) {
            case 'beforeSave':
                foreach ($model::$dates as $field) {
                    if ($model->$field instanceof Carbon)
                        $model->$field = $model->$field->toDateTimeString();
                }
            break;

            case 'afterFetch':
            case 'afterSave':
                foreach ($model::$dates as $field) {
                    if (!$model->$field instanceof Carbon) $model->$field = new Carbon($model->$field);
                }
            break;
        }
        return true;
    }
}