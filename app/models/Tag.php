<?php namespace BeriDelay\Models;

use System\Models\Model;
use System\Traits\SoftDelete;

/**
 * Model Tag
 * @package BeriDelay\Models
 */
class Tag extends Model
{
    use SoftDelete;
    /**
     *
     * @var string
     */
    public $name;

    /**
     *
     * @var string
     */
    public $color;


    public $attachOne = [
        'file' => ['System\Models\File']
    ];

}
