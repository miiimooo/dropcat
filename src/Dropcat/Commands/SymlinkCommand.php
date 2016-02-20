<?php

namespace Dropcat\Commands;

use phpseclib\Net\SSH2;
use phpseclib\Crypt\RSA;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class SymlinkCommand extends Command {

    protected function configure() {
      $original = '/my/path';
      $target = '/my/target';
      $server = 'localhost';
      $user = 'ubuntu';
      $port = '22';
      $identity_file = '/srv/www/wk_basebox/.vagrant/machines/default/virtualbox/private_key';

      $this->setName("dropcat:symlink")
        ->setDescription("Create symlink")
        ->setDefinition( array (
          new InputOption('original', 'o', InputOption::VALUE_OPTIONAL, 'Original', $original),
          new InputOption('target', 't', InputOption::VALUE_OPTIONAL, 'Target', $target),
          new InputOption('server', 's', InputOption::VALUE_OPTIONAL, 'Server addreess', $server),
          new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User', $user),
          new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $port),
          new InputOption('identity_file', 'i', InputOption::VALUE_OPTIONAL, 'IdentityFile', $identity_file),
        ))
        ->setHelp('Create symlink');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
      $original = $input->getOption('original');
      $target = $input->getOption('target');
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

      $ssh->exec('rm '. $target .' 2> /dev/null');
      $ssh->exec('ln -s '. $original .' '. $target);

      $output->writeln('<info>Task: dropcat:symlink finished</info>');
    }
}

?>
