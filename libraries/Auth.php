<?php
/**
 * Authentication
 *
 * @version 1.0.0
 */
namespace App\Lib;

class Auth
{
    private static $PHPAuth = null;

    /**
     * init
     *
     * @return mixed
     * @access public
     */
    public static function init() : mixed
    {
        if (!self::$PHPAuth) {
            self::$PHPAuth = controller('PHPAuth');
        }
        return self::$PHPAuth->init();
    }

    /**
     * group
     *
     * @param string $roles
     * @param callable $callback
     * @return mixed
     * @access public
     */
    public static function group($roles, $callback) : mixed
    {
        return self::$PHPAuth->group($roles, $callback);
    }
}
