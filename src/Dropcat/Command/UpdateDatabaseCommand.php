<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Dropcat\Lib\CheckDrupal;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Formatter\OutputFormatter;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class UpdateDatabaseCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>update-database</info> command updates db if needed.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat update-database</info>
To override config in dropcat.yml, using options:
<info>dropcat update-database -d mysite</info>';

        $this->setName("update-database")
            ->setDescription("Updates database")
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
                        'no-entity-update',
                        'noe',
                        InputOption::VALUE_NONE,
                        'Do not run entity-updates'
                    ),
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush_alias = $input->getOption('drush_alias');
        $no_entity_update = $input->getOption('no-entity-update') ? 'TRUE' : 'FALSE';
        // Some environment dependent extras.
        $extra = '';
        if ($output->isVerbose()) {
            echo 'using drush alias: ' . $drush_alias . "\n";
        }
        if ($no_entity_update === 'FALSE') {
            // check if we should use entity-updates - default to yes on d8
            $check = new CheckDrupal();
            if ($check->version() === '8') {
                $extra .= ' --entity-updates';
            }
        }
        $process = new Process("drush @$drush_alias updb -y $extra");
        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: entity-update finished</info>');
    }
}
