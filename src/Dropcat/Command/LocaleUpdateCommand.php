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

class LocaleUpdateCommand extends Command
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
    $HelpText = 'The <info>locale-update</info> command updates translations on a drupal site.
<comment>Samples:</comment>
To run with default options (using config from dropcat.yml in the currrent dir):
<info>dropcat locale-update</info>
To override config in dropcat.yml, using options:
<info>dropcat locale-update -d mysite</info>';

    $this->setName("locale-update")
      ->setDescription("Update translations")
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
    $process = new Process("drush @$drush_alias locale-update -y");
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
