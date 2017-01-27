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

class SettingsOverrideCommand extends DropcatCommand
{
    protected function configure()
    {
        $HelpText = '<info>Overrides settings.php settings.</info>';

        $this->setName("settings-override")
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
    $app_path = $this->configuration->localEnvironmentAppPath();
    $predeploy_settings_file = $app_path .'/sites/default/settings.predeploy.php';

    $v = array();
// values to 'override'.
    $v['settings']['database']['default']['name'] = 'BEPPESNYADB';

// read file.
    $fh = fopen($predeploy_settings_file, 'w+');

// Using extra to put "first" level of array to a variable.
    $contents = '<?php' . "\nextract(";

// ... to get the OUT
    ob_start();

// dump variable to parseable PHP
    var_export($v);

    $contents .= ob_get_clean().'';
    $contents .= ');';

// finally write it!
    fwrite($fh, $contents);
    fclose($fh);

  }
}
