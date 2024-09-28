<?php
/**
 * Settings
 *
 * @version 1.0.0
 */
namespace Core;

class Settings
{
    private static $dir = '';

    /**
     * Initialize the application
     *
     * @return void
     * @access public
     */
    public static function index()
    {
        ob_get_clean();
        self::$dir = dirname(debug_backtrace()[0]['file']);
        self::php_ini();
        self::defineConstant(self::$dir);
        if (self::publicFileExists()) return false;
        Language::init();
        Template::init();
        Library::init();
        Route::init();
    }

    /**
     * defineConstant
     *
     * @param string $dir
     * @access private
     */
    private static function defineConstant($dir)
    {
        define('APP_PATH', $dir);
        $path = parse_ini_file(APP_PATH . '/config/path.ini', true);
        foreach ($path as $key => $value) {
            $path[$key] = APP_PATH . $path[$key];
        }
        $path['app']      = APP_PATH;
        $path['packages'] = dirname($path['vendor']);
        define('PATH', $path);
        ini_set('session.gc_probability', 1);
        ini_set('session.save_path', $path['sessions']);
        session_start();
        self::addNamespace();
    }

    /**
     * If the public file exists it will return false for the web server to take
     * care of serving the file.
     *
     * @return bool
     * @access private
     */
    private static function publicFileExists() : bool
    {
        if (isset($_SERVER['REQUEST_URI']) &&
            is_file(PATH['public'] . $_SERVER['REQUEST_URI'])) {
            return true;
        }
        return false;
    }

    /**
     * addNamespace
     *
     * @return void
     * @access private
     */
    private static function addNamespace() : void
    {
        $ns = parse_ini_file(APP_PATH . '/config/namespace.ini', true);
        define('NS', $ns);
        $Psr4AutoloaderClass = new Psr4AutoloaderClass;
        $Psr4AutoloaderClass->register();
        $Psr4AutoloaderClass->addNamespace(NS['controller'],
                                           PATH['controllers']);
        $Psr4AutoloaderClass->addNamespace(NS['model'],
                                           PATH['models']);
        $Psr4AutoloaderClass->addNamespace(NS['library'],
                                           PATH['libraries']);
    }

    /**
     * Define php.ini file settings
     *
     * @return void
     * @access private
     */
    private static function php_ini() : void
    {
        $list = parse_ini_file(self::$dir . '/config/php.ini', true)['PHP'];
        $list['debug'] = isset($list['debug']) ? $list['debug'] : false;
        Dev::debug($list['debug']);
        unset($list['debug']);
        foreach ($list as $key => $value) {
            ini_set($key, $value);
        }
    }

    /**
     * Host
     *
     * @param bool $use_forwarded_host
     * @return string
     * @access public
     */
    public static function host($use_forwarded_host = false) : string
    {
        $s    = $_SERVER;
        $ssl  = (! empty($s['HTTPS']) && $s['HTTPS'] == 'on');
        $sp   = strtolower($s['SERVER_PROTOCOL']);
        $prot = substr($sp, 0, strpos($sp, '/')) . (($ssl) ? 's' : '');
        $port = $s['SERVER_PORT'];
        $port = ((! $ssl && $port == '80') || ($ssl && $port == '443')) ? '' :
                ':' . $port;
        $host = ($use_forwarded_host && isset($s['HTTP_X_FORWARDED_HOST'])) ?
                 $s['HTTP_X_FORWARDED_HOST'] : (isset($s['HTTP_HOST']) ?
                 $s['HTTP_HOST'] : null);
        $host = isset($host) ? $host : $s['SERVER_NAME'] . $port;
        return "$prot://$host";
    }

    /**
     * PHP version comparison.
     *
     * @param string $version
     * @param string $op
     * @return bool
     * @access public
     */
    public static function phpcompare($version, $op) : bool
    {
        if (version_compare(phpversion(), $version, $op)) {
            return true;
        }
        return false;
    }

    /**
     * language
     *
     * @return string
     * @access public
     */
    public static function language() : string
    {
        if (!isset($_SESSION['language'])) {
            $_SESSION['language'] = 'en';
        } else if (isset($_GET['language'])) {
            $_SESSION['language'] = $_GET['language'];
        }
        return $_SESSION['language'];
    }
}
