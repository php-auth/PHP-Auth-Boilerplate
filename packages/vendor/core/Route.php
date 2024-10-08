<?php
/**
 * Route
 *
 * @version 1.0.0
 */
namespace Core;

class Route
{
    private static $routes           = Array();
    private static $errors           = Array();
    private static $pathNotFound     = null;
    private static $methodNotAllowed = null;

    /**
     * init
     *
     * @access public
     */
    public static function init()
    {
        self::$errors = include_once 'errors.php';
        self::includeRoutes();
        self::pathNotFound(function ($path) {
            self::error(404);
        });
        self::methodNotAllowed(function ($path, $method) {
            self::error(405);
        });
        self::run();
    }

    /**
     * includeRoutes
     *
     * @access private
     */
    private static function includeRoutes()
    {
	    $routes = PATH['routes'] . '/*.php';
        foreach (glob($routes) as $file) {
            include_once $file;
        }
        self::reserved(PATH['packages'], PATH['node_modules']);
    }

    /**
     * Reserved routes.
     *
     * @param string $file
     * @param string $node_modules
     * @access private
     */
    private static function reserved($file, $node_modules)
    {
        $file        .= '/package.json';
        $package_json = file_get_contents($file);
        $package_json = json_decode($package_json, true);
        if (isset($package_json['public'])) {
            foreach ($package_json['public'] as $alias => $dir) {
                self::add("/packages/$alias/(.*)",
                fn($file) => File::getStaticFile("/$node_modules/$dir/$file"));
            }
        }
    }

    /**
     * error
     *
     * @param int $code
     * @param string $message
     * @return string
     * @access public
     */
    public static function error($code, $message = '')
    {
        $data            = [];
        $data['title']   = self::$errors[$code][0];
        $data['message'] = $message ? $message : self::$errors[$code][1];
        $data['request'] = $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
        header('HTTP/1.0 ' . $data['title']);
        return view('error', $data);
    }

   /**
    * Function used to add a new route.
    * @param string $expression Route string or expression.
    * @param callable $function Function to call if route with allowed method is
    * found.
    * @param string|array $method Either a string of allowed method or an array
    * with string values.
    *
    */
    public static function add($expression, $function, $method = ['get','post'])
    {
        array_push(self::$routes, Array(
          'expression' => $expression,
          'function' => $function,
          'method' => $method
        ));
    }

    /**
     * pathNotFound
     *
     * @param mixed $function
     * @access public
     */
    public static function pathNotFound($function)
    {
        self::$pathNotFound = $function;
    }

    /**
     * methodNotAllowed
     *
     * @param mixed $function
     * @access public
     */
    public static function methodNotAllowed($function)
    {
        self::$methodNotAllowed = $function;
    }

    /**
     * run
     *
     * @param string $basepath
     * @param bool $case_matters
     * @param bool $trailing_slash_matters
     * @param bool $multimatch
     * @access public
     */
    public static function run($basepath               = '',
                               $case_matters           = true,
                               $trailing_slash_matters = false,
                               $multimatch             = false)
    {
        $basepath   = rtrim($basepath, '/');
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $path       = '/';

        if (isset($parsed_url['path'])) {
            if ($trailing_slash_matters) {
                $path = $parsed_url['path'];
            } elseif ($basepath . '/' != $parsed_url['path']) {
                $path = rtrim($parsed_url['path'], '/');
            } else {
                $path = $parsed_url['path'];
            }
        }
        $method            = $_SERVER['REQUEST_METHOD'];
        $path_match_found  = false;
        $route_match_found = false;

        foreach (self::$routes as $route) {

            if ($basepath != '' && $basepath != '/') {
                $route['expression'] = '(' . $basepath . ')' .
                $route['expression'];
            }
            $route['expression'] = '^' . $route['expression'];
            $route['expression'] = $route['expression'] . '$';

            if (preg_match('#' . $route['expression'] . '#' .
               ($case_matters ? '' : 'i'), $path, $matches)) {

                $path_match_found = true;

                foreach ((array)$route['method'] as $allowedMethod) {

                    if (strtolower($method) == strtolower($allowedMethod)) {
                        array_shift($matches);
                        if ($basepath != '' && $basepath != '/') {
                            array_shift($matches);
                        }
                        call_user_func_array($route['function'], $matches);
                        $route_match_found = true;
                        break;
                    }
                }
            }
            if ($route_match_found&&!$multimatch) {
                break;
            }
        }
        if (!$route_match_found) {
            if ($path_match_found) {
                if (self::$methodNotAllowed) {
                    call_user_func_array(self::$methodNotAllowed,
                                         Array($path,$method));
                }
            } elseif (self::$pathNotFound) {
                call_user_func_array(self::$pathNotFound, Array($path));
            }
        }
    }
}
