<?php namespace System\Helpers;

class Client
{
    public static function getIp()
    {
        return isset($_SERVER['HTTP_X_REAL_IP']) && strlen($_SERVER['HTTP_X_REAL_IP']) ? $_SERVER['HTTP_X_REAL_IP'] : $_SERVER['REMOTE_ADDR'];
    }
}