<?php
namespace Dropcat\Lib;

use Symfony\Component\Filesystem\Filesystem;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use Exception;

/**
 * Class Name
 *
 * Create a name from something to something.
 *
 * @package Dropcat\Lib
 */
class Name
{

    /**
     * Create a folder on a remote server.
     */
    static function site($name)
    {
        $cleaned_string = str_replace(".", "", $name);
        $site_name = mb_strimwidth($cleaned_string, 0, 59);
        $new_site_name = preg_replace("#[^A-Za-z0-9]#", "_", $site_name);
        return $new_site_name;
    }
}
