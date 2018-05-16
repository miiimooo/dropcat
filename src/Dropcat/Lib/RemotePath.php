<?php
namespace Dropcat\Lib;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Console\Output\ConsoleOutput;
use Exception;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;

/**
 * Class Remote
 *
 * Check a remote path
 *
 * @package Dropcat\Lib
 */
class RemotePath
{
    public $fs;
    public $mark;
    public $verbose;

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        $this->fs = new Filesystem();
        $this->output = new ConsoleOutput();
        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $this->mark = $style->colorize('yellow', $mark);
    }

    public function siteRealPath($conf)
    {
        $alias = $conf['alias'];
        $web_root = $conf['web-root'];
        $remote_path = "$web_root/$alias";
        $server = $conf['server'];
        $user = $conf['user'];
        $port = $conf['port'];
        $key = $conf['key'];
        $identity_file_content = file_get_contents($key);
        $pass = $conf['pass'];

        $ssh = new SSH2($server, $port);
        $ssh->setTimeout(999);
        $auth = new RSA();
        if (isset($pass)) {
            $auth->setPassword($pass);
        }
        $auth->loadKey($identity_file_content);

        try {
            $login = $ssh->login($user, $auth);
            if (!$login) {
                throw new Exception('Login Failed using ' . $key . ' at port '
                  . $port . ' and user ' . $user . ' at ' . $server
                  . ' ' . $ssh->getLastError());
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            exit(1);
        }

        $get_real_path = $ssh->exec("readlink -f $remote_path");
        $path = str_replace(array("\r", "\n"), '', trim($get_real_path));
        //$basename = $ssh->exec("basename $get_real_path");

        $status = $ssh->getExitStatus();
        if ($status !== 0) {
            echo "Could not get path, error code $status\n";
            $ssh->disconnect();
            exit($status);
        }
        $ssh->disconnect();
        return $path;
    }
}
