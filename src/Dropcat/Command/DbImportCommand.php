<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
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

class DbImportCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>dbimport</info> command will import.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat dbimport</info>
To override config in dropcat.yml, using options:
<info>dropcat dbimport -d mysite -i /var/dump -t 120</info>';

        $this->setName("db-import")
            ->setDescription("Import DB to site")
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
                        'db_import',
                        'i',
                        InputOption::VALUE_OPTIONAL,
                        'Backup path',
                        $this->configuration->localEnvironmentDbImport()
                    ),
                    new InputOption(
                        'time_out',
                        'to',
                        InputOption::VALUE_OPTIONAL,
                        'Time out',
                        $this->configuration->timeOut()
                    ),
                )
            )
          ->setHelp($HelpText);

    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush_alias      = $input->getOption('drush_alias');
        $path_to_db       = $input->getOption('db_import');
        $timeout          = $input->getOption('time_out');
        $appname          = $this->configuration->localEnvironmentAppName();
        $db_dump          = "/tmp/$appname-db.sql";

        // Remove '@' if the alias beginns with it.
        $drush_alias = preg_replace('/^@/', '', $drush_alias);

        if (file_exists($path_to_db)) {
            if ($output->isVerbose()) {
                echo "Db exists at $path_to_db \n";
            }
            $file_type = pathinfo($path_to_db);
            switch($file_type['extension'])
            {
                case "gz":
                    if ($output->isVerbose()) {
                        echo "Filetype is gz \n";
                    }
                    $process = new Process(
                      "gunzip $path_to_db --force -c > $db_dump"
                    );
                    $process->setTimeout($timeout);
                    $process->run();
                    if (!$process->isSuccessful()) {
                        throw new ProcessFailedException($process);
                        $this->exitCommand(1);
                    }
                    echo $process->getOutput();
                    $output->writeln("gzipped db dump written to $db_dump");
                    break;
                default: // Handle no file extension
                    echo "only gzip (.gz) is supported for now";
                    $this->exitCommand(1);
                    break;
            }
        } else {
            if ($output->isVerbose()) {
                echo "Db does not exist at $path_to_db \n";
                $this->exitCommand(1);
            }
        }
        $process =  $this->runProcess(
            "drush @$drush_alias sql-drop -y &&
            drush @$drush_alias sql-cli < $db_dump"
        );
        $process->setTimeout($timeout);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();
        $output->writeln('<info>Task: dbimport finished</info>');
    }
}
