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

        $this->setName("configimport")
          ->setDescription("Configuration import")
          ->setDefinition(
              array(
                  new InputOption(
                      'drush_alias',
                      'd',
                      InputOption::VALUE_OPTIONAL,
                      'Folder',
                      $this->configuration->siteEnvironmentDrushAlias()
                  ),
                  new InputOption(
                      'config_name',
                      'c',
                      InputOption::VALUE_OPTIONAL,
                      'Id',
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
        $process = new Process("drush @$drush_alias cim $config_name -y");
        $process->run();
        // executes after the command finishes
        if (!$process->isSuccessful()) {

            /** @var \PEAR_Error $error_object */
            $error_object = $process->error_object;
            $exceptionMessage = sprintf(
                "Unable to import config, Error message:\n%s\n\n",
                $error_object->message
            );
            throw new \RuntimeException($exceptionMessage, $error_object->code);
        }
        echo $process->getOutput();

        $output = new ConsoleOutput();
        $output->writeln('<info>Task: configimport finished</info>');

    }
}
