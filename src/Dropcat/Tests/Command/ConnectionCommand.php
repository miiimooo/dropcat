<?php

namespace Dropcat\Tests\Command;

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

class ConnectionCommand extends Command {

  protected function configure()  {
    $server = '192.168.10.20';
    $user = 'vagrant';
    $port = '22';
    $identity_file = '/srv/www/wk_basebox/.vagrant/machines/default/virtualbox/private_key';

    $this->setName("tests:connection")
      ->setDescription("Deploying on remote server")
      ->setDefinition( array (
        new InputOption('server', 's', InputOption::VALUE_OPTIONAL, 'Server addreess', $server),
        new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User', $user),
        new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $port),
        new InputOption('identity_file', 'i', InputOption::VALUE_OPTIONAL, 'IdentityFile', $identity_file),
      ))
      ->setHelp('Test connection');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
      $server = $input->getOption('server');
      $user = $input->getOption('user');
      $port = $input->getOption('port');
      $identity_file = $input->getOption('identity_file');
      $identity_file_content = file_get_contents($identity_file);

      $ssh = new SSH2($server, $port);
      $auth = new RSA();
      $auth->loadKey($identity_file_content);

      if (!$ssh->login($user, $auth)) {
        exit('Login Failed');
      }

      $ssh->disconnect();

      $output->writeln('<info>Task: Test connection finished</info>');
    }
}

?>
