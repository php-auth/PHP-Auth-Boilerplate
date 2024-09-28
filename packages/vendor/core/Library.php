<?php
/**
 * Library
 *
 * @version 1.0.0
 */
namespace Core;

class Library
{
    /**
     * init
     *
     * @return void
     * @access public
     */
    public static function init() : void
    {
        require_once(PATH['libraries'] . '/Main.php');
        $Main = '\\' . NS['library'] . '\\Main';
        $Main = new $Main;
        define('LIB_CLASS', $Main->index());
    }
}
