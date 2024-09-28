<?php
/**
 * Language
 *
 * @version 1.0.0
 */
namespace Core;

use \Twig;

class Language
{
    private static $language = [];

    /**
     * init
     *
     * @return void
     * @access public
     */
    public static function init() : void
    {
        self::readFile();
        self::cache(Settings::language());
    }

    /**
     * readFile
     *
     * @return void
     * @access private
     */
    private static function readFile() : void
    {
        if (isset($_GET['lang'])) {
            $file = PATH['cache'] . '/' . $_GET['lang'] . '.json';
            if (file_exists($file)) {
                header('Content-Type: application/json; charset=utf-8');
                include_once($file);
                exit;
            }
        }
    }

    /**
     * indexChange
     *
     * @param array $data
     * @param integer $index
     * @return array
     * @access private
     */
    private static function indexChange($data, $index) : array
    {
        $arr = [];
        foreach ($data as $key => $value) {
            if ($value) {
                $arr[$index] = $value;
                $index++;
            }
        }
        return $arr;
    }

    /**
     * scan
     *
     * @param string $path
     * @return array
     * @access private
     */
    private static function scan($path) : array
    {
        $arr1 = [];
        $arr2 = [];
        $dir  = scandir($path);
        unset($dir[array_search('.', $dir, true)]);
        unset($dir[array_search('..', $dir, true)]);

        foreach ($dir as $file) {
            $file = "$path/$file";
            if (is_dir($file)) {
                foreach (glob("$file/*.json") as $file) {
                    $arr1 += json_decode(file_get_contents($file), true);
                    $arr1 = self::indexChange($arr1, 0);
                }
            } else {
                $arr2 += json_decode(file_get_contents($file), true);
                $arr2 = self::indexChange($arr2, count($arr1));
            }
        }
        return array_merge($arr1, $arr2);
    }

    /**
     * getContent
     *
     * @param string $path
     * @return array
     * @access private
     */
    private static function getContent($path) : array
    {
        $data         = [];
        $data['en']   = self::scan("$path/en");
        $data['lang'] = self::scan("$path/" . Settings::language());
        return $data;
    }

    /**
     * generate
     *
     * @param string $file
     * @return void
     * @access private
     */
    private static function generate($file) : void
    {
        $data = self::getContent(PATH['languages']);
        file_put_contents($file, json_encode(
                                     array_combine($data['en'], $data['lang'])
                                 )
                             );
        self::readFile();
    }

    /**
     * cache
     *
     * @param string $language
     * @return void
     * @access private
     */
    private static function cache($language) : void
    {
        if ($language !== 'en') {
            $file = PATH['cache'] . "/$language.json";
            if (!file_exists($file) || ini_get('display_errors')) {
                self::generate($file);
            }
            self::$language = json_decode(file_get_contents($file), true);
        }
    }

    /**
     * translate
     *
     * @param string $text
     * @return string
     * @access public
     */
    public static function translate($text) : string
    {
        $text = (string) $text;
        if (Settings::language() && isset(self::$language[$text])) {
            return self::$language[$text];
        }
        return $text;
    }
}
