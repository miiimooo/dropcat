<?php

namespace Dropcat\Command;

use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class CacheClearCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = 'The <info>%command.name%</info> command clear caches on a drupal site.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat cache-clear</info>
To override config in dropcat.yml, using options:
<info>dropcat cache-clear -d mysite</info>';

        $this->setName("cache-clear")
            ->setDescription("Clears cache (d6, d7)")
            ->setDefinition(
                array(
                    new InputOption(
                        'drush_alias',
                        'd',
                        InputOption::VALUE_OPTIONAL,
                        'Drush alias',
                        $this->configuration->siteEnvironmentDrushAlias()
                    ),
                )
            )
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush_alias = $input->getOption('drush_alias');
        if ($output->isVerbose()) {
            echo 'using drush alias: ' . $drush_alias . "\n";
        }
        $process = new Process("drush @$drush_alias cc all");
        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output->writeln('<info>' . $this->mark .
          'entity-update finished</info>');
    }
}
