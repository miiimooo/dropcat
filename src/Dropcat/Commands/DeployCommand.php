<?php

namespace Dropcat\Commands;

use phpseclib\Crypt\RSA;
use phpseclib\Net\SSH2;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class DeployCommand extends Command {

    protected function configure()
    {
      $zip = 'dropcat.zip';
      $server = '192.168.10.20';
      $user = 'vagrant';
      $target_path = 'foo';
      $port = '22';
      $identity_file = '/srv/www/wk_basebox/.vagrant/machines/default/virtualbox/private_key';
      $alias = 'default';
      $web_root = '/home/vagrant/dropcat/';
      $temp_folder = '/tmp';
      $debug = false;

      $this->setName("deploy")
           ->setDescription("Deploying on remote server")
           ->setDefinition( array (
             new InputOption('zip', 'z', InputOption::VALUE_OPTIONAL, 'Zip', $zip),
             new InputOption('server', 's', InputOption::VALUE_OPTIONAL, 'Server addreess', $server),
             new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User', $user),
             new InputOption('target_path', 't', InputOption::VALUE_OPTIONAL, 'Target path', $target_path),
             new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $port),
             new InputOption('identity_file', 'i', InputOption::VALUE_OPTIONAL, 'IdentityFile', $identity_file),
             new InputOption('alias', 'a', InputOption::VALUE_OPTIONAL, 'Alias', $alias),
             new InputOption('web_root', 'w', InputOption::VALUE_OPTIONAL, 'Web root', $web_root),
             new InputOption('temp_folder', 'tf', InputOption::VALUE_OPTIONAL, 'Temp folder', $temp_folder),
             new InputOption('debug', 'd', InputOption::VALUE_OPTIONAL, 'Debug', $debug),

           ))

           ->setHelp('Deploy');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
      $zip = $input->getOption('zip');
      $server = $input->getOption('server');
      $user = $input->getOption('user');
      $target_path = $input->getOption('target_path');
      $port = $input->getOption('port');
      $identity_file = $input->getOption('identity_file');
      $identity_file_content = file_get_contents($identity_file);
      $web_root = $input->getOption('web_root');
      $alias = $input->getOption('alias');
      $temp_folder = $input->getOption('temp_folder');
      $debug = $input->getOption('debug');

      $ssh = new SSH2($server, $port);
      $auth = new RSA();
      $auth->loadKey($identity_file_content);

      if (!$ssh->login($user, $auth)) {
        exit('Login Failed');
      }

      // Debug mode does not delete anything and does not create the symlink to build
      if ($debug == true) {
        $ssh->exec('mkdir '. $temp_folder .'/'. $target_path);
        $ssh->exec('cp '. $temp_folder .'/'. $zip .' '- $temp_folder .'/'. $target_path);
        $ssh->exec('cd '. $temp_folder .'/'. $target_path);
        $ssh->exec('unzip '. $zip);
        $ssh->exec('cd ..');
        $ssh->exec('cp '. $target_path .' '. $web_root);
      }
      else {
        $ssh->exec('mkdir '. $temp_folder .'/'. $target_path);
        $ssh->exec('mv '. $temp_folder .'/'. $zip .' '. $temp_folder .'/'. $target_path);
        $ssh->exec('cd '. $temp_folder .'/'. $target_path);
        $ssh->exec('unzip '. $zip);
        $ssh->exec('rm *.zip');
        $ssh->exec('cd ..');
        $ssh->exec('mv '. $target_path .' '. $web_root);
        $ssh->exec('cd '. $web_root);
        $ssh->exec('rm '. $alias);
        $ssh->exec('ln -s '. $target_path .' '. $alias);
      }

      $ssh->disconnect();

      $output->writeln('<info>Task: deploy finished</info>');
    }
}

?>
