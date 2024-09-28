<?php
/**
 * Main
 *
 * Auto loaded class.
 *
 * @version 1.0.0
 */
namespace App\Lib;

class Main
{
    /**
     * index
     *
     * @return array
     * @access public
     */
    public function index() : array
    {
        $Class = [];
        $Class['Auth']         = Auth::init();
        $Class['TwigFilter']   = new TwigFilter;
        $Class['TwigFunction'] = new TwigFunction;
        return $Class;
    }
}
