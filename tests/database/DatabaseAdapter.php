<?php namespace BeriDelay\Tests\Database;

/**
 * Абстрактный класс адаптера базы данных.
 * Для занесения тестовых данных в БД и удаления их оттуда.
 * @package BeriDelay\Tests\Database
 */
abstract class DatabaseAdapter
{
    public $table = null;
    public $key = 'id';

    abstract protected function setUp($fixtures);
    abstract protected function tearDown($fixtures);
}