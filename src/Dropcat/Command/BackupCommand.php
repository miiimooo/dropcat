<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;

class BackupCommand extends Command
{
    /** @var Configuration configuration */
    private $configuration;

    protected function configure()
    {
        $HelpText = 'The <info>backup</info> command will create a backup of site db.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat backup</info>
To override config in dropcat.yml, using options:
<info>dropcat backup -d mysite -b /var/dump -t 20160101</info>';

        $this->configuration = new Configuration();
        $this->setName("backup")
            ->setDescription("Tar folder")
            ->setDefinition(
                array(
                    new InputOption(
                        'drush_alias',
                        'd',
                        InputOption::VALUE_OPTIONAL,
                        'Drush alias',
                        $this->configuration->siteEnvironmentDrushAlias()
                    ),
                    new InputOption(
                        'backup_path',
                        'b',
                        InputOption::VALUE_OPTIONAL,
                        'Backuo path',
                        $this->configuration->siteEnvironmentBackupPath()
                    ),
                    new InputOption(
                        'time_stamp',
                        't',
                        InputOption::VALUE_OPTIONAL,
                        'Time stamp',
                        $this->configuration->timeStamp()
                    ),
                )
            )
          ->setHelp($HelpText);

    }
    protected function execute(InputInterface $input, OutputInterface $output) {

      var_dump($this->setApplication( 'foo'));
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
      $output->writeln('<info>Task: backup finished</info>');
    }

}

?>
