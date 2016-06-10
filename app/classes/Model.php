<?php namespace System\Models;

use Phalcon\Mvc\Model as PhalconModel;
use Phalcon\Text;
use Carbon\Carbon;
use System\Traits\Validation;

/**
 * Базовый класс для всех моделей. Предоставляет более удобный интерфейс объявления связей между моделями.
 * Кроме того, реализаует связи типа ORM->ODM.
 * @package System\Models
 */
class Model extends PhalconModel
{
    use Validation;
    
    /**
     * @var integer ID модели
     */
    public $id;

    /**
     * @var Carbon Время создания
     */
    public $created_at;

    /**
     * @var Carbon Время последнего обновления
     */
    public $updated_at;

    /**
     * @var Carbon Время удаления
     */
    public $deleted_at;

    /**
     * @var string Если задано, то будет использована указанная таблица. Если нет -- имя таблицы генерируется из имени класса.
     */
    protected $table = null;

    /**
     * Связь "Один-Ко-Многим"
     * [
     *  $alias => [
     *      $ReferenceModel,
     *      'key' => $referencedFields,
     *      'otherKey' => $fields
     *  ]
     * ]
     * @var array
     */
    protected $hasMany = [];

    /**
     * Связь "Один-К-Одному"
     * [
     *  $alias => [
     *      $ReferenceModel,
     *      'key' => $referencedFields,
     *      'otherKey' => $fields
     *  ]
     * ]
     * @var array
     */
    protected $hasOne = [];

    /**
     * Связь "Многие-К-Одному"
     * [
     *  '$alias' => [
     *      $ReferenceModel,
     *      'key' => $fields,
     *      'otherKey' => $referencedFields
     *  ]
     * ]
     * @var array
     */
    protected $belongsTo = [];

    /**
     * Связь "Многие-Ко-Многим"
     * [
     *  '$alias' => [
     *      $ReferenceModel,
     *      'model' => $intermediateModel,
     *      'key' => $intermediateFields,
     *      'other_key' => $intermediateReferencedFields,
     *      'field' => $fields,
     *      'referencedField' => $referencedField
     *  ]
     * ]
     * @var array
     */
    protected $hasManyToMany = [];

    /**
     * Присоединение модели
     * Используется в основном с классом System\Models\File, т.е. для добавления файлов к модели
     * [
     *  '$alias' => [
     *      $ReferenceModel,
     *      'type' => поле, в котором хранится имя модели,
     *      'key' => поле, в котором хранится значение ключа модели,
     *      'other_key' => поле ключа,
     *      'field' => поле, в котором хранится alias,
     *  ]
     * @var array
     */
    protected $attachOne = [];

    /**
     * Присоединение модели
     * Используется в основном с классом System\Models\File, т.е. для добавления файлов к модели
     * [
     *  '$alias' => [
     *      $ReferenceModel,
     *      'type' => поле, в котором хранится имя модели,
     *      'key' => поле, в котором хранится значение ключа модели,
     *      'other_key' => поле ключа,
     *      'field' => поле, в котором хранится alias,
     *  ]
     * @var array
     */
    protected $attachMany = [];

    /**
     * @var array Поля, для которых необходима конвертация в дату
     */
    public $dates = [];

    /**
     * @var array Поля, для которых нужна автоматическая конвертация в JSON
     */
    public $json = [];

    /**
     * Автоматическая конвертация полей created_at, updated_at и deleted_at в дату
     * @var bool
     */
    protected $timestamps = true;

    /**
     * Поведения
     * @var array
     */
    protected $behaviors = [];

    /**
     * Поля, которые не будут участвовать в сохранении
     * @var array
     */
    protected $skipped = [];

    /**
     * Динамическое обновление
     * @var boolean
     */
    protected $dynamicUpdate = true;

    /**
     * Поля по умолчанию для вывода
     * @var array
     */
    protected static $fields = [];
    
    private $defaultBehaviors = [
        'System\Behaviors\Dates',
        'System\Behaviors\Json'
    ];

    private $relations = [];
    private $attaches = [];

