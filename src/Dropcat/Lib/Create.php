<?php
namespace Dropcat\Lib;

use Symfony\Component\Filesystem\Filesystem;
use phpseclib\Crypt\RSA;
use phpseclib\Net\SFTP;
use Exception;

/**
 * Class Upload
 *
 * Upload something somewhere.
 *
 * @package Dropcat\Lib
 */
class Create
{
    public $dir;
    public $fs;

    public function __construct()
    {
        $this->dir = getcwd();
        $this->fs = new Filesystem();
    }

    /**
     * Create a folder on a remote server.
     */
    public function folder($config)
    {
        $server = $config['server'];
        $port = $config['port'];
        $user = $config['user'];
        $timeout = $config['timeout'];
        $ssh_key_password = $config['pass'];
        $target = $config['target'];
        $identity_file = $config['key'];
        $identity_file_content = file_get_contents($identity_file);

        $sftp = new SFTP($server, $port, $timeout);
        $sftp->setTimeout(999);

        $auth = new RSA();
        if (isset($ssh_key_password)) {
            $auth->setPassword($ssh_key_password);
        }
        $auth->loadKey($identity_file_content);
        try {
            $login = $sftp->login($user, $auth);
            if (!$login) {
                throw new Exception('login Failed using ' . $identity_file . ' and user ' . $user . ' at ' . $server);
            }
            $mkdir = $sftp->mkdir($target);
            if(!$mkdir) {
                    throw new Exception('Could not create directory at ' . $target);
            }
        } catch (Exception $e) {
            echo $e->getMessage() . "\n";
            exit(1);
        }
    }
}
