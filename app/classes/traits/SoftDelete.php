<?php namespace System\Traits;

use Carbon\Carbon;

/**
 * Трайт "Нежесткое удаление"
 * При удалении модели, она помечается как удаленная. При получении модели помеченные удаленные записи не возвращаются.
 * @package System\Traits
 */
trait SoftDelete
{
    protected static function getConditions($parameters = null)
    {
        if ($parameters === null) return 'deleted_at is null';

        if (is_string($parameters)) return '(' . $parameters . ') and deleted_at is null';

        if (is_array($parameters)) {
            if (isset($parameters['conditions']))
                $conditions = $parameters['conditions'];
            else
                $conditions = array_shift($parameters);
            
            if(is_string($conditions)){
                $parameters['conditions'] = '(' . $conditions . ') and deleted_at is null';
            }
            
            return $parameters;
        }

        return $parameters;
    }
    
    protected function beforeDelete()
    {
        $this->deleted_at = Carbon::now()->toDateTimeString();
        $this->save();
        return false;
    }
}