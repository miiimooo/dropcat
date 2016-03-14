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

    public function __construct(Configuration $conf)
    {
        $this->configuration = $conf;
        parent::__construct();
    }

    protected function configure()
    {
        $HelpText = 'The <info>backup</info> command will create a backup of site db.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat backup</info>
To override config in dropcat.yml, using options:
<info>dropcat backup -d mysite -b /var/dump -t 20160101</info>';

        $this->setName("backup")
            ->setDescription("Backup site")
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
                        'Backup path',
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

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush_alias      = $input->getOption('drush_alias');
        $timestamp        = $input->getOption('time_stamp');
        $backup_path      = $input->getOption('backup_path');

        // Remove '@' if the alias beginns with it.
        $drush_alias = preg_replace('/^@/', '', $drush_alias);

        $process = new Process(
            "drush @$drush_alias sql-dump > $backup_path" . '/' . "$drush_alias" . '_' . "$timestamp.sql"
        );
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            /** @var \PEAR_Error $error_object */
            $error_object = $process->error_object;
            $exceptionMessage = sprintf(
                "Unable to make backup, Error message:\n%s\n\n",
                $error_object->message
            );
            throw new \RuntimeException($exceptionMessage, $error_object->code);
        }

        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: backup finished</info>');
    }
}