    protected function initialize()
    {
        // Устанавливаем таблицу
        if ($this->table !== null) $this->setSource($this->table);

        // Устанавливаем связи
        foreach ($this->hasMany as $alias => $relation) $this->relationHasMany($alias, $relation);
        foreach ($this->hasOne as $alias => $relation) $this->relationHasOne($alias, $relation);
        foreach ($this->belongsTo as $alias => $relation) $this->relationBelongsTo($alias, $relation);
        foreach ($this->hasManyToMany as $alias => $relation) $this->relationHasManyToMany($alias, $relation);

        // Устанавливаем прикрепляемые модели
        foreach ($this->attachOne as $alias => $attach) $this->attachRelation('one', $alias, $attach);
        foreach ($this->attachMany as $alias => $attach) $this->attachRelation('many', $alias, $attach);

        foreach ($this->defaultBehaviors as $behavior) $this->behaviors[] = $behavior;

        // Устанавливаем поведения
        foreach ($this->behaviors as $behavior) {
            if (is_array($behavior)) {
                $this->addBehavior(new $behavior[0]($behavior[1]));
            } else {
                $this->addBehavior(new $behavior());
            }
        }

        // Устанавливаем формат дат
        if ($this->timestamps) {
            $this->dates[] = 'created_at';
            $this->dates[] = 'updated_at';
            $this->dates[] = 'deleted_at';
        }

        // Динамичное обновление полей
        $this->useDynamicUpdate($this->dynamicUpdate);

        // Пропуск аттрибутов при сохранении
        if ($this->timestamps) $this->skipped = $this->skipped + ['created_at', 'updated_at'];
        $this->skipAttributes($this->skipped);

        $this->keepSnapshots(true);
    }

    public static function find($parameters = null)
    {
        return parent::find(static::getConditions($parameters));
    }

    public static function findFirst($parameters = null)
    {
        return parent::findFirst(static::getConditions($parameters));
    }

    protected static function getConditions($parameters = null)
    {
        return $parameters;
    }

    /**
     * @param string $alias
     * @param array $relation
     */
    protected function relationHasMany($alias, $relation)
    {
        $referenceModel = array_shift($relation);

        $referencedField = isset($relation['key']) ? $relation['key'] : $this->getKeyFromModelName(get_class($this));
        $field = isset($relation['otherKey']) ? $relation['otherKey'] : 'id';

        if (is_subclass_of($referenceModel, 'Phalcon\Mvc\Collection')) {
            $reference = [
                'type' => 'hasMany',
                'referenceModel' => $referenceModel,
                'key' => $referencedField,
                'otherKey' => $field,
            ];

            $this->relations[$alias] = $reference;
        } else {
            $this->hasMany($field, $referenceModel, $referencedField, ['alias' => $alias]);
        }
        $this->skipped[] = $alias;
    }

    /**
     * @param string $alias
     * @param array $relation
     */
    protected function relationHasOne($alias, $relation)
    {
        $referenceModel = array_shift($relation);
        $referencedField = isset($relation['key']) ? $relation['key'] : $this->getKeyFromModelName(get_class($this));
        $field = isset($relation['otherKey']) ? $relation['otherKey'] : 'id';

        if (is_subclass_of($referenceModel, 'Phalcon\Mvc\Collection')) {
            $reference = [
                'type' => 'hasOne',
                'referenceModel' => $referenceModel,
                'key' => $referencedField,
                'otherKey' => $field,
            ];

            $this->relations[$alias] = $reference;
        } else {
            $this->hasOne($field, $referenceModel, $referencedField, ['alias' => $alias]);
        }
        $this->skipped[] = $alias;
    }

    /**
     * @param string $alias
     * @param array $relation
     */
    protected function relationBelongsTo($alias, $relation)
    {
        $referenceModel = array_shift($relation);
        $field = isset($relation['key']) ? $relation['key'] : $this->getKeyFromModelName($referenceModel);
        $referencedField = isset($relation['otherKey']) ? $relation['otherKey'] : 'id';

        if (is_subclass_of($referenceModel, 'Phalcon\Mvc\Collection')) {
            $reference = [
                'type' => 'hasOne',
                'referenceModel' => $referenceModel,
                'key' => $referencedField,
                'otherKey' => $field,
            ];

            $this->relations[$alias] = $reference;
        } else {
            $this->belongsTo($field, $referenceModel, $referencedField, ['alias' => $alias]);
        }
        $this->skipped[] = $alias;
    }

    /**
     * @param string $alias
     * @param array $relation
     */
    protected function relationHasManyToMany($alias, $relation)
    {
        $referenceModel = array_shift($relation);
        $intermediateModel = $relation['model'];

        $intermediateFields = isset($relation['key']) ? $relation['key'] : $this->getKeyFromModelName(get_class($this));
        $intermediateReferencedFields = isset($relation['otherKey']) ? $relation['otherKey'] : $this->getKeyFromModelName($referenceModel);

        $fields = isset($relation['field']) ? $relation['field'] : 'id';
        $referencedField = isset($relation['referencedField']) ? $relation['referencedField'] : 'id';

        //TODO: Реализовать поддержку ORM ManyToMany ODM

        $this->hasManyToMany(
            $fields,
            $intermediateModel, $intermediateFields, $intermediateReferencedFields,
            $referenceModel, $referencedField,
            ['alias' => $alias]
        );
        $this->skipped[] = $alias;
    }

