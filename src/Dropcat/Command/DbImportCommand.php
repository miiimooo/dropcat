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

class DbImportCommand extends Command
{
    /** @var Configuration configuration */
    private $configuration;
    protected function configure()
    {
        $HelpText = 'The <info>dbimport</info> command will import.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat dbimport</info>
To override config in dropcat.yml, using options:
<info>dropcat dbimport -d mysite -i /var/dump -t 120</info>';

        $this->configuration = new Configuration();
        $this->setName("dbimport")
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
                        'db_import',
                        'i',
                        InputOption::VALUE_OPTIONAL,
                        'Backup path',
                        $this->configuration->localEnvironmentDbImport()
                    ),
                    new InputOption(
                        'time_out',
                        't',
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
        $drush_alias = $input->getOption('drush_alias');
        $path_to_db = $input->getOption('db_import');
        $timeout = $input->getOption('time_out');

        // Remove '@' if the alias beginns with it.
        $drush_alias = preg_replace('/^@/', '', $drush_alias);

        $process = new Process(
            "drush @$drush_alias sql-drop -y &&
            drush @$drush_alias sql-cli < $path_to_db"
        );
        $process->setTimeout($timeout);
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {
            /** @var \PEAR_Error $error_object */
            $error_object = $process->error_object;
            $exceptionMessage = sprintf(
                "Unable to import db, Error message:\n%s\n\n",
                $error_object->message
            );
            throw new \RuntimeException($exceptionMessage, $error_object->code);
        }
        echo $process->getOutput();
        $output = new ConsoleOutput();
        $output->writeln('<info>Task: dbimport finished</info>');
    }
}
