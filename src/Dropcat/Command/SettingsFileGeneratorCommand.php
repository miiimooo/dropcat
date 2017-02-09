<?php

namespace Dropcat\Command;

use Dropcat\Services\Configuration;
use Dropcat\Lib\DropcatCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Style\SymfonyStyle;

class SettingsFileGeneratorCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = '<info>Overrides settings.php settings.</info>';

        $this->setName("settingsfile-generator")
            ->setDescription("Overrides settings.php settings.")
            ->setHelp($HelpText);
    }
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output = new ConsoleOutput();
        $new_settings_file = $this->addNewSettingsFile();
        $output->writeln($new_settings_file);
    }


  protected function addNewSettingsFile()
  {
    $settings_variables = array();
// values to 'override'.
    $settings_variables['settings']['database']['default']['name'] = 'BEPPESNYADBfoo';

// read file.
    $fh = fopen($this->settingsFileName(), 'w+');

// Using extra to put "first" level of array to a variable.
    $contents = '<?php' . "\n";

// ... to get the OUT
    ob_start();

// dump variable to parseable PHP
    var_export($settings_variables);

    $contents .= ob_get_clean().'';

// finally write it!
    fwrite($fh, $contents);
    fclose($fh);

  }

  protected function settingsFileName()
  {
    $app_path = $this->configuration->localEnvironmentAppPath();
    $settings_filename = $app_path .'/sites/default/settings.db.local.php';
    return $settings_filename;
  }
}
