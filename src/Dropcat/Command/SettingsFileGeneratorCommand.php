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
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Filesystem\Filesystem;

class SettingsFileGeneratorCommand extends DropcatCommand
{
    /** @var  Filesystem */
    private $filesystem;

    public function __construct(
        ContainerBuilder $container,
        Configuration $conf
    ) {
        parent::__construct($container, $conf);
    }

    protected function configure()
    {
        $HelpText = '<info>Overrides settings.php settings.</info>';

        $this->setName("settingsfile-generator")
            ->setDescription("Overrides settings.php settings.")
            ->setHelp($HelpText);
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output            = new ConsoleOutput();
        $new_settings_file = $this->addCustomSettings();
        $output->writeln($new_settings_file);
    }

    /**
     * 1. Check if we have settings in yml
     * 2. check if we have settings in command line value
     * 3. parse the yml
     * 4. append to settings file ( should be in configs )
     */
    protected function addCustomSettings()
    {
        $custom_settings = $this->configuration->getCustomSettings();
        if ($custom_settings) {
            $this->filesystem = new Filesystem();
            // Make local settings file writable.
            $this->setLocalSettingsfilePermissions(0777);
            $local_settingsfile = $this->localSettingsFile();
            $this->filesystem->dumpFile($local_settingsfile, 'fooofaaaa');
            // Reset local settings file permissions to normal, not writable.
            $this->setLocalSettingsfilePermissions(0644);
        }
        return 'wrote something?';
    }

    protected function getLocalSettingsfileContent()
    {
        // $this->filesystem->
    }

    protected function writeToLocalSettingsfile()
    {

    }

    protected function setLocalSettingsfilePermissions($permissions = 0644)
    {
        $localSettingsFile = $this->localSettingsFile();
        $this->filesystem->chmod($localSettingsFile, $permissions);
    }

    protected function localSettingsFile()
    {
        $run_path          = getcwd();
        $app_path          = $this->configuration->localEnvironmentAppPath();
        $settings_filename = $run_path . '/web' . $app_path . '/sites/default/settings.local.php';
        return $settings_filename;
    }
}
