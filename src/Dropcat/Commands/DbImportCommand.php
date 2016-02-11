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

class DbImportCommand extends Command {

    protected function configure() {
      $path_to_db = '/mydb/backup/db.sql';
      $drush_alias = 'default';
      $backup_path = '/tmp';
      $identify_backup = "$drush_alias" . '_' . date("Ymd_His") . '.sql';
      $timeout = '3600';
      $this->setName("dropcat:dbimport")
        ->setDescription("Import db")
        ->setDefinition( array (
          new InputOption('path_to_db', 'p', InputOption::VALUE_OPTIONAL, 'Path to database', $path_to_db),
          new InputOption('drush_alias', 'd', InputOption::VALUE_OPTIONAL, 'Drush alias', $drush_alias),
          new InputOption('backup_path', 'b', InputOption::VALUE_OPTIONAL, 'Backup path', $backup_path),
          new InputOption('identify_backup', 'i', InputOption::VALUE_OPTIONAL, 'Identify backup', $identify_backup),
          new InputOption('timeout', 'o', InputOption::VALUE_OPTIONAL, 'Timeout', $timeout),
        ))
        ->setHelp('Import db task');
    }

    protected function execute(InputInterface $input, OutputInterface $output) {
        $drush_alias = $input->getOption('drush_alias');
        $path_to_db = $input->getOption('path_to_db');
        $backup_path = $input->getOption('backup_path');
        $identify_backup = $input->getOption('identify_backup');
        $timeout = $input->getOption('timeout');

        $process = new Process("drush @$drush_alias sql-dump > $identify_backup &&
        drush @$drush_alias sql-drop -y &&
        drush @$drush_alias sql-cli < $path_to_db");
        $process->setTimeout($timeout);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
          throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
        $output = new ConsoleOutput();
        $output->writeln('<info>Task: dropcat:dbimport finished</info>');
    }
}

?>
