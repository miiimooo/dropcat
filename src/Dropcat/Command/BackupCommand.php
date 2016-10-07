<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Services\Configuration;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class BackupCommand extends DropcatCommand
{

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
                    new InputOption(
                        'time_out',
                        'to',
                        InputOption::VALUE_OPTIONAL,
                        'Time out',
                        $this->configuration->timeOut()
                    ),
                    new InputOption(
                        'backup_site',
                        'bs',
                        InputOption::VALUE_NONE,
                        'Backup whole site',
                        null
                    ),
                    new InputOption(
                        'links',
                        'l',
                        InputOption::VALUE_NONE,
                        'Keep symlinks',
                        null
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
        $timeout          = $input->getOption('time_out');
        $backup_site      = $input->getOption('backup_site');
        $links            = $input->getOption('links');


        // Remove '@' if the alias beginns with it.
        $drush_alias = preg_replace('/^@/', '', $drush_alias);

        $backupDb= $this->runProcess(
            "mkdir -p $backup_path/$drush_alias &&
            drush @$drush_alias sql-dump > $backup_path/$drush_alias/$timestamp.sql"
        );
        $backupDb->setTimeout($timeout);
        $backupDb->run();
        // executes after the command finishes
        if (!$backupDb->isSuccessful()) {
            throw new ProcessFailedException($backupDb);
        }

        echo $backupDb->getOutput();
        $output = new ConsoleOutput();
        $output->writeln('<info>Successfully backed up db</info>');

        if ($backup_site === true) {
            $options = '';
            if ($links === true) {
                $options = '--links ';
            }

            $backupSite = $this->runProcess(
                "mkdir -p $backup_path/$drush_alias &&
                drush -y rsync @$drush_alias $backup_path/$drush_alias/$timestamp/ $options --include-conf --include-vcs"
            );
            $backupSite->setTimeout($timeout);
            $backupSite->run();
            // executes after the command finishes
            if (!$backupSite->isSuccessful()) {
                throw new ProcessFailedException($backupSite);
            }
            $output->writeln('<info>Successfully backed up site</info>');
            echo $backupSite->getOutput();
        }
        $output = new ConsoleOutput();
        $output->writeln('<info>Task: backup finished</info>');
    }
}