    /**
     * @param string $type
     * @param string $alias
     * @param array $attach
     */
    protected function attachRelation($type, $alias, $attach)
    {
        $referenceModel = array_shift($attach);
        $classField = isset($attach['type']) ? $attach['type'] : 'attachment_type';
        $keyField = isset($attach['key']) ? $attach['key'] : 'attachment_id';
        $aliasField = isset($attach['field']) ? $attach['field'] : 'attachment_field';
        $field = isset($attach['otherKey']) ? $attach['otherKey'] : 'id';

        $attachment_type = get_class($this);

        $params = [
            'alias'  => $alias,
            'params' => [
                'conditions' => '[' . $referenceModel . '].' . $classField . ' = :attachment_type: AND [' . $referenceModel . '].' . $aliasField . ' = :attachment_field:',
                'bind' => ['attachment_type' => $attachment_type, 'attachment_field' => $alias],
                'order'  => '[' . $referenceModel . '].created_at',
            ]
        ];

        $this->attaches[$alias] = [
            'classField' => $classField,
            'keyField' => $keyField,
            'aliasField' => $aliasField,
            'field' => $field
        ];

        if ($type == 'one') {
            $this->hasOne($field, $referenceModel, $keyField, $params);
        } else {
            $this->hasMany($field, $referenceModel, $keyField, $params);
        }
        $this->skipped[] = $alias;
    }

    /**
     * Возвращает ключ по имени модели
     * @param string $model_name
     * @return string
     */
    protected function getKeyFromModelName($model_name)
    {
        $model_name = basename($model_name);
        return Text::uncamelize($model_name) . '_id';
    }

    public function __get($property)
    {
        if (isset($this->relations[$property])) return $this->getRelationObject($this->relations[$property]);

        return parent::__get($property);
    }

    /**
     * Возвращает присоединенную модель ODM
     * @param string $alias Название связи
     * @return mixed
     */
    public function getRelation($alias)
    {
        if (!isset($this->relations[$alias])) return false;
        $relation = $this->relations[$alias];
        $value = $this->$relation['otherKey'];
        if (is_numeric($value)) $value = $value + 0;

        $method = '';
        switch ($relation['type']) {
            case 'hasMany':
                $method = $relation['referenceModel'] . '::find';
                break;

            case 'hasOne':
            case 'belongsTo':
                $method = $relation['referenceModel'] . '::findFirst';
                break;
        }

        return call_user_func_array($method, [
            [
                [$relation['key'] => $value]
            ]
        ]);
    }

    public function __isset($property)
    {
        if (isset($this->relations[$property])) return true;
        if (isset($this->attachments[$property])) return true;

        return parent::__isset($property);
    }

    public function __call($name, $arguments)
    {
        if ($field = $this->is_method($name, 'count')) {

            if (!isset($this->relations[$field])) return parent::__call($name, $arguments);
            return $this->relationCount($field);

        } elseif ($field = $this->is_method($name, 'attach')) {

            if (!isset($this->attaches[$field])) return parent::__call($name, $arguments);
            return $this->attach($field, $arguments[0]);

        } else return parent::__call($name, $arguments);
    }

    /**
     * Возвращает количество элементов в связанной модели
     * @param string $alias Название связи
     * @return integer Количество элементов
     */
    public function relationCount($alias)
    {
        $relation = $this->relations[$alias];

        $value = $this->$relation['otherKey'];
        if (is_numeric($value)) $value = $value + 0;

        return call_user_func_array($relation['referenceModel'] . '::count', [
            [[$relation['key'] => $value]]
        ]);
    }

    /**
     * Присоединяет объект (файл) к текущей модели
     * @param string $alias Название связи
     * @param Model $object Объект, который присоединяется
     * @return bool|string false, если связь не найдена либо результат сохранения модели
     */
    public function attach($alias, Model $object)
    {
        if (!isset($this->attaches[$alias])) return false;
        $attach = $this->attaches[$alias];

        $object->$attach['classField'] = get_class($this);
        $object->$attach['keyField'] = $this->$attach['field'];
        $object->$attach['aliasField'] = $alias;

        return $object->save();
    }

    /**
     * Проверяет, содержится ли в переданном методе определенное имя метода
     * Например:
     * is_method('countObjects', 'count') -> 'objects'
     * is_method('attachFiles', 'count') -> false
     * @param string $name Имя метода, в котором искать
     * @param string $method Имя метода, которое искать
     * @return bool|string false, если не содержится, либо часть строки с удаленным именем
     */
    protected function is_method($name, $method)
    {
        $result = strpos($name, $method) === 0;
        if ($result)
            return Text::uncamelize(substr($name, strlen($method)));
        else
            return false;
    }

    /**
     * Воращает массив сообщений от модели
     */
    public function getMessagesArray()
    {
        $result = [];
        foreach ($this->getMessages() as $message) {
            $result[] = $message->getMessage();
        }
        return $result;
    }

    public function toArray($columns = null)
    {
        if ($columns == null && !empty(self::$fields)) $columns = self::$fields;
        return parent::toArray($columns);
    }
}