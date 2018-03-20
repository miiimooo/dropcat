<?php
namespace Dropcat\Lib;

use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;

/**
 * Class Upload
 *
 * Upload something somewhere.
 *
 * @package Dropcat\Lib
 */
class Vhost
{
    public $dir;
    public $fs;

    public function __construct()
    {
        $this->dir = getcwd();
        $this->fs = new Filesystem();
        $this->output = new ConsoleOutput();
        $style = new Styles();
        $mark = $style->heavyCheckMark();
        $this->mark = $style->colorize('yellow', $mark);
    }

    /**
     * Create a folder on a remote server.
     */
    public function create($config)
    {
        $target = $config['target'];
        $file_name = $config['file-name'] . '.conf';
        $document_root = $config['document-root'];
        $vhost_port = $config['port'];
        $server_name = $config['server-name'];
        $server_alias = $config['server-alias'];
        $extra = $config['extra'];
        $bash_command = $config['bash-command'];
        $server = $config['server'];
        $user = $config['user'];
        $port = $config['ssh-port'];
        $ssh_key_password = $config['ssh-key-password'];
        $identity_file = $config['identity-file'];
        $identity_file_content = file_get_contents($identity_file);

        $runbash = '';
        if (isset($bash_command)) {
            $runbash = " && $bash_command";
        }
        if (isset($server_alias)) {
            $server_alias = "ServerAlias $server_alias\n";
        }
        if (isset($extra)) {
            $extra = "$extra\n";
        }
        $virtualHost ="<VirtualHost *:$vhost_port>\n" .
          "  DocumentRoot $document_root\n" .
          "  ServerName $server_name\n" .
          "$server_alias" .
          "$extra" .
          "</VirtualHost>\n";

        $aliasCreate= new Process(
            "ssh -o LogLevel=Error $user@$server -p $port \"echo '$virtualHost' > $target/$file_name $runbash\""
        );
        $aliasCreate->setTimeout(999);
        $aliasCreate->run();
        // executes after the command finishes
        if (!$aliasCreate->isSuccessful()) {
            throw new ProcessFailedException($aliasCreate);
        }

        echo $aliasCreate->getOutput();

        $this->output->writeln('<info>' . $this->mark . ' vhost created</info>');
    }
}
