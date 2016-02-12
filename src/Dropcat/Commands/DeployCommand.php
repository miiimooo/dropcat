<?php

namespace Dropcat\Commands;

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
      $zip = 'foo.zip';
      $server = 'localhost';
      $user = 'ubuntu';
      $target_path = 'foo';
      $port = '22';
      $alias = 'default';
      $web_root = '/var/www/webroot/';
      $temp_folder = '/tmp';

      $this->setName("dropcat:deploy")
           ->setDescription("Deploying on remote server")
           ->setDefinition( array (
             new InputOption('zip', 'z', InputOption::VALUE_OPTIONAL, 'Zip', $zip),
             new InputOption('server', 's', InputOption::VALUE_OPTIONAL, 'Server addreess', $server),
             new InputOption('user', 'u', InputOption::VALUE_OPTIONAL, 'User', $user),
             new InputOption('target_path', 't', InputOption::VALUE_OPTIONAL, 'Target path', $target_path),
             new InputOption('port', 'p', InputOption::VALUE_OPTIONAL, 'Port', $port),
             new InputOption('alias', 'a', InputOption::VALUE_OPTIONAL, 'Alias', $alias),
             new InputOption('web_root', 'w', InputOption::VALUE_OPTIONAL, 'Web root', $web_root),
             new InputOption('temp_folder', 'tf', InputOption::VALUE_OPTIONAL, 'Temp folder', $temp_folder),
           ))
           ->setHelp('Deploy');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $zip = $input->getOption('zip');
        $server = $input->getOption('server');
        $user = $input->getOption('user');
        $target_path = $input->getOption('target_path');
        $port = $input->getOption('port');
        $web_root = $input->getOption('web_root');
        $alias = $input->getOption('alias');
        $temp_folder = $input->getOption('temp_folder');

        $process = new Process("ssh -p $port $user@$server << EOF
        mkdir $temp_folder/$target_path
        mv $temp_folder/$zip $temp_folder/$target_path
        cd $temp_folder/$target_path
        unzip $zip
        rm *.zip
        cd ..
        mv $target_path $web_root
        cd $web_root
        rm $alias
        ln -s $target_path $alias
EOF");
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
    }
}

?>
