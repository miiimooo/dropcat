<?php

namespace Wkse\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class RemoteDeployCommand extends Command {

    protected function configure()
    {
      $folder = 'foo.zip';
      $server = 'localhost';
      $user = 'ubuntu';
      $targetdir = '/tmp';
      $port = '22';
      $drush_alias = 'default'

      $this->setName("wkse:remotedeploy")
           ->setDescription("Deploying on remote server")
           ->setDefinition( array (
             new InputOption('folder', 'f', InputOption::VALUE_OPTIONAL, 'Folder', $folder),
             new InputOption('server', 's', InputOption::VALUE_OPTIONAL, 'Server addreess', $server),
             new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User', $user),
             new InputOption('targetdir', 't', InputOption::VALUE_OPTIONAL, 'Targetdir', $targetdir),
             new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $port),
             new InputOption('drush_alias', 'd', InputOption::VALUE_OPTIONAL, 'Drush alias', $drush_alias),
           ))
           ->setHelp('Remote Deploy');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $folder = $input->getOption('folder');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $targetdir = $input->getOption('targetdir');
        $port = $input->getOption('port');
        $drush_alias = $input->getOption('drush_alias');

        $process = new Process("ssh -p $port $user@$server");
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output->writeln("\n $folder uploaded to $server in directory $targetdir\n");
    }
}

?>
