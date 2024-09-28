<?php
/**
 * TwigFunction
 *
 * @version 1.1.0
 */
namespace App\Lib;

use Core\Template;
use Core\Language;

class TwigFunction
{
    /**
     * __construct
     *
     * @access public
     */
    public function __construct()
    {
        Template::twigGlobal('session', $_SESSION);

        Template::twigFunction('box', function ($text, $bgcolor = 'primary',
        $btnclose = false, $css = '') {
            return $this->box($text, $bgcolor, $btnclose, $css);
        });

        Template::twigFunction('language_list', function () {
            return $this->languageList();
        });

        Template::twigFunction('html_meta_tag', function ($data) {
            return Template::htmlMetaTag($data);
        });

        Template::twigFunction('copyright', function ($year) {
            return $this->copyright($year);
        });

        Template::twigFunction('version', function () {
            return $this->version();
        });

        Template::twigFunction('youtube', function ($user) {
            $Youtube = new Youtube($user);
            return $Youtube->data();
        });

        Template::twigFunction('parsedown', function ($file) {
            return $this->parsedown($file);
        });

        Template::twigFunction('session', function ($key, $value = '') {
            return $this->session($key, $value);
        });

        Template::twigFunction('previous_date', function () {
            return $this->previousDate();
        });

        Template::twigFunction('translate', function ($text) {
            return Language::translate($text);
        });

        Template::twigFunction('redirect', function ($url) {
            return header("Location: $url");
        });
    }

    /**
     * Message box
     *
     * {{ box('Text...', 'Background color', 'Close button', 'CSS classes') }}
     *
     * Background color:
     * primary | secondary | success | danger | warning | info | light | dark
     * Close button: true | false
     *
     * Demo:
     *
     * {{ box('Text...') }}
     * {{ box('Text...', 'primary') }}
     * {{ box('Text...', 'secondary', true) }}
     * {{ box('Text...', 'success', true, 'm-2 p-2 shadow') }}
     *
     * @param string $text
     * @param string $bgcolor
     * @param boolean $btnclose
     * @param string $css
     * @return void
     * @access private
     */
    private function box($text, $bgcolor, $btnclose, $css) : void
    {
        $button = $btnclose ? '<button type="button" class="btn-close outline' .
        '-none box-shadow-none" data-bs-dismiss="alert"></button>' : '';
        echo <<<EOD
        <div class="alert alert-$bgcolor alert-dismissible fade show $css role="
        alert">$text $button</div>
        EOD;
    }

    /**
     * Language list
     *
     * {{ language_list() }}
     *
     * @return array
     * @access private
     */
    private function languageList() : array
    {
        $language = array_filter(glob(PATH['app'] . '/languages/*'), 'is_dir');
        foreach ($language as $index => $value) {
            $arr = explode('/', $value);
            $language[$index] = end($arr);
        }
        return $language;
    }

    /**
     * .md file interpreter
     *
     * {{ parsedown('/path/to/file.md')|raw }}
     *
     * @param string $file
     * @return mixed
     * @access private
     */
    private function parsedown($file) : mixed
    {
        $contents  = file_get_contents(PATH['app'] . $file);
        $Parsedown = new Parsedown();
        return $Parsedown->text($contents);
    }

    /**
     * Session
     *
     * Set session - {{ session('key', 'value') }}
     * Get session - {{ session('key') }}
     *
     * @param string $key
     * @param string $value
     * @return string
     * @access private
     */
    private function session($key, $value) : string
    {
        if (!$value && isset($_SESSION[$key])) {
            return $_SESSION[$key];
        }
        if ($key && $value) {
            $_SESSION[$key] = $value;
        }
        return '';
    }

    /**
     * Previous date
     *
     * {{ previous_date() }}
     *
     * @return mixed
     * @access private
     */
    private function previousDate() : mixed
    {
        $date = strtotime('now');
        $date = date('Y-m-d', $date);
        $date = strtotime($date);
        $date = strtotime('-1 day', $date);
        $date = date('Y-m-d', $date);
        return $date;
    }

    /**
     * Copyright footer text
     *
     * {{ copyright('now'|date('Y')) }}
     *
     * @param string $year
     * @return void
     * @access private
     */
    private function copyright($year) : void
    {
        echo "&copy; $year ", Template::$meta['copyright'];
    }

    /**
     * Application version
     *
     * {{ version() }}
     *
     * @return void
     * @access private
     */
    private function version() : void
    {
        echo Template::$meta['version'];
    }
}
