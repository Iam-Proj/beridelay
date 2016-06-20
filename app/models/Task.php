<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;
use System\Traits\Filters;
use Carbon\Carbon;
use Phalcon\Mvc\Model\Query;

/**
 * Модель "Задание"
 * @package BeriDelay\Models
 * @method static Task findFirstById(integer $id)
 */
class Task extends Model
{
    use SoftDelete;
    use Filters;

    /**
     * @var int ID пользователя
     */
    public $user_id;

    /**
     * @var int Статус задания
     */
    public $status;

    /**
     * @var string Причина отклонения/блокировки
     */
    public $reason;

    /**
     * @var string Комментарий администратора
     */
    public $comment;

    /**
     * @var Carbon Время завершения задания
     */
    public $finished_at;

    public $behaviors = [
        'System\Behaviors\Loggable'
    ];

    /**
     * @var int Все статусы 
     */
    public static $statuses = [
        0 => 'новое задание',
        1 => 'в работе',
        2 => 'на проверку',
        10 => 'завершено',
        20 => 'отклонено',
        30 => 'заблокировано',
    ];
    
    public static $dates = ['finished_at'];

    protected $hasManyToMany = [
        'targets' => [
            'BeriDelay\Models\Target',
            'model' => 'BeriDelay\Models\Task2Target'
        ]
    ];
    
    public $validation = [
        'user_id' => 'required|integer',
        'status' => 'in:0,1,2,10,20,30',
    ];

    public static $fields = ['id', 'targets', 'status', 'user_id', 'reason', 'comment', 'finished_at'];

    /**
     * Возвращает для текущего задания указанное количество целей
     * @param integer $salary
     * @param integer $count
     * @return array
     */
    public function generate($salary, $count = 3)
    {
        $showedTargets = Target2User::find('user_id = ' . $this->user_id);
        $target_ids = [];
        foreach ($showedTargets as $target) {
            $target_ids[] = $target->target_id;
            $table = $target->table;
        }

        // Выбираем цели с минимальным количеством стартов, а также еще не просмотренные пользователем
        $query = new Query(self::getQuery($salary, $count, $target_ids), $this->getDI());
        $targets = $query->execute();

        if (count($targets) < $count) {

            // TODO: HACK: PHQL генерирует несколько! запросов удаления (по количеству строк) вместо выполнения одного
            // $query = new Query("DELETE FROM BeriDelay\Models\Target2User WHERE user_id = " . $this->user_id, $this->getDI());
            // $query->execute();

            $connection = $this->getDI()->getDb();
            $table = Target2User::$table;
            $connection->query("DELETE FROM " . $table . " WHERE user_id = " . $this->user_id);

            $query = new Query(self::getQuery($salary, $count, []), $this->getDI());
            $targets = $query->execute();
        }

        $ids = [];
        foreach ($targets as $target) $ids[] = (int) $target->target_id;

        $targets = Target::find('id IN (' . implode(',', $ids) . ')');

        $result = [];
        /** @var Target $target */
        foreach ($targets as $target) {
            $taskTarget = new Task2Target();
            $taskTarget->task_id = $this->id;
            $taskTarget->target_id = $target->id;
            $taskTarget->save();

            if (!Target2User::findFirst('user_id = ' . $this->user_id . ' and target_id = ' . $target->id)) {
                $userTarget = new Target2User();
                $userTarget->user_id = $this->user_id;
                $userTarget->target_id = $target->id;
                $userTarget->save();
            }

            $result[] = $target->toArray();
        }

        return $result;
    }

    /**
     * Возвращает запрос для получения целей задания
     * @param integer $user_id
     * @param integer $salary
     * @param integer $limit
     * @return string
     */
    public static function getQuery($salary, $count = 3, $target_ids)
    {
        /*return "SELECT *
FROM BeriDelay\Models\Target
WHERE id in (
	SELECT min(id)
	FROM BeriDelay\Models\Target as t1
	WHERE 
		t1.start_count IN (
			SELECT min(start_count) 
			FROM BeriDelay\Models\Target as t2 
			WHERE t1.category_id = t2.category_id AND t2.salary = " . $salary . "  AND t2.id NOT IN (
			    SELECT target_id 
			    FROM BeriDelay\Models\Target2User
			    WHERE user_id = " . $user_id . "
		    )
		)
	GROUP BY category_id
)
ORDER BY start_count
LIMIT " . $limit;*/
        $notIds = count($target_ids) ? " AND t2.id NOT IN (" . implode(',', $target_ids) . ")" : "";
        return "
SELECT min(id) as target_id
FROM BeriDelay\Models\Target as t1
WHERE 
  t1.start_count IN (
	SELECT min(start_count) 
	FROM BeriDelay\Models\Target as t2 
	WHERE t1.category_id = t2.category_id AND t2.salary = " . $salary . $notIds . "
)
GROUP BY category_id
LIMIT " . $count;

    }

    public static function getFiltersBase($data, $query)
    {
        if (isset($data['user_id'])) self::filterValue($query, 'user_id', $data['user_id']);
        if (isset($data['status'])) self::filterValue($query, 'status', $data['status'], [0, 1, 2, 10, 20, 30]);

        return $query;
    }

    public function toArray($columns = null)
    {
        if ($columns == null && !empty(static::$fields)) $columns = static::$fields;

        $result = parent::toArray($columns);
        if ($columns != null && in_array('targets', $columns) && $this->targets) {
            $result['targets'] = [];
            /** @var Target $target */
            foreach ($this->targets as $target) $result['targets'][] = $target->toArray();
        }

        return $result;
    }
}