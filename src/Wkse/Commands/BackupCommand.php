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

class BackupCommand extends Command {

    protected function configure()
    {
        $drush_alias = 'default';
        $timestamp = date("Ymd_His");
        $backup_folder = '/backup';
        $this->setName("wkse:backup")
             ->setDescription("Run backup task")
             ->setDefinition( array (
               new InputOption('drush_alias', 'd', InputOption::VALUE_OPTIONAL, 'Drush alias', $drush_alias),
               new InputOption('timestamp', 't', InputOption::VALUE_OPTIONAL, 'Timestamp', $timestamp),
               new InputOption('backup_folder', 'b', InputOption::VALUE_OPTIONAL, 'Backup folder', $backup_folder),
             ))
             ->setHelp('Backup task');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $drush_alias = $input->getOption('drush_alias');
        $timestamp = $input->getOption('timestamp');
        $backup_folder = $input->getOption('backup_folder');
        $process = new Process("drush @$drush_alias sql-dump > $backup_folder" . '/' . "$drush_alias" . '_' . "$timestamp.dmp");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: wkse:backup finished</info>');

    }
}

?>
