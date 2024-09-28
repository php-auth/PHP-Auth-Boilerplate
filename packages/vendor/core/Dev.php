<?php
/**
 * Dev
 *
 * @version 1.0.0
 */
namespace Core;

class Dev
{
    /**
     * Development server
     *
     * @param string $config
     * @return void
     * @access public
     */
    public static function server($config) : void
    {
        $path = dirname(debug_backtrace()[0]['file']);
        shell_exec("php -q -S $config -t $path/public $path/index.php");
    }

    /**
     * Debug app in development environment
     *
     * @param boolean $status
     * @return void
     * @access public
     */
    public static function debug($status) : void
    {
        if ($status) {
            error_reporting(E_ALL);
            ini_set('display_errors', '1');
        } else {
            error_reporting(0);
            ini_set('display_errors', '0');
        }
    }

    /**
     * print_f
     *
     * @param string $data
     * @return void
     * @access public
     */
    public static function print_f($data) : void
    {
        if (is_array($data) || is_object($data)) {
            self::highlight($data, 'php');
        } elseif (json_decode($data, true)) {
            self::highlight($data, 'json');
        }
    }

    /**
     * highlight
     *
     * @param string $data
     * @param string $lang
     * @return void
     * @access private
     */
    private static function highlight($data, $lang) : void
    {
        echo <<<EOT
        <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/
        highlight.js/11.7.0/styles/default.min.css">
        <script src="//cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/
        highlight.min.js"></script><script>hljs.highlightAll();</script>
        <pre style="font-size: 12pt"><code class="language-$lang">
        EOT;
        if ($lang === 'php') {
            print_r($data);
        } else {
            echo json_encode(json_decode($data),
            JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        }
        echo '</code></pre>';
    }
}
