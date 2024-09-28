<?php
/**
 * Template
 *
 * @version 1.0.0
 */
namespace Core;

use \Twig;

class Template
{
    private static $Twig = null;
    public static $meta  = [];

    /**
     * init
     *
     * @access public
     */
    public static function init() : void
    {
        if (ini_get('display_errors')) {
            $twig_env = ['auto_reload' => true, 'cache' => false];
        } else {
            $twig_env = ['cache' => PATH['cache']];
        }
        self::$meta = parse_ini_file(APP_PATH . '/config/meta.ini', true);
        $TwigLoader = new Twig\Loader\FilesystemLoader(PATH['views']);
        self::$Twig = new Twig\Environment($TwigLoader, $twig_env);
    }

    /**
     * render
     *
     * @param string $file
     * @param array $data
     * @access public
     */
    public static function render($file, $data) : void
    {
        if (!isset($data['header'])) {
            $data['header'] = 'layout/header.html';
        }
        if (!isset($data['footer'])) {
            $data['footer'] = 'layout/footer.html';
        }
        if (!isset($data['navbar'])) {
            $data['navbar'] = 'layout/navbar.html';
        }
        $data['file'] = 'include_static_files.html';
        $data['ajax'] = false;
        //$headers = getallheaders();
        $headers = self::getallheaders();
        if (isset($headers['Ajax'])) {
            $data['ajax'] = true;
        }
        if (pathinfo($file, PATHINFO_EXTENSION)) {
            echo self::$Twig->render("{$file}", $data);
        } else {
            echo self::$Twig->render("{$file}.html", $data);
        }
    }

    /**
     * twigGlobal
     *
     * @param string $name
     * @param mixed $data
     * @access public
     */
    public static function twigGlobal($name, $data) : void
    {
        self::$Twig->addGlobal($name, $data);
    }

    /**
     * twigFunction
     *
     * @param string $name
     * @param mixed $fnc
     * @access public
     */
    public static function twigFunction($name, $fnc) : void
    {
        $TwigFunction = new Twig\TwigFunction($name, $fnc);
        self::$Twig->addFunction($TwigFunction);
    }

    /**
     * twigFilter
     *
     * @param string $name
     * @param mixed $fnc
     * @access public
     */
    public static function twigFilter($name, $fnc) : void
    {
        $TwigFilter = new Twig\TwigFilter($name, $fnc);
        self::$Twig->addFilter($TwigFilter);
    }

    /**
     * getallheaders
     *
     * @return array
     * @access private
     */
    private static function getallheaders() : array
    {
        $headers = [];
        foreach($_SERVER as $name => $value) {
            if($name !== 'HTTP_MOD_REWRITE' &&
              (substr($name, 0, 5) === 'HTTP_' || $name === 'CONTENT_LENGTH' ||
              $name === 'CONTENT_TYPE')) {
                $name = str_replace(' ', '-',
                            ucwords(
                                strtolower(
                                    str_replace('_', ' ',
                                        str_replace('HTTP_', '', $name)
                                    )
                                )
                            )
                        );
                if($name === 'Content-Type') $name = 'Content-type';
                $headers[$name] = $value;
            }
        }
        return $headers;
    }

    /**
     * HTML meta tag
     *
     * {% set meta = html_meta_tag(array) %}
     *
     * @param array $data
     * @return array
     * @access public
     */
    public static function htmlMetaTag($data) : array
    {
        $data['language'] = $_SESSION['language'];
        return array_merge(self::$meta, $data);
    }
}
