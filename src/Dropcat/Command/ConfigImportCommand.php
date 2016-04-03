<?php

namespace Dropcat\Command;

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

class ConfigImportCommand extends Command
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
        $HelpText = 'The <info>configimport</info> command import configuration to drupal site.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat configimport</info>
To override config in dropcat.yml, using options:
<info>dropcat configimport -d mysite -c myconfig</info>';

        $this->setName("config-import")
          ->setDescription("Configuration import")
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
                      'config_name',
                      'c',
                      InputOption::VALUE_OPTIONAL,
                      'Name of config to import',
                      $this->configuration->siteEnvironmentConfigName()
                  ),
              )
          )
          ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $drush_alias = $input->getOption('drush_alias');
        $config_name = $input->getOption('config_name');
        if ($output->isVerbose()) {
            echo 'using drush alias: ' . $drush_alias . "\n";
            echo 'using config: ' . $config_name . "\n";
        }
        if ($output->isVerbose()) {
            $process = new Process("drush @$drush_alias cim $config_name -y");
        } else {
            $process = new Process("drush @$drush_alias cim $config_name -q -y");
        }

        $process->run();
        // Executes after the command finishes.
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: configimport finished</info>');

    }
}
