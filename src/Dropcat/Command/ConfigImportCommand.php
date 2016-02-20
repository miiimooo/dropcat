<?php

namespace Dropcat\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class ConfigImportCommand extends Command {

    protected function configure()
    {
        $drush_alias = 'default';
        $config_name = 'staging';

        $this->setName("configimport")
             ->setDescription("Run config import task")
             ->setDefinition( array (
               new InputOption('drush_alias', 'd', InputOption::VALUE_OPTIONAL, 'Drush alias', $drush_alias),
               new InputOption('config_name', 'c', InputOption::VALUE_OPTIONAL, 'Config name', $config_name),
             ))
             ->setHelp('Config import task');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $drush_alias = $input->getOption('drush_alias');
        $config_name = $input->getOption('config_name');
        $process = new Process("drush @$drush_alias cim $config_name -y");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: configimport finished</info>');

    }
}

?>
