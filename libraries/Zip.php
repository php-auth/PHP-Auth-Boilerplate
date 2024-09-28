<?php
/**
 * Zip
 *
 * use App\Lib\Zip;
 * $Zip = new Zip;
 * $Zip->generate('/path/to/folder', '/path/to/file.zip');
 *
 * @version 1.0.0
 */
namespace App\Lib;

use \ZipArchive;
use \RecursiveIteratorIterator;
use \RecursiveDirectoryIterator;

class Zip
{
    public function generate($dir, $out)
    {
        $this->compress($dir, $out);
    }

    public function compress($source, $destination, $include_dir = true)
    {
        if (!extension_loaded('zip') || !file_exists($source)) {
            return false;
        }
        if (file_exists($destination)) {
            unlink($destination);
        }
        $ZipArchive = new ZipArchive();

        if (!$ZipArchive->open($destination, ZIPARCHIVE::CREATE)) {
            return false;
        }
        $source = str_replace('\\', '/', realpath($source));

        if (is_dir($source) === true) {
            $files = new RecursiveIteratorIterator(
                     new RecursiveDirectoryIterator($source),
                         RecursiveIteratorIterator::SELF_FIRST);

            if ($include_dir) {
                $arr     = explode('/',$source);
                $maindir = $arr[count($arr)- 1];
                $source  = '';
                for ($i=0; $i < count($arr) - 1; $i++) { 
                    $source .= '/' . $arr[$i];
                }
                $source = substr($source, 1);
                $ZipArchive->addEmptyDir($maindir);
            }

            foreach ($files as $file) {
                $file = str_replace('\\', '/', $file);

                // Ignore "." and ".." folders
                if(in_array(substr($file, strrpos($file, '/')+1), array('.', '..')))
                    continue;

                $file = realpath($file);

                if (is_dir($file) === true) {
                    $ZipArchive->addEmptyDir(
                        str_replace($source . '/', '', $file . '/')
                    );
                } else if (is_file($file) === true) {
                    $ZipArchive->addFromString(
                        str_replace($source . '/', '', $file),
                        file_get_contents($file)
                    );
                }
            }
        } else if (is_file($source) === true) {
            $ZipArchive->addFromString(basename($source),
                                       file_get_contents($source));
        }
        return $ZipArchive->close();
    }
}
