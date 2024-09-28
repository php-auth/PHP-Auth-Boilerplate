<?php
/**
 * Tar
 * -----------------------------------------------------------------------------
 * https://github.com/koszik/php-tar/blob/master/create-tar.php
 * based on https://github.com/splitbrain/php-archive/blob/master/src/Tar.php
 * -----------------------------------------------------------------------------
 *
 * use App\Lib\Tar;
 * $Tar = new Tar;
 * $Tar->generate('/path/to/folder', '/path/to/file.tar');
 *
 * @version 1.0.0
 */
namespace App\Lib;

define('S_IFLNK', 0120000);
define('S_IFREG', 0100000);
define('S_IFBLK', 0060000);
define('S_IFDIR', 0040000);
define('S_IFCHR', 0020000);
define('S_IFLLL', 0770000);
define('S_IFMT',  0770000);

function isdir($stat) {
    return ($stat['mode'] & S_IFMT) == S_IFDIR;
}
function islink($stat) {
    return ($stat['mode'] & S_IFMT) == S_IFLNK;
}
function isreg($stat) {
    return ($stat['mode'] & S_IFMT) == S_IFREG;
}

class Tar
{
    public function generate($dir, $out)
    {
        chdir($dir);
        chdir('../');
        $arr = explode('/', $dir);
        $dir = end($arr);
        $this->create($out);
        $this->recursiveAdd($dir);
    }

    protected function writebytes($data)
    {
        return fwrite($this->fh, $data);
    }

    public function addFile($file, $stat)
    {
	    if($stat['dev'] . '-' . $stat['ino'] == $this->dev_inode) {
	        printf("ignoring output $file\n");
	        return;
	    }
	    $target = islink($stat)? readlink($file) : '';
	    if(strlen($target) > 99) {
	        print "link target too long for $file -> $target\n";
	        return;
	    }
        $this->writeFileHeader($file, $stat, $target);
	    if(!isreg($stat))
	        return;

        if(!($fp = fopen($file, 'rb')))
    	    return;

        $read = 0;
        while(!feof($fp)) {
            $data = fread($fp, 512);
            if($data === false || $data === '') {
                break;
            }
            $read  += strlen($data);
            $packed = pack('a512', $data);
            $this->writebytes($packed);
        }
        fclose($fp);
        if($read != $stat['size']) {
            print("The size of $file changed while reading, archive " .
                  "corrupted. read $read expected " . $stat['size'] . "\n");
        }
    }

    public function create($file = '')
    {
        if(!($this->fh = fopen($file, 'wb')))
            return;
        $stat = fstat($this->fh);
        $this->dev_inode = $stat['dev'] . '-' . $stat['ino'];
    }

    protected function writeFileHeader($name, $stat, $linkname = '')
    {
	    switch($stat['mode'] & S_IFMT) {
	        case S_IFREG: $typeflag = '0'; break;
	        case S_IFLNK: $typeflag = '2'; break;
	        case S_IFCHR: $typeflag = '3'; break;
	        case S_IFBLK: $typeflag = '4'; break;
	        case S_IFDIR: $typeflag = '5'; break;
	        case S_IFLLL: $typeflag = 'L'; break;
	        default: $typeflag = '0';
            printf("unknown file mode for $name: %o", $stat['mode']);
	    }
	    if($name[0] == '/') $name = ".$name";

        $prefix  = '';
        $namelen = strlen($name);
        if ($namelen > 100) {
            $file = basename($name);
            $dir  = dirname($name);
            if (strlen($file) > 100 || strlen($dir) > 155) {
                // we're still too large, let's use GNU longlink
                $this->writeFileHeader('././@LongLink', ['uid'   => 0,
                                                         'gid'   => 0,
                                                         'perm'  => S_IFLLL,
                                                         'size'  => $namelen,
                                                         'mtime' => 0]);
                for ($s = 0; $s < $namelen; $s += 512) {
                    $this->writebytes(pack("a512", substr($name, $s, 512)));
                }
                $name = substr($name, 0, 100); // cut off name
            } else {
                // we're fine when splitting, use POSIX ustar
                $prefix = $dir;
                $name   = $file;
            }
        }
        $uid   = sprintf("%06o ", $stat['uid']);
        $gid   = sprintf("%06o ", $stat['gid']);
        $perm  = sprintf("%06o ", $stat['mode'] & 0777);
        $size  = sprintf("%011o ", isreg($stat)? $stat['size'] : 0);
        $mtime = sprintf("%011o ", $stat['mtime']);
        $data  = pack("a100a8a8a8a12A12",
                $name, $perm, $uid, $gid, $size, $mtime) . "        " .
                pack("a1a100a6a2a32a32a8a8a155a12", $typeflag, $linkname,
                     'ustar', '00', '', '',
                     sprintf("%06o ", $stat['rdev'] >> 8),
                     sprintf("%06o ", $stat['rdev'] & 255), $prefix, "");
        for ($i = 0, $chks = 0; $i < 512; $i++)
            $chks += ord($data[$i]);
        $data = substr_replace($data,
                               pack("a8", sprintf("%6o ", $chks)), 148, 8);
        $this->writebytes($data);
    }

    public function recursiveAdd($directory)
    {
        for ($handle = opendir($directory); false !==
            ($entry  = readdir($handle));) {
            if($entry == '.' || $entry == '..') {
                continue;
            }
            $Entry = $directory . DIRECTORY_SEPARATOR  . $entry;
            //print $Entry."\n";
            $stat = lstat($Entry);
            $this->addFile($Entry, $stat);
            if(isdir($stat)) $this->recursiveAdd($Entry);
        }
        closedir($handle);
    }
}
