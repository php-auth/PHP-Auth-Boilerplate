<?php
/**
 * TwigFilter
 *
 * @version 1.0.0
 */
namespace App\Lib;

use Core\Template;

class TwigFilter
{
    /**
     * __construct
     *
     * @access public
     */
    public function __construct()
    {
        Template::twigFilter('datef', function ($date) {
            return $this->datef($date);
        });

        Template::twigFilter('remove_accent', function ($str) {
            return $this->removeAccent($str);
        });

        Template::twigFilter('route', function ($str) {
            return $this->route($str);
        });

        Template::twigFilter('currency_formatting', function ($value) {
            return $this->currencyFormatting($value);
        });
    }

    /**
     * Date format - Required extension: php-intl
     *
     * {{ '2022-11-02' | datef }}
     *
     * Output [en]: November 2, 2022
     * Output [pt-BR]: 2 de novembro de 2022
     *
     * @param string $date
     * @return mixed
     * @access private
     */
    private function datef($date) : mixed
    {
        $formatter = new \IntlDateFormatter(Settings::language(),
                                           \IntlDateFormatter::LONG,
                                           \IntlDateFormatter::NONE);
        return $formatter->format(new \DateTime($date));
    }

    /**
     * Remove accent
     *
     * {{ 'ÁÉÍóú são àçô' | remove_accent }} // AEIou sao aco
     *
     * @param string $str
     * @return mixed
     * @access private
     */
    private function removeAccent($str) : mixed
    {
        return preg_replace(array("/(á|à|ã|â|ä)/",
                                  "/(Á|À|Ã|Â|Ä)/",
                                  "/(é|è|ê|ë)/",
                                  "/(É|È|Ê|Ë)/",
                                  "/(í|ì|î|ï)/",
                                  "/(Í|Ì|Î|Ï)/",
                                  "/(ó|ò|õ|ô|ö)/",
                                  "/(Ó|Ò|Õ|Ô|Ö)/",
                                  "/(ú|ù|û|ü)/",
                                  "/(Ú|Ù|Û|Ü)/",
                                  "/(ñ)/",
                                  "/(Ñ)/",
                                  "/(ç)/",
                                  "/(Ç)/"),
                            explode(" ","a A e E i I o O u U n N c C"), $str);
    }

    /**
     * Format route
     *
     * {{ 'ÁÉÍóú são àçô' | route }} // aeiou-sao-aco
     *
     * @param string $str
     * @return mixed
     * @access private
     */
    private function route($str) : mixed
    {
        $str = trim($str);
        $str = preg_replace(array("/(á|à|ã|â|ä)/",
                                  "/(Á|À|Ã|Â|Ä)/",
                                  "/(é|è|ê|ë)/",
                                  "/(É|È|Ê|Ë)/",
                                  "/(í|ì|î|ï)/",
                                  "/(Í|Ì|Î|Ï)/",
                                  "/(ó|ò|õ|ô|ö)/",
                                  "/(Ó|Ò|Õ|Ô|Ö)/",
                                  "/(ú|ù|û|ü)/",
                                  "/(Ú|Ù|Û|Ü)/",
                                  "/(ñ)/",
                                  "/(Ñ)/",
                                  "/(ç)/",
                                  "/(Ç)/"),
                            explode(" ","a A e E i I o O u U n N c C"), $str);
        $str = strtolower($str);
        $str = str_replace(' ', '-', $str);
        $str = preg_replace('/[^a-zA-Z0-9-]/', '', $str);
        return $str;
    }

    /**
     * Currency formatting
     *
     * {{ '1.200,00' | currency_formatting }} // 1200.00
     *
     * @param string $value
     * @return mixed
     * @access private
     */
    private function currencyFormatting($value) : mixed
    {
        return str_replace(array('.', ','), array('', '.'), $value);
    }
}
