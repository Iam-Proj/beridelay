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

    /**
     * Возвращает для текущего задания указанное количество целей
     * @param integer $salary
     * @param integer $count
     * @return array
     */
    public function generate($salary, $count = 3)
    {
        //Выбираем цели с минимальным количеством стартов, а также еще не просмотренные пользователем
        $query = new Query(self::getQuery($this->user_id, $salary, $count), $this->getDI());
        $targets = $query->execute();

        if (count($targets) < $count) {
            Target2User::find('user_id = ' . $this->user_id)->delete();

            $query = new Query(self::getQuery($this->user_id, $salary), $this->getDI());
            $targets = $query->execute();
        }

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
    public static function getQuery($user_id, $salary, $limit = 3)
    {
        return "SELECT * 
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
LIMIT " . $limit;
    }

    public static function getFiltersBase($data, $query)
    {
        if (isset($data['user_id'])) self::filterValue($query, 'user_id', $data['user_id']);
        if (isset($data['status'])) self::filterValue($query, 'status', $data['status'], [0, 1, 2, 10, 20, 30]);

        return $query;
    }
}